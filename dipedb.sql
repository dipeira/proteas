-- phpMyAdmin SQL Dump
-- version 4.1.12
-- http://www.phpmyadmin.net
--
-- Φιλοξενητής: 127.0.0.1
-- Χρόνος δημιουργίας: 16 Φεβ 2015 στις 13:50:30
-- Έκδοση διακομιστή: 5.5.36
-- Έκδοση PHP: 5.4.27

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES greek */;

--
-- Βάση δεδομένων: `dipedb`
--

-- --------------------------------------------------------

--
-- Δομή πίνακα για τον πίνακα `adeia`
--

CREATE TABLE IF NOT EXISTS `adeia` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `emp_id` int(11) NOT NULL,
  `type` int(11) NOT NULL,
  `prot_apof` int(11) NOT NULL COMMENT 'Πρωτόκολλο απόφασης',
  `hm_apof` date NOT NULL COMMENT 'Ημ/νία απόφασης',
  `prot` int(11) NOT NULL COMMENT 'Αρ. Πρωτοκόλου',
  `hm_prot` date NOT NULL,
  `date` date NOT NULL COMMENT 'Ημ/νία  αίτησης',
  `vev_dil` tinyint(4) NOT NULL COMMENT 'Βεβαίωση / Δήλωση (για αναρρωτική Υπ.Δηλ.)',
  `days` int(11) NOT NULL COMMENT 'Διάρκεια σε ημέρες',
  `start` date NOT NULL,
  `finish` date NOT NULL,
  `logos` varchar(70) NOT NULL COMMENT 'Λόγος (για ειδικές)',
  `comments` text NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `emp_id` (`emp_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=greek AUTO_INCREMENT=9237 ;

-- --------------------------------------------------------

--
-- Δομή πίνακα για τον πίνακα `adeia_deleted`
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
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=greek AUTO_INCREMENT=4221 ;

-- --------------------------------------------------------

--
-- Δομή πίνακα για τον πίνακα `adeia_del_log`
--

CREATE TABLE IF NOT EXISTS `adeia_del_log` (
  `adeia_id` int(11) NOT NULL,
  `userid` int(11) NOT NULL,
  `ektaktos` tinyint(4) NOT NULL COMMENT '1 gia ektakto proswpiko',
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`adeia_id`)
) ENGINE=InnoDB DEFAULT CHARSET=greek;

-- --------------------------------------------------------

--
-- Δομή πίνακα για τον πίνακα `adeia_ekt`
--

CREATE TABLE IF NOT EXISTS `adeia_ekt` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `emp_id` int(11) NOT NULL,
  `type` int(11) NOT NULL,
  `prot_apof` int(11) NOT NULL COMMENT 'Πρωτόκολλο απόφασης',
  `hm_apof` date NOT NULL COMMENT 'Ημ/νία απόφασης',
  `prot` int(11) NOT NULL COMMENT 'Αρ. Πρωτοκόλου',
  `hm_prot` date NOT NULL,
  `date` date NOT NULL COMMENT 'Ημ/νία  αίτησης',
  `vev_dil` tinyint(4) NOT NULL COMMENT 'Βεβαίωση / Δήλωση (για αναρρωτική Υπ.Δηλ.)',
  `days` int(11) NOT NULL COMMENT 'Διάρκεια σε ημέρες',
  `start` date NOT NULL,
  `finish` date NOT NULL,
  `logos` varchar(70) NOT NULL COMMENT 'Λόγος (για ειδικές)',
  `comments` text NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=greek AUTO_INCREMENT=451 ;

-- --------------------------------------------------------

--
-- Δομή πίνακα για τον πίνακα `adeia_ekt_deleted`
--

CREATE TABLE IF NOT EXISTS `adeia_ekt_deleted` (
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
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=greek AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Δομή πίνακα για τον πίνακα `adeia_type`
--

CREATE TABLE IF NOT EXISTS `adeia_type` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(50) NOT NULL,
  `descr` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=greek AUTO_INCREMENT=14 ;

-- --------------------------------------------------------

--
-- Δομή πίνακα για τον πίνακα `apofaseis`
--

CREATE TABLE IF NOT EXISTS `apofaseis` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `prwt` int(11) NOT NULL,
  `sent` tinyint(4) NOT NULL,
  `result` text NOT NULL,
  `stamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=greek AUTO_INCREMENT=266 ;

-- --------------------------------------------------------

--
-- Δομή πίνακα για τον πίνακα `changelog`
--

CREATE TABLE IF NOT EXISTS `changelog` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ch_date` date NOT NULL,
  `change_txt` varchar(400) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=greek AUTO_INCREMENT=13 ;

-- --------------------------------------------------------

--
-- Δομή πίνακα για τον πίνακα `dimos`
--

CREATE TABLE IF NOT EXISTS `dimos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=greek AUTO_INCREMENT=9 ;

-- --------------------------------------------------------

--
-- Δομή πίνακα για τον πίνακα `ekdromi`
--

CREATE TABLE IF NOT EXISTS `ekdromi` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sch` int(11) NOT NULL,
  `taksi` int(11) NOT NULL,
  `tmima` varchar(20) NOT NULL,
  `prot` int(11) NOT NULL,
  `proorismos` varchar(200) NOT NULL,
  `date` date NOT NULL,
  `comments` text NOT NULL,
  `sxol_etos` varchar(30) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=greek AUTO_INCREMENT=4005 ;

-- --------------------------------------------------------

--
-- Δομή πίνακα για τον πίνακα `ektaktoi`
--

CREATE TABLE IF NOT EXISTS `ektaktoi` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(20) NOT NULL,
  `surname` varchar(30) NOT NULL,
  `patrwnymo` varchar(30) NOT NULL,
  `mhtrwnymo` varchar(30) NOT NULL,
  `klados` int(11) NOT NULL,
  `sx_yphrethshs` varchar(30) NOT NULL COMMENT '0 κυρίως σχολείο, 1,2... συμπλήρωση ωραρίου',
  `vathm` varchar(4) NOT NULL,
  `hm_vathm` date NOT NULL,
  `mk` tinyint(2) NOT NULL,
  `hm_mk` date NOT NULL,
  `analipsi` varchar(5) NOT NULL,
  `hm_anal` date NOT NULL,
  `ya` varchar(50) NOT NULL COMMENT 'Υπουργική Απόφαση',
  `apofasi` varchar(50) NOT NULL COMMENT 'Απόφαση Δ/ντή',
  `met_did` tinyint(11) NOT NULL COMMENT '0 οχι, 1 μεταπτ, 2 διδ, 3 μετ+διδ',
  `comments` longtext NOT NULL,
  `status` int(11) NOT NULL COMMENT '1 εργάζεται, 2 Λύση Σχέσης-Παραίτηση, 3 Άδεια, 4 Διαθεσιμότητα',
  `afm` varchar(11) NOT NULL,
  `type` int(11) NOT NULL COMMENT 'τυπος απασχόλησης (1 ωρομ, 2 αναπλ, 3 αναπλ ΕΣΠΑ, 4 ΕΕΠ, 5 ΕΒΠ)',
  `stathero` varchar(30) NOT NULL,
  `kinhto` varchar(30) NOT NULL,
  `metakinhsh` text NOT NULL COMMENT 'Μετακινήσεις κατά τη σχολική χρονιά',
  `praxi` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `klados` (`klados`)
) ENGINE=InnoDB  DEFAULT CHARSET=greek AUTO_INCREMENT=763 ;

-- --------------------------------------------------------

--
-- Δομή πίνακα για τον πίνακα `ektaktoi_201314`
--

CREATE TABLE IF NOT EXISTS `ektaktoi_201314` (
  `id` int(11) NOT NULL DEFAULT '0',
  `name` varchar(20) CHARACTER SET greek NOT NULL,
  `surname` varchar(30) CHARACTER SET greek NOT NULL,
  `patrwnymo` varchar(30) CHARACTER SET greek NOT NULL,
  `mhtrwnymo` varchar(30) CHARACTER SET greek NOT NULL,
  `klados` int(11) NOT NULL,
  `sx_yphrethshs` varchar(30) CHARACTER SET greek NOT NULL COMMENT '0 κυρίως σχολείο, 1,2... συμπλήρωση ωραρίου',
  `vathm` varchar(4) CHARACTER SET greek NOT NULL,
  `hm_vathm` date NOT NULL,
  `mk` tinyint(2) NOT NULL,
  `hm_mk` date NOT NULL,
  `analipsi` varchar(5) CHARACTER SET greek NOT NULL,
  `hm_anal` date NOT NULL,
  `ya` varchar(50) CHARACTER SET greek NOT NULL COMMENT 'Υπουργική Απόφαση',
  `apofasi` varchar(50) CHARACTER SET greek NOT NULL COMMENT 'Απόφαση Δ/ντή',
  `met_did` tinyint(11) NOT NULL COMMENT '0 οχι, 1 μεταπτ, 2 διδ, 3 μετ+διδ',
  `comments` longtext CHARACTER SET greek NOT NULL,
  `status` int(11) NOT NULL COMMENT '1 εργάζεται, 2 Λύση Σχέσης-Παραίτηση, 3 Άδεια, 4 Διαθεσιμότητα',
  `afm` varchar(11) CHARACTER SET greek NOT NULL,
  `type` int(11) NOT NULL COMMENT 'τυπος απασχόλησης (1 ωρομ, 2 αναπλ, 3 αναπλ ΕΣΠΑ, 4 ΕΕΠ, 5 ΕΒΠ)',
  `stathero` varchar(30) CHARACTER SET greek NOT NULL,
  `kinhto` varchar(30) CHARACTER SET greek NOT NULL,
  `metakinhsh` text CHARACTER SET greek NOT NULL COMMENT 'Μετακινήσεις κατά τη σχολική χρονιά',
  `praxi` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Δομή πίνακα για τον πίνακα `ektaktoi_bkp`
--

CREATE TABLE IF NOT EXISTS `ektaktoi_bkp` (
  `id` int(11) NOT NULL DEFAULT '0',
  `name` varchar(20) NOT NULL,
  `surname` varchar(30) NOT NULL,
  `patrwnymo` varchar(30) NOT NULL,
  `mhtrwnymo` varchar(30) NOT NULL,
  `klados` int(11) NOT NULL,
  `sx_yphrethshs` varchar(30) NOT NULL COMMENT '0 κυρίως σχολείο, 1,2... συμπλήρωση ωραρίου',
  `vathm` varchar(4) NOT NULL,
  `hm_vathm` date NOT NULL,
  `mk` tinyint(2) NOT NULL,
  `hm_mk` date NOT NULL,
  `analipsi` varchar(5) NOT NULL,
  `hm_anal` date NOT NULL,
  `ya` varchar(50) NOT NULL COMMENT 'Υπουργική Απόφαση',
  `apofasi` varchar(50) NOT NULL COMMENT 'Απόφαση Δ/ντή',
  `met_did` tinyint(11) NOT NULL COMMENT '0 οχι, 1 μεταπτ, 2 διδ, 3 μετ+διδ',
  `comments` longtext NOT NULL,
  `status` int(11) NOT NULL COMMENT '1 εργάζεται, 2 Λύση Σχέσης-Παραίτηση, 3 Άδεια, 4 Διαθεσιμότητα',
  `afm` varchar(11) NOT NULL,
  `type` int(11) NOT NULL COMMENT 'τυπος απασχόλησης'
) ENGINE=InnoDB DEFAULT CHARSET=greek;

-- --------------------------------------------------------

--
-- Δομή πίνακα για τον πίνακα `ektaktoi_not`
--

CREATE TABLE IF NOT EXISTS `ektaktoi_not` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(20) NOT NULL,
  `surname` varchar(30) NOT NULL,
  `patrwnymo` varchar(30) NOT NULL,
  `mhtrwnymo` varchar(30) NOT NULL,
  `klados` int(11) NOT NULL,
  `sx_yphrethshs` varchar(30) NOT NULL COMMENT '0 κυρίως σχολείο, 1,2... συμπλήρωση ωραρίου',
  `vathm` varchar(4) NOT NULL,
  `hm_vathm` date NOT NULL,
  `mk` tinyint(2) NOT NULL,
  `hm_mk` date NOT NULL,
  `analipsi` varchar(5) NOT NULL,
  `hm_anal` date NOT NULL,
  `ya` varchar(50) NOT NULL COMMENT 'Υπουργική Απόφαση',
  `apofasi` varchar(50) NOT NULL COMMENT 'Απόφαση Δ/ντή',
  `met_did` tinyint(11) NOT NULL COMMENT '0 οχι, 1 μεταπτ, 2 διδ, 3 μετ+διδ',
  `comments` longtext NOT NULL,
  `status` int(11) NOT NULL COMMENT '1 εργάζεται, 2 Λύση Σχέσης-Παραίτηση, 3 Άδεια, 4 Διαθεσιμότητα',
  `afm` varchar(11) NOT NULL,
  `type` int(11) NOT NULL COMMENT 'τυπος απασχόλησης (1 ωρομ, 2 αναπλ, 3 αναπλ ΕΣΠΑ, 4 ΕΕΠ, 5 ΕΒΠ)',
  `stathero` varchar(30) NOT NULL,
  `kinhto` varchar(30) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `klados` (`klados`)
) ENGINE=InnoDB  DEFAULT CHARSET=greek AUTO_INCREMENT=323 ;

-- --------------------------------------------------------

--
-- Δομή πίνακα για τον πίνακα `ektaktoi_types`
--

CREATE TABLE IF NOT EXISTS `ektaktoi_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(30) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=greek AUTO_INCREMENT=6 ;

-- --------------------------------------------------------

--
-- Δομή πίνακα για τον πίνακα `employee`
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
  `thesi` tinyint(4) NOT NULL COMMENT '0 εκπαιδευτικός, 1 υποδιευθυντης, 2 δ/ντής-πρ/νος, 3 Τμ.Ένταξης, 4 Διοικητικός, 5 Ιδιωτικός',
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
  `status` int(11) NOT NULL COMMENT '1 εργάζεται, 2 Λύση Σχέσης-Παραίτηση, 3 Άδεια, 4 Διαθεσιμότητα',
  `afm` varchar(11) NOT NULL,
  `eidikh` tinyint(1) NOT NULL,
  `tel` varchar(40) NOT NULL,
  `address` varchar(80) NOT NULL,
  `idnum` varchar(40) NOT NULL,
  `amka` varchar(40) NOT NULL,
  `aney` int(11) NOT NULL,
  `aney_xr` int(11) NOT NULL COMMENT 'Συνολ. χρόνος αδ. άνευ αποδοχών',
  `aney_apo` date NOT NULL COMMENT 'Αδ. ανευ από',
  `aney_ews` date NOT NULL COMMENT 'Αδ. άνευ έως',
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `wres` int(11) NOT NULL COMMENT 'Διδακτικές ώρες (βάσει ετών υπηρεσίας)',
  `idiwtiko` tinyint(4) NOT NULL,
  `idiwtiko_id` tinyint(4) NOT NULL,
  `idiwtiko_liksi` date NOT NULL,
  `idiwtiko_enarxi` date NOT NULL,
  `idiwtiko_id_enarxi` date NOT NULL,
  `idiwtiko_id_liksi` date NOT NULL,
  `katoikon` tinyint(4) NOT NULL,
  `katoikon_apo` date NOT NULL,
  `katoikon_ews` date NOT NULL,
  `katoikon_comm` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `klados` (`klados`),
  KEY `sx_organikhs` (`sx_organikhs`)
) ENGINE=InnoDB  DEFAULT CHARSET=greek AUTO_INCREMENT=2768 ;

