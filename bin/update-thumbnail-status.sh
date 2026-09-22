#!/bin/bash

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )";
PARENT_DIR="$( dirname "${DIR}" )";
ALBUM_ROOT="${PARENT_DIR}/public/albums";

mysql_cmd=(mysql -N -B -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME");

while IFS=$'\t' read -r id location image_count; do
    status=0;

    if [ "$image_count" -gt 0 ] && [ -d "$ALBUM_ROOT/$location/full" ]; then
        status=1;
        while IFS= read -r image_location; do
            filename=$(basename "$image_location");
            if [ ! -f "$ALBUM_ROOT/$location/$filename" ] || [ ! -f "$ALBUM_ROOT/$location/full/$filename" ]; then
                status=0;
                break;
            fi
        done < <("${mysql_cmd[@]}" -e "SELECT location FROM album_images WHERE album = $id;");
    fi

    "${mysql_cmd[@]}" -e "UPDATE albums SET thumbsCreated = $status WHERE id = $id;";
done < <("${mysql_cmd[@]}" -e "SELECT albums.id, albums.location, COUNT(album_images.id) FROM albums LEFT JOIN album_images ON album_images.album = albums.id WHERE albums.thumbsCreated IS NULL GROUP BY albums.id, albums.location;");
