--
-- Table structure for table `usage`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE IF NOT EXISTS `review_types` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`),
) ENGINE=InnoDB DEFAULT AUTO_INCREMENT=5 CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;