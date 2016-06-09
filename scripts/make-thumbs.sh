#!/bin/bash

output="../scripts/status.txt";

if [ "$#" -ne 3 ]; then
    echo "Error: Appropriate album information not provided" > $output;
    sleep 1;
    rm $output;
    exit;
fi
id=$1;
markup=$2;
album=$3;
location="../albums/$album";

if [ ! -d "$location" ]; then
    echo "Error: Album doesn't exist" > $output;
    sleep 1;
    rm $output;
    exit;
fi

if [ ! -d "$location/full" ]; then
    mkdir "$location/full";
fi

for file in $location/{.,}*; do
    if [ -f "$file" ]; then	#if it is a file
        filename=$(basename "$file")
        file_info=`identify $file`;
        file_type=`echo $file_info | cut -d ' ' -f 2`;
        file_size=`echo $file_info | cut -d ' ' -f 3`;
        width=`echo $file_size | cut -d 'x' -f 1`;
        height=`echo $file_size | cut -d 'x' -f 2`;
        if [ "$file_type" != "TXT" ] && ( [ "$width" -gt "1000" ] || [ "$height" -gt "1000" ] ); then
            echo "Resizing file $filename..." > $output;
            cp "$file" "$location/full/";
            mogrify -resize 1000x1000\> "$file";
            convert -units PixelsPerInch -density 72 "$file" "$file";
            if [ "$markup" == "proof" ]; then
                composite -dissolve 40 -tile ../img/proof.png "$file" "$file";
            elif [ "$markup" == "watermark" ]; then
                composite -dissolve 85 -gravity southwest -geometry 200x150+30+0 ../img/watermark.png "$file" "$file";
            elif [ "$markup" == "none" ]; then
                echo "no watermark needed";
            else
                echo "Markup not properly provided" > $output;
                sleep 1;
                rm $output;
                exit;
            fi
            #update mysql
            file_info=`identify $file`;
            file_size=`echo $file_info | cut -d ' ' -f 3`;
            width=`echo $file_size | cut -d 'x' -f 1`;
            height=`echo $file_size | cut -d 'x' -f 2`;
            mysql -u root -psecret -e "UPDATE \`saperstone-studios\`.\`album_images\` SET width='$width', height='$height' WHERE album='$id' AND location='${file:2}';"
        fi
    fi
done

echo "Done" > $output;
sleep 1;
rm $output;
