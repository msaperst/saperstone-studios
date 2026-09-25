#!/bin/bash

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
PARENT_DIR="$( dirname "${DIR}" )"

output="${PARENT_DIR}/status/thumbnail-status.txt"
mkdir -p "$( dirname "${output}" )"
touch "${output}"

fail() {
    echo "Error: $1" > "$output"
    sleep 1
    rm -f "$output"
    exit 1
}

if [[ "$#" -ne 3 ]]; then
    fail "Appropriate album information not provided"
fi

id=$1
markup=$2
mode=$3
album=$(mysql -N -B -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -e "SELECT location FROM albums WHERE id = $id LIMIT 1;")
location="${PARENT_DIR}/public/albums/$album"
full_dir="$location/full"

if [[ "$mode" != "missing" && "$mode" != "all" ]]; then
    fail "Thumbnail mode not properly provided"
fi
if [[ "$markup" != "proof" && "$markup" != "watermark" && "$markup" != "none" ]]; then
    fail "Markup not properly provided"
fi
if [[ ! -d "$location" ]]; then
    fail "Album doesn't exist"
fi

mkdir -p "$full_dir" "$location/thumbs/400" "$location/thumbs/800"
chmod 777 "$full_dir" "$location/thumbs" "$location/thumbs/400" "$location/thumbs/800"

if ! mysql -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -e "UPDATE albums SET thumbsCreated=FALSE WHERE id='$id';" > /dev/null 2>&1; then
    fail "Unable to update thumbnail status"
fi

apply_markup() {
    local file=$1
    local size=$2

    if [[ "$markup" == "proof" ]]; then
        composite -dissolve 30 -tile "${PARENT_DIR}/public/img/proof.png" "$file" "$file"
    elif [[ "$markup" == "watermark" ]]; then
        local watermark_width=$((size / 5))
        local watermark_height=$((size * 3 / 20))
        local offset=$((size * 3 / 100))
        composite -dissolve 85 -gravity southwest -geometry "${watermark_width}x${watermark_height}+${offset}+0" \
            "${PARENT_DIR}/public/img/watermark.png" "$file" "$file"
    fi
}

create_derivative() {
    local source=$1
    local destination=$2
    local size=$3

    # Read from the full-resolution source every time. Never resize a derivative.
    convert "$source" -auto-orient -resize "${size}x${size}>" -strip -interlace Plane -quality 88 \
        -units PixelsPerInch -density 72 "$destination" || return 1
    apply_markup "$destination" "$size"
}

processed=0

while IFS= read -r image_location; do
    filename=${image_location##*/}
    public_file="$location/$filename"
    full_file="$full_dir/$filename"
    small_file="$location/thumbs/400/$filename"
    medium_file="$location/thumbs/800/$filename"
    process_file=false

    # Preserve the original before replacing the legacy public derivative.
    if [[ ! -f "$full_file" && -f "$public_file" ]]; then
        cp "$public_file" "$full_file" || fail "Unable to preserve original $filename"
    fi

    if [[ ! -f "$full_file" ]]; then
        fail "Full-resolution source missing for $filename"
    fi

    if [[ "$mode" == "all" || ! -f "$public_file" || ! -f "$small_file" || ! -f "$medium_file" ]]; then
        process_file=true
    fi

    if [[ "$process_file" == true ]]; then
        echo "Creating responsive thumbnails $filename..." > "$output"

        create_derivative "$full_file" "$small_file" 400 || fail "Unable to create 400px thumbnail for $filename"
        create_derivative "$full_file" "$medium_file" 800 || fail "Unable to create 800px thumbnail for $filename"
        create_derivative "$full_file" "$public_file" 1600 || fail "Unable to create 1600px thumbnail for $filename"

        file_size=$(identify -format "%wx%h" "$public_file") || fail "Unable to inspect $filename"
        width=${file_size%x*}
        height=${file_size#*x}
        mysql -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" \
            -e "UPDATE album_images SET width='$width', height='$height' WHERE album='$id' AND location='$image_location';"

        ((processed+=1))
    fi

    if [[ ! -f "$public_file" || ! -f "$full_file" || ! -f "$small_file" || ! -f "$medium_file" ]]; then
        fail "Thumbnail generation incomplete for $filename"
    fi
done < <(mysql -N -B -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -e "SELECT location FROM album_images WHERE album = $id;")

if ! mysql -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -e "UPDATE albums SET thumbsCreated=TRUE WHERE id='$id';" > /dev/null 2>&1; then
    fail "Unable to update thumbnail status"
fi

touch "$location"

if [[ "$mode" == "missing" && "$processed" -eq 0 ]]; then
    echo "Done: No missing thumbnails" > "$output"
else
    echo "Done" > "$output"
fi

sleep 1
rm -f "$output"
