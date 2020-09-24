-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               10.4.13-MariaDB - mariadb.org binary distribution
-- Server OS:                    Win64
-- HeidiSQL Version:             11.0.0.5919
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

-- Dumping structure for table phhsystem.production_weight_2008
CREATE TABLE IF NOT EXISTS `production_weight_2008` (
  `wid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `qid` int(10) unsigned NOT NULL,
  `quono` varchar(20) NOT NULL,
  `company` varchar(10) NOT NULL DEFAULT 'PST',
  `cid` int(10) unsigned NOT NULL,
  `quantity` int(10) NOT NULL,
  `grade` varchar(30) NOT NULL,
  `dimension` varchar(50) DEFAULT NULL,
  `process` varchar(20) DEFAULT NULL,
  `cuttingtype` varchar(20) DEFAULT NULL,
  `cncmach` decimal(20,2) DEFAULT NULL,
  `noposition` int(10) unsigned NOT NULL,
  `runningno` varchar(4) NOT NULL,
  `jobno` varchar(3) NOT NULL,
  `date_issue` date NOT NULL,
  `completion_date` date NOT NULL,
  `dateofcompletion` date DEFAULT NULL,
  `jlfor` varchar(5) NOT NULL,
  `status` varchar(10) NOT NULL,
  `date_start` date DEFAULT NULL,
  `staffname` varchar(50) DEFAULT NULL,
  `machineModel` varchar(50) DEFAULT NULL,
  `model` varchar(20) DEFAULT NULL,
  `packing` datetime DEFAULT NULL,
  `operation` int(3) NOT NULL DEFAULT 1,
  `unit_weight` decimal(10,2) NOT NULL DEFAULT 1.00,
  `total_weight` decimal(10,2) NOT NULL DEFAULT 1.00,
  `index_per_shift` decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`wid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Dumping data for table phhsystem.production_weight_2008: ~4,320 rows (approximately)
/*!40000 ALTER TABLE `production_weight_2008` DISABLE KEYS */;
/*!40000 ALTER TABLE `production_weight_2008` ENABLE KEYS */;

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
