#!/bin/bash

mysql_cmd=(mysql -N -B -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME")

# Blank codes are equivalent to no code and would otherwise consume a unique value.
if ! "${mysql_cmd[@]}" -e "UPDATE albums SET code = NULL WHERE code = '';" > /dev/null; then
    echo "Error: Unable to normalize blank album codes" >&2
    exit 1
fi

if ! duplicates=$("${mysql_cmd[@]}" -e "
    SELECT code, GROUP_CONCAT(id ORDER BY id)
    FROM albums
    WHERE code IS NOT NULL
    GROUP BY code
    HAVING COUNT(*) > 1;
"); then
    echo "Error: Unable to check album code uniqueness" >&2
    exit 1
fi

if [[ -n "$duplicates" ]]; then
    echo "Error: Duplicate album codes must be resolved before uniqueness can be enforced:" >&2
    echo "$duplicates" >&2
    exit 1
fi

if ! unique_index_count=$("${mysql_cmd[@]}" -e "
    SELECT COUNT(*)
    FROM (
        SELECT index_name
        FROM information_schema.statistics
        WHERE table_schema = DATABASE()
          AND table_name = 'albums'
          AND non_unique = 0
        GROUP BY index_name
        HAVING COUNT(*) = 1
           AND MAX(column_name = 'code') = 1
    ) AS unique_code_indexes;
"); then
    echo "Error: Unable to inspect album code indexes" >&2
    exit 1
fi

if [[ "$unique_index_count" -eq 0 ]]; then
    echo "Adding unique album code index"
    if ! "${mysql_cmd[@]}" -e "ALTER TABLE albums ADD UNIQUE KEY album_code_unique (code);"; then
        echo "Error: Unable to enforce unique album codes" >&2
        exit 1
    fi
fi
