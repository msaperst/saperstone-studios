--
-- Table structure for table `product_options`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE IF NOT EXISTS `product_options` (
  `product_type` int(11) NOT NULL,
  `opt` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  UNIQUE KEY `unique_index` (`product_type`,`opt`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;