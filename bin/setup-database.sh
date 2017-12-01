#!/bin/bash

#get our connection information
host=$(awk -F '"' '/db.host/ {print $2}' ../php/env.ini);
username=$(awk -F '"' '/db.username/ {print $2}' ../php/env.ini);
password=$(awk -F '"' '/db.password/ {print $2}' ../php/env.ini);
database=$(awk -F '"' '/db.database/ {print $2}' ../php/env.ini);

#create our database if it doesn't exist
echo "Creating Database"
mysql -h $host -u $username -p$password -e "CREATE DATABASE IF NOT EXISTS \`$database\`;" > /dev/null 2>&1

for file in ../sql/*.sql; do
    filename=${file##*/}
    echo "Running ${filename%.sql}";
    mysql -h $host -u $username -p$password $database < "$file" #> /dev/null 2>&1
done
