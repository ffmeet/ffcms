#!/usr/bin/env bash

set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
VERSION="${1:-v1.0.0}"
OUTPUT_DIR="${2:-${ROOT_DIR}/dist}"
PACKAGE_NAME="ffmeet-${VERSION}-source"
STAGING_DIR="$(mktemp -d "/tmp/${PACKAGE_NAME}.XXXXXX")"
PACKAGE_DIR="${STAGING_DIR}/${PACKAGE_NAME}"
IGNORE_FILE="${ROOT_DIR}/.releaseignore"

cleanup() {
    rm -rf "${STAGING_DIR}"
}

trap cleanup EXIT

mkdir -p "${OUTPUT_DIR}"

if [[ ! -f "${IGNORE_FILE}" ]]; then
    echo "Missing ignore file: ${IGNORE_FILE}" >&2
    exit 1
fi

rsync -a \
    --delete \
    --exclude-from="${IGNORE_FILE}" \
    "${ROOT_DIR}/" "${PACKAGE_DIR}/"

rm -f "${PACKAGE_DIR}/.env"
rm -f "${PACKAGE_DIR}/database/database.sqlite"
rm -rf "${PACKAGE_DIR}/storage/logs" "${PACKAGE_DIR}/storage/debugbar"
mkdir -p "${PACKAGE_DIR}/storage/logs" "${PACKAGE_DIR}/storage/debugbar"
touch "${PACKAGE_DIR}/storage/logs/.gitignore" "${PACKAGE_DIR}/storage/debugbar/.gitignore"

(
    cd "${STAGING_DIR}"
    tar -czf "${OUTPUT_DIR}/${PACKAGE_NAME}.tar.gz" "${PACKAGE_NAME}"
)

if command -v shasum >/dev/null 2>&1; then
    shasum -a 256 "${OUTPUT_DIR}/${PACKAGE_NAME}.tar.gz" > "${OUTPUT_DIR}/${PACKAGE_NAME}.sha256"
fi

echo "Created package:"
echo "  ${OUTPUT_DIR}/${PACKAGE_NAME}.tar.gz"
if [[ -f "${OUTPUT_DIR}/${PACKAGE_NAME}.sha256" ]]; then
    echo "  ${OUTPUT_DIR}/${PACKAGE_NAME}.sha256"
fi

