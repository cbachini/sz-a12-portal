#!/usr/bin/env python3
"""Uploader paralelo de mídias WordPress para S3 com UI rica.

Uso:
    python3 scripts/upload-parallel.py \
        --root wp-content/uploads \
        --bucket a12-dev-uploads \
        --prefix uploads \
        --profile a12-dev \
        --region sa-east-1 \
        --workers 32
"""
from __future__ import annotations

import argparse
import mimetypes
import os
import sys
import threading
import time
from pathlib import Path
from typing import NamedTuple

import boto3
from botocore.config import Config
from botocore.exceptions import BotoCoreError, ClientError
from rich.columns import Columns
from rich.console import Console
from rich.live import Live
from rich.panel import Panel
from rich.progress import (
    BarColumn,
    MofNCompleteColumn,
    Progress,
    SpinnerColumn,
    TaskProgressColumn,
    TextColumn,
    TimeElapsedColumn,
    TimeRemainingColumn,
)
from rich.table import Table
from rich.text import Text
import concurrent.futures


# ─── args ────────────────────────────────────────────────────────────────────

def parse_args() -> argparse.Namespace:
    p = argparse.ArgumentParser(description="Upload paralelo de mídias para S3")
    p.add_argument("--root",       required=True,  help="Diretório raiz dos uploads")
    p.add_argument("--bucket",     required=True,  help="Bucket S3")
    p.add_argument("--prefix",     default="uploads")
    p.add_argument("--profile",    default="a12-dev")
    p.add_argument("--region",     default="sa-east-1")
    p.add_argument("--workers",    type=int, default=32)
    p.add_argument("--state-file", default="/tmp/a12_s3_media_state.tsv")
    p.add_argument("--failed-file",default="/tmp/a12_s3_media_failed.tsv")
    p.add_argument("--log-file",   default="/tmp/a12_parallel_upload.log")
    p.add_argument("--max-files",  type=int, default=0, help="Limite para testes (0=tudo)")
    return p.parse_args()


# ─── state ───────────────────────────────────────────────────────────────────

class Stats(NamedTuple):
    uploaded: int
    skipped:  int
    failed:   int
    in_flight: int


class SharedState:
    def __init__(self, state_file: Path, failed_file: Path, log_file: Path):
        self._lock       = threading.Lock()
        self._done: set[str] = set()
        self.state_file  = state_file
        self.failed_file = failed_file
        self.log_file    = log_file
        self.uploaded    = 0
        self.skipped     = 0
        self.failed      = 0
        self.in_flight   = 0
        self.recent: list[str] = []

        # Carregar progresso anterior
        if state_file.exists():
            with state_file.open("r", encoding="utf-8") as fh:
                for line in fh:
                    line = line.strip()
                    if line:
                        self._done.add(line.split("\t", 1)[0])
            self.skipped = len(self._done)  # pré-população

    def is_done(self, key: str) -> bool:
        with self._lock:
            return key in self._done

    def mark_uploaded(self, key: str, size: int) -> None:
        with self._lock:
            self._done.add(key)
            self.uploaded += 1
            msg = f"[UP] {key}"
            self.recent = (self.recent + [msg])[-5:]
            with self.state_file.open("a", encoding="utf-8") as fh:
                fh.write(f"{key}\tUPLOADED\t{size}\n")
            with self.log_file.open("a", encoding="utf-8") as fh:
                fh.write(f"{time.strftime('%H:%M:%S')} UPLOADED {key}\n")

    def mark_skipped(self, key: str, size: int) -> None:
        with self._lock:
            self._done.add(key)
            self.skipped += 1
            with self.state_file.open("a", encoding="utf-8") as fh:
                fh.write(f"{key}\tSKIP\t{size}\n")

    def mark_failed(self, key: str, path: Path, err: Exception) -> None:
        with self._lock:
            self.failed += 1
            msg = f"[ERR] {key}: {err}"
            self.recent = (self.recent + [msg])[-5:]
            with self.failed_file.open("a", encoding="utf-8") as fh:
                fh.write(f"{key}\t{path}\t{err}\n")
            with self.log_file.open("a", encoding="utf-8") as fh:
                fh.write(f"{time.strftime('%H:%M:%S')} FAILED {key} | {err}\n")

    def enter_flight(self)  -> None:
        with self._lock:
            self.in_flight += 1

    def leave_flight(self) -> None:
        with self._lock:
            self.in_flight -= 1

    def snapshot(self) -> Stats:
        with self._lock:
            return Stats(self.uploaded, self.skipped, self.failed, self.in_flight)


