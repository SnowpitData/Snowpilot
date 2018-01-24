-- phpMyAdmin SQL Dump
-- version 4.0.10.7
-- http://www.phpmyadmin.net
--
-- Host: localhost:3306
-- Generation Time: Apr 25, 2016 at 11:59 AM
-- Server version: 5.6.29
-- PHP Version: 5.4.31

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Database: `jimurl_avscience_3_21_16`
--

-- --------------------------------------------------------

--
-- Table structure for table `download_table`
--

CREATE TABLE IF NOT EXISTS `download_table` (
  `TARGET` text,
  `LOCAL_TIME` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `SERIAL` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`SERIAL`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3776 ;

-- --------------------------------------------------------

--
-- Table structure for table `layers`
--

CREATE TABLE IF NOT EXISTS `layers` (
  `id` int(8) NOT NULL AUTO_INCREMENT COMMENT 'Primary Key',
  `pid` int(8) NOT NULL COMMENT 'foreign key to SERIAL',
  `startDepth` float(4,1) NOT NULL,
  `endDepth` float(4,1) NOT NULL,
  `layerNumber` int(3) NOT NULL,
  `waterContent` varchar(31) NOT NULL,
  `grainType1` varchar(63) NOT NULL,
  `grainType2` varchar(31) DEFAULT NULL,
  `grainSize1` float(3,1) NOT NULL,
  `grainSize2` float(3,1) DEFAULT NULL,
  `grainSizeUnits1` varchar(7) NOT NULL,
  `grainSizeUnits2` varchar(7) DEFAULT NULL,
  `grainSuffix1` varchar(3) NOT NULL,
  `grainSuffix2` varchar(3) DEFAULT NULL,
  `hardness1` varchar(7) NOT NULL,
  `hardness2` varchar(7) DEFAULT NULL,
  `hsuffix1` varchar(7) NOT NULL,
  `hsuffix2` varchar(7) DEFAULT NULL,
  `density1` float NOT NULL,
  `density2` float DEFAULT NULL,
  `fromTop` tinyint(4) NOT NULL,
  `multipleHardness` tinyint(4) DEFAULT NULL,
  `multipleDensity` tinyint(4) DEFAULT NULL,
  `multipleGrainType` tinyint(4) DEFAULT NULL,
  `multipleGrainSize` tinyint(4) DEFAULT NULL,
  `iLayerNumber` tinyint(4) DEFAULT NULL COMMENT 'Boolean, T if this is the layer of greatest concern',
  `iDepth` float(4,1) DEFAULT NULL COMMENT 'If this is the iLayer, this has the iDepth, which could be top/bottom/middle of layer',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=41937 ;

-- --------------------------------------------------------

--
-- Table structure for table `OCC_TABLE`
--

CREATE TABLE IF NOT EXISTS `OCC_TABLE` (
  `OCC_DATA` longtext,
  `OBS_DATE` date DEFAULT NULL,
  `TIMESTAMP` date DEFAULT NULL,
  `ELV_START` int(11) DEFAULT NULL,
  `ELV_DEPOSIT` int(11) DEFAULT NULL,
  `ASPECT` smallint(6) DEFAULT NULL,
  `TYPE` text,
  `TRIGGER_TYPE` text,
  `TRIGGER_CODE` text,
  `US_SIZE` text,
  `CDN_SIZE` decimal(2,1) DEFAULT NULL,
  `AVG_FRACTURE_DEPTH` int(11) DEFAULT NULL,
  `MAX_FRACTURE_DEPTH` int(11) DEFAULT NULL,
  `WEAK_LAYER_TYPE` text,
  `WEAK_LAYER_HARDNESS` text,
  `SNOW_PACK_TYPE` text,
  `FRACTURE_WIDTH` int(11) DEFAULT NULL,
  `FRACTURE_LENGTH` int(11) DEFAULT NULL,
  `AV_LENGTH` int(11) DEFAULT NULL,
  `AVG_START_ANGLE` smallint(6) DEFAULT NULL,
  `MAX_START_ANGLE` smallint(6) DEFAULT NULL,
  `MIN_START_ANGLE` smallint(6) DEFAULT NULL,
  `ALPHA_ANGLE` smallint(6) DEFAULT NULL,
  `DEPTH_DEPOSIT` int(11) DEFAULT NULL,
  `LOC_NAME` text,
  `LOC_ID` text,
  `STATE` text,
  `MTN_RANGE` text,
  `LAT` decimal(6,4) DEFAULT NULL,
  `LONGITUDE` decimal(7,4) DEFAULT NULL,
  `NORTH` tinyint(1) DEFAULT NULL,
  `WEST` tinyint(1) DEFAULT NULL,
  `USERNAME` text,
  `NAME` text,
  `SERIAL` int(11) NOT NULL AUTO_INCREMENT,
  `LOCAL_SERIAL` text,
  `SHARE` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`SERIAL`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=275 ;

-- --------------------------------------------------------

--
-- Table structure for table `PIT_TABLE`
--

CREATE TABLE IF NOT EXISTS `PIT_TABLE` (
  `PIT_DATA` longtext,
  `AIR_TEMP` decimal(3,1) DEFAULT NULL,
  `ASPECT` smallint(6) DEFAULT NULL,
  `CROWN_OBS` tinyint(1) DEFAULT NULL,
  `OBS_DATE` date DEFAULT NULL,
  `TIMESTAMP` date DEFAULT NULL,
  `INCLINE` smallint(6) DEFAULT NULL,
  `LOC_NAME` text,
  `LOC_ID` text,
  `STATE` text,
  `RANGE` text,
  `LAT` decimal(6,4) DEFAULT NULL,
  `LONGITUDE` decimal(7,4) DEFAULT NULL,
  `NORTH` tinyint(1) DEFAULT NULL,
  `WEST` tinyint(1) DEFAULT NULL,
  `ELEVATION` int(11) DEFAULT NULL,
  `USERNAME` text,
  `WINDLOADING` tinyint(1) DEFAULT NULL,
  `HASLAYERS` tinyint(1) DEFAULT NULL,
  `PIT_NAME` text,
  `SHARE` tinyint(1) DEFAULT NULL,
  `SERIAL` int(11) NOT NULL AUTO_INCREMENT,
  `LOCAL_SERIAL` text,
  `WINDLOAD` text,
  `PRECIP` text,
  `SKY_COVER` text,
  `WIND_SPEED` text,
  `WIND_DIR` text,
  `STABILITY` text,
  `OBS_DATETIME` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ACTIVITIES` longtext,
  `TEST_PIT` tinyint(1) DEFAULT NULL,
  `PLATFORM` varchar(50) DEFAULT NULL,
  `PIT_XML` longtext,
  `MTN_RANGE` text,
  `iLayerNumber` int(2) DEFAULT NULL,
  `iDepth` float(4,1) DEFAULT NULL,
  `skiAreaPit` tinyint(1) DEFAULT NULL,
  `aviPit` tinyint(1) DEFAULT NULL,
  `bcPit` tinyint(1) DEFAULT NULL,
  `heightOfSnowpack` decimal(4,1) DEFAULT NULL,
  `calculatedHoS` decimal(4,1) DEFAULT NULL,
  `measureFrom` enum('bottom','top') DEFAULT NULL,
  `sufacePen` int(3) DEFAULT NULL,
  `aviLoc` enum('crown','flank','other') DEFAULT NULL,
  `skiBoot` enum('ski','foot') DEFAULT NULL,
  `crownObs` tinyint(1) DEFAULT NULL,
  `pitNotes` varchar(8191) DEFAULT NULL,
  `surfacePen` int(4) DEFAULT NULL,
  `prof` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`SERIAL`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=28377 ;

-- --------------------------------------------------------

--
-- Table structure for table `PIT_TABLE_TRUNC`
--

CREATE TABLE IF NOT EXISTS `PIT_TABLE_TRUNC` (
  `AIR_TEMP` decimal(3,1) DEFAULT NULL,
  `ASPECT` smallint(6) DEFAULT NULL,
  `CROWN_OBS` tinyint(1) DEFAULT NULL,
  `OBS_DATE` date DEFAULT NULL,
  `TIMESTAMP` date DEFAULT NULL,
  `INCLINE` smallint(6) DEFAULT NULL,
  `LOC_NAME` text CHARACTER SET latin1,
  `LOC_ID` text CHARACTER SET latin1,
  `STATE` text CHARACTER SET latin1,
  `RANGE` text CHARACTER SET latin1,
  `LAT` decimal(6,4) DEFAULT NULL,
  `LONGITUDE` decimal(7,4) DEFAULT NULL,
  `NORTH` tinyint(1) DEFAULT NULL,
  `WEST` tinyint(1) DEFAULT NULL,
  `ELEVATION` int(11) DEFAULT NULL,
  `USERNAME` text CHARACTER SET latin1,
  `WINDLOADING` tinyint(1) DEFAULT NULL,
  `HASLAYERS` tinyint(1) DEFAULT NULL,
  `PIT_NAME` text CHARACTER SET latin1,
  `SHARE` tinyint(1) DEFAULT NULL,
  `SERIAL` int(11) NOT NULL DEFAULT '0',
  `LOCAL_SERIAL` text CHARACTER SET latin1,
  `WINDLOAD` text CHARACTER SET latin1,
  `PRECIP` text CHARACTER SET latin1,
  `SKY_COVER` text CHARACTER SET latin1,
  `WIND_SPEED` text CHARACTER SET latin1,
  `WIND_DIR` text CHARACTER SET latin1,
  `STABILITY` text CHARACTER SET latin1,
  `OBS_DATETIME` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `ACTIVITIES` longtext CHARACTER SET latin1,
  `TEST_PIT` tinyint(1) DEFAULT NULL,
  `MTN_RANGE` text CHARACTER SET latin1,
  `iLayerNumber` int(2) DEFAULT NULL,
  `iDepth` float(4,1) DEFAULT NULL,
  `skiAreaPit` tinyint(1) DEFAULT NULL,
  `aviPit` tinyint(1) DEFAULT NULL,
  `bcPit` tinyint(1) DEFAULT NULL,
  `heightOfSnowpack` decimal(4,1) DEFAULT NULL,
  `calculatedHoS` decimal(4,1) DEFAULT NULL,
  `measureFrom` enum('bottom','top') CHARACTER SET latin1 DEFAULT NULL,
  `sufacePen` int(3) DEFAULT NULL,
  `aviLoc` enum('crown','flank','other') CHARACTER SET latin1 DEFAULT NULL,
  `skiBoot` enum('ski','foot') CHARACTER SET latin1 DEFAULT NULL,
  `crownObs` tinyint(1) DEFAULT NULL,
  `pitNotes` varchar(8191) CHARACTER SET latin1 DEFAULT NULL,
  `surfacePen` int(4) DEFAULT NULL,
  `prof` tinyint(1) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `shear_tests`
--

CREATE TABLE IF NOT EXISTS `shear_tests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pid` int(11) NOT NULL COMMENT 'foreign key to Pit table primary Key: id',
  `code` varchar(4) NOT NULL,
  `ctScore` int(4) DEFAULT NULL,
  `ecScore` int(4) DEFAULT NULL,
  `score` varchar(10) DEFAULT NULL,
  `quality` varchar(4) DEFAULT NULL,
  `sdepth` decimal(4,1) DEFAULT NULL,
  `depthUnits` varchar(2) DEFAULT NULL,
  `comments` varchar(511) DEFAULT NULL,
  `dateString` date DEFAULT NULL,
  `releaseType` varchar(127) DEFAULT NULL,
  `s` varchar(255) DEFAULT NULL,
  `numberOfTaps` int(63) DEFAULT NULL,
  `lengthOfColumn` float(4,1) DEFAULT NULL,
  `fractureCat` varchar(63) DEFAULT NULL,
  `lengthOfCut` float(4,1) DEFAULT NULL,
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=16742 ;

-- --------------------------------------------------------

--
-- Table structure for table `webuser_table`
--

CREATE TABLE IF NOT EXISTS `webuser_table` (
  `USERNAME` text,
  `EMAIL` text,
  `PROF` tinyint(1) DEFAULT NULL,
  `AFFILIATION` text,
  `REAL_NAME` text,
  `SHARE_DATA` tinyint(1) DEFAULT NULL,
  `SUPERUSER` tinyint(1) DEFAULT NULL,
  `DATAUSER` tinyint(1) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
