-- Joli Bois site database
-- Hostinger: select u889201362_joli_bois, then run this.
-- Local: CREATE DATABASE joli_bois; USE joli_bois; then run this.

CREATE TABLE IF NOT EXISTS `plant_active_power` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date` datetime NOT NULL,
  `meter_id` int(11) NOT NULL,
  `meter_name` varchar(15) NOT NULL,
  `active_power` float NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `plant_irradiance` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date` datetime NOT NULL,
  `irradiance` float NOT NULL,
  `insolation` double NOT NULL,
  `ambient_temp` float NOT NULL,
  `panel_temp` float NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `tbl_group` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `group_name` varchar(200) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `tbl_client` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `client_name` varchar(200) NOT NULL,
  `group_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `tbl_hourly_prod` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `meter_id` int(11) NOT NULL,
  `datetime` datetime NOT NULL,
  `meter_name` varchar(15) NOT NULL,
  `starting_datetime` datetime NOT NULL,
  `ending_datetime` datetime NOT NULL,
  `production` float NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `tbl_main_FAP` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date` datetime NOT NULL,
  `dev_eui` varchar(20) NOT NULL,
  `status` tinyint(4) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `tbl_main_meter` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date` datetime NOT NULL,
  `total_active_energy` float NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `tbl_meters` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `meter_name` varchar(30) NOT NULL,
  `address` int(11) NOT NULL,
  `header` int(11) NOT NULL,
  `controller_eui` varchar(50) NOT NULL,
  `client_id` int(11) NOT NULL,
  `device_type` varchar(30) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `tbl_sub_meters` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date` datetime NOT NULL,
  `meter_id` int(11) NOT NULL,
  `meter_name` varchar(50) NOT NULL,
  `active_power` double NOT NULL,
  `power_factor` double NOT NULL,
  `total_active_energy` double NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `tbl_group` (`id`, `group_name`) VALUES (1, 'Ecoasis')
  ON DUPLICATE KEY UPDATE `group_name` = VALUES(`group_name`);

INSERT INTO `tbl_client` (`id`, `client_name`, `group_id`) VALUES (1, 'Joli Bois', 1)
  ON DUPLICATE KEY UPDATE `client_name` = VALUES(`client_name`);

INSERT INTO `tbl_meters` (`id`, `meter_name`, `address`, `header`, `controller_eui`, `client_id`, `device_type`) VALUES
  (1,   'METER 1',    1,   0, '', 1, 'Modbus sub-meter'),
  (2,   'METER 2',    2,   0, '', 1, 'Modbus sub-meter'),
  (3,   'METER 3',    3,   0, '', 1, 'Modbus sub-meter'),
  (4,   'METER 4',    4,   0, '', 1, 'Modbus sub-meter'),
  (5,   'METER 5',    5,   0, '', 1, 'Modbus sub-meter'),
  (6,   'METER 6',    6,   0, '', 1, 'Modbus sub-meter'),
  (7,   'METER 7',    7,   0, '', 1, 'Modbus sub-meter'),
  (8,   'MAIN LV',    8,   0, '', 1, 'Modbus main (LV)'),
  (100, 'MAIN METER', 100, 0, '', 1, 'Milesight UC300')
ON DUPLICATE KEY UPDATE
  `meter_name` = VALUES(`meter_name`),
  `address` = VALUES(`address`),
  `device_type` = VALUES(`device_type`);
