--
-- Changes for bug 526700, hiding repacks from public searches
--

ALTER TABLE `repacks`
    ADD COLUMN `is_public` tinyint(2) NOT NULL default '1';
