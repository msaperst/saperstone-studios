--
-- Dumping data for table `product_types`
--

LOCK TABLES `product_types` WRITE;
/*!40000 ALTER TABLE `product_types` DISABLE KEYS */;
INSERT IGNORE INTO `product_types` VALUES (1, 'signature', 'Acrylic Prints');
INSERT IGNORE INTO `product_types` VALUES (2, 'signature', 'Metal Prints');
INSERT IGNORE INTO `product_types` VALUES (3, 'standard', 'Canvas Gallery Wraps');
INSERT IGNORE INTO `product_types` VALUES (4, 'signature', 'Bamboo Prints');
INSERT IGNORE INTO `product_types` VALUES (5, 'standard', 'Stand Out Frames');
INSERT IGNORE INTO `product_types` VALUES (6, 'standard', 'Wall Portraits');
INSERT IGNORE INTO `product_types` VALUES (7, 'prints', 'Gift Prints');
INSERT IGNORE INTO `product_types` VALUES (8, 'prints', 'Photo Prints');
INSERT IGNORE INTO `product_types` VALUES (9, 'digital', 'USB of All Images');
INSERT IGNORE INTO `product_types` VALUES (10, 'digital', 'Per File');
INSERT IGNORE INTO `product_types` VALUES (11, '', '5x7 Keepsake Box');
INSERT IGNORE INTO `product_types` VALUES (12, '', 'Album Block');
/*!40000 ALTER TABLE `product_types` ENABLE KEYS */;
UNLOCK TABLES;