--
-- Table structure for table `contracts`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE IF NOT EXISTS `contracts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `link` varchar(255) NOT NULL,
  `type` enum('wedding','portrait','commercial','contractor','event','partnership','other') NOT NULL,
  `name` text NOT NULL,
  `address` text,
  `number` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `date` date DEFAULT NULL,
  `location` text,
  `session` varchar(255) NOT NULL,
  `details` text DEFAULT NULL,
  `amount` decimal(10,0) NOT NULL DEFAULT '0',
  `deposit` decimal(10,0) NOT NULL DEFAULT '0',
  `invoice` varchar(255) DEFAULT NULL,
  `content` text NOT NULL,
  `signature` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;