--
-- Table structure for table `logevents`
--

DROP TABLE IF EXISTS `logevents`;
CREATE TABLE `logevents` (
  `id` int(11) NOT NULL auto_increment,
  `uuid` char(64) default NULL,
  `profile_id` int(11) default NULL,
  `action` varchar(255) default NULL,
  `details` text,
  `data` text,
  `created` datetime default NULL,
  PRIMARY KEY  (`id`),
  KEY `uuid` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `login_email_verification_tokens`
--

DROP TABLE IF EXISTS `login_email_verification_tokens`;
CREATE TABLE `login_email_verification_tokens` (
  `id` int(11) NOT NULL auto_increment,
  `login_id` int(11) default NULL,
  `token` varchar(32) default NULL,
  `value` varchar(255) default NULL,
  PRIMARY KEY  (`id`),
  KEY `login_email_verification_tokens_ibfk_2` (`login_id`),
  CONSTRAINT `login_email_verification_tokens_ibfk_2` FOREIGN KEY (`login_id`) REFERENCES `logins` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `login_password_reset_tokens`
--

DROP TABLE IF EXISTS `login_password_reset_tokens`;
CREATE TABLE `login_password_reset_tokens` (
  `id` int(11) NOT NULL auto_increment,
  `login_id` int(11) default NULL,
  `token` varchar(32) default NULL,
  PRIMARY KEY  (`id`),
  KEY `login_password_reset_tokens_ibfk_2` (`login_id`),
  CONSTRAINT `login_password_reset_tokens_ibfk_2` FOREIGN KEY (`login_id`) REFERENCES `logins` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `logins`
--

DROP TABLE IF EXISTS `logins`;
CREATE TABLE `logins` (
  `id` int(11) NOT NULL auto_increment,
  `login_name` varchar(64) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(32) NOT NULL,
  `created` datetime default NULL,
  `last_login` datetime default NULL,
  `active` tinyint(2) NOT NULL default '1',
  `modified` datetime default NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `login_name` (`login_name`),
  KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `logins_profiles`
--

DROP TABLE IF EXISTS `logins_profiles`;
CREATE TABLE `logins_profiles` (
  `id` int(11) NOT NULL auto_increment,
  `login_id` int(11) NOT NULL,
  `profile_id` int(11) NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `login_id_profile_id` (`login_id`,`profile_id`),
  KEY `logins_profiles_ibfk_2` (`profile_id`),
  CONSTRAINT `logins_profiles_ibfk_1` FOREIGN KEY (`login_id`) REFERENCES `logins` (`id`) ON DELETE CASCADE,
  CONSTRAINT `logins_profiles_ibfk_2` FOREIGN KEY (`profile_id`) REFERENCES `profiles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `message_queue`
--

DROP TABLE IF EXISTS `message_queue`;
CREATE TABLE `message_queue` (
  `uuid` varchar(40) NOT NULL,
  `owner` varchar(255) default NULL,
  `batch_uuid` varchar(40) default NULL,
  `batch_seq` int(11) default '0',
  `created` datetime default NULL,
  `modified` datetime default NULL,
  `scheduled_for` datetime default NULL,
  `reserved_at` datetime default NULL,
  `reserved_until` datetime default NULL,
  `finished_at` datetime default NULL,
  `priority` int(11) default '0',
  `topic` varchar(255) default NULL,
  `object` varchar(255) default NULL,
  `method` varchar(255) default NULL,
  `context` text,
  `body` text,
  `signature` char(32) default NULL,
  PRIMARY KEY  (`uuid`),
  KEY `created` (`created`),
  KEY `priority` (`priority`),
  KEY `batch_seq` (`batch_seq`),
  KEY `signature` (`signature`),
  KEY `reserved_at` (`reserved_at`),
  KEY `finished_at` (`finished_at`),
  KEY `scheduled_for` (`scheduled_for`),
  KEY `batch_uuid` (`batch_uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `posts`
--

DROP TABLE IF EXISTS `posts`;
CREATE TABLE `posts` (
  `id` int(11) NOT NULL auto_increment,
  `uuid` varchar(40) NOT NULL,
  `signature` varchar(40) NOT NULL,
  `profile_id` int(11) NOT NULL,
  `url_id` int(11) NOT NULL,
  `title` varchar(255) default NULL,
  `notes` text,
  `tags` text,
  `visibility` int(11) default NULL,
  `user_date` datetime default NULL,
  `created` datetime default NULL,
  `modified` datetime default NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `profile_id_url_id` (`profile_id`,`url_id`),
  UNIQUE KEY `uuid` (`uuid`),
  KEY `url_id` (`url_id`),
  KEY `user_date` (`user_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `profile_attributes`
--

DROP TABLE IF EXISTS `profile_attributes`;
CREATE TABLE `profile_attributes` (
  `id` int(11) NOT NULL auto_increment,
  `profile_id` int(11) NOT NULL,
  `name` varchar(255) default NULL,
  `value` text,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `profile_id_name` (`profile_id`,`name`),
  CONSTRAINT `profile_attributes_ibfk_1` FOREIGN KEY (`profile_id`) REFERENCES `profiles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `profiles`
--

DROP TABLE IF EXISTS `profiles`;
CREATE TABLE `profiles` (
  `id` int(11) NOT NULL auto_increment,
  `uuid` varchar(40) NOT NULL,
  `screen_name` varchar(64) NOT NULL,
  `full_name` varchar(128) NOT NULL,
  `created` datetime default NULL,
  `phone` varchar(24) default NULL,
  `fax` varchar(24) default NULL,
  `org_address` text,
  `org_name` varchar(255) default NULL,
  `org_type` varchar(32) default NULL,
  `org_type_other` varchar(32) default NULL,
  `modified` datetime default NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `uuid` (`uuid`),
  UNIQUE KEY `screen_name` (`screen_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `repacks`
--

DROP TABLE IF EXISTS `repacks`;
CREATE TABLE `repacks` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `uuid` char(64) NOT NULL default '0',
  `created` datetime default NULL,
  `modified` datetime default NULL,
  `profile_id` int(10) unsigned NOT NULL,
  `product_id` int(10) unsigned NOT NULL,
  `short_name` varchar(128) default NULL,
  `title` varchar(255) default NULL,
  `description` text,
  `state` int(11) default '0',
  `json_data` text,
  PRIMARY KEY  (`id`),
  KEY `created_by` (`profile_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
CREATE TABLE `roles` (
  `id` int(11) NOT NULL auto_increment,
  `profile_id` int(11) default NULL,
  `name` varchar(32) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `profiles_roles`
--

DROP TABLE IF EXISTS `profiles_roles`;
CREATE TABLE `profiles_roles` (
  `id` int(11) NOT NULL auto_increment,
  `role_id` int(11) NOT NULL,
  `profile_id` int(11) NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `role_id_profile_id` (`role_id`,`profile_id`),
  KEY `profiles_roles_ibfk_2` (`profile_id`),
  CONSTRAINT `profiles_roles_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  CONSTRAINT `profiles_roles_ibfk_2` FOREIGN KEY (`profile_id`) REFERENCES `profiles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
