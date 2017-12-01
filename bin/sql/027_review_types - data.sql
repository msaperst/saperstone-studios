--
-- Dumping data for table `review_types`
--

LOCK TABLES `review_types` WRITE;
/*!40000 ALTER TABLE `review_types` DISABLE KEYS */;
INSERT IGNORE INTO `review_types` VALUES (1,'portrait');
INSERT IGNORE INTO `review_types` VALUES (2,'wedding');
INSERT IGNORE INTO `review_types` VALUES (3,'commercial');
INSERT IGNORE INTO `review_types` VALUES (4,'other');
/*!40000 ALTER TABLE `review_types` ENABLE KEYS */;
UNLOCK TABLES;