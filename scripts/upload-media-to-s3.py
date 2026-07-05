#!/usr/bin/env python3
"""Uploader resiliente de uploads WordPress para S3.

Evita os erros de IncompleteBody observados no AWS CLI do macOS ao enviar
arquivo por arquivo com boto3, com retry, resume e verificacao de tamanho.
"""

from __future__ import annotations

import argparse
import mimetypes
import os
import sys
import time
from pathlib import Path

import boto3
from boto3.s3.transfer import TransferConfig
from botocore.config import Config
from botocore.exceptions import ClientError, BotoCoreError


def parse_args() -> argparse.Namespace:
    parser = argparse.ArgumentParser(description="Upload confiavel de midias para S3")
    parser.add_argument("--root", required=True, help="Diretorio raiz dos uploads")
    parser.add_argument("--bucket", required=True, help="Bucket S3")
    parser.add_argument("--prefix", default="uploads", help="Prefixo no bucket")
    parser.add_argument("--profile", default="a12-dev", help="Perfil AWS CLI")
    parser.add_argument("--region", default="sa-east-1", help="Regiao AWS")
    parser.add_argument("--state-file", default="/tmp/a12_s3_media_state.tsv", help="Arquivo de progresso")
    parser.add_argument("--failed-file", default="/tmp/a12_s3_media_failed.tsv", help="Arquivo de falhas")
    parser.add_argument("--max-files", type=int, default=0, help="Limite para teste")
    parser.add_argument("--start-subdir", default="", help="Processa apenas um subdiretorio relativo")
    return parser.parse_args()


def load_done(state_file: Path) -> set[str]:
    if not state_file.exists():
        return set()
    done: set[str] = set()
    with state_file.open("r", encoding="utf-8") as handle:
        for line in handle:
            line = line.strip()
            if not line:
                continue
            done.add(line.split("\t", 1)[0])
    return done


def append_line(path: Path, line: str) -> None:
    path.parent.mkdir(parents=True, exist_ok=True)
    with path.open("a", encoding="utf-8") as handle:
        handle.write(line + "\n")


def iter_files(root: Path, start_subdir: str) -> list[Path]:
    base = root / start_subdir if start_subdir else root
    files: list[Path] = []
    for current_root, _, filenames in os.walk(base):
        for filename in filenames:
            if filename == ".DS_Store":
                continue
            files.append(Path(current_root) / filename)
    files.sort()
    return files


def s3_key(prefix: str, root: Path, file_path: Path) -> str:
    rel = file_path.relative_to(root).as_posix()
    return f"{prefix.rstrip('/')}/{rel}"


def remote_matches(s3_client, bucket: str, key: str, local_size: int) -> bool:
    try:
        response = s3_client.head_object(Bucket=bucket, Key=key)
    except ClientError as exc:
        code = exc.response.get("Error", {}).get("Code", "")
        if code in {"404", "NoSuchKey", "NotFound"}:
            return False
        raise
    return int(response.get("ContentLength", -1)) == local_size


def upload_one(s3_client, bucket: str, key: str, file_path: Path, transfer_config: TransferConfig) -> None:
    content_type, _ = mimetypes.guess_type(str(file_path))
    extra_args = {}
    if content_type:
        extra_args["ContentType"] = content_type
    s3_client.upload_file(
        str(file_path),
        bucket,
        key,
        ExtraArgs=extra_args or None,
        Config=transfer_config,
    )


def main() -> int:
    args = parse_args()
    root = Path(args.root).resolve()
    if not root.exists():
        print(f"ERRO: diretorio nao existe: {root}", file=sys.stderr)
        return 2

    session = boto3.Session(profile_name=args.profile, region_name=args.region)
    s3_client = session.client(
        "s3",
        config=Config(
            region_name=args.region,
            retries={"max_attempts": 10, "mode": "standard"},
            connect_timeout=30,
            read_timeout=300,
            max_pool_connections=4,
        ),
    )
    transfer_config = TransferConfig(
        multipart_threshold=64 * 1024 * 1024,
        multipart_chunksize=8 * 1024 * 1024,
        max_concurrency=1,
        use_threads=False,
    )

    state_file = Path(args.state_file)
    failed_file = Path(args.failed_file)
    done = load_done(state_file)
    files = iter_files(root, args.start_subdir)
    if args.max_files > 0:
        files = files[: args.max_files]

    total = len(files)
    uploaded = 0
    skipped = 0
    failed = 0
    started = time.time()

    print(f"Iniciando upload: {total} arquivo(s) | root={root} | bucket={args.bucket}/{args.prefix}")
    for index, file_path in enumerate(files, start=1):
        key = s3_key(args.prefix, root, file_path)
        size = file_path.stat().st_size

        if key in done:
            skipped += 1
            continue

        try:
            if remote_matches(s3_client, args.bucket, key, size):
                append_line(state_file, f"{key}\tSKIP_REMOTE_OK\t{size}")
                skipped += 1
                done.add(key)
            else:
                last_error = None
                for attempt in range(1, 6):
                    try:
                        upload_one(s3_client, args.bucket, key, file_path, transfer_config)
                        append_line(state_file, f"{key}\tUPLOADED\t{size}")
                        uploaded += 1
                        done.add(key)
                        last_error = None
                        break
                    except (ClientError, BotoCoreError, OSError) as exc:
                        last_error = exc
                        wait_seconds = min(30, attempt * 2)
                        print(f"retry {attempt}/5: {key} | erro={exc}")
                        time.sleep(wait_seconds)
                if last_error is not None:
                    failed += 1
                    append_line(failed_file, f"{key}\t{file_path}\t{last_error}")
                    print(f"FALHOU: {key} | {last_error}", file=sys.stderr)
        except (ClientError, BotoCoreError, OSError) as exc:
            failed += 1
            append_line(failed_file, f"{key}\t{file_path}\t{exc}")
            print(f"FALHOU: {key} | {exc}", file=sys.stderr)

        if index % 100 == 0 or index == total:
            elapsed = time.time() - started
            print(
                f"progresso {index}/{total} | uploaded={uploaded} | skipped={skipped} | failed={failed} | elapsed={elapsed:.0f}s"
            )

    print(
        f"FINAL | total={total} | uploaded={uploaded} | skipped={skipped} | failed={failed} | state={state_file} | failed_log={failed_file}"
    )
    return 1 if failed else 0


if __name__ == "__main__":
    raise SystemExit(main())