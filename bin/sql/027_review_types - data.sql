--
-- Dumping data for table `review_types`
--

LOCK TABLES `review_types` WRITE;
/*!40000 ALTER TABLE `review_types` DISABLE KEYS */;
INSERT IGNORE INTO `review_types` VALUES (1,'portrait');
INSERT IGNORE INTO `review_types` VALUES (2,'wedding');
INSERT IGNORE INTO `review_types` VALUES (3,'commercial');
INSERT IGNORE INTO `review_types` VALUES (4,'other');

UPDATE `review_types` set `id` = 5 WHERE `id` = 4;
INSERT IGNORE INTO `review_types` VALUES (4,'mitzvah');
UPDATE `review_types` set `name` = 'b\'nai mitzvah' WHERE `id` = 4;
/*!40000 ALTER TABLE `review_types` ENABLE KEYS */;
UNLOCK TABLES;