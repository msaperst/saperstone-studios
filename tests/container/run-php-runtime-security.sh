#!/bin/sh
# Host-side entry point for production-image integration tests.
set -eu

image=${1:?Usage: sh tests/container/run-php-runtime-security.sh IMAGE}
root=$(CDPATH= cd -- "$(dirname -- "$0")/../.." && pwd)

docker run --rm --user www-data --entrypoint /bin/sh \
    --mount "type=bind,source=$root/tests/container,target=/tests,readonly" \
    --mount "type=bind,source=$root/tests/resources,target=/fixtures,readonly" \
    "$image" /tests/php-runtime-security.sh
