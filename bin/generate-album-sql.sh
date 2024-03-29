#!/bin/bash

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )";
PARENTDIR="$( dirname ${DIR} )";

for directory in "${PARENTDIR}/public/albums/"*; do
    if [ ! -d $directory ]; then
        continue
    fi
    location=`basename $directory`
    echo ""
    name=""
    # create our album in mysql
    if [ -f "$directory/details.txt" ]; then
        sed -i "s/'/\"/g" $directory/details.txt
        name=`sed '2q;d' $directory/details.txt | cut -d '"' -f 2`
        description=`sed '3q;d' $directory/details.txt | cut -d '"' -f 2`
        month=`sed '4q;d' $directory/details.txt | cut -d ',' -f 4 | tr -d '[[:space:]]'`
        day=`sed '4q;d' $directory/details.txt | cut -d ',' -f 5 | tr -d '[[:space:]]'`
        year=`sed '4q;d' $directory/details.txt | cut -d ',' -f 6 | tr -d '[[:space:]]' | cut -c1-4`
    fi
    if [ -z "$name" ]; then
        name=`basename $location`
    fi
    echo "INSERT INTO \`albums\` (\`id\`, \`name\`, \`description\`, \`date\`, \`location\`) VALUES (NULL, '$name', '$description', '$year-$month-$day', '$location');"

    if [ ! -d "$directory/full" ]; then        #if no full directory, move our files
        mkdir "$directory/full"

        for file in $directory/{.,}*; do
            if [ -f $file ]; then    #if it is a file
                file_info=`identify $file`
                file_type=`echo $file_info | cut -d ' ' -f 2`
                if [ "$file_type" != "TXT" ]; then
                    mv $file $directory/full/    #move our large file into the full folder
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
        if [ -f $file ]; then    #if it is a file
            file_info=`identify $file`
            file_type=`echo $file_info | cut -d ' ' -f 2`
            size=`echo $file_info | cut -d ' ' -f 3`
            width=`echo $size | cut -d 'x' -f 1`
            height=`echo $size | cut -d 'x' -f 2`
            fileLocation=${file:2}
            title=${file##*/}
            echo "INSERT INTO \`album_images\` (\`album\`, \`title\`, \`sequence\`, \`caption\`, \`location\`, \`width\`, \`height\`, \`active\`) SELECT id, '$title', '$count', '', '$fileLocation', '$width', '$height', '1' FROM albums WHERE location='$location';"
            ((count++))
        fi
    done
    
done
