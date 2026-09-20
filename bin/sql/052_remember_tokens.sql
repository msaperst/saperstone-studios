CREATE TABLE IF NOT EXISTS `remember_tokens` (
  `selector` char(32) COLLATE utf8_bin NOT NULL,
  `user` int(11) NOT NULL,
  `token_hash` char(64) COLLATE utf8_bin NOT NULL,
  `expires_at` timestamp NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_used_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`selector`),
  KEY `remember_tokens_user` (`user`),
  KEY `remember_tokens_expires` (`expires_at`),
  CONSTRAINT `remember_tokens_user_fk` FOREIGN KEY (`user`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
