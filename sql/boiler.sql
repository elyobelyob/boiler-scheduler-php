/*
 Navicat MySQL Data Transfer

 Source Server         : 192.168.1.87
 Source Server Version : 50528
 Source Host           : localhost
 Source Database       : boiler

 Target Server Version : 50528
 File Encoding         : utf-8

 Date: 03/06/2013 21:42:42 PM
*/

SET NAMES utf8;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
--  Table structure for `configuration`
-- ----------------------------
DROP TABLE IF EXISTS `configuration`;
CREATE TABLE `configuration` (
  `key` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `value` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `type` enum('int','string','boolean','long') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'string'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=COMPACT;

-- ----------------------------
--  Records of `configuration`
-- ----------------------------
BEGIN;
INSERT INTO `configuration` VALUES ('boostTime', '60', 'int'), ('holidayFrom', '1362268800', 'long'), ('holidayTemp', '8', 'int'), ('holidayTo', '1362349140', 'long');
COMMIT;

-- ----------------------------
--  Table structure for `override`
-- ----------------------------
DROP TABLE IF EXISTS `override`;
CREATE TABLE `override` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `type` varchar(75) DEFAULT NULL,
  `date` datetime DEFAULT NULL,
  `length` int(5) DEFAULT NULL,
  `boost` int(1) DEFAULT '1',
  `enabled` int(1) DEFAULT '1',
  `heatingTemp` int(2) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 ROW_FORMAT=COMPACT;

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
) ENGINE=InnoDB AUTO_INCREMENT=98 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=COMPACT;

-- ----------------------------
--  Records of `schedule`
-- ----------------------------
BEGIN;
INSERT INTO `schedule` VALUES ('58', '1', '2', '05:00:00', '07:00:00', '1', '18', '0', '1'), ('60', '1', '4', '05:00:00', '07:00:00', '1', '18', '0', '1'), ('61', '1', '5', '05:00:00', '07:00:00', '1', '18', '0', '1'), ('62', '1', '6', '05:00:00', '07:00:00', '1', '18', '0', '1'), ('63', '3', '1', '19:00:00', '23:00:00', '1', '18', '0', '1'), ('64', '1', '4', '19:00:00', '23:00:00', '1', '18', '0', '1'), ('65', '1', '2', '19:00:00', '23:00:00', '1', '18', '0', '1'), ('66', '1', '3', '19:00:00', '23:00:00', '1', '18', '0', '1'), ('67', '3', '7', '07:00:00', '10:00:00', '1', '18', '0', '1'), ('68', '3', '7', '19:00:00', '23:00:00', '1', '18', '0', '1'), ('69', '1', '5', '19:00:00', '23:00:00', '1', '18', '0', '1'), ('70', '1', '6', '19:00:00', '23:00:00', '1', '18', '0', '1'), ('71', '3', '1', '07:00:00', '10:00:00', '1', '18', '0', '1'), ('97', '1', '3', '05:00:00', '07:00:00', '1', '18', '0', '1');
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
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=COMPACT;

-- ----------------------------
--  Records of `schedule_groups`
-- ----------------------------
BEGIN;
INSERT INTO `schedule_groups` VALUES ('1', 'Weekdays'), ('3', 'Weekend');
COMMIT;

SET FOREIGN_KEY_CHECKS = 1;
