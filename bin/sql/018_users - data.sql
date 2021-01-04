--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT IGNORE INTO `users` VALUES 
        (0,'<i>All Users</i>','','','','','admin','',0,now(),null,null);
UPDATE `users` SET `id` = 0 WHERE `usr` = '<i>All Users</i>';
INSERT IGNORE INTO `users` VALUES 
        (1,'msaperst','968464ea59cee5de1581ee9a3d039c5e','Max','Saperstone','msaperst@gmail.com','admin','1d7505e7f434a7713e84ba399e937191',1,now(),null,null);
INSERT IGNORE INTO `users` VALUES 
        (2,'lsaperst','99cac6be874ee7b7087a09bc6dbc527b','Leigh Ann','Saperstone','la@saperstonestudios.com','admin','398702d7c97b7fcf8aa31379bab01828',1,now(),null,null);
INSERT IGNORE INTO `users` VALUES 
        (3,'downloader','5f4dcc3b5aa765d61d8327deb882cf99','Download','User','email@example.org','downloader','5510b5e6fffd897c234cafe499f76146',1,now(),null,null);
INSERT IGNORE INTO `users` VALUES 
        (4,'uploader','5f4dcc3b5aa765d61d8327deb882cf99','Upload','User','uploader@example.org','uploader','c90788c0e409eac6a95f6c6360d8dbf7',1,now(),null,null);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;