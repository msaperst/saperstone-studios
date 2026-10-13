ALTER TABLE `contracts`
    ADD COLUMN `arrival_time` VARCHAR(100) DEFAULT NULL,
    ADD COLUMN `operating_hours` VARCHAR(100) DEFAULT NULL,
    ADD COLUMN `compensation_unit` VARCHAR(100) DEFAULT NULL;
