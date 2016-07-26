#!/bin/bash


for directory in "../img/portrait/"*; do
    if [ ! -d $directory ]; then
        continue
    fi
    location=`basename $directory`
    echo ""
    name="portrait-$directory"

    count=0;
    for file in $directory/{.,}*; do
        if [ -f $file ]; then	#if it is a file
            #resize image
            mogrify -resize 900x600 $file
            mogrify -density 72 $file
            #get file information
            file_info=`identify $file`
            file_type=`echo $file_info | cut -d ' ' -f 2`
            size=`echo $file_info | cut -d ' ' -f 3`
            width=`echo $size | cut -d 'x' -f 1`
            height=`echo $size | cut -d 'x' -f 2`
            location=${file:2}
            title=${file##*/}
            echo "INSERT INTO \`gallery_images\` (\`gallery\`, \`title\`, \`sequence\`, \`caption\`, \`location\`, \`width\`, \`height\`, \`active\`) SELECT id, '$title', '$count', '', '$location', '$width', '$height', '1' FROM galleries WHERE name='$name';"
            ((count++))
        fi
    done
    
done
