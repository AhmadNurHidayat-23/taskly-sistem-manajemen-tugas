#!/usr/bin/env bash
set -euo pipefail

image="${1:-taskly:ci}"
container="taskly-smoke-${RANDOM}"
volume="${container}-storage"
state_file="$(mktemp)"
cleanup() {
    result=$?
    if [ "$result" -ne 0 ]; then docker logs "$container" 2>/dev/null || true; fi
    docker rm -f "$container" >/dev/null 2>&1 || true
    docker volume rm "$volume" >/dev/null 2>&1 || true
    rm -f "$state_file"
    exit "$result"
}
trap cleanup EXIT

APP_KEY="$(docker run --rm --entrypoint php "$image" -r 'echo "base64:".base64_encode(random_bytes(32));')"
export APP_KEY
docker volume create "$volume" >/dev/null

start_container() {
    docker run -d --name "$container" -p 127.0.0.1::80 \
        --env APP_KEY --env APP_URL=http://localhost \
        --mount "type=volume,source=$volume,target=/var/www/html/storage" \
        "$image" >/dev/null
    port="$(docker port "$container" 80/tcp | awk -F: '{print $NF}')"
    base_url="http://127.0.0.1:$port"
    for attempt in {1..60}; do
        if curl --silent --fail "$base_url/up" >/dev/null; then return; fi
        sleep 1
    done
    echo "Container tidak siap dalam 60 detik." >&2
    return 1
}

start_container
docker exec "$container" test ! -e /var/www/html/.env
docker exec "$container" test ! -d /var/www/html/.git
docker exec "$container" php -r 'exit(extension_loaded("pdo_sqlite") ? 0 : 1);'
python3 docker/smoke-test.py create "$base_url" "$state_file"
docker rm -f "$container" >/dev/null
start_container
python3 docker/smoke-test.py verify "$base_url" "$state_file"
echo "Container smoke test berhasil: HTTP, aset, CSRF, CRUD, dan persistensi volume."
