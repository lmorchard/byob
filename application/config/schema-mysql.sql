DROP TABLE IF EXISTS `repacks`;
CREATE TABLE `repacks` (
    `id` int(10) unsigned NOT NULL auto_increment,
    `uuid` varchar(64) NOT NULL default '0',
    `created` datetime default NULL,
    `modified` datetime default NULL,
    `created_by` int(11) unsigned NOT NULL,
    `approved_by` int(11) unsigned default NULL,
    `approved_on` datetime default NULL,
    `short_name` varchar(128) default NULL,
    `title` varchar(255) default NULL,
    `description` text default NULL,
    `category` varchar(64) default NULL,
    `json_data` text,
    PRIMARY KEY  (`id`),
    KEY `created_by` (`created_by`),
    KEY `approval` (`approved_on`,`approved_by`),
    UNIQUE KEY `uniq_uuid` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

