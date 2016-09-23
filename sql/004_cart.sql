--
-- Table structure for table `cart`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE IF NOT EXISTS `cart` (
  `user` int(11) NOT NULL,
  `album` int(11) NOT NULL,
  `image` int(11) NOT NULL,
  `product` int(11) NOT NULL,
  `count` int(11) NOT NULL,
  UNIQUE KEY `unique_index` (`user`,`album`,`image`,`product`,`count`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;