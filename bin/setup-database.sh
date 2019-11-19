#!/bin/bash

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )";
PARENTDIR="$( dirname ${DIR} )";

#wait for database to be available
while ! mysqladmin ping -h $DB_HOST -P $DB_PORT --silent; do
    echo "Waiting for db"
    sleep 1
done

#create our database if it doesn't exist
echo "Creating Database"
mysql -h $DB_HOST -P $DB_PORT -u $DB_USERNAME -p$DB_PASSWORD -e "CREATE DATABASE IF NOT EXISTS \`$DB_DATABASE\`;" > /dev/null 2>&1

#setup our schema
for file in ${DIR}/sql/*.sql; do
    filename=${file##*/}
    echo "Running ${filename%.sql}";
    mysql -h $DB_HOST -P $DB_PORT -u $DB_USERNAME -p$DB_PASSWORD $DB_DATABASE < "$file" #> /dev/null 2>&1
done

#set server name
echo "export SERVER_NAME='${SERVER_NAME}'" >> /etc/apache2/envvars

#launch apache2
apache2-foreground

#cleanup sql files
rm -r bin/sql
rm bin/setup-database.sh