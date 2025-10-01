#!/bin/bash
DATE=$(date '+%Y-%m-%d')
FILE="${HOME}/backup-${DATE}.sql"
BACKUP="msaperst@192.168.0.133:/share/SaperstoneStudios/"

source /home/dietpi/.env
docker exec saperstonestudios_mysql /usr/bin/mysqldump -u root -p${DB_ROOT} ${DB_NAME} > ${FILE}
scp -O ${FILE} ${BACKUP}
rm ${FILE}
rsync --update -ra /mnt/server-data/content/* ${BACKUP}