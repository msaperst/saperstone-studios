#!/bin/bash

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
PARENT_DIR="$( dirname "${DIR}" )"
ALBUM_ROOT="${PARENT_DIR}/public/albums"

mysql_cmd=(mysql -N -B -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME")

while IFS=$'\t' read -r id location; do
    status=0

    if [[ -d "$ALBUM_ROOT/$location/full" ]]; then
        has_images=false
        status=1

        while IFS= read -r image_location; do
            has_images=true
            filename=${image_location##*/}
            if [[ ! -f "$ALBUM_ROOT/$location/$filename" || ! -f "$ALBUM_ROOT/$location/full/$filename" ]]; then
                status=0
                break
            fi
        done < <("${mysql_cmd[@]}" -e "SELECT location FROM album_images WHERE album = $id;")

        if [[ "$has_images" == false ]]; then
            status=0
        fi
    fi

    "${mysql_cmd[@]}" -e "UPDATE albums SET thumbsCreated = $status WHERE id = $id AND thumbsCreated IS NULL;"
done < <("${mysql_cmd[@]}" -e "SELECT id, location FROM albums WHERE thumbsCreated IS NULL;")
