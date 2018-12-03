SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `backups`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;

CREATE TABLE `backups` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `contents` text,
  `related_table` varchar (255),
  `related_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ip` char(42) DEFAULT NULL,
  `user_id` int(11) NULL,
  PRIMARY KEY (`id`),
  KEY `related` (`related_table`, `related_id`),
  CONSTRAINT `fk_backups_user_id` FOREIGN KEY (`user_id`) REFERENCES `users`(`user_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

/*!40101 SET character_set_client = @saved_cs_client */;

SET FOREIGN_KEY_CHECKS=1;