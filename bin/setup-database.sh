#!/bin/bash
DRYRUN=$1;
DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )";

#wait for database to be available
while ! mysqladmin ping -h $DB_HOST -P $DB_PORT --silent; do
    echo "Waiting for db"
    sleep 1
done

#create our database if it doesn't exist
echo "Creating Database"
mysql -h $DB_HOST -P $DB_PORT -u $DB_USER -p$DB_PASS -e "CREATE DATABASE IF NOT EXISTS \`$DB_NAME\`;" > /dev/null 2>&1

#setup our schema
shopt -s nullglob
sql_files=("${DIR}"/sql/*.sql)
if [ ${#sql_files[@]} -eq 0 ]; then
    echo "No database migration files found in ${DIR}/sql" >&2
    exit 1
fi

for file in "${sql_files[@]}"; do
    filename=${file##*/}
    echo "Running ${filename%.sql}";
    mysql --force -h $DB_HOST -P $DB_PORT -u $DB_USER -p$DB_PASS $DB_NAME < "$file" #> /dev/null 2>&1
done

echo "Finished Setting up DB"

echo "Checking album code uniqueness"
if ! bash "${DIR}/ensure-album-code-uniqueness.sh"; then
    exit 1
fi

echo "Checking thumbnail status"
bash "${DIR}/update-thumbnail-status.sh"

if [ "$DRYRUN" = true ]; then
    exit 0;
fi


#set server name
echo "export SERVER_NAME='${SERVER_NAME}'" >> /etc/apache2/envvars

#launch apache2
exec apache2-foreground
