--
-- Dumping data for table `galleries`
--

LOCK TABLES `galleries` WRITE;
/*!40000 ALTER TABLE `galleries` DISABLE KEYS */;
-- INSERT IGNORE INTO `galleries` VALUES (0, NULL, NULL, 'Leigh Ann');
INSERT IGNORE INTO `galleries` VALUES (1, NULL, NULL, 'Portrait');
INSERT IGNORE INTO `galleries` VALUES (2, 1, 'maternity.jpg', 'Maternity');
INSERT IGNORE INTO `galleries` VALUES (3, 1, 'newborn.jpg', 'Newborn');
INSERT IGNORE INTO `galleries` VALUES (4, 1, '6-month.jpg', '6 Months');
INSERT IGNORE INTO `galleries` VALUES (5, 1, '1-year.jpg', 'First Birthday');
INSERT IGNORE INTO `galleries` VALUES (6, 1, 'family.jpg', 'Kids and Family');
INSERT IGNORE INTO `galleries` VALUES (7, 1, 'senior.jpg', 'Seniors');
INSERT IGNORE INTO `galleries` VALUES (8, NULL, NULL, 'Wedding');
INSERT IGNORE INTO `galleries` VALUES (9, 8, 'surprise.jpg', 'Surprise Proposals');
INSERT IGNORE INTO `galleries` VALUES (10, 8, 'engagement.jpg', 'Engagements');
INSERT IGNORE INTO `galleries` VALUES (11, 8, 'wedding.jpg', 'Weddings');
INSERT IGNORE INTO `galleries` VALUES (12, 8, 'night.jpg', 'Night Photography');
INSERT IGNORE INTO `galleries` VALUES (13, 3, 'newborn-fav.jpg', 'Favorites');
INSERT IGNORE INTO `galleries` VALUES (14, 3, 'home.jpg', 'At Your Home');
INSERT IGNORE INTO `galleries` VALUES (15, 3, 'studio.jpg', 'Studio');
INSERT IGNORE INTO `galleries` VALUES (16, NULL, NULL, 'Home Studio');
INSERT IGNORE INTO `galleries` VALUES (17, 9, 'nat-harbor.jpg', 'National Harbor');
INSERT IGNORE INTO `galleries` VALUES (18, 9, 'proposal-dc.jpg', 'DC Mall');
INSERT IGNORE INTO `galleries` VALUES (19, 9, 'georgetown.jpg', 'Georgetown');
INSERT IGNORE INTO `galleries` VALUES (20, 10, 'engagement-fav.jpg', 'Favorites');
INSERT IGNORE INTO `galleries` VALUES (21, 10, 'engagement-dc.jpg', 'Washington DC');
INSERT IGNORE INTO `galleries` VALUES (22, 10, 'old-town.jpg', 'Old Town Alexandria');
INSERT IGNORE INTO `galleries` VALUES (23, 10, 'paint-war.jpg', 'Paint War');
INSERT IGNORE INTO `galleries` VALUES (24, 11, 'wedding-fav.jpg', 'Favorites');
INSERT IGNORE INTO `galleries` VALUES (25, 11, 'wedding-1.jpg', 'Wedding 1');
INSERT IGNORE INTO `galleries` VALUES (26, 11, 'wedding-2.jpg', 'Wedding 2');
INSERT IGNORE INTO `galleries` VALUES (27, 11, 'wedding-3.jpg', 'Wedding 3');
INSERT IGNORE INTO `galleries` VALUES (28, 1, NULL, 'Products');
INSERT IGNORE INTO `galleries` VALUES (29, 28, 'story-grid.jpg', 'Story Grids');
INSERT IGNORE INTO `galleries` VALUES (30, 28, 'keepsake-album.jpg', 'Heirloom Albums');
INSERT IGNORE INTO `galleries` VALUES (31, 28, 'acrylic-print.jpg', 'Acrylic Prints');
INSERT IGNORE INTO `galleries` VALUES (32, 28, 'keepsake-box.jpg', 'Keepsake Boxes');
INSERT IGNORE INTO `galleries` VALUES (33, 28, 'stand-out-frame.jpg', 'Stand Out Frames');
INSERT IGNORE INTO `galleries` VALUES (34, 28, 'canvas-print.jpg', 'Canvas Prints');
INSERT IGNORE INTO `galleries` VALUES (35, 4, '6-month-studio.jpg', 'In Studio');
INSERT IGNORE INTO `galleries` VALUES (36, 4, '6-month-location.jpg', 'On Location');
INSERT IGNORE INTO `galleries` VALUES (37, 8, 'photobooth.jpg', 'Photobooth');
INSERT IGNORE INTO `galleries` VALUES (38, 8, NULL, 'Products'),
INSERT IGNORE INTO `galleries` VALUES (39, 38, 'story-grid.jpg', 'Story Grids'),
INSERT IGNORE INTO `galleries` VALUES (40, 38, 'keepsake-album.jpg', 'Heirloom Albums'),
INSERT IGNORE INTO `galleries` VALUES (41, 38, 'acrylic-print.jpg', 'Acrylic Prints'),
INSERT IGNORE INTO `galleries` VALUES (42, 38, 'keepsake-box.jpg', 'Keepsake Boxes'),
INSERT IGNORE INTO `galleries` VALUES (43, 38, 'stand-out-frame.jpg', 'Stand Out Frames'),
INSERT IGNORE INTO `galleries` VALUES (44, 38, 'canvas-print.jpg', 'Canvas Prints');
INSERT IGNORE INTO `galleries` VALUES (45, 40, 'standard-albums.jpg', 'Standard Albums');
INSERT IGNORE INTO `galleries` VALUES (46, 40, 'signature-albums.jpg', 'Signature Albums');

/*!40000 ALTER TABLE `galleries` ENABLE KEYS */;
UNLOCK TABLES;