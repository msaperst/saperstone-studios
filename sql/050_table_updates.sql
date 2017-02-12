--- Add image count column to albums table ---
ALTER TABLE `albums` ADD `images` int(11) NOT NULL default '0';
