SET @user_logs_has_identity = (
    SELECT COUNT(*)
    FROM `information_schema`.`columns`
    WHERE `table_schema` = DATABASE()
      AND `table_name` = 'user_logs'
      AND `column_name` = 'id'
);

SET @user_logs_identity_migration = IF(
    @user_logs_has_identity = 0,
    'ALTER TABLE `user_logs` DROP INDEX `id`, ADD COLUMN `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT FIRST, ADD PRIMARY KEY (`id`), ADD INDEX `user_logs_user_time` (`user`, `time`)',
    'SELECT 1'
);

PREPARE user_logs_identity_statement FROM @user_logs_identity_migration;
EXECUTE user_logs_identity_statement;
DEALLOCATE PREPARE user_logs_identity_statement;
