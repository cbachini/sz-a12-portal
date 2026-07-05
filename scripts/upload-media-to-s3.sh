#!/usr/bin/env bash
# scripts/upload-media-to-s3.sh — Wrapper para upload resiliente de midias ao S3

set -euo pipefail

ROOT_DIR="$(cd "$(dirname "$0")/.." && pwd)"
UPLOADS_DIR="${ROOT_DIR}/wp-content/uploads"
VENV_ACTIVATE="/Users/soyuz/Documents/Dev/A12/.venv/bin/activate"

AWS_PROFILE="${AWS_PROFILE:-a12-dev}"
AWS_REGION="${AWS_REGION:-sa-east-1}"
S3_BUCKET="${S3_BUCKET:-a12-dev-uploads}"
S3_PREFIX="${S3_PREFIX:-uploads}"

if [[ ! -f "${VENV_ACTIVATE}" ]]; then
  echo "ERRO: venv nao encontrada em ${VENV_ACTIVATE}" >&2
  exit 1
fi

if [[ ! -d "${UPLOADS_DIR}" ]]; then
  echo "ERRO: uploads nao encontrado em ${UPLOADS_DIR}" >&2
  exit 1
fi

# shellcheck disable=SC1090
source "${VENV_ACTIVATE}"

python "${ROOT_DIR}/scripts/upload-media-to-s3.py" \
  --root "${UPLOADS_DIR}" \
  --bucket "${S3_BUCKET}" \
  --prefix "${S3_PREFIX}" \
  --profile "${AWS_PROFILE}" \
  --region "${AWS_REGION}" \
  "$@"