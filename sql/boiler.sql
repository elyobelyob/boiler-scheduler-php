
SET NAMES utf8;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
--  Table structure for `configuration`
-- ----------------------------
DROP TABLE IF EXISTS `configuration`;
CREATE TABLE `configuration` (
  `key` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `value` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `type` enum('int','string','boolean','long') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'string',
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
--  Records of `configuration`
-- ----------------------------
BEGIN;
INSERT INTO `configuration` VALUES ('boostTime', '60', 'int'), ('holidayFrom', '1361404800000', 'long'), ('holidayTemp', '8', 'int'), ('holidayUntil', '1362009600000', 'long');
COMMIT;

-- ----------------------------
--  Table structure for `override`
-- ----------------------------
DROP TABLE IF EXISTS `override`;
CREATE TABLE `override` (
  `id` int(5) NOT NULL,
  `type` varchar(75) DEFAULT NULL,
  `date` datetime DEFAULT NULL,
  `length` int(5) DEFAULT NULL,
  `boost` int(1) DEFAULT '1',
  `enabled` int(1) DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
--  Records of `override`
-- ----------------------------
BEGIN;
INSERT INTO `override` VALUES ('1', 'heat', '2013-02-13 14:20:01', '120', '1', '1');
COMMIT;

-- ----------------------------
--  Table structure for `schedule`
-- ----------------------------
DROP TABLE IF EXISTS `schedule`;
CREATE TABLE `schedule` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `group` smallint(6) unsigned NOT NULL,
  `day` int(2) NOT NULL,
  `timeOn` time NOT NULL,
  `timeOff` time NOT NULL,
  `heatingOn` tinyint(1) NOT NULL,
  `heatingTemp` int(2) NOT NULL DEFAULT '0',
  `waterOn` tinyint(1) NOT NULL,
  `enabled` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `group` (`group`),
  KEY `day` (`day`),
  KEY `hourOn` (`timeOn`),
  CONSTRAINT `schedule_ibfk_1` FOREIGN KEY (`group`) REFERENCES `schedule_groups` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=98 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
--  Records of `schedule`
-- ----------------------------
BEGIN;
INSERT INTO `schedule` VALUES ('58', '1', '2', '05:00:00', '07:00:00', '1', '18', '0', '0'), ('59', '1', '3', '05:00:00', '07:00:00', '1', '18', '0', '0'), ('60', '1', '4', '05:00:00', '07:00:00', '1', '18', '0', '0'), ('61', '1', '5', '05:00:00', '07:00:00', '1', '18', '0', '0'), ('62', '1', '6', '05:00:00', '07:00:00', '1', '18', '0', '0'), ('63', '3', '1', '19:00:00', '23:00:00', '1', '18', '0', '0'), ('64', '1', '4', '19:00:00', '23:00:00', '1', '18', '0', '0'), ('65', '1', '2', '19:00:00', '23:00:00', '1', '18', '0', '0'), ('66', '1', '3', '19:00:00', '23:00:00', '1', '18', '0', '0'), ('67', '3', '7', '07:00:00', '10:00:00', '1', '18', '0', '0'), ('68', '3', '7', '19:00:00', '23:00:00', '1', '18', '0', '0'), ('69', '1', '5', '19:00:00', '23:00:00', '1', '18', '0', '0'), ('70', '1', '6', '19:00:00', '23:00:00', '1', '18', '0', '0'), ('71', '3', '1', '07:00:00', '10:00:00', '1', '18', '0', '0'), ('97', '1', '3', '05:00:00', '07:00:00', '1', '18', '0', '0');
COMMIT;

-- ----------------------------
--  Table structure for `schedule_groups`
-- ----------------------------
DROP TABLE IF EXISTS `schedule_groups`;
CREATE TABLE `schedule_groups` (
  `id` smallint(6) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
--  Records of `schedule_groups`
-- ----------------------------
BEGIN;
INSERT INTO `schedule_groups` VALUES ('1', 'Weekdays'), ('3', 'Weekend');
COMMIT;

SET FOREIGN_KEY_CHECKS = 1;