# ─── S3 helpers ──────────────────────────────────────────────────────────────

def make_client(profile: str, region: str):
    session = boto3.Session(profile_name=profile, region_name=region)
    return session.client(
        "s3",
        config=Config(
            retries={"max_attempts": 5, "mode": "standard"},
            connect_timeout=15,
            read_timeout=90,
            max_pool_connections=64,
        ),
    )


def s3_key(prefix: str, root: Path, file_path: Path) -> str:
    return f"{prefix.rstrip('/')}/{file_path.relative_to(root).as_posix()}"


def remote_ok(client, bucket: str, key: str, local_size: int) -> bool:
    try:
        r = client.head_object(Bucket=bucket, Key=key)
        return int(r.get("ContentLength", -1)) == local_size
    except ClientError as e:
        code = e.response.get("Error", {}).get("Code", "")
        if code in {"404", "NoSuchKey", "NotFound"}:
            return False
        raise


# ─── worker ──────────────────────────────────────────────────────────────────

def upload_file(
    file_path: Path,
    root: Path,
    bucket: str,
    prefix: str,
    profile: str,
    region: str,
    state: SharedState,
    progress_task,
    progress: Progress,
) -> None:
    key   = s3_key(prefix, root, file_path)
    size  = file_path.stat().st_size

    if state.is_done(key):
        progress.advance(progress_task)
        return

    state.enter_flight()
    client = make_client(profile, region)

    try:
        if remote_ok(client, bucket, key, size):
            state.mark_skipped(key, size)
            progress.advance(progress_task)
            return

        ct, _ = mimetypes.guess_type(str(file_path))
        extra = {"ContentType": ct} if ct else {}
        last_err = None

        for attempt in range(1, 6):
            try:
                client.upload_file(
                    str(file_path), bucket, key,
                    ExtraArgs=extra or None,
                )
                state.mark_uploaded(key, size)
                last_err = None
                break
            except (ClientError, BotoCoreError, OSError) as exc:
                last_err = exc
                time.sleep(min(30, attempt * 3))

        if last_err is not None:
            state.mark_failed(key, file_path, last_err)

    except Exception as exc:
        state.mark_failed(key, file_path, exc)
    finally:
        state.leave_flight()
        progress.advance(progress_task)


# ─── UI ──────────────────────────────────────────────────────────────────────

def build_ui(state: SharedState, total: int, start_time: float, workers: int) -> Table:
    snap    = state.snapshot()
    done    = snap.uploaded + snap.skipped + snap.failed
    elapsed = time.time() - start_time
    rate    = snap.uploaded / elapsed * 3600 if elapsed > 0 and snap.uploaded > 0 else 0
    pending = max(0, total - done)
    eta_s   = pending / (rate / 3600) if rate > 0 else None

    def fmt_eta(s):
        if s is None: return "[dim]calculando...[/dim]"
        h, r = divmod(int(s), 3600)
        m, s2 = divmod(r, 60)
        return f"[bold green]{h:02d}h {m:02d}m {s2:02d}s[/bold green]"

    grid = Table.grid(padding=(0, 2))
    grid.add_column(justify="right", style="bold cyan")
    grid.add_column()

    grid.add_row("Total",      f"[white]{total:,}[/white]")
    grid.add_row("Enviados",   f"[bold green]{snap.uploaded:,}[/bold green]")
    grid.add_row("Pulados",    f"[dim]{snap.skipped:,}[/dim]")
    grid.add_row("Falhas",     f"[red]{snap.failed:,}[/red]" if snap.failed else "[dim]0[/dim]")
    grid.add_row("Em voo",     f"[yellow]{snap.in_flight:,}[/yellow] / {workers}")
    grid.add_row("Taxa",       f"[bold]{rate:,.0f}[/bold] arquivos/h" if rate else "[dim]aguardando...[/dim]")
    grid.add_row("ETA",        fmt_eta(eta_s))
    grid.add_row("Decorrido",  f"{int(elapsed//3600):02d}h {int((elapsed%3600)//60):02d}m {int(elapsed%60):02d}s")

    recent_lines = "\n".join(state.recent[-5:]) if state.recent else "[dim]aguardando primeiro upload...[/dim]"

    outer = Table.grid(padding=1)
    outer.add_column()
    outer.add_column(min_width=50)
    outer.add_row(
        Panel(grid,         title="[bold]Status[/bold]",  border_style="blue"),
        Panel(recent_lines, title="[bold]Recentes[/bold]", border_style="dim"),
    )
    return outer