-- --------------------------------------------------------

--
-- Δομή πίνακα για τον πίνακα `employee_deleted`
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
  `thesi` tinyint(4) NOT NULL COMMENT '0 εκπαιδευτικός, 1 υποδιευθυντης, 2 δ/ντής-πρ/νος, 3 Διοικητικός, 4 Ιδιωτικός',
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
  `status` int(11) NOT NULL COMMENT '1 εργάζεται, 2 Λύση Σχέσης-Παραίτηση, 3 Άδεια, 4 Διαθεσιμότητα',
  `afm` varchar(11) NOT NULL,
  `eidikh` tinyint(1) NOT NULL,
  `tel` varchar(40) NOT NULL,
  `address` varchar(80) NOT NULL,
  `idnum` varchar(40) NOT NULL,
  `amka` varchar(40) NOT NULL,
  `aney` int(11) NOT NULL,
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `klados` (`klados`),
  KEY `sx_organikhs` (`sx_organikhs`)
) ENGINE=InnoDB  DEFAULT CHARSET=greek AUTO_INCREMENT=2663 ;

-- --------------------------------------------------------

--
-- Δομή πίνακα για τον πίνακα `employee_deleted_140109`
--

CREATE TABLE IF NOT EXISTS `employee_deleted_140109` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(20) NOT NULL,
  `surname` varchar(30) NOT NULL,
  `patrwnymo` varchar(30) NOT NULL,
  `mhtrwnymo` varchar(30) NOT NULL,
  `klados` int(11) NOT NULL,
  `am` int(10) NOT NULL,
  `sx_organikhs` int(11) NOT NULL,
  `sx_yphrethshs` int(11) NOT NULL,
  `thesi` tinyint(4) NOT NULL COMMENT '0 εκπαιδευτικός, 1 υποδιευθυντης, 2 δ/ντής-πρ/νος',
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
  `status` int(11) NOT NULL COMMENT '1 εργάζεται, 2 Λύση Σχέσης-Παραίτηση, 3 Άδεια, 4 Διαθεσιμότητα',
  `afm` varchar(11) NOT NULL,
  `eidikh` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `klados` (`klados`),
  KEY `sx_organikhs` (`sx_organikhs`)
) ENGINE=InnoDB  DEFAULT CHARSET=greek AUTO_INCREMENT=2469 ;

