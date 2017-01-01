--
-- Dumping data for table `galleries`
--

LOCK TABLES `galleries` WRITE;
/*!40000 ALTER TABLE `galleries` DISABLE KEYS */;
INSERT IGNORE INTO `galleries` VALUES (0, NULL, NULL, 'Leigh Ann'),
INSERT IGNORE INTO `galleries` VALUES (1, NULL, NULL, 'Portrait'),
INSERT IGNORE INTO `galleries` VALUES (2, 1, 'maternity.jpg', 'Maternity'),
INSERT IGNORE INTO `galleries` VALUES (3, 1, 'newborn.jpg', 'Newborn'),
INSERT IGNORE INTO `galleries` VALUES (4, 1, '6-month.jpg', '6 Months'),
INSERT IGNORE INTO `galleries` VALUES (5, 1, '1-year.jpg', 'First Birthday'),
INSERT IGNORE INTO `galleries` VALUES (6, 1, 'family.jpg', 'Kids and Family'),
INSERT IGNORE INTO `galleries` VALUES (7, 1, 'senior.jpg', 'Seniors'),
INSERT IGNORE INTO `galleries` VALUES (8, NULL, NULL, 'Wedding'),
INSERT IGNORE INTO `galleries` VALUES (9, 8, 'surprise.jpg', 'Surprise Proposals'),
INSERT IGNORE INTO `galleries` VALUES (10, 8, 'engagement.jpg', 'Engagements'),
INSERT IGNORE INTO `galleries` VALUES (11, 8, 'wedding.jpg', 'Weddings'),
INSERT IGNORE INTO `galleries` VALUES (12, 8, 'night.jpg', 'Night Photography'),
INSERT IGNORE INTO `galleries` VALUES (13, 3, 'newborn-fav.jpg', 'Favorites'),
INSERT IGNORE INTO `galleries` VALUES (14, 3, 'home.jpg', 'At Your Home'),
INSERT IGNORE INTO `galleries` VALUES (15, 3, 'studio.jpg', 'Studio'),
INSERT IGNORE INTO `galleries` VALUES (16, NULL, NULL, 'Home Studio'),
INSERT IGNORE INTO `galleries` VALUES (17, 9, 'nat-harbor.jpg', 'National Harbor'),
INSERT IGNORE INTO `galleries` VALUES (18, 9, 'proposal-dc.jpg', 'DC Mall'),
INSERT IGNORE INTO `galleries` VALUES (19, 9, 'georgetown.jpg', 'Georgetown'),
INSERT IGNORE INTO `galleries` VALUES (20, 10, 'engagement-fav.jpg', 'Favorites'),
INSERT IGNORE INTO `galleries` VALUES (21, 10, 'engagement-dc.jpg', 'Washington DC'),
INSERT IGNORE INTO `galleries` VALUES (22, 10, 'old-town.jpg', 'Old Town Alexandria'),
INSERT IGNORE INTO `galleries` VALUES (23, 10, 'paint-war.jpg', 'Paint War'),
INSERT IGNORE INTO `galleries` VALUES (24, 11, 'wedding-fav.jpg', 'Favorites'),
INSERT IGNORE INTO `galleries` VALUES (25, 11, 'wedding-1.jpg', 'Wedding 1'),
INSERT IGNORE INTO `galleries` VALUES (26, 11, 'wedding-2.jpg', 'Wedding 2'),
INSERT IGNORE INTO `galleries` VALUES (27, 11, 'wedding-3.jpg', 'Wedding 3');
/*!40000 ALTER TABLE `galleries` ENABLE KEYS */;
UNLOCK TABLES;