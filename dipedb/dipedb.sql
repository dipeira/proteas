-- phpMyAdmin SQL Dump
-- version 3.4.5
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Apr 06, 2012 at 10:37 AM
-- Server version: 5.5.16
-- PHP Version: 5.3.8

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES greek */;

--
-- Database: `dipedb`
--

-- --------------------------------------------------------

--
-- Table structure for table `adeia`
--

CREATE TABLE IF NOT EXISTS `adeia` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `emp_id` int(11) NOT NULL,
  `type` int(11) NOT NULL,
  `prot` int(11) NOT NULL COMMENT 'Αρ. Πρωτοκόλου',
  `hm_prot` date NOT NULL,
  `date` date NOT NULL COMMENT 'Ημ/νία  αίτησης',
  `vev_dil` tinyint(4) NOT NULL COMMENT 'Βεβαίωση / Δήλωση (για αναρρωτική Υπ.Δηλ.)',
  `days` int(11) NOT NULL COMMENT 'Διάρκεια σε ημέρες',
  `start` date NOT NULL,
  `finish` date NOT NULL,
  `logos` varchar(70) NOT NULL COMMENT 'Λόγος (για ειδικές)',
  `comments` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=greek AUTO_INCREMENT=11 ;

-- --------------------------------------------------------

--
-- Table structure for table `adeia_deleted`
--

CREATE TABLE IF NOT EXISTS `adeia_deleted` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `emp_id` int(11) NOT NULL,
  `type` int(11) NOT NULL,
  `prot` int(11) NOT NULL COMMENT 'Αρ. Πρωτοκόλου',
  `hm_prot` date NOT NULL,
  `date` date NOT NULL COMMENT 'Ημ/νία  αίτησης',
  `vev_dil` tinyint(4) NOT NULL COMMENT 'Βεβαίωση / Δήλωση (για αναρρωτική Υπ.Δηλ.)',
  `days` int(11) NOT NULL COMMENT 'Διάρκεια σε ημέρες',
  `start` date NOT NULL,
  `finish` date NOT NULL,
  `logos` varchar(70) NOT NULL COMMENT 'Λόγος (για ειδικές)',
  `comments` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=greek AUTO_INCREMENT=6 ;

-- --------------------------------------------------------

--
-- Table structure for table `adeia_del_log`
--

CREATE TABLE IF NOT EXISTS `adeia_del_log` (
  `adeia_id` int(11) NOT NULL,
  `userid` int(11) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`adeia_id`)
) ENGINE=InnoDB DEFAULT CHARSET=greek;

-- --------------------------------------------------------

--
-- Table structure for table `adeia_type`
--

CREATE TABLE IF NOT EXISTS `adeia_type` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(50) NOT NULL,
  `descr` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=greek AUTO_INCREMENT=12 ;

-- --------------------------------------------------------

--
-- Table structure for table `employee`
--

CREATE TABLE IF NOT EXISTS `employee` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(20) NOT NULL,
  `surname` varchar(30) NOT NULL,
  `patrwnymo` varchar(30) NOT NULL,
  `mhtrwnymo` varchar(30) NOT NULL,
  `klados` int(11) NOT NULL,
  `am` int(10) NOT NULL,
  `sx_organikhs` int(11) NOT NULL,
  `sx_yphrethshs` int(11) NOT NULL,
  `fek_dior` varchar(10) NOT NULL,
  `hm_dior` date NOT NULL,
  `vathm_old` varchar(4) NOT NULL,
  `vathm` varchar(4) NOT NULL,
  `hm_vathm` date NOT NULL,
  `mk_old` tinyint(2) NOT NULL,
  `mk` tinyint(2) NOT NULL,
  `hm_mk` date NOT NULL,
  `analipsi` varchar(5) NOT NULL,
  `hm_anal` date NOT NULL,
  `met_did` tinyint(11) NOT NULL COMMENT '0 οχι, 1 μεταπτ, 2 διδ, 3 μετ+διδ',
  `proyp_old` int(11) NOT NULL COMMENT 'se hmeres',
  `proyp` int(11) NOT NULL COMMENT 'se hmeres (apo excel)',
  `anatr_katataksi` int(11) NOT NULL COMMENT 'se hmeres',
  `anatr` int(11) NOT NULL COMMENT 'se hmeres',
  `anatr_excel` int(11) NOT NULL COMMENT 'se hmeres',
  `comments` longtext NOT NULL,
  `status` int(11) NOT NULL COMMENT '1 εργάζεται, 2 Λύση Σχέσης-Παραίτηση, 3 ¶δεια, 4 Διαθεσιμότητα',
  `afm` varchar(11) NOT NULL,
  `eidikh` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `klados` (`klados`),
  KEY `sx_organikhs` (`sx_organikhs`)
) ENGINE=InnoDB  DEFAULT CHARSET=greek AUTO_INCREMENT=2278 ;