-- --------------------------------------------------------

--
-- Δομή πίνακα για τον πίνακα `employee_log`
--

CREATE TABLE IF NOT EXISTS `employee_log` (
  `emp_id` int(11) NOT NULL,
  `userid` int(11) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `action` int(11) NOT NULL COMMENT '0 add, 1 edit, 2 delete',
  `ip` varchar(30) NOT NULL,
  `query` text NOT NULL,
  PRIMARY KEY (`timestamp`),
  KEY `userid` (`userid`)
) ENGINE=InnoDB DEFAULT CHARSET=greek;

-- --------------------------------------------------------

--
-- Δομή πίνακα για τον πίνακα `employee_moved`
--

CREATE TABLE IF NOT EXISTS `employee_moved` (
  `id` int(11) NOT NULL DEFAULT '0',
  `name` varchar(20) CHARACTER SET greek NOT NULL,
  `surname` varchar(30) CHARACTER SET greek NOT NULL,
  `patrwnymo` varchar(30) CHARACTER SET greek NOT NULL,
  `mhtrwnymo` varchar(30) CHARACTER SET greek NOT NULL,
  `klados` int(11) NOT NULL,
  `am` int(10) NOT NULL,
  `sx_organikhs` int(11) NOT NULL,
  `sx_yphrethshs` int(11) NOT NULL,
  `thesi` tinyint(4) NOT NULL COMMENT '0 εκπαιδευτικός, 1 υποδιευθυντης, 2 δ/ντής-πρ/νος, 3 Τμ.Ένταξης, 4 Διοικητικός, 5 Ιδιωτικός',
  `fek_dior` varchar(10) CHARACTER SET greek NOT NULL,
  `hm_dior` date NOT NULL,
  `vathm_old` varchar(4) CHARACTER SET greek NOT NULL,
  `vathm` varchar(4) CHARACTER SET greek NOT NULL,
  `hm_vathm` date NOT NULL,
  `mk_old` tinyint(2) NOT NULL,
  `mk` tinyint(2) NOT NULL,
  `hm_mk` date NOT NULL,
  `analipsi` varchar(5) CHARACTER SET greek NOT NULL,
  `hm_anal` date NOT NULL,
  `met_did` tinyint(11) NOT NULL COMMENT '0 οχι, 1 μεταπτ, 2 διδ, 3 μετ+διδ',
  `proyp_old` int(11) NOT NULL COMMENT 'se hmeres',
  `proyp` int(11) NOT NULL COMMENT 'se hmeres (apo excel)',
  `anatr_katataksi` int(11) NOT NULL COMMENT 'se hmeres',
  `anatr` int(11) NOT NULL COMMENT 'se hmeres',
  `anatr_excel` int(11) NOT NULL COMMENT 'se hmeres',
  `comments` longtext CHARACTER SET greek NOT NULL,
  `status` int(11) NOT NULL COMMENT '1 εργάζεται, 2 Λύση Σχέσης-Παραίτηση, 3 Άδεια, 4 Διαθεσιμότητα',
  `afm` varchar(11) CHARACTER SET greek NOT NULL,
  `eidikh` tinyint(1) NOT NULL,
  `tel` varchar(40) CHARACTER SET greek NOT NULL,
  `address` varchar(80) CHARACTER SET greek NOT NULL,
  `idnum` varchar(40) CHARACTER SET greek NOT NULL,
  `amka` varchar(40) CHARACTER SET greek NOT NULL,
  `aney` int(11) NOT NULL,
  `aney_xr` int(11) NOT NULL COMMENT 'Συνολ. χρόνος αδ. άνευ αποδοχών',
  `aney_apo` date NOT NULL COMMENT 'Αδ. ανευ από',
  `aney_ews` date NOT NULL COMMENT 'Αδ. άνευ έως',
  `updated` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Δομή πίνακα για τον πίνακα `employee_unused`
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
  `status` int(11) NOT NULL COMMENT '1 εργάζεται, 2 Λύση Σχέσης-Παραίτηση, 3 Άδεια, 4 Διαθεσιμότητα',
  `afm` varchar(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `klados` (`klados`),
  KEY `sx_organikhs` (`sx_organikhs`)
) ENGINE=InnoDB  DEFAULT CHARSET=greek AUTO_INCREMENT=1840 ;

-- --------------------------------------------------------

--
-- Δομή πίνακα για τον πίνακα `excel`
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
-- Δομή πίνακα για τον πίνακα `klados`
--

CREATE TABLE IF NOT EXISTS `klados` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `perigrafh` varchar(30) NOT NULL,
  `onoma` varchar(30) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=greek AUTO_INCREMENT=26 ;

-- --------------------------------------------------------

--
-- Δομή πίνακα για τον πίνακα `logon`
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
) ENGINE=InnoDB  DEFAULT CHARSET=greek AUTO_INCREMENT=16 ;

