#!/usr/bin/env bash
set -euo pipefail

root="${1:-/var/www}"
# Preserve host ownership of bind mounts while allowing Apache group writes.
write_group="${CONTENT_WRITE_GROUP:-www-data}"

writable_directories=(
  "${root}/content"
  "${root}/content/commercial"
  "${root}/content/portrait"
  "${root}/content/wedding"
  "${root}/content/b-nai-mitzvah"
  "${root}/content/main"
  "${root}/content/reviews"
  "${root}/content/albums"
  "${root}/content/blog"
  "${root}/content/contracts"
  "${root}/logs"
  "${root}/public/tmp"
)

for directory in "${writable_directories[@]}"; do
  mkdir -p "${directory}"
  chgrp "${write_group}" "${directory}"
  chmod 2775 "${directory}"
done
