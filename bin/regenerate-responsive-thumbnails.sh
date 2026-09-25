#!/bin/bash
set -u

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

usage() {
    echo "Usage: $0 <proof|watermark|none> [album-id]"
    echo "Without album-id, regenerates every album currently marked as having thumbnails."
    exit 1
}

[[ "$#" -ge 1 && "$#" -le 2 ]] || usage
markup=$1
album_id=${2:-}

[[ "$markup" == "proof" || "$markup" == "watermark" || "$markup" == "none" ]] || usage

if [[ -n "$album_id" ]]; then
    albums="$album_id"
else
    albums=$(mysql -N -B -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" \
        -e "SELECT id FROM albums WHERE thumbsCreated = TRUE ORDER BY id;")
fi

if [[ -z "$albums" ]]; then
    echo "No albums with thumbnails found."
    exit 0
fi

failed=0
while IFS= read -r id; do
    [[ -n "$id" ]] || continue
    echo "Regenerating album $id with $markup markup..."
    if ! "$DIR/make-thumbs.sh" "$id" "$markup" all; then
        echo "Album $id failed." >&2
        failed=$((failed + 1))
    fi
done <<< "$albums"

if (( failed > 0 )); then
    echo "$failed album(s) failed." >&2
    exit 1
fi

echo "Responsive thumbnail migration complete."