-- --------------------------------------------------------

--
-- Δομή πίνακα για τον πίνακα `metdid`
--

CREATE TABLE IF NOT EXISTS `metdid` (
  `afm` int(11) NOT NULL,
  `metdid` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=greek;

-- --------------------------------------------------------

--
-- Δομή πίνακα για τον πίνακα `misth`
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
-- Δομή πίνακα για τον πίνακα `misth2`
--

CREATE TABLE IF NOT EXISTS `misth2` (
  `idnum` varchar(20) NOT NULL,
  `tel` varchar(40) NOT NULL,
  `afm` int(11) NOT NULL,
  `amka` bigint(20) NOT NULL,
  KEY `afm` (`afm`)
) ENGINE=InnoDB DEFAULT CHARSET=greek;

-- --------------------------------------------------------

--
-- Δομή πίνακα για τον πίνακα `my_test`
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
  `status` int(11) NOT NULL COMMENT '1 εργάζεται, 2 Λύση Σχέσης-Παραίτηση, 3 Άδεια, 4 Διαθεσιμότητα',
  `afm` varchar(11) NOT NULL,
  `excel_anatr` int(11) NOT NULL,
  `exc_v` varchar(5) NOT NULL,
  `ex_mk` int(11) NOT NULL,
  `proyp_misth` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=greek;

-- --------------------------------------------------------

--
-- Δομή πίνακα για τον πίνακα `params`
--

CREATE TABLE IF NOT EXISTS `params` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(20) NOT NULL COMMENT 'Όνομα Παραμέτρου',
  `value` varchar(100) NOT NULL COMMENT 'Τιμή Παραμέτρου',
  `descr` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=greek AUTO_INCREMENT=11 ;

-- --------------------------------------------------------

--
-- Δομή πίνακα για τον πίνακα `praxi`
--

CREATE TABLE IF NOT EXISTS `praxi` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `ya` varchar(50) NOT NULL,
  `ada` varchar(20) NOT NULL,
  `apofasi` varchar(50) NOT NULL COMMENT 'απόφαση τοποθέτησης',
  `sxolio` varchar(300) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=greek AUTO_INCREMENT=38 ;

-- --------------------------------------------------------

--
-- Δομή πίνακα για τον πίνακα `requests`
--

CREATE TABLE IF NOT EXISTS `requests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` varchar(20) NOT NULL,
  `req_name` varchar(50) NOT NULL,
  `req_date` date NOT NULL,
  `req_txt` text NOT NULL,
  `req_done` date NOT NULL,
  `req_comment` varchar(200) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=greek AUTO_INCREMENT=4 ;

-- --------------------------------------------------------

--
-- Δομή πίνακα για τον πίνακα `school`
--

CREATE TABLE IF NOT EXISTS `school` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(10) NOT NULL COMMENT '7-ψήφιος Κωδικός Υπουργείου',
  `category` int(10) NOT NULL COMMENT 'κατηγορία σχολείου: 1 - Α, 2 - Β κλπ.',
  `type` int(4) NOT NULL COMMENT '0 λοιπά, 1 Δημ, 2 Νηπ.',
  `eaep` tinyint(1) NOT NULL,
  `name` varchar(40) NOT NULL,
  `address` varchar(40) NOT NULL,
  `tk` int(5) NOT NULL COMMENT 'Ταχυδρομικός κώδικας',
  `tel` varchar(18) NOT NULL,
  `fax` varchar(18) NOT NULL,
  `email` varchar(50) NOT NULL,
  `organikothta` int(11) NOT NULL,
  `organikes` varchar(200) NOT NULL,
  `leitoyrg` int(11) NOT NULL COMMENT 'Λειτουργικότητα',
  `students` text NOT NULL COMMENT 'Οι τάξεις να χωρίζονται με κόμμα',
  `tmimata` varchar(60) NOT NULL COMMENT 'Nα χωρίζονται με κόμμα',
  `ekp_ee` varchar(10) NOT NULL COMMENT 'Nα χωρίζονται με κόμμα',
  `entaksis` int(11) NOT NULL,
  `ypodoxis` int(11) NOT NULL,
  `frontistiriako` int(11) NOT NULL,
  `oloimero` int(11) NOT NULL,
  `oloimero_stud` int(11) NOT NULL,
  `oloimero_tea` int(11) NOT NULL COMMENT 'Δάσκαλοι ολοημέρου',
  `oloimero_nip` varchar(40) NOT NULL COMMENT 'Nα χωρίζονται με κόμμα',
  `klasiko` varchar(40) NOT NULL COMMENT 'Nα χωρίζονται με κόμμα',
  `nip` varchar(20) NOT NULL COMMENT 'Nα χωρίζονται με κόμμα',
  `comments` text NOT NULL,
  `kena_org` varchar(200) NOT NULL,
  `kena_leit` varchar(200) NOT NULL,
  `type2` tinyint(1) NOT NULL COMMENT '0 δημόσιο, 1 ιδιωτικό, 2 ειδικό',
  `dimos` tinyint(4) NOT NULL COMMENT 'Από τον πίνακα dimos',
  `titlos` varchar(100) NOT NULL COMMENT 'τίτλος σχολείου (ολογράφως)',
  `updated` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `anenergo` tinyint(4) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=greek AUTO_INCREMENT=402 ;

-- --------------------------------------------------------

--
-- Δομή πίνακα για τον πίνακα `school_bkp`
--

CREATE TABLE IF NOT EXISTS `school_bkp` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(40) NOT NULL,
  `address` varchar(40) NOT NULL,
  `tel` varchar(18) NOT NULL,
  `fax` varchar(18) NOT NULL,
  `email` varchar(50) NOT NULL,
  `organikothta` int(11) NOT NULL,
  `leitoyrg` int(11) NOT NULL COMMENT 'Λειτουργικότητα',
  `students` text NOT NULL COMMENT 'Οι τάξεις να χωρίζονται με κόμμα',
  `entaksis` int(11) NOT NULL,
  `ypodoxis` int(11) NOT NULL,
  `frontistiriako` int(11) NOT NULL,
  `oloimero` int(11) NOT NULL,
  `comments` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=greek AUTO_INCREMENT=390 ;

-- --------------------------------------------------------

--
-- Δομή πίνακα για τον πίνακα `yphrethsh`
--

CREATE TABLE IF NOT EXISTS `yphrethsh` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `emp_id` int(10) NOT NULL,
  `yphrethsh` varchar(10) NOT NULL,
  `hours` varchar(10) NOT NULL,
  `organikh` varchar(10) NOT NULL,
  `sxol_etos` varchar(10) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `emp_id` (`emp_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=greek AUTO_INCREMENT=8741 ;

-- --------------------------------------------------------

--
-- Δομή πίνακα για τον πίνακα `yphrethsh_ekt`
--

CREATE TABLE IF NOT EXISTS `yphrethsh_ekt` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `emp_id` int(10) NOT NULL,
  `yphrethsh` varchar(10) NOT NULL,
  `hours` varchar(10) NOT NULL,
  `sxol_etos` varchar(10) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=greek AUTO_INCREMENT=1426 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
