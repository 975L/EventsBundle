/*
 * (c) 2017: 975l <contact@975l.com>
 * (c) 2017: Laurent Marquet <laurent.marquet@laposte.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for events
-- ----------------------------
-- DROP TABLE IF EXISTS `events`;
CREATE TABLE `events` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `suppressed` bit(1) NULL,
  `title` varchar(128) NOT NULL,
  `slug` varchar(128) NOT NULL,
  `start_date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_date` date NOT NULL,
  `end_time` time NOT NULL,
  `place` varchar(256) NOT NULL,
  `description` blob DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;