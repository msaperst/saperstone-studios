--
-- Table structure for table `blog_details`
--

CREATE TABLE IF NOT EXISTS `blog_details` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `date` date NOT NULL,
  `preview` varchar(255) NOT NULL,
  `offset` int(11) NOT NULL
  UNIQUE KEY `id` (`id`),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;