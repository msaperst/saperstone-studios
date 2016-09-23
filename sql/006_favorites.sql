--
-- Table structure for table `favorites`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE IF NOT EXISTS `favorites` (
  `user` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `album` int(11) NOT NULL,
  `image` int(11) NOT NULL,
  UNIQUE KEY `unique_index` (`user`,`album`,`image`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;