ALTER TABLE `contracts` CHANGE `type` `type` ENUM('wedding','portrait','commercial','contractor','event','partnership','other') CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL;