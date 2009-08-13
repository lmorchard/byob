--
-- Changes for bug 510245 for further profile changes
--
ALTER TABLE `profiles`
    ADD COLUMN `is_personal` tinyint(2) NOT NULL default '1',
    ADD COLUMN `website` varchar(255);
