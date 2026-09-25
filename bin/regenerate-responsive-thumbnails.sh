#!/bin/bash
set -u

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
PARENT_DIR="$( dirname "${DIR}" )"

usage() {
    echo "Usage: $0 [album-id]"
    echo
    echo "Backfills the responsive 400/800/1200/1600 thumbnail workflow."
    echo "All generated derivatives use NO proof/watermark markup."
    echo "Without album-id, every album in the database is checked."
    echo "Albums that already have the complete responsive structure are skipped."
    exit 1
}

[[ "$#" -le 1 ]] || usage
album_id=${1:-}

if [[ -n "$album_id" ]]; then
    albums="$album_id"
else
    albums=$(mysql -N -B -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" \
        -e "SELECT id FROM albums ORDER BY id;")
fi

if [[ -z "$albums" ]]; then
    echo "No albums found."
    exit 0
fi

album_needs_migration() {
    local id=$1
    local album_location
    album_location=$(mysql -N -B -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" \
        -e "SELECT location FROM albums WHERE id = $id LIMIT 1;")
    local album_dir="${PARENT_DIR}/public/albums/$album_location"

    [[ -d "$album_dir" ]] || return 0

    while IFS= read -r image_location; do
        local filename=${image_location##*/}
        [[ -f "$album_dir/$filename" ]] || return 0
        [[ -f "$album_dir/full/$filename" ]] || return 0
        [[ -f "$album_dir/thumbs/400/$filename" ]] || return 0
        [[ -f "$album_dir/thumbs/800/$filename" ]] || return 0
        [[ -f "$album_dir/thumbs/1200/$filename" ]] || return 0
    done < <(mysql -N -B -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" \
        -e "SELECT location FROM album_images WHERE album = $id;")

    return 1
}

failed=0
processed=0
skipped=0

while IFS= read -r id; do
    [[ -n "$id" ]] || continue

    if ! album_needs_migration "$id"; then
        echo "Skipping album $id: responsive thumbnails already complete."
        ((skipped+=1))
        continue
    fi

    echo "Generating responsive thumbnails for album $id (no markup)..."
    if "$DIR/make-thumbs.sh" "$id" none missing; then
        ((processed+=1))
    else
        echo "Album $id failed." >&2
        ((failed+=1))
    fi
done <<< "$albums"

echo "Responsive thumbnail backfill complete: $processed processed, $skipped skipped, $failed failed."

if (( failed > 0 )); then
    exit 1
fi
