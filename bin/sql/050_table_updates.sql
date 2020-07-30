ALTER TABLE `albums` ADD `images` int(11) NOT NULL default '0';
ALTER TABLE `contracts` CHANGE `amount` `amount` DECIMAL(10,2) NOT NULL DEFAULT '0';
ALTER TABLE `contracts` CHANGE `deposit` `deposit` DECIMAL(10,2) NOT NULL DEFAULT '0';
ALTER TABLE `contract_line_items` CHANGE `amount` `amount` DECIMAL(10,2) NOT NULL;
