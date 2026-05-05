#!/usr/bin/env bash

set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
PUBLIC_DIR="$ROOT_DIR/public"
ROUTER_SCRIPT="$ROOT_DIR/vendor/laravel/framework/src/Illuminate/Foundation/resources/server.php"

HOST="${HOST:-127.0.0.1}"
PORT="${PORT:-8000}"
UPLOAD_MAX_FILESIZE="${UPLOAD_MAX_FILESIZE:-16M}"
POST_MAX_SIZE="${POST_MAX_SIZE:-20M}"
MAX_FILE_UPLOADS="${MAX_FILE_UPLOADS:-20}"
MAX_INPUT_TIME="${MAX_INPUT_TIME:-120}"
MAX_EXECUTION_TIME="${MAX_EXECUTION_TIME:-120}"

if [[ ! -f "$ROUTER_SCRIPT" ]]; then
    echo "Laravel router script not found: $ROUTER_SCRIPT" >&2
    exit 1
fi

cd "$PUBLIC_DIR"

exec php \
    -d "upload_max_filesize=${UPLOAD_MAX_FILESIZE}" \
    -d "post_max_size=${POST_MAX_SIZE}" \
    -d "max_file_uploads=${MAX_FILE_UPLOADS}" \
    -d "max_input_time=${MAX_INPUT_TIME}" \
    -d "max_execution_time=${MAX_EXECUTION_TIME}" \
    -S "${HOST}:${PORT}" \
    "$ROUTER_SCRIPT"
