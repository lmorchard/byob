DROP TABLE IF EXISTS `products`;
CREATE TABLE `products` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(32) NOT NULL default 'Firefox',
  `version` varchar(32) NOT NULL default '',
  `build` int(11) default NULL,
  `locales` text,
  `disable_migration` tinyint(1) default '0',
  `created` datetime default NULL,
  `modified` datetime default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=24 DEFAULT CHARSET=latin1;

LOCK TABLES `products` WRITE;
/*!40000 ALTER TABLE `products` DISABLE KEYS */;
INSERT INTO `products` VALUES (20,'Firefox','3.5rc2',2,'en-US',0,'2009-06-16 00:00:00',NULL),(21,'Firefox','3.5rc3',2,'en-US',0,'2009-06-24 00:00:00',NULL),(22,'Firefox','3.5.1',1,'af ar as be bg bn-BD bn-IN ca cs cy da de el en-GB en-US eo es-AR es-CL es-ES es-MX et eu fa fi fr fy-NL ga-IE gl gu-IN he hi-IN hr hu id is it ja-JP-mac ka kk kn ko ku lt lv mk ml mn mr nb-NO nl nn-NO oc or pa-IN pl pt-BR pt-PT rm ro ru si sk sl sq sr sv-SE ta-LK ta te th tr uk vi xpi zh-CN zh-TW',0,'2009-07-21 20:24:33','2009-07-21 20:24:33'),(23,'Tomcats-bla','1.0',NULL,'de',NULL,'2009-07-29 17:13:41','2009-07-29 17:13:41');
/*!40000 ALTER TABLE `products` ENABLE KEYS */;
UNLOCK TABLES;
