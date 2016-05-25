#!/bin/bash

for directory in "../albums/"*; do
    echo ""
    # create our album in mysql
    if [ -f "$directory/details.txt" ]; then
        name=`sed '2q;d' $directory/details.txt | cut -d '"' -f 2`
        description=`sed '3q;d' $directory/details.txt | cut -d '"' -f 2`
        date=`sed '4q;d' $directory/details.txt | cut -d ' ' -f 7`
        month=`echo $date | cut -d ',' -f 1`
        day=`echo $date | cut -d ',' -f 2`
        year=`echo $date | cut -d ',' -f 3`
    fi
    echo "INSERT INTO \`albums\` (\`id\`, \`name\`, \`description\`, \`date\`) VALUES (NULL, '$name', '$description', '$year-$month-$day');"
exit

    if [ ! -d "$directory/full" ]; then		#if no full directory, move our files
        mkdir "$directory/full"

        for file in $directory/{.,}*; do
            if [ -f $file ]; then	#if it is a file
                file_info=`identify $file`
                file_type=`echo $file_info | cut -d ' ' -f 2`
                if [ "$file_type" != "TXT" ]; then
                    mv $file $directory/full/	#move our large file into the full folder
                fi
            fi
        done
    
        #move our files around
        rm -f $directory/*
        mv $directory/thumbs-m/* $directory/
        rm -r $directory/thumbs-m $directory/thumbs-s
    fi

    count=0;
    for file in $directory/{.,}*; do
        if [ -f $file ]; then	#if it is a file
            file_info=`identify $file`
            file_type=`echo $file_info | cut -d ' ' -f 2`
            size=`echo $file_info | cut -d ' ' -f 3`
            width=`echo $size | cut -d 'x' -f 1`
            height=`echo $size | cut -d 'x' -f 2`
            location=${file:2}
            title=${file##*/}
            echo "INSERT INTO \`album_images\` (\`album\`, \`title\`, \`sequence\`, \`caption\`, \`location\`, \`width\`, \`height\`, \`active\`) SELECT id, '$title', '$count', '', '$location', '$width', '$height', '1' FROM albums WHERE name='"${directory##*/}"';"
            ((count++))
        fi
    done
    
done
