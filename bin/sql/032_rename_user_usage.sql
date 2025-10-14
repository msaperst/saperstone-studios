RENAME TABLE `saperstone-studios`.`user_usage` TO `saperstone-studios`.`user_logs`;

ALTER TABLE user_logs
    DROP INDEX id,
    ADD UNIQUE KEY id (user, time, what(100));