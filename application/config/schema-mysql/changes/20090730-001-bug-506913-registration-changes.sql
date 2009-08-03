--
-- Changes for bug 506913 for registration reorg
--
ALTER TABLE `profiles`
    DROP COLUMN `org_address`,
    CHANGE COLUMN `full_name` `first_name` varchar(255),
    ADD COLUMN `last_name` varchar(128),
    ADD COLUMN `is_personal` tinyint(2) NOT NULL default '0',
    ADD COLUMN `address_1` varchar(255),
    ADD COLUMN `address_2` varchar(255),
    ADD COLUMN `city` varchar(255),
    ADD COLUMN `state` varchar(32),
    ADD COLUMN `zip` varchar(32),
    ADD COLUMN `country` varchar(255);
