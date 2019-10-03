-- phpMyAdmin SQL Dump
-- version 4.4.14
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Generation Time: Oct 15, 2018 at 12:34 PM
-- Server version: 5.6.26
-- PHP Version: 5.5.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES greek */;


-- --------------------------------------------------------

--
-- Table structure for table `adeia`
--

CREATE TABLE IF NOT EXISTS `adeia` (
  `id` int(11) NOT NULL,
  `emp_id` int(11) NOT NULL,
  `type` int(11) NOT NULL,
  `prot_apof` int(11) NOT NULL COMMENT '���������� ��������',
  `hm_apof` date NOT NULL COMMENT '��/��� ��������',
  `prot` int(11) NOT NULL COMMENT '��. ����������',
  `hm_prot` date NOT NULL,
  `date` date NOT NULL COMMENT '��/���  �������',
  `vev_dil` tinyint(4) NOT NULL COMMENT '�������� / ������ (��� ���������� ��.���.)',
  `days` int(11) NOT NULL COMMENT '�������� �� ������',
  `start` date NOT NULL,
  `finish` date NOT NULL,
  `logos` varchar(70) NOT NULL COMMENT '����� (��� �������)',
  `comments` text NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=greek;

-- --------------------------------------------------------

--
-- Table structure for table `adeia_deleted`
--

CREATE TABLE IF NOT EXISTS `adeia_deleted` (
  `id` int(11) NOT NULL,
  `emp_id` int(11) NOT NULL,
  `type` int(11) NOT NULL,
  `prot` int(11) NOT NULL COMMENT '��. ����������',
  `hm_prot` date NOT NULL,
  `date` date NOT NULL COMMENT '��/���  �������',
  `vev_dil` tinyint(4) NOT NULL COMMENT '�������� / ������ (��� ���������� ��.���.)',
  `days` int(11) NOT NULL COMMENT '�������� �� ������',
  `start` date NOT NULL,
  `finish` date NOT NULL,
  `logos` varchar(70) NOT NULL COMMENT '����� (��� �������)',
  `comments` text NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=greek;

-- --------------------------------------------------------

--
-- Table structure for table `adeia_del_log`
--

CREATE TABLE IF NOT EXISTS `adeia_del_log` (
  `adeia_id` int(11) NOT NULL,
  `userid` int(11) NOT NULL,
  `ektaktos` tinyint(4) NOT NULL COMMENT '1 gia ektakto proswpiko',
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=greek;

-- --------------------------------------------------------

--
-- Table structure for table `adeia_ekt`
--

CREATE TABLE IF NOT EXISTS `adeia_ekt` (
  `id` int(11) NOT NULL,
  `emp_id` int(11) NOT NULL,
  `type` int(11) NOT NULL,
  `prot_apof` int(11) NOT NULL COMMENT '���������� ��������',
  `hm_apof` date NOT NULL COMMENT '��/��� ��������',
  `prot` int(11) NOT NULL COMMENT '��. ����������',
  `hm_prot` date NOT NULL,
  `date` date NOT NULL COMMENT '��/���  �������',
  `vev_dil` tinyint(4) NOT NULL COMMENT '�������� / ������ (��� ���������� ��.���.)',
  `days` int(11) NOT NULL COMMENT '�������� �� ������',
  `start` date NOT NULL,
  `finish` date NOT NULL,
  `logos` varchar(70) NOT NULL COMMENT '����� (��� �������)',
  `comments` text NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `sxoletos` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=greek;

-- --------------------------------------------------------

--
-- Table structure for table `adeia_ekt_deleted`
--

CREATE TABLE IF NOT EXISTS `adeia_ekt_deleted` (
  `id` int(11) NOT NULL,
  `emp_id` int(11) NOT NULL,
  `type` int(11) NOT NULL,
  `prot` int(11) NOT NULL COMMENT '��. ����������',
  `hm_prot` date NOT NULL,
  `date` date NOT NULL COMMENT '��/���  �������',
  `vev_dil` tinyint(4) NOT NULL COMMENT '�������� / ������ (��� ���������� ��.���.)',
  `days` int(11) NOT NULL COMMENT '�������� �� ������',
  `start` date NOT NULL,
  `finish` date NOT NULL,
  `logos` varchar(70) NOT NULL COMMENT '����� (��� �������)',
  `comments` text NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=greek;

-- --------------------------------------------------------

--
-- Table structure for table `adeia_ekt_type`
--

CREATE TABLE IF NOT EXISTS `adeia_ekt_type` (
  `id` int(11) NOT NULL,
  `type` varchar(50) NOT NULL,
  `descr` varchar(50) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=greek;

--
-- Dumping data for table `adeia_ekt_type`
--

INSERT INTO `adeia_ekt_type` (`id`, `type`, `descr`) VALUES
(1, '����������', ''),
(2, '��������', ''),
(3, '���������� �� ����.������������', ''),
(4, '������', ''),
(5, '�������', ''),
(6, '������', ''),
(7, '���������', ''),
(8, '������', ''),
(9, '�������� ���������', ''),
(10, '?��� ��������', ''),
(11, '������', ''),
(12, '?��� �������� 4 ����� (���������)', ''),
(13, '��������', ''),
(15, '���������� �������', ''),
(16, '������ ������ �����', ''),
(17, '�������', ''),
(18, '����� ��������', '');

-- --------------------------------------------------------

--
-- Table structure for table `adeia_type`
--

CREATE TABLE IF NOT EXISTS `adeia_type` (
  `id` int(11) NOT NULL,
  `type` varchar(50) NOT NULL,
  `descr` varchar(50) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=greek;

--
-- Dumping data for table `adeia_type`
--

INSERT INTO `adeia_type` (`id`, `type`, `descr`) VALUES
(1, '����������', ''),
(2, '��������', ''),
(3, '���������� �� ����.������������', ''),
(4, '������', ''),
(5, '�������', ''),
(6, '������', ''),
(7, '���������', ''),
(8, '������', ''),
(9, '�������� ���������', ''),
(10, '���� ��������', ''),
(11, '������', ''),
(12, '���� �������� 1 �����', ''),
(13, '��������', ''),
(14, '����� ��������� ������', '');

-- --------------------------------------------------------

--
-- Table structure for table `apofaseis`
--

CREATE TABLE IF NOT EXISTS `apofaseis` (
  `id` int(11) NOT NULL,
  `prwt` int(11) NOT NULL,
  `year` int(11) NOT NULL,
  `sent` tinyint(4) NOT NULL,
  `result` text NOT NULL,
  `stamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=greek;

-- --------------------------------------------------------

--
-- Table structure for table `dimos`
--

CREATE TABLE IF NOT EXISTS `dimos` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=greek;

--
-- Dumping data for table `dimos`
--

INSERT INTO `dimos` (`id`, `name`) VALUES
(1, '������� - �����������'),
(2, '�������'),
(3, '��������'),
(4, '���������'),
(5, '����������'),
(6, '����� ��������'),
(7, '�������'),
(8, '����������');

-- --------------------------------------------------------

--
-- Table structure for table `ekdromi`
--

CREATE TABLE IF NOT EXISTS `ekdromi` (
  `id` int(11) NOT NULL,
  `sch` int(11) NOT NULL,
  `taksi` int(11) NOT NULL,
  `tmima` varchar(20) NOT NULL,
  `prot` int(11) NOT NULL,
  `proorismos` varchar(200) NOT NULL,
  `date` date NOT NULL,
  `comments` text NOT NULL,
  `sxol_etos` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=greek;

-- --------------------------------------------------------

--
-- Table structure for table `ektaktoi`
--

CREATE TABLE IF NOT EXISTS `ektaktoi` (
  `id` int(11) NOT NULL,
  `name` varchar(20) NOT NULL,
  `surname` varchar(30) NOT NULL,
  `patrwnymo` varchar(30) NOT NULL,
  `mhtrwnymo` varchar(30) NOT NULL,
  `klados` int(11) NOT NULL,
  `sx_yphrethshs` varchar(30) NOT NULL COMMENT '0 ������ �������, 1,2... ���������� �������',
  `vathm` varchar(4) NOT NULL,
  `hm_vathm` date NOT NULL,
  `mk` tinyint(2) NOT NULL,
  `hm_mk` date NOT NULL,
  `analipsi` varchar(5) NOT NULL,
  `hm_anal` date NOT NULL,
  `hm_apox` date NOT NULL,
  `ya` varchar(50) NOT NULL COMMENT '��������� �������',
  `apofasi` varchar(50) NOT NULL COMMENT '������� �/���',
  `met_did` tinyint(11) NOT NULL COMMENT '0 ���, 1 ������, 2 ���, 3 ���+���',
  `comments` longtext NOT NULL,
  `status` int(11) NOT NULL COMMENT '1 ���������, 2 ���� ������-���������, 3 ?����, 4 �������������',
  `afm` varchar(11) NOT NULL,
  `type` int(11) NOT NULL COMMENT '����� ����������� (1 ����, 2 �����, 3 ����� ����, 4 ���, 5 ���)',
  `stathero` varchar(30) NOT NULL,
  `kinhto` varchar(30) NOT NULL,
  `metakinhsh` text NOT NULL COMMENT '������������ ���� �� ������� ������',
  `praxi` int(11) NOT NULL,
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `thesi` int(11) NOT NULL,
  `wres` int(11) NOT NULL,
  `email` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=greek;

-- --------------------------------------------------------

--
-- Table structure for table `ektaktoi_log`
--

CREATE TABLE IF NOT EXISTS `ektaktoi_log` (
  `emp_id` int(11) NOT NULL,
  `userid` int(11) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `action` int(11) NOT NULL COMMENT '0 add, 1 edit, 2 delete',
  `ip` varchar(30) NOT NULL,
  `query` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=greek;

-- --------------------------------------------------------

--
-- Table structure for table `ektaktoi_old`
--

CREATE TABLE IF NOT EXISTS `ektaktoi_old` (
  `id` int(11) NOT NULL DEFAULT '0',
  `name` varchar(20) CHARACTER SET greek NOT NULL,
  `surname` varchar(30) CHARACTER SET greek NOT NULL,
  `patrwnymo` varchar(30) CHARACTER SET greek NOT NULL,
  `mhtrwnymo` varchar(30) CHARACTER SET greek NOT NULL,
  `klados` int(11) NOT NULL,
  `sx_yphrethshs` varchar(30) CHARACTER SET greek NOT NULL COMMENT '0 ������ �������, 1,2... ���������� �������',
  `vathm` varchar(4) CHARACTER SET greek NOT NULL,
  `hm_vathm` date NOT NULL,
  `mk` tinyint(2) NOT NULL,
  `hm_mk` date NOT NULL,
  `analipsi` varchar(5) CHARACTER SET greek NOT NULL,
  `hm_anal` date NOT NULL,
  `hm_apox` date NOT NULL,
  `ya` varchar(50) CHARACTER SET greek NOT NULL COMMENT '��������� �������',
  `apofasi` varchar(50) CHARACTER SET greek NOT NULL COMMENT '������� �/���',
  `met_did` tinyint(11) NOT NULL COMMENT '0 ���, 1 ������, 2 ���, 3 ���+���',
  `comments` longtext CHARACTER SET greek NOT NULL,
  `status` int(11) NOT NULL COMMENT '1 ���������, 2 ���� ������-���������, 3 ?����, 4 �������������',
  `afm` varchar(11) CHARACTER SET greek NOT NULL,
  `type` int(11) NOT NULL COMMENT '����� ����������� (1 ����, 2 �����, 3 ����� ����, 4 ���, 5 ���)',
  `stathero` varchar(30) CHARACTER SET greek NOT NULL,
  `kinhto` varchar(30) CHARACTER SET greek NOT NULL,
  `metakinhsh` text CHARACTER SET greek NOT NULL COMMENT '������������ ���� �� ������� ������',
  `praxi` int(11) NOT NULL,
  `updated` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `thesi` int(11) NOT NULL,
  `wres` int(11) NOT NULL,
  `email` text NOT NULL,
  `sxoletos` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `ektaktoi_types`
--

CREATE TABLE IF NOT EXISTS `ektaktoi_types` (
  `id` int(11) NOT NULL,
  `type` varchar(30) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=greek;

--
-- Dumping data for table `ektaktoi_types`
--

INSERT INTO `ektaktoi_types` (`id`, `type`) VALUES
(1, '����������� �.�.'),
(2, '�����������'),
(3, '����������� ����'),
(4, '���'),
(5, '���'),
(6, '��� / ���');

-- --------------------------------------------------------

--
-- Table structure for table `employee`
--

CREATE TABLE IF NOT EXISTS `employee` (
  `id` int(11) NOT NULL,
  `name` varchar(20) NOT NULL,
  `surname` varchar(30) NOT NULL,
  `patrwnymo` varchar(30) NOT NULL,
  `mhtrwnymo` varchar(30) NOT NULL,
  `klados` int(11) NOT NULL,
  `am` int(10) NOT NULL,
  `sx_organikhs` int(11) NOT NULL,
  `sx_yphrethshs` int(11) NOT NULL,
  `thesi` tinyint(4) NOT NULL COMMENT '0 �������������, 1 �������������, 2 �/����-��/���, 3 ��.�������, 4 �����������, 5 ���������',
  `org_ent` BOOLEAN NOT NULL COMMENT '�������� �� ����� �������',
  `fek_dior` varchar(10) NOT NULL,
  `hm_dior` date NOT NULL,
  `vathm_old` varchar(4) NOT NULL,
  `vathm` varchar(4) NOT NULL,
  `hm_vathm` date NOT NULL,
  `mk_old` tinyint(2) NOT NULL,
  `mk` tinyint(2) NOT NULL,
  `hm_mk` date NOT NULL,
  `mk11` tinyint(4) NOT NULL,
  `hm_mk11` date NOT NULL,
  `analipsi` varchar(5) NOT NULL,
  `hm_anal` date NOT NULL,
  `met_did` tinyint(11) NOT NULL COMMENT '0 ���, 1 ������, 2 ���, 3 ���+���',
  `proyp_old` int(11) NOT NULL COMMENT 'se hmeres',
  `proyp` int(11) NOT NULL COMMENT 'se hmeres (apo excel)',
  `proyp_not` int(11) NOT NULL COMMENT 'proyp poy de lambanetai gia ypologismo wrarioy, se hmeres',
  `anatr_katataksi` int(11) NOT NULL COMMENT 'se hmeres',
  `anatr` int(11) NOT NULL COMMENT 'se hmeres',
  `anatr_excel` int(11) NOT NULL COMMENT 'se hmeres',
  `comments` longtext NOT NULL,
  `status` int(11) NOT NULL COMMENT '1 ���������, 2 ���� ������-���������, 3 ?����, 4 �������������',
  `afm` varchar(11) NOT NULL,
  `eidikh` tinyint(1) NOT NULL,
  `tel` varchar(40) NOT NULL,
  `address` varchar(80) NOT NULL,
  `idnum` varchar(40) NOT NULL,
  `amka` varchar(40) NOT NULL,
  `aney` int(11) NOT NULL,
  `aney_xr` int(11) NOT NULL COMMENT '�����. ������ ��. ���� ��������',
  `aney_apo` date NOT NULL COMMENT '��. ���� ���',
  `aney_ews` date NOT NULL COMMENT '��. ���� ���',
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `wres` int(11) NOT NULL COMMENT '���������� ���� (����� ���� ���������)',
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
  `email` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=greek;

-- --------------------------------------------------------

--
-- Table structure for table `employee_deleted`
--

CREATE TABLE IF NOT EXISTS `employee_deleted` (
  `id` int(11) NOT NULL,
  `name` varchar(20) NOT NULL,
  `surname` varchar(30) NOT NULL,
  `patrwnymo` varchar(30) NOT NULL,
  `mhtrwnymo` varchar(30) NOT NULL,
  `klados` int(11) NOT NULL,
  `am` int(10) NOT NULL,
  `sx_organikhs` int(11) NOT NULL,
  `sx_yphrethshs` int(11) NOT NULL,
  `thesi` tinyint(4) NOT NULL COMMENT '0 �������������, 1 �������������, 2 �/����-��/���, 3 ��.�������, 4 �����������, 5 ���������',
  `fek_dior` varchar(10) NOT NULL,
  `hm_dior` date NOT NULL,
  `vathm_old` varchar(4) NOT NULL,
  `vathm` varchar(4) NOT NULL,
  `hm_vathm` date NOT NULL,
  `mk_old` tinyint(2) NOT NULL,
  `mk` tinyint(2) NOT NULL,
  `hm_mk` date NOT NULL,
  `mk11` tinyint(4) NOT NULL,
  `hm_mk11` date NOT NULL,
  `analipsi` varchar(5) NOT NULL,
  `hm_anal` date NOT NULL,
  `met_did` tinyint(11) NOT NULL COMMENT '0 ���, 1 ������, 2 ���, 3 ���+���',
  `proyp_old` int(11) NOT NULL COMMENT 'se hmeres',
  `proyp` int(11) NOT NULL COMMENT 'se hmeres (apo excel)',
  `proyp_not` int(11) NOT NULL COMMENT 'proyp poy de lambanetai gia ypologismo wrarioy, se hmeres',
  `anatr_katataksi` int(11) NOT NULL COMMENT 'se hmeres',
  `anatr` int(11) NOT NULL COMMENT 'se hmeres',
  `anatr_excel` int(11) NOT NULL COMMENT 'se hmeres',
  `comments` longtext NOT NULL,
  `status` int(11) NOT NULL COMMENT '1 ���������, 2 ���� ������-���������, 3 ?����, 4 �������������',
  `afm` varchar(11) NOT NULL,
  `eidikh` tinyint(1) NOT NULL,
  `tel` varchar(40) NOT NULL,
  `address` varchar(80) NOT NULL,
  `idnum` varchar(40) NOT NULL,
  `amka` varchar(40) NOT NULL,
  `aney` int(11) NOT NULL,
  `aney_xr` int(11) NOT NULL COMMENT '�����. ������ ��. ���� ��������',
  `aney_apo` date NOT NULL COMMENT '��. ���� ���',
  `aney_ews` date NOT NULL COMMENT '��. ���� ���',
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `wres` int(11) NOT NULL COMMENT '���������� ���� (����� ���� ���������)',
  `idiwtiko` tinyint(4) NOT NULL,
  `idiwtiko_id` tinyint(4) NOT NULL,
  `idiwtiko_liksi` date NOT NULL,
  `idiwtiko_enarxi` date NOT NULL,
  `idiwtiko_id_enarxi` date NOT NULL,
  `idiwtiko_id_liksi` date NOT NULL,
  `katoikon` tinyint(4) NOT NULL,
  `katoikon_apo` date NOT NULL,
  `katoikon_ews` date NOT NULL,
  `katoikon_comm` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=greek;

-- --------------------------------------------------------

--
-- Table structure for table `employee_log`
--

CREATE TABLE IF NOT EXISTS `employee_log` (
  `emp_id` int(11) NOT NULL,
  `userid` int(11) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `action` int(11) NOT NULL COMMENT '0 add, 1 edit, 2 delete',
  `ip` varchar(30) NOT NULL,
  `query` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=greek;

-- --------------------------------------------------------

--
-- Table structure for table `employee_moved`
--

CREATE TABLE IF NOT EXISTS `employee_moved` (
  `id` int(11) NOT NULL,
  `name` varchar(20) NOT NULL,
  `surname` varchar(30) NOT NULL,
  `patrwnymo` varchar(30) NOT NULL,
  `mhtrwnymo` varchar(30) NOT NULL,
  `klados` int(11) NOT NULL,
  `am` int(10) NOT NULL,
  `sx_organikhs` int(11) NOT NULL,
  `sx_yphrethshs` int(11) NOT NULL,
  `thesi` tinyint(4) NOT NULL COMMENT '0 �������������, 1 �������������, 2 �/����-��/���, 3 ��.�������, 4 �����������, 5 ���������',
  `fek_dior` varchar(10) NOT NULL,
  `hm_dior` date NOT NULL,
  `vathm_old` varchar(4) NOT NULL,
  `vathm` varchar(4) NOT NULL,
  `hm_vathm` date NOT NULL,
  `mk_old` tinyint(2) NOT NULL,
  `mk` tinyint(2) NOT NULL,
  `hm_mk` date NOT NULL,
  `mk11` tinyint(4) NOT NULL,
  `hm_mk11` date NOT NULL,
  `analipsi` varchar(5) NOT NULL,
  `hm_anal` date NOT NULL,
  `met_did` tinyint(11) NOT NULL COMMENT '0 ���, 1 ������, 2 ���, 3 ���+���',
  `proyp_old` int(11) NOT NULL COMMENT 'se hmeres',
  `proyp` int(11) NOT NULL COMMENT 'se hmeres (apo excel)',
  `proyp_not` int(11) NOT NULL COMMENT 'proyp poy de lambanetai gia ypologismo wrarioy, se hmeres',
  `anatr_katataksi` int(11) NOT NULL COMMENT 'se hmeres',
  `anatr` int(11) NOT NULL COMMENT 'se hmeres',
  `anatr_excel` int(11) NOT NULL COMMENT 'se hmeres',
  `comments` longtext NOT NULL,
  `status` int(11) NOT NULL COMMENT '1 ���������, 2 ���� ������-���������, 3 ?����, 4 �������������',
  `afm` varchar(11) NOT NULL,
  `eidikh` tinyint(1) NOT NULL,
  `tel` varchar(40) NOT NULL,
  `address` varchar(80) NOT NULL,
  `idnum` varchar(40) NOT NULL,
  `amka` varchar(40) NOT NULL,
  `aney` int(11) NOT NULL,
  `aney_xr` int(11) NOT NULL COMMENT '�����. ������ ��. ���� ��������',
  `aney_apo` date NOT NULL COMMENT '��. ���� ���',
  `aney_ews` date NOT NULL COMMENT '��. ���� ���',
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `wres` int(11) NOT NULL COMMENT '���������� ���� (����� ���� ���������)',
  `idiwtiko` tinyint(4) NOT NULL,
  `idiwtiko_id` tinyint(4) NOT NULL,
  `idiwtiko_liksi` date NOT NULL,
  `idiwtiko_enarxi` date NOT NULL,
  `idiwtiko_id_enarxi` date NOT NULL,
  `idiwtiko_id_liksi` date NOT NULL,
  `katoikon` tinyint(4) NOT NULL,
  `katoikon_apo` date NOT NULL,
  `katoikon_ews` date NOT NULL,
  `katoikon_comm` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `klados`
--

CREATE TABLE IF NOT EXISTS `klados` (
  `id` int(11) NOT NULL,
  `perigrafh` varchar(30) NOT NULL,
  `onoma` varchar(30) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=greek;

--
-- Dumping data for table `klados`
--

INSERT INTO `klados` (`id`, `perigrafh`, `onoma`) VALUES
(1, '��60', '����������'),
(2, '��70', '��������'),
(3, '��06', '��������'),
(4, '��08', '�������������'),
(5, '��11', '������� ������'),
(6, '��79', '��������'),
(7, '��09', '�������������'),
(8, '��23', '���������'),
(9, '��26', '��������������'),
(10, '��28', '����������������'),
(11, '��30', '����.����������'),
(12, '��1���', '����.����.���.��.'),
(13, '��05', '��������'),
(14, '��07', '����������'),
(15, '��86', '������������'),
(16, '��60.50', '���������� ���.��.'),
(17, '��61', '���������� ���.��.'),
(18, '��70.50', '�������� ���.����.'),
(19, '��71', '�������� ���.����.'),
(20, '��91', '��������� �������'),
(21, '��25', '��.����������'),
(22, '��01', '�����������'),
(23, '��01', '�����������'),
(24, '��01', '�����������'),
(25, '��03', '�����������'),
(26, '��21', '��������������'),
(27, '��29', '��������������');

-- --------------------------------------------------------

--
-- Table structure for table `logon`
--

CREATE TABLE IF NOT EXISTS `logon` (
  `userid` int(11) NOT NULL,
  `useremail` varchar(50) NOT NULL DEFAULT '',
  `password` varchar(50) NOT NULL DEFAULT '',
  `userlevel` int(1) NOT NULL DEFAULT '0' COMMENT '0 admin, 1 add/delete/edit, 2 edit, 3 view only',
  `requests` tinyint(1) NOT NULL,
  `adeia` tinyint(4) NOT NULL COMMENT 'an 1 tote ektypwnei adeies',
  `username` varchar(20) NOT NULL,
  `lastlogin` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=greek;

--
-- Dumping data for table `logon`
--

INSERT INTO `logon` (`userid`, `useremail`, `password`, `userlevel`, `adeia`, `username`, `lastlogin`) VALUES
(1, 'admin@test.com', 'admin', 0, 0, 'admin', '2018-10-15 10:27:11');

-- --------------------------------------------------------

--
-- Table structure for table `params`
--

CREATE TABLE IF NOT EXISTS `params` (
  `id` int(11) NOT NULL,
  `name` varchar(20) NOT NULL COMMENT '����� ����������',
  `value` varchar(100) NOT NULL COMMENT '���� ����������',
  `descr` varchar(100) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=greek;

--
-- Dumping data for table `params`
--

INSERT INTO `params` (`id`, `name`, `value`, `descr`) VALUES
(5, 'sxol_etos', '201819', '������� ���� (�� ������� ���� 1� ���������)'),
(6, 'head_title', '� �/���� �.�. XXXXXX', '������ �/��� (��� ����������)'),
(7, 'head_name', 'XXXXX XXXXX', '������������� �/���'),
(8, 'endofyear', '21-06-2019', '����� ������� ���������� �����������'),
(9, 'endofyear2', '21-06-2019', '��������� ����� �������� ����������� (��� ����������)'),
(10, 'protapol', '99999', '���������� ��������'),
(11, 'yp_wr', '24', '����������� ������ ��������'),
(12, 'dnsh', '�.�. XXXXXXX', '��������� �����������');

-- --------------------------------------------------------

--
-- Table structure for table `praxi`
--

CREATE TABLE IF NOT EXISTS `praxi` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `ya` varchar(50) NOT NULL,
  `ada` varchar(20) NOT NULL,
  `apofasi` varchar(100) NOT NULL COMMENT '������� �����������',
  `ada_apof` varchar(30) NOT NULL,
  `sxolio` varchar(300) NOT NULL,
  `type` varchar(10) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=greek;

--
-- Dumping data for table `praxi`
--

INSERT INTO `praxi` (`id`, `name`, `ya`, `ada`, `apofasi`, `sxolio`, `type`) VALUES
(1, '�����', '', '', '', '', '');

-- --------------------------------------------------------

--
-- Table structure for table `praxi_old`
--

CREATE TABLE IF NOT EXISTS `praxi_old` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `ya` varchar(50) NOT NULL,
  `ada` varchar(20) NOT NULL,
  `apofasi` varchar(100) NOT NULL COMMENT '������� �����������',
  `ada_apof` varchar(30) NOT NULL,
  `sxolio` varchar(300) NOT NULL,
  `type` varchar(10) NOT NULL,
  `sxoletos` int(11) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=greek;


--
-- Table structure for table `school`
--

CREATE TABLE IF NOT EXISTS `school` (
  `id` int(11) NOT NULL,
  `code` varchar(10) NOT NULL COMMENT '7-������ ������� ����������',
  `category` int(10) NOT NULL COMMENT '��������� ��������: 1 - �, 2 - � ���.',
  `type` int(4) NOT NULL COMMENT '0 �����, 1 ���, 2 ���.',
  `eaep` int(2) NOT NULL,
  `name` varchar(40) NOT NULL,
  `address` varchar(40) NOT NULL,
  `tk` int(5) NOT NULL COMMENT '������������ �������',
  `tel` varchar(18) NOT NULL,
  `fax` varchar(18) NOT NULL,
  `email` varchar(50) NOT NULL,
  `organikothta` int(11) NOT NULL,
  `organikes` text NOT NULL,
  `leitoyrg` int(11) NOT NULL COMMENT '���������������',
  `students` text NOT NULL COMMENT '�� ������ �� ���������� �� �����',
  `tmimata` varchar(60) NOT NULL COMMENT 'N� ���������� �� �����',
  `ekp_ee` varchar(10) NOT NULL COMMENT 'N� ���������� �� �����',
  `entaksis` varchar(30) NOT NULL,
  `ypodoxis` int(11) NOT NULL,
  `frontistiriako` int(11) NOT NULL,
  `ted` smallint(6) NOT NULL,
  `oloimero` int(11) NOT NULL,
  `oloimero_stud` int(11) NOT NULL,
  `oloimero_tea` int(11) NOT NULL COMMENT '�������� ���������',
  `oloimero_nip` varchar(40) NOT NULL COMMENT 'N� ���������� �� �����',
  `klasiko` varchar(40) NOT NULL COMMENT 'N� ���������� �� �����',
  `nip` varchar(20) NOT NULL COMMENT 'N� ���������� �� �����',
  `comments` text NOT NULL,
  `kena_org` varchar(200) NOT NULL,
  `kena_leit` varchar(200) NOT NULL,
  `type2` tinyint(1) NOT NULL COMMENT '0 �������, 1 ��������, 2 ������',
  `dimos` tinyint(4) NOT NULL COMMENT '��� ��� ������ dimos',
  `titlos` varchar(100) NOT NULL COMMENT '������ �������� (���������)',
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `anenergo` tinyint(4) NOT NULL,
  `perif` int(11) NOT NULL,
  `systeg` int(11) NOT NULL,
  `vivliothiki` tinyint(1) NOT NULL,
  `archive` text NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=405 DEFAULT CHARSET=greek;

--
-- Dumping data for table `school`
--

INSERT INTO `school` (`id`, `code`, `category`, `type`, `eaep`, `name`, `address`, `tk`, `tel`, `fax`, `email`, `organikothta`, `organikes`, `leitoyrg`, `students`, `tmimata`, `ekp_ee`, `entaksis`, `ypodoxis`, `frontistiriako`, `ted`, `oloimero`, `oloimero_stud`, `oloimero_tea`, `oloimero_nip`, `klasiko`, `nip`, `comments`, `kena_org`, `kena_leit`, `type2`, `dimos`, `titlos`, `updated`, `anenergo`, `perif`, `systeg`, `vivliothiki`) VALUES
(1, '2222222', 0, 0, 0, '������� �����', '', 0, '0', '0', '', 0, '', 0, '', '', '', '0', 0, 0, 0, 0, 0, 0, '', '', '', '', '', '', 0, 0, '������� �����', '2015-06-16 12:04:27', 0, 0, 0, 0),
(387, '1234567', 0, 0, 0, '?������', '', 0, '', '', '', 0, '', 0, '', '', '', '0', 0, 0, 0, 0, 0, 0, '', '', '', '', '', '', 0, 0, '?������', '2015-06-16 12:04:16', 0, 0, 0, 0),
(388, '', 0, 0, 0, '���� �����', '', 0, '', '', '', 0, '', 0, '', '', '', '0', 0, 0, 0, 0, 0, 0, '', '', '', '', '', '', 0, 0, '?��� �����', '0000-00-00 00:00:00', 0, 0, 0, 0),
(389, '', 0, 0, 0, '�������� �� �����', '', 0, '', '', '', 0, '', 0, '', '', '', '0', 0, 0, 0, 0, 0, 0, '', '', '', '', '', '', 0, 0, '�������� �� �����', '0000-00-00 00:00:00', 0, 0, 0, 0),
(394, '', 0, 0, 0, '���� �����', '', 0, '', '', '', 0, '', 0, '', '', '', '0', 0, 0, 0, 0, 0, 0, '', '', '', '', '', '', 0, 0, '?��� �����', '0000-00-00 00:00:00', 0, 0, 0, 0),
(397, '', 0, 0, 0, '�������� ���������', '', 0, '', '', '', 0, '', 0, '', '', '', '0', 0, 0, 0, 0, 0, 0, '', '', '', '', '', '', 0, 0, '�������� ���������', '0000-00-00 00:00:00', 0, 0, 0, 0),
(398, '', 0, 0, 0, '�/��� �� ���������', '������������ 15', 0, '2810529300', '', 'mail@dipe.ira.sch.gr', 0, '', 0, '', '', '', '0', 0, 0, 0, 0, 0, 0, '', '', '', '��� ������������', '', '', 0, 4, '�/��� �� ���������', '2018-10-15 10:33:24', 0, 0, 0, 0),
(399, '', 0, 0, 0, '�������� ��� ���������', '', 0, '', '', '', 0, '', 0, '', '', '', '0', 0, 0, 0, 0, 0, 0, '', '', '', '', '', '', 0, 0, '�������� ��� ���������', '0000-00-00 00:00:00', 0, 0, 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `yphrethsh`
--

CREATE TABLE IF NOT EXISTS `yphrethsh` (
  `id` int(11) NOT NULL,
  `emp_id` int(10) NOT NULL,
  `yphrethsh` varchar(10) NOT NULL,
  `hours` varchar(10) NOT NULL,
  `organikh` varchar(10) NOT NULL,
  `sxol_etos` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=greek;

-- --------------------------------------------------------

--
-- Table structure for table `yphrethsh_ekt`
--

CREATE TABLE IF NOT EXISTS `yphrethsh_ekt` (
  `id` int(11) NOT NULL,
  `emp_id` int(10) NOT NULL,
  `yphrethsh` varchar(10) NOT NULL,
  `hours` varchar(10) NOT NULL,
  `sxol_etos` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=greek;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `adeia`
--
ALTER TABLE `adeia`
  ADD PRIMARY KEY (`id`),
  ADD KEY `emp_id` (`emp_id`);

--
-- Indexes for table `adeia_deleted`
--
ALTER TABLE `adeia_deleted`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `adeia_del_log`
--
ALTER TABLE `adeia_del_log`
  ADD PRIMARY KEY (`adeia_id`);

--
-- Indexes for table `adeia_ekt`
--
ALTER TABLE `adeia_ekt`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `adeia_ekt_deleted`
--
ALTER TABLE `adeia_ekt_deleted`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `adeia_ekt_type`
--
ALTER TABLE `adeia_ekt_type`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `adeia_type`
--
ALTER TABLE `adeia_type`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `apofaseis`
--
ALTER TABLE `apofaseis`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `dimos`
--
ALTER TABLE `dimos`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ekdromi`
--
ALTER TABLE `ekdromi`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ektaktoi`
--
ALTER TABLE `ektaktoi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `klados` (`klados`);

--
-- Indexes for table `ektaktoi_log`
--
ALTER TABLE `ektaktoi_log`
  ADD PRIMARY KEY (`timestamp`),
  ADD KEY `userid` (`userid`);

--
-- Indexes for table `ektaktoi_old`
--
ALTER TABLE `ektaktoi_old`
  ADD PRIMARY KEY (`id`,`sxoletos`);

--
-- Indexes for table `ektaktoi_types`
--
ALTER TABLE `ektaktoi_types`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `employee`
--
ALTER TABLE `employee`
  ADD PRIMARY KEY (`id`),
  ADD KEY `klados` (`klados`),
  ADD KEY `sx_organikhs` (`sx_organikhs`),
  ADD KEY `sx_yphrethshs` (`sx_yphrethshs`);

--
-- Indexes for table `employee_deleted`
--
ALTER TABLE `employee_deleted`
  ADD PRIMARY KEY (`id`),
  ADD KEY `klados` (`klados`),
  ADD KEY `sx_organikhs` (`sx_organikhs`);

--
-- Indexes for table `employee_log`
--
ALTER TABLE `employee_log`
  ADD PRIMARY KEY (`timestamp`),
  ADD KEY `userid` (`userid`);

--
-- Indexes for table `klados`
--
ALTER TABLE `klados`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `logon`
--
ALTER TABLE `logon`
  ADD PRIMARY KEY (`userid`);

--
-- Indexes for table `params`
--
ALTER TABLE `params`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `praxi`
--
ALTER TABLE `praxi`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `praxi_old`
--
ALTER TABLE `praxi_old`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `school`
--
ALTER TABLE `school`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `yphrethsh`
--
ALTER TABLE `yphrethsh`
  ADD PRIMARY KEY (`id`),
  ADD KEY `emp_id` (`emp_id`),
  ADD KEY `yphrethsh` (`yphrethsh`),
  ADD KEY `sxol_etos` (`sxol_etos`);

--
-- Indexes for table `yphrethsh_ekt`
--
ALTER TABLE `yphrethsh_ekt`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `adeia`
--
ALTER TABLE `adeia`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `adeia_deleted`
--
ALTER TABLE `adeia_deleted`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `adeia_ekt`
--
ALTER TABLE `adeia_ekt`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `adeia_ekt_deleted`
--
ALTER TABLE `adeia_ekt_deleted`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `adeia_ekt_type`
--
ALTER TABLE `adeia_ekt_type`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=19;
--
-- AUTO_INCREMENT for table `adeia_type`
--
ALTER TABLE `adeia_type`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=15;
--
-- AUTO_INCREMENT for table `apofaseis`
--
ALTER TABLE `apofaseis`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `dimos`
--
ALTER TABLE `dimos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=9;
--
-- AUTO_INCREMENT for table `ekdromi`
--
ALTER TABLE `ekdromi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `ektaktoi`
--
ALTER TABLE `ektaktoi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `ektaktoi_types`
--
ALTER TABLE `ektaktoi_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=7;
--
-- AUTO_INCREMENT for table `employee`
--
ALTER TABLE `employee`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `employee_deleted`
--
ALTER TABLE `employee_deleted`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `klados`
--
ALTER TABLE `klados`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=28;
--
-- AUTO_INCREMENT for table `logon`
--
ALTER TABLE `logon`
  MODIFY `userid` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=17;
--
-- AUTO_INCREMENT for table `params`
--
ALTER TABLE `params`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=12;
--
-- AUTO_INCREMENT for table `praxi`
--
ALTER TABLE `praxi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=8;
--
-- AUTO_INCREMENT for table `school`
--
ALTER TABLE `school`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=405;
--
-- AUTO_INCREMENT for table `yphrethsh`
--
ALTER TABLE `yphrethsh`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `yphrethsh_ekt`
--
ALTER TABLE `yphrethsh_ekt`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

CREATE TABLE `school_requests` (
  `id` int(11) NOT NULL COMMENT '�/�',
  `request` text NOT NULL COMMENT '������ �������',
  `comment` text COMMENT '������ �/����',
  `school` int(11) NOT NULL COMMENT '���. ��������',
  `done` int(11) NOT NULL DEFAULT '0' COMMENT '��������������',
  `submitted` datetime NOT NULL COMMENT '����������',
  `handled` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP COMMENT '�������������� ����',
  `school_name` text NOT NULL COMMENT '����� ��������',
  `sxol_etos` int(11) NOT NULL COMMENT '������� ����',
  `hidden` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Indexes for table `school_requests`
--
ALTER TABLE `school_requests`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for table `school_requests`
--
ALTER TABLE `school_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '�/�';

CREATE TABLE IF NOT EXISTS `school_log` (
  `id` int(11) NOT NULL,
  `school_id` int(11) NOT NULL,
  `school` varchar(100) NOT NULL,
  `action` text NOT NULL,
  `datetime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `school_log`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `school_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
