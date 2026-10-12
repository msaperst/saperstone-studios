ALTER TABLE `albums`
    ADD `images` int(11) NOT NULL default '0';
ALTER TABLE `contracts`
    CHANGE `amount` `amount` DECIMAL(10, 2) NOT NULL DEFAULT '0';
ALTER TABLE `contracts`
    CHANGE `deposit` `deposit` DECIMAL(10, 2) NOT NULL DEFAULT '0';
ALTER TABLE `contract_line_items`
    CHANGE `amount` `amount` DECIMAL(10, 2) NOT NULL;
ALTER TABLE `notification_emails`
    ADD `contacted` BOOLEAN NOT NULL DEFAULT FALSE AFTER `email`;
ALTER TABLE `contracts`
    CHANGE `type` `type` ENUM ('wedding','portrait','commercial','contractor','event','partnership','photo-booth','other') CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL;
ALTER TABLE `blog`
    DROP COLUMN `twitter`;
UPDATE `galleries`
SET `title` = 'Photo Booth'
WHERE `id` = 37;
UPDATE `galleries`
SET `title` = 'Photo Booth'
WHERE `id` = 58;
UPDATE `galleries`
SET `title` = 'Photo Booth'
WHERE `id` = 77;
