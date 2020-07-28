#!/bin/bash
DRYRUN=$1;
DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )";
PARENTDIR="$( dirname ${DIR} )";

#wait for database to be available
while ! mysqladmin ping -h $DB_HOST -P $DB_PORT --silent; do
    echo "Waiting for db"
    sleep 1
done

#create our database if it doesn't exist
echo "Creating Database"
mysql -h $DB_HOST -P $DB_PORT -u $DB_USER -p$DB_PASS -e "CREATE DATABASE IF NOT EXISTS \`$DB_NAME\`;" > /dev/null 2>&1

#setup our schema
for file in ${DIR}/sql/*.sql; do
    filename=${file##*/}
    echo "Running ${filename%.sql}";
    mysql -h $DB_HOST -P $DB_PORT -u $DB_USER -p$DB_PASS $DB_NAME < "$file" #> /dev/null 2>&1
done

if [ "$DRYRUN" = true ]; then
    echo "Finished Setting up DB"
    exit 0;
fi


#set server name
echo "export SERVER_NAME='${SERVER_NAME}'" >> /etc/apache2/envvars

#launch apache2
apache2-foreground

#cleanup sql files
rm -r bin/sql
rm bin/setup-database.sh