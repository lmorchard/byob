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
