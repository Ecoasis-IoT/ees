-- EES admin / central database — register Joli Bois
-- Hostinger test: select u889201362_testees, then run this.
-- Hostinger prod: select u889201362_ees_pv, then run this.
-- Local: USE ees; then run this.

INSERT INTO `tbl_site`
  (`site_name`, `db_name`, `capacity`, `location`, `commissioned`, `num_pvdb`, `main_meter`)
SELECT
  'Moka City 2 Joli Bois', 'joli_bois.php', '2000', '', 1, 1, 100
WHERE NOT EXISTS (
  SELECT 1 FROM `tbl_site` WHERE `db_name` IN ('joli_bois.php', 'joli_bois')
);

-- Display names: Name then Region
UPDATE `tbl_site` SET `site_name` = 'Moka City 1 (Moka)'
  WHERE `db_name` IN ('moka_city.php', 'moka_city');

UPDATE `tbl_site` SET `site_name` = 'Heritage Telfaire (Case Noyal)'
  WHERE `db_name` IN ('case_noyal.php', 'case_noyal');

UPDATE `tbl_site` SET `site_name` = 'Moka City 2 (Joli Bois)'
  WHERE `db_name` IN ('joli_bois.php', 'joli_bois');
