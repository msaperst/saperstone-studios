ALTER TABLE `user_logs`
    DROP INDEX `id`,
    ADD COLUMN `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT FIRST,
    ADD PRIMARY KEY (`id`),
    ADD INDEX `user_logs_user_time` (`user`, `time`);