-- --------------------------------------------------------

--
-- Table structure for table `employee_bkp_29-3-2012`
--

CREATE TABLE IF NOT EXISTS `employee_bkp_29-3-2012` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(20) NOT NULL,
  `surname` varchar(30) NOT NULL,
  `patrwnymo` varchar(30) NOT NULL,
  `mhtrwnymo` varchar(30) NOT NULL,
  `klados` int(11) NOT NULL,
  `am` int(10) NOT NULL,
  `sx_organikhs` int(11) NOT NULL,
  `sx_yphrethshs` int(11) NOT NULL,
  `fek_dior` varchar(10) NOT NULL,
  `hm_dior` date NOT NULL,
  `vathm` varchar(4) NOT NULL,
  `hm_vathm` date NOT NULL,
  `mk` tinyint(2) NOT NULL,
  `hm_mk` date NOT NULL,
  `analipsi` varchar(5) NOT NULL,
  `hm_anal` date NOT NULL,
  `met_did` tinyint(11) NOT NULL COMMENT '0 οχι, 1 μεταπτ, 2 διδ, 3 μετ+διδ',
  `proyp_old` int(11) NOT NULL COMMENT 'se hmeres',
  `proyp` int(11) NOT NULL COMMENT 'se hmeres (apo excel)',
  `anatr_katataksi` int(11) NOT NULL COMMENT 'se hmeres',
  `anatr` int(11) NOT NULL COMMENT 'se hmeres',
  `anatr_excel` int(11) NOT NULL COMMENT 'se hmeres',
  `comments` longtext NOT NULL,
  `status` int(11) NOT NULL COMMENT '1 εργάζεται, 2 Λύση Σχέσης-Παραίτηση, 3 ¶δεια, 4 Διαθεσιμότητα',
  `afm` varchar(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `klados` (`klados`),
  KEY `sx_organikhs` (`sx_organikhs`)
) ENGINE=InnoDB  DEFAULT CHARSET=greek AUTO_INCREMENT=2278 ;

-- --------------------------------------------------------

--
-- Table structure for table `employee_deleted`
--

CREATE TABLE IF NOT EXISTS `employee_deleted` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(20) NOT NULL,
  `surname` varchar(30) NOT NULL,
  `patrwnymo` varchar(30) NOT NULL,
  `mhtrwnymo` varchar(30) NOT NULL,
  `klados` int(11) NOT NULL,
  `am` int(10) NOT NULL,
  `sx_organikhs` int(11) NOT NULL,
  `sx_yphrethshs` int(11) NOT NULL,
  `fek_dior` varchar(10) NOT NULL,
  `hm_dior` date NOT NULL,
  `vathm` varchar(4) NOT NULL,
  `hm_vathm` date NOT NULL,
  `mk` tinyint(2) NOT NULL,
  `hm_mk` date NOT NULL,
  `analipsi` varchar(5) NOT NULL,
  `hm_anal` date NOT NULL,
  `met_did` tinyint(11) NOT NULL,
  `proyp` int(11) NOT NULL COMMENT 'se hmeres',
  `anatr` int(11) NOT NULL COMMENT 'se hmeres',
  `comments` longtext NOT NULL,
  `status` int(11) NOT NULL,
  `afm` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `klados` (`klados`),
  KEY `sx_organikhs` (`sx_organikhs`)
) ENGINE=InnoDB  DEFAULT CHARSET=greek AUTO_INCREMENT=2282 ;

