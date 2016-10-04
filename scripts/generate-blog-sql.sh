#!/bin/bash

IFS=$'\n'       # make newlines the only separator
for f in $(find ../blog -name '*.php'); do
	
	#verify we're only importing live, working versions
	if [[ "$f" == *"_"* ]]; then
 		#echo "Skipping old file $f";
		continue;
	fi

	location=$(dirname $f);
	filename=$(basename $f);
 	#echo "Parsing $filename";

	#general details of our blog	
	title=`sed -n 's/<h2>\(.*\)<\/h2>/\1/p' "$f"`;
	date=`sed -n 's/<div id="date">\(.*\)<\/div>/\1/p' "$f"`;
	date=`date -d "$date" +%Y-%m-%d`
	if [ -f "${location}/offset.o" ]; then #if the file doesn't exist
		offset=$(cat "${location}/offset.o");
		offset="${offset//[^0-9\-]/}";
		if [ -z $offset ]; then
			offset=0;
		fi
	else
		offset=0;
	fi
	preview="${location}/preview_image.jpg";
	if [ ! -f $preview ]; then #if the file doesn't exist
		preview="";
	else
		preview=${preview:2}
	fi
	echo "INSERT INTO \`blog_details\` (\`title\`, \`date\`, \`preview\`, \`offset\`) VALUES (\"$title\", '$date', '$preview', '$offset');"

	#tags details of our blog
	tags=`grep 'id="tags"' "$f"`;
	tags=( $(echo $tags | grep -oP '>(.*?)<') );
	tags_arr=();
	for ((i=0; i<${#tags[*]}; i++)); do
		if [ "${tags[$i]}" != "><" ] && [ "${tags[$i]}" != ">, <" ] && [ "${tags[$i]}" != ">&nbsp;<" ]; then
			tag=`echo "${tags[$i]}" | sed 's/^>\(.*\)<$/\1/'`
			echo "INSERT INTO \`blog_tags\` (\`blog\`, \`tag\`) VALUES ( (SELECT id FROM blog_details WHERE title=\"$title\" and date='$date'), (SELECT id FROM tags WHERE tag=\"$tag\") );";
		fi
	done
	
	#details of our blog
	contentGroup=0;
	contentType="nothing";
	while read line; do
		#setup our content type and content group
		if [[ $line == *'id="text"'* ]]; then
			contentType="text";
			content="";
			((contentGroup++));
		elif [[ $line == *'class="allBlogImages"'* ]]; then
			contentType="image";
			((contentGroup++));
		fi
	
		if [ "$contentType" == "text" ]; then
			content+=$line;
		fi
		
		if [[ "$line" == *'class="blogImage"'* ]]; then
			location=`echo "$line" | cut -d '"' -f 4`;
			height=`echo "$line" | sed -n 's/.*height:\s*\([0-9\.]\+\)px.*/\1/p'`;
			width=`echo "$line" | sed -n 's/.*width:\s*\([0-9\.]\+\)px.*/\1/p'`;
			left=`echo "$line" | sed -n 's/.*left:\s*\([0-9\.]\+\)px.*/\1/p'`;
			top=`echo "$line" | sed -n 's/.*top:\s*\([0-9\.]\+\)px.*/\1/p'`;
			echo "INSERT INTO \`blog_images\` (\`blog\`, \`contentGroup\`, \`location\`, \`width\`, \`height\`, \`left\`, \`top\`) SELECT id, '$contentGroup', '$location', '$width', '$height', '$left', '$top' FROM blog_details WHERE title=\"$title\" and date='$date';";
		fi
	
		if [[ $line == *'</div>' && "$contentType" == "text" ]]; then
			contentType="nothing";
			content=${content:15};
			content=${content::-6};
			content=${content//\"/\'};
			echo "INSERT INTO \`blog_texts\` (\`blog\`, \`contentGroup\`, \`text\`) SELECT id, '$contentGroup', \"$content\" FROM blog_details WHERE title=\"$title\" and date='$date';";
		fi
	done < "$f"

done