# ─── main ────────────────────────────────────────────────────────────────────

def main() -> int:
    args   = parse_args()
    root   = Path(args.root).resolve()
    state  = SharedState(Path(args.state_file), Path(args.failed_file), Path(args.log_file))
    console = Console()

    if not root.exists():
        console.print(f"[red]ERRO:[/red] diretório não existe: {root}")
        return 2

    # Enumerar arquivos
    console.print("[dim]Enumerando arquivos...[/dim]")
    files: list[Path] = []
    for dp, _, fns in os.walk(root):
        for fn in fns:
            if fn == ".DS_Store":
                continue
            files.append(Path(dp) / fn)
    files.sort()
    if args.max_files > 0:
        files = files[:args.max_files]

    total = len(files)
    console.print(f"[bold]Total:[/bold] {total:,} arquivos | "
                  f"[bold]Já processados:[/bold] {state.skipped:,} | "
                  f"[bold]Workers:[/bold] {args.workers}")

    progress = Progress(
        SpinnerColumn(),
        TextColumn("[progress.description]{task.description}"),
        BarColumn(bar_width=40),
        MofNCompleteColumn(),
        TaskProgressColumn(),
        TimeElapsedColumn(),
        TimeRemainingColumn(),
        console=console,
        refresh_per_second=4,
    )
    task_id = progress.add_task("[cyan]Fazendo upload...", total=total)

    # Avançar barra pelos já pulados na inicialização
    progress.advance(task_id, state.skipped)

    start_time = time.time()

    with Live(console=console, refresh_per_second=2) as live:
        with concurrent.futures.ThreadPoolExecutor(max_workers=args.workers) as executor:
            futures = [
                executor.submit(
                    upload_file,
                    f, root, args.bucket, args.prefix,
                    args.profile, args.region,
                    state, task_id, progress,
                )
                for f in files
            ]

            while True:
                done_count = sum(1 for fu in futures if fu.done())
                snap = state.snapshot()
                live.update(
                    Panel(
                        build_ui(state, total, start_time, args.workers),
                        title=f"[bold yellow]A12 S3 Upload[/bold yellow]  [{done_count}/{total} tarefas concluídas]",
                        border_style="yellow",
                    )
                )

                if done_count == total:
                    break
                time.sleep(1)

        # Garantir exceções não silenciosas
        for fu in concurrent.futures.as_completed(futures):
            exc = fu.exception()
            if exc:
                console.print(f"[red]Thread exception:[/red] {exc}")

    snap = state.snapshot()
    elapsed = time.time() - start_time
    rate_final = snap.uploaded / elapsed * 3600 if elapsed > 0 and snap.uploaded > 0 else 0

    console.rule("[bold green]CONCLUÍDO[/bold green]")
    console.print(f"Enviados:  [bold green]{snap.uploaded:,}[/bold green]")
    console.print(f"Pulados:   {snap.skipped:,}")
    console.print(f"Falhas:    [red]{snap.failed:,}[/red]")
    console.print(f"Taxa média: {rate_final:,.0f} arquivos/h")
    console.print(f"Log: {args.log_file}")
    return 1 if snap.failed else 0


if __name__ == "__main__":
    raise SystemExit(main())