-- --------------------------------------------------------

--
-- Table structure for table `employee_log`
--

CREATE TABLE IF NOT EXISTS `employee_log` (
  `emp_id` int(11) NOT NULL,
  `userid` int(11) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `action` int(11) NOT NULL COMMENT '0 add, 1 edit, 2 delete',
  PRIMARY KEY (`timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=greek;

-- --------------------------------------------------------

--
-- Table structure for table `employee_unused`
--

CREATE TABLE IF NOT EXISTS `employee_unused` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(20) NOT NULL,
  `surname` varchar(30) NOT NULL,
  `patrwnymo` varchar(30) NOT NULL,
  `mhtrwnymo` varchar(30) NOT NULL,
  `klados` int(11) NOT NULL,
  `am` int(10) NOT NULL,
  `sx_organikhs` int(11) NOT NULL,
  `sx_yphrethshs` int(11) NOT NULL,
  `fek_dior` varchar(10) NOT NULL,
  `hm_dior` date NOT NULL,
  `vathm_old` varchar(4) NOT NULL,
  `vathm` varchar(4) NOT NULL,
  `hm_vathm` date NOT NULL,
  `mk_old` tinyint(2) NOT NULL,
  `mk` tinyint(2) NOT NULL,
  `hm_mk` date NOT NULL,
  `analipsi` varchar(5) NOT NULL,
  `hm_anal` date NOT NULL,
  `met_did` tinyint(11) NOT NULL COMMENT '0 οχι, 1 μεταπτ, 2 διδ, 3 μετ+διδ',
  `proyp_old` int(11) NOT NULL COMMENT 'se hmeres',
  `proyp` int(11) NOT NULL COMMENT 'se hmeres (apo excel)',
  `anatr_katataksi` int(11) NOT NULL COMMENT 'se hmeres',
  `anatr` int(11) NOT NULL COMMENT 'se hmeres',
  `anatr_excel` int(11) NOT NULL COMMENT 'se hmeres',
  `comments` longtext NOT NULL,
  `status` int(11) NOT NULL COMMENT '1 εργάζεται, 2 Λύση Σχέσης-Παραίτηση, 3 ¶δεια, 4 Διαθεσιμότητα',
  `afm` varchar(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `klados` (`klados`),
  KEY `sx_organikhs` (`sx_organikhs`)
) ENGINE=InnoDB  DEFAULT CHARSET=greek AUTO_INCREMENT=1840 ;

-- --------------------------------------------------------

--
-- Table structure for table `excel`
--

CREATE TABLE IF NOT EXISTS `excel` (
  `surname` varchar(30) NOT NULL,
  `name` varchar(30) NOT NULL,
  `patr` varchar(30) NOT NULL,
  `am` int(11) NOT NULL,
  `anatr` int(11) NOT NULL,
  `vathmos` varchar(5) NOT NULL,
  `mk` int(11) NOT NULL,
  PRIMARY KEY (`am`)
) ENGINE=InnoDB DEFAULT CHARSET=greek;

-- --------------------------------------------------------

--
-- Table structure for table `klados`
--

CREATE TABLE IF NOT EXISTS `klados` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `perigrafh` varchar(30) NOT NULL,
  `onoma` varchar(30) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=greek AUTO_INCREMENT=13 ;

-- --------------------------------------------------------

--
-- Table structure for table `logon`
--

CREATE TABLE IF NOT EXISTS `logon` (
  `userid` int(11) NOT NULL AUTO_INCREMENT,
  `useremail` varchar(50) NOT NULL DEFAULT '',
  `password` varchar(50) NOT NULL DEFAULT '',
  `userlevel` int(1) NOT NULL DEFAULT '0' COMMENT '0 admin, 1 add/delete/edit, 2 edit, 3 view only',
  `adeia` tinyint(4) NOT NULL COMMENT 'an 1 tote ektypwnei adeies',
  `username` varchar(20) NOT NULL,
  `lastlogin` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`userid`)
) ENGINE=InnoDB  DEFAULT CHARSET=greek AUTO_INCREMENT=7 ;

-- --------------------------------------------------------

--
-- Table structure for table `metdid`
--

CREATE TABLE IF NOT EXISTS `metdid` (
  `afm` int(11) NOT NULL,
  `metdid` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=greek;

-- --------------------------------------------------------

--
-- Table structure for table `misth`
--

CREATE TABLE IF NOT EXISTS `misth` (
  `city` varchar(20) NOT NULL,
  `street` varchar(20) NOT NULL,
  `num` varchar(20) NOT NULL,
  `tk` varchar(20) NOT NULL,
  `born` date NOT NULL,
  `start_date` date NOT NULL,
  `anal_date` date NOT NULL,
  `proyp_misth` int(11) NOT NULL,
  `afm1` int(11) NOT NULL,
  PRIMARY KEY (`afm1`)
) ENGINE=InnoDB DEFAULT CHARSET=greek;

-- --------------------------------------------------------

--
-- Table structure for table `my_test`
--

CREATE TABLE IF NOT EXISTS `my_test` (
  `id` int(11) NOT NULL DEFAULT '0',
  `name` varchar(20) NOT NULL,
  `surname` varchar(30) NOT NULL,
  `patrwnymo` varchar(30) NOT NULL,
  `mhtrwnymo` varchar(30) NOT NULL,
  `klados` int(11) NOT NULL,
  `am` int(10) NOT NULL,
  `sx_organikhs` int(11) NOT NULL,
  `sx_yphrethshs` int(11) NOT NULL,
  `fek_dior` varchar(10) NOT NULL,
  `hm_dior` date NOT NULL,
  `vathm` varchar(4) NOT NULL,
  `hm_vathm` date NOT NULL,
  `mk` tinyint(2) NOT NULL,
  `hm_mk` date NOT NULL,
  `analipsi` varchar(5) NOT NULL,
  `hm_anal` date NOT NULL,
  `met_did` tinyint(11) NOT NULL COMMENT '0 οχι, 1 μεταπτ, 2 διδ, 3 μετ+διδ',
  `proyp` int(11) NOT NULL COMMENT 'se hmeres',
  `proyp_excel` int(11) NOT NULL,
  `anatr_katataksi` int(11) NOT NULL COMMENT 'se hmeres',
  `anatr` int(11) NOT NULL COMMENT 'se hmeres',
  `comments` longtext NOT NULL,
  `status` int(11) NOT NULL COMMENT '1 εργάζεται, 2 Λύση Σχέσης-Παραίτηση, 3 ¶δεια, 4 Διαθεσιμότητα',
  `afm` varchar(11) NOT NULL,
  `excel_anatr` int(11) NOT NULL,
  `exc_v` varchar(5) NOT NULL,
  `ex_mk` int(11) NOT NULL,
  `proyp_misth` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=greek;

-- --------------------------------------------------------

--
-- Table structure for table `school`
--

CREATE TABLE IF NOT EXISTS `school` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(40) NOT NULL,
  `address` varchar(40) NOT NULL,
  `tel` varchar(18) NOT NULL,
  `fax` varchar(18) NOT NULL,
  `email` varchar(50) NOT NULL,
  `organikothta` int(11) NOT NULL,
  `entaksis` int(11) NOT NULL,
  `ypodoxis` int(11) NOT NULL,
  `frontistiriako` int(11) NOT NULL,
  `oloimero` int(11) NOT NULL,
  `comments` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=greek AUTO_INCREMENT=389 ;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `adeia_del_log`
--
ALTER TABLE `adeia_del_log`
  ADD CONSTRAINT `adeia_del_log_ibfk_1` FOREIGN KEY (`adeia_id`) REFERENCES `adeia_deleted` (`id`);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
