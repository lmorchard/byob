-- MySQL dump 10.9
--
-- Host: localhost    Database: repack2
-- ------------------------------------------------------
-- Server version	4.1.22-log

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
CREATE TABLE `products` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(32) NOT NULL default 'Firefox',
  `version` varchar(32) NOT NULL default '',
  `url` varchar(255) NOT NULL default '',
  `locales` varchar(255) NOT NULL default 'en-US',
  `disable_migration` tinyint(1) default '0',
  `created` datetime default NULL,
  `modified` datetime default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=17 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `products`
--

LOCK TABLES `products` WRITE;
/*!40000 ALTER TABLE `products` DISABLE KEYS */;
INSERT INTO `products` VALUES (1,'Firefox','2.0.0.2','http://stage.mozilla.org/pub/mozilla.org/firefox/nightly/2.0.0.2-candidates/rc5','af ar bg be ca cs da de el en-GB en-US es-AR es-ES eu fi fr fy-NL ga-IE gu-IN he hu it ja-JP-mac ja ka ko ku lt mk mn nb-NO nl nn-NO pa-IN pl pt-BR pt-PT ru sk sl sv-SE tr zh-CN zh-TW',1,'2007-04-11 18:17:04','2007-04-11 18:17:04'),(2,'Firefox','2.0.0.3','http://stage.mozilla.org/pub/mozilla.org/firefox/nightly/2.0.0.3-candidates/rc1/','af ar bg be ca cs da de el en-GB en-US es-AR es-ES eu fi fr fy-NL ga-IE gu-IN he hu it ja-JP-mac ja ka ko ku lt mk mn nb-NO nl nn-NO pa-IN pl pt-BR pt-PT ru sk sl sv-SE tr zh-CN zh-TW',1,'2007-04-11 18:17:06','2007-04-11 18:17:06'),(3,'Firefox','2.0.0.4','http://stage.mozilla.org/pub/mozilla.org/firefox/nightly/2.0.0.4-candidates/rc3/','af ar bg be ca cs da de el en-GB en-US es-AR es-ES eu fi fr fy-NL ga-IE gu-IN he hu it ja-JP-mac ja ka ko ku lt mk mn nb-NO nl nn-NO pa-IN pl pt-BR pt-PT ro ru sk sl sv-SE tr zh-CN zh-TW',0,'2007-06-20 20:44:20','2007-06-20 20:44:20'),(4,'Firefox','2.0.0.5','http://stage.mozilla.org/pub/mozilla.org/firefox/nightly/2.0.0.5-candidates/rc2/','af ar bg be ca cs da de el en-GB en-US es-AR es-ES eu fi fr fy-NL ga-IE gu-IN he hu it ja-JP-mac ja ka ko ku lt mk mn nb-NO nl nn-NO pa-IN pl pt-BR pt-PT ro ru sk sl sv-SE tr zh-CN zh-TW',0,'2007-07-18 15:42:46','2007-07-18 15:42:46'),(5,'Firefox','2.0.0.6','http://stage.mozilla.org/pub/mozilla.org/firefox/nightly/2.0.0.6-candidates/rc2/','af ar bg be ca cs da de el en-GB en-US es-AR es-ES eu fi fr fy-NL ga-IE gu-IN he hu it ja-JP-mac ja ka ko ku lt mk mn nb-NO nl nn-NO pa-IN pl pt-BR pt-PT ro ru sk sl sv-SE tr zh-CN zh-TW',0,'2007-08-01 16:06:05','2007-08-01 16:06:05'),(6,'Firefox','2.0.0.7','http://stage.mozilla.org/pub/mozilla.org/firefox/nightly/2.0.0.7-candidates/rc2/','af ar bg be ca cs da de el en-GB en-US es-AR es-ES eu fi fr fy-NL ga-IE gu-IN he hu it ja-JP-mac ja ka ko ku lt mk mn nb-NO nl nn-NO pa-IN pl pt-BR pt-PT ro ru sk sl sv-SE tr zh-CN zh-TW',0,'2007-09-19 14:32:42','2007-09-19 14:32:42'),(7,'Firefox','2.0.0.9','http://stage.mozilla.org/pub/mozilla.org/firefox/nightly/2.0.0.9-candidates/rc1/','af ar bg be ca cs da de el en-GB en-US es-AR es-ES eu fi fr fy-NL ga-IE gu-IN he hu it ja-JP-mac ja ka ko ku lt mk mn nb-NO nl nn-NO pa-IN pl pt-BR pt-PT ro ru sk sl sv-SE tr zh-CN zh-TW',0,'2007-11-02 17:26:34','2007-11-02 17:26:34'),(8,'Firefox','2.0.0.11','http://stage.mozilla.org/pub/mozilla.org/firefox/nightly/2.0.0.11-candidates/rc1/','af ar be bg ca cs da de el en-GB en-US es-AR es-ES eu fi fr fy-NL ga-IE gu-IN he hu it ja-JP-mac ja ka ko ku lt mk mn nb-NO nl nn-NO pa-IN pl pt-BR pt-PT ro ru sk sl sv-SE tr uk zh-CN zh-TW',0,'2007-12-03 09:23:54','2007-12-03 09:23:54'),(9,'Firefox','2.0.0.12','http://stage.mozilla.org/pub/mozilla.org/firefox/nightly/2.0.0.12-candidates/rc4/','af ar be bg ca cs da de el en-GB en-US es-AR es-ES eu fi fr fy-NL ga-IE gu-IN he hu it ja-JP-mac ja ka ko ku lt mk mn nb-NO nl nn-NO pa-IN pl pt-BR pt-PT ro ru sk sl sv-SE tr uk zh-CN zh-TW',0,'2008-02-08 05:44:11','2008-02-08 05:44:11'),(10,'Firefox','2.0.0.13','http://stage.mozilla.org/pub/mozilla.org/firefox/nightly/2.0.0.13-candidates/rc1/','af ar be bg ca cs da de el en-GB en-US es-AR es-ES eu fi fr fy-NL ga-IE gu-IN he hu it ja-JP-mac ja ka ko ku lt mk mn nb-NO nl nn-NO pa-IN pl pt-BR pt-PT ro ru sk sl sv-SE tr uk zh-CN zh-TW',0,'2008-03-25 10:38:46','2008-03-25 10:38:46'),(11,'Firefox','2.0.0.14','http://stage.mozilla.org/pub/mozilla.org/firefox/nightly/2.0.0.14-candidates/rc1/','af ar be bg ca cs da de el en-GB en-US es-AR es-ES eu fi fr fy-NL ga-IE gu-IN he hu it ja-JP-mac ja ka ko ku lt mk mn nb-NO nl nn-NO pa-IN pl pt-BR pt-PT ro ru sk sl sv-SE tr uk zh-CN zh-TW',0,'2008-04-16 07:21:15','2008-04-16 07:21:15'),(12,'Firefox RC3','3.0','http://stage.mozilla.org/pub/mozilla.org/firefox/nightly/3.0rc3-candidates/build1/','af ar be ca cs da de el en-GB en-US es-AR es-ES eu fi fr fy-NL ga-IE gu-IN he hu id it ja-JP-mac ja ka ko ku lt mk mn nb-NO nl nn-NO pa-IN pl pt-BR pt-PT ro ru si sk sl sq sr sv-SE tr uk zh-CN zh-TW',0,'2008-06-12 09:06:27','2008-06-12 09:06:27'),(13,'Firefox','2.0.0.16','http://stage.mozilla.org/pub/mozilla.org/firefox/nightly/2.0.0.16-candidates/build1/','af ar be bg ca cs da de el en-GB en-US es-AR es-ES eu fi fr fy-NL ga-IE gu-IN he hu it ja-JP-mac ja ka ko ku lt mk mn nb-NO nl nn-NO pa-IN pl pt-BR pt-PT ro ru sk sl sv-SE tr uk zh-CN zh-TW',0,'2008-08-21 06:39:23','2008-08-21 06:39:23'),(14,'Firefox','2.0.0.19','http://stage.mozilla.org/pub/mozilla.org/firefox/nightly/2.0.0.19-candidates/build2/','af ar be bg ca cs da de el en-GB en-US es-AR es-ES eu fi fr fy-NL ga-IE gu-IN he hu it ja-JP-mac ja ka ko ku lt mk mn nb-NO nl nn-NO pa-IN pl pt-BR pt-PT ro ru sk sl sv-SE tr uk zh-CN zh-TW',0,'2008-12-29 10:55:52','2008-12-29 10:55:52'),(15,'Firefox','3.0.5','http://stage.mozilla.org/pub/mozilla.org/firefox/nightly/3.0.5-candidates/build1','af ar be bn-IN ca cs da de el en-GB en-US eo es-AR es-ES et eu fi fr fy-NL ga-IE gl he hi-IN hu id is it ja-JP-mac ja ka kn ko ku lt lv mk mn nb-NO nl nn-NO pa-IN pl pt-BR pt-PT ro ru si sk sl sq sr sv-SE te th tr uk zh-CN zh-TW',0,'2008-12-29 11:05:50','2008-12-29 11:05:50'),(16,'Firefox','3.0.7','http://stage.mozilla.org/pub/mozilla.org/firefox/nightly/3.0.7-candidates/build2','af ar be bn-IN ca cs da de el en-GB en-US eo es-AR es-ES et eu fi fr fy-NL ga-IE gl he hi-IN hu id is it ja-JP-mac ja ka kn ko ku lt lv mk mn nb-NO nl nn-NO pa-IN pl pt-BR pt-PT ro ru si sk sl sq sr sv-SE te th tr uk zh-CN zh-TW',0,'2009-03-20 09:10:13','2009-03-20 09:10:13');
/*!40000 ALTER TABLE `products` ENABLE KEYS */;
UNLOCK TABLES;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

