#!/bin/bash

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
PARENT_DIR="$( dirname "${DIR}" )"

output="${PARENT_DIR}/public/tmp/status.txt"
mkdir -p "$( dirname "${output}" )"
touch "${output}"

if [[ "$#" -ne 4 ]]; then
    echo "Error: Appropriate album information not provided" > "$output"
    sleep 1
    rm "$output"
    exit 1
fi

id=$1
markup=$2
album=$3
mode=$4
location="${PARENT_DIR}/public/albums/$album"

if [[ "$mode" != "missing" && "$mode" != "all" ]]; then
    echo "Error: Thumbnail mode not properly provided" > "$output"
    sleep 1
    rm "$output"
    exit 1
fi

if [[ ! -d "$location" ]]; then
    echo "Error: Album doesn't exist" > "$output"
    sleep 1
    rm "$output"
    exit 1
fi

if [[ ! -d "$location/full" ]]; then
    mkdir "$location/full"
    chmod 777 "$location/full"
fi

if ! mysql -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -e "UPDATE albums SET thumbsCreated=FALSE WHERE id='$id';" > /dev/null 2>&1; then
    echo "Error: Unable to update thumbnail status" > "$output"
    sleep 1
    rm "$output"
    exit 1
fi

processed=0

while IFS= read -r image_location; do
    filename=${image_location##*/}
    file="$location/$filename"
    full_file="$location/full/$filename"
    process_file=false

    if [[ "$mode" == "all" ]]; then
        if [[ -f "$full_file" ]]; then
            cp "$full_file" "$file"
            process_file=true
        elif [[ -f "$file" ]]; then
            cp "$file" "$full_file"
            process_file=true
        fi
    elif [[ ! -f "$file" && -f "$full_file" ]]; then
        cp "$full_file" "$file"
        process_file=true
    elif [[ -f "$file" && ! -f "$full_file" ]]; then
        cp "$file" "$full_file"
        process_file=true
    fi

    if [[ "$process_file" == true ]]; then
        echo "Creating thumbnail $filename..." > "$output"
        file_info=$(identify "$file")
        file_size=$(echo "$file_info" | cut -d ' ' -f 3)
        width=$(echo "$file_size" | cut -d 'x' -f 1)
        height=$(echo "$file_size" | cut -d 'x' -f 2)

        if (( width > 1000 || height > 1000 )); then
            mogrify -resize 1000x1000\> "$file"
            convert -units PixelsPerInch -density 72 "$file" "$file"
        fi

        file_info=$(identify "$file")
        file_size=$(echo "$file_info" | cut -d ' ' -f 3)
        width=$(echo "$file_size" | cut -d 'x' -f 1)
        height=$(echo "$file_size" | cut -d 'x' -f 2)
        mysql -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -e "UPDATE album_images SET width='$width', height='$height' WHERE album='$id' AND location='$image_location';"

        if [[ "$markup" == "proof" ]]; then
            composite -dissolve 30 -tile ../img/proof.png "$file" "$file"
        elif [[ "$markup" == "watermark" ]]; then
            composite -dissolve 85 -gravity southwest -geometry 200x150+30+0 ../img/watermark.png "$file" "$file"
        elif [[ "$markup" != "none" ]]; then
            echo "Error: Markup not properly provided" > "$output"
            sleep 1
            rm "$output"
            exit 1
        fi

        ((processed+=1))
    fi

    if [[ ! -f "$file" || ! -f "$full_file" ]]; then
        echo "Error: Thumbnail generation incomplete" > "$output"
        sleep 1
        rm "$output"
        exit 1
    fi
done < <(mysql -N -B -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -e "SELECT location FROM album_images WHERE album = $id;")

if ! mysql -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -e "UPDATE albums SET thumbsCreated=TRUE WHERE id='$id';" > /dev/null 2>&1; then
    echo "Error: Unable to update thumbnail status" > "$output"
    sleep 1
    rm "$output"
    exit 1
fi

touch "$location"

if [[ "$mode" == "missing" && "$processed" -eq 0 ]]; then
    echo "Done: No missing thumbnails" > "$output"
else
    echo "Done" > "$output"
fi

sleep 1
rm "$output"
