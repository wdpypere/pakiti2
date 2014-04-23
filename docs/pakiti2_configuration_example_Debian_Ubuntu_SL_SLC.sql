-- MySQL dump 10.11
--
-- Host: localhost    Database: pakiti_new
-- ------------------------------------------------------
-- Server version	5.0.32-Debian_7etch11

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `repositories`
--

DROP TABLE IF EXISTS `repositories`;
CREATE TABLE `repositories` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(1024) NOT NULL,
  `url` varchar(1024) NOT NULL,
  `is_sec` int(1) NOT NULL,
  `type` char(5) NOT NULL,
  `enabled` int(1) default NULL,
  `last_access_ok` int(1) default NULL,
  `arch_id` int(10) unsigned default NULL,
  `os_group_id` int(10) unsigned default NULL,
  `file_checksum` char(33) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

--
-- Dumping data for table `repositories`
--

LOCK TABLES `repositories` WRITE;
/*!40000 ALTER TABLE `repositories` DISABLE KEYS */;
INSERT INTO `repositories` (`id`, `name`, `url`, `is_sec`, `type`, `enabled`, `last_access_ok`, `arch_id`, `os_group_id`, `file_checksum`) VALUES (2,'Debian 4.0 i686 contrib','http://ftp.zcu.cz/mirrors/debian/dists/etch/contrib/binary-i386/Packages.gz',0,'dpkg',1,1,1,2,NULL),(63,'Debian 4.0 x86_64 contrib','http://ftp.zcu.cz/mirrors/debian/dists/etch/contrib/binary-amd64/Packages.gz',0,'dpkg',1,1,2,2,NULL),(64,'Debian 4.0 x86_64 contrib sec','http://security.debian.org/debian-security/dists/etch/updates/contrib/binary-amd64/Packages.gz',1,'dpkg',1,1,2,2,NULL),(65,'Debian 4.0 x86_64 main','http://ftp.zcu.cz/mirrors/debian/dists/etch/main/binary-amd64/Packages.gz',0,'dpkg',1,1,2,2,NULL),(66,'Debian 4.0 x86_64 main sec','http://security.debian.org/debian-security/dists/etch/updates/main/binary-amd64/Packages.gz',1,'dpkg',1,1,2,2,NULL),(7,'Debian 5.0 i686 contrib sec','http://security.debian.org/debian-security/dists/lenny/updates/contrib/binary-i386/Packages.gz',1,'dpkg',1,1,1,5,NULL),(8,'Debian 5.0 i686 main sec','http://security.debian.org/debian-security/dists/lenny/updates/main/binary-i386/Packages.gz',1,'dpkg',1,1,1,5,NULL),(9,'Debian 5.0 i686 non-free sec','http://security.debian.org/debian-security/dists/lenny/updates/non-free/binary-i386/Packages.gz',0,'dpkg',1,1,1,5,NULL),(10,'Debian 5.0 x86_64 contrib sec','http://security.debian.org/debian-security/dists/lenny/updates/contrib/binary-amd64/Packages.gz',1,'dpkg',1,1,2,5,NULL),(11,'Debian 5.0 x86_64 main sec','http://security.debian.org/debian-security/dists/lenny/updates/main/binary-amd64/Packages.gz',0,'dpkg',1,1,2,5,NULL),(12,'Debian 5.0 x86_64 non-free sec','http://security.debian.org/debian-security/dists/lenny/updates/non-free/binary-amd64/Packages.gz',0,'dpkg',1,1,2,5,NULL),(13,'Ubuntu 8.04 main','http://cz.archive.ubuntu.com/ubuntu/dists/hardy/main/binary-i386/Packages.gz',0,'dpkg',1,1,1,12,NULL),(14,'Ubuntu 8.04 main','http://cz.archive.ubuntu.com/ubuntu/dists/hardy/main/binary-amd64/Packages.gz',0,'dpkg',1,1,2,12,NULL),(15,'Ubuntu 8.04 main security','http://cz.archive.ubuntu.com/ubuntu/dists/hardy-security/main/binary-i386/Packages.gz',1,'dpkg',1,1,1,12,NULL),(16,'Ubuntu 8.04 main security','http://cz.archive.ubuntu.com/ubuntu/dists/hardy-security/main/binary-amd64/Packages.gz',0,'dpkg',1,1,2,12,NULL),(17,'Ubuntu 8.04 restricted','http://cz.archive.ubuntu.com/ubuntu/dists/hardy/restricted/binary-i386/Packages.gz',0,'dpkg',1,1,1,12,NULL),(18,'Ubuntu 8.04 restricted','http://cz.archive.ubuntu.com/ubuntu/dists/hardy/restricted/binary-amd64/Packages.gz',0,'dpkg',1,1,2,12,NULL),(19,'Ubuntu 8.04 restricted security','http://cz.archive.ubuntu.com/ubuntu/dists/hardy-security/restricted/binary-i386/Packages.gz',1,'dpkg',1,1,1,12,NULL),(20,'Ubuntu 8.04 restricted security','http://cz.archive.ubuntu.com/ubuntu/dists/hardy-security/restricted/binary-amd64/Packages.gz',1,'dpkg',1,1,2,12,NULL),(21,'SL 5.2 CERN','http://ftp.scientificlinux.org/linux/scientific/52/i386/SL/repodata/primary.xml.gz',0,'rpm',1,1,1,13,NULL),(22,'SL 5.2 CERN updates','http://ftp.scientificlinux.org/linux/scientific/52/i386/updates/security/repodata/primary.xml.gz',1,'rpm',1,1,1,13,NULL),(23,'SL 5.2 CERN','http://ftp.scientificlinux.org/linux/scientific/52/x86_64/SL/repodata/primary.xml.gz',0,'rpm',1,1,2,13,NULL),(24,'SL 5.2 CERN updates','http://ftp.scientificlinux.org/linux/scientific/52/x86_64/updates/security/repodata/primary.xml.gz',1,'rpm',1,1,2,13,NULL),(25,'OpenSuSE 11.0','ftp://ftp5.gwdg.de/pub/linux/suse/opensuse/update/11.0/repodata/primary.xml.gz',0,'rpm',1,1,2,15,NULL),(26,'SL 4.8 CERN os','http://ftp.scientificlinux.org/linux/scientific/48/i386/SL/RPMS/repodata/primary.xml.gz',0,'rpm',1,1,1,19,NULL),(27,'SL 4.8 CERN os','http://ftp.scientificlinux.org/linux/scientific/48/x86_64/SL/RPMS/repodata/primary.xml.gz',0,'rpm',1,1,2,19,NULL),(28,'SL 4.8 CERN updates','http://ftp.scientificlinux.org/linux/scientific/48/i386/errata/SL/RPMS/repodata/primary.xml.gz',1,'rpm',1,1,1,19,NULL),(29,'SL 4.8 CERN updates','http://ftp.scientificlinux.org/linux/scientific/48/x86_64/errata/SL/RPMS/repodata/primary.xml.gz',1,'rpm',1,1,2,19,NULL),(30,'SL 4.7 CERN updates','http://ftp.scientificlinux.org/linux/scientific/47/x86_64/errata/SL/RPMS/repodata/primary.xml.gz',1,'rpm',1,1,2,17,NULL),(31,'SL 4.6 CERN os','http://ftp.scientificlinux.org/linux/scientific/46/i386/SL/RPMS/repodata/primary.xml.gz',0,'rpm',1,1,1,18,NULL),(32,'SL 4.6 CERN os','http://ftp.scientificlinux.org/linux/scientific/46/x86_64/SL/RPMS/repodata/primary.xml.gz',0,'rpm',1,1,2,18,NULL),(33,'SL 4.6 CERN updates','http://ftp.scientificlinux.org/linux/scientific/46/i386/errata/SL/RPMS/repodata/primary.xml.gz',1,'rpm',1,1,1,18,NULL),(34,'SL 4.6 CERN updates','http://ftp.scientificlinux.org/linux/scientific/46/x86_64/errata/SL/RPMS/repodata/primary.xml.gz',1,'rpm',1,1,2,18,NULL),(35,'SL 4.5 CERN os','http://ftp.scientificlinux.org/linux/scientific/45/i386/SL/RPMS/repodata/primary.xml.gz',0,'rpm',1,1,1,16,NULL),(36,'SL 4.5 CERN os','http://ftp.scientificlinux.org/linux/scientific/45/x86_64/SL/RPMS/repodata/primary.xml.gz',0,'rpm',1,1,2,16,NULL),(37,'SL 4.5 CERN updates','http://ftp.scientificlinux.org/linux/scientific/45/i386/errata/SL/RPMS/repodata/primary.xml.gz',1,'rpm',1,1,1,16,NULL),(38,'SL 4.5 CERN updates','http://ftp.scientificlinux.org/linux/scientific/45/x86_64/errata/SL/RPMS/repodata/primary.xml.gz',1,'rpm',1,1,2,16,NULL),(39,'SL 5.3 CERN os','http://ftp.scientificlinux.org/linux/scientific/53/i386/SL/repodata/primary.xml.gz',0,'rpm',1,1,1,14,NULL),(40,'SL 5.3 CERN os','http://ftp.scientificlinux.org/linux/scientific/53/x86_64/SL/repodata/primary.xml.gz',0,'rpm',1,1,2,14,NULL),(41,'SL 5.3 CERN updates','http://ftp.scientificlinux.org/linux/scientific/53/i386/updates/security/repodata/primary.xml.gz',1,'rpm',1,1,1,14,NULL),(42,'SL 5.3 CERN updates','http://ftp.scientificlinux.org/linux/scientific/53/x86_64/updates/security/repodata/primary.xml.gz',1,'rpm',1,1,2,14,NULL),(43,'SLC 4.6 os','http://linuxsoft.cern.ch/cern/slc46/i386/yum/os/repodata/primary.xml.gz',0,'rpm',1,1,1,10,NULL),(44,'SLC 4.6 os','http://linuxsoft.cern.ch/cern/slc46/x86_64/yum/os/repodata/primary.xml.gz',0,'rpm',1,1,2,10,NULL),(45,'SLC 4.6 updates','http://linuxsoft.cern.ch/cern/slc46/i386/yum/updates/repodata/primary.xml.gz',1,'rpm',1,1,1,10,NULL),(46,'SLC 4.6 updates','http://linuxsoft.cern.ch/cern/slc46/x86_64/yum/updates/repodata/primary.xml.gz',1,'rpm',1,1,2,10,NULL),(47,'SLC 4.7 os','http://linuxsoft.cern.ch/cern/slc47/i386/yum/os/repodata/primary.xml.gz',0,'rpm',1,1,1,11,NULL),(48,'SLC 4.7 os','http://linuxsoft.cern.ch/cern/slc47/x86_64/yum/os/repodata/primary.xml.gz',0,'rpm',1,1,2,11,NULL),(49,'SLC 4.7 updates','http://linuxsoft.cern.ch/cern/slc47/i386/yum/updates/repodata/primary.xml.gz',1,'rpm',1,1,1,11,NULL),(50,'SLC 4.7 updates','http://linuxsoft.cern.ch/cern/slc47/x86_64/yum/updates/repodata/primary.xml.gz',1,'rpm',1,1,2,11,NULL),(51,'SLC 4.8 os','http://linuxsoft.cern.ch/cern/slc48/i386/yum/os/repodata/primary.xml.gz',0,'rpm',1,1,1,9,NULL),(52,'SLC 4.8 os','http://linuxsoft.cern.ch/cern/slc48/x86_64/yum/os/repodata/primary.xml.gz',0,'rpm',1,1,2,9,NULL),(53,'SLC 4.8 updates','http://linuxsoft.cern.ch/cern/slc48/i386/yum/updates/repodata/primary.xml.gz',1,'rpm',1,1,1,9,NULL),(54,'SLC 4.8 updates','http://linuxsoft.cern.ch/cern/slc48/x86_64/yum/updates/repodata/primary.xml.gz',1,'rpm',1,1,2,9,NULL),(55,'Ubuntu 6.06 os','http://cz.archive.ubuntu.com/ubuntu/dists/dapper/main/binary-i386/Packages.gz',0,'dpkg',1,1,1,20,NULL),(56,'Ubuntu 6.06 os security','http://cz.archive.ubuntu.com/ubuntu/dists/dapper-security/main/binary-i386/Packages.gz',1,'dpkg',1,1,1,20,NULL),(59,'Ubuntu 6.06 restricted','http://cz.archive.ubuntu.com/ubuntu/dists/dapper/restricted/binary-i386/Packages.gz',0,'dpkg',1,1,1,20,NULL),(58,'Ubuntu 6.06 restricted security','http://cz.archive.ubuntu.com/ubuntu/dists/dapper-security/restricted/binary-i386/Packages.gz',1,'dpkg',1,1,1,20,NULL),(60,'Debian 4.0 i686 main sec','http://security.debian.org/debian-security/dists/etch/updates/main/binary-i386/Packages.gz',1,'dpkg',1,1,1,2,NULL),(61,'Debian 4.0 i686 main','http://ftp.zcu.cz/mirrors/debian/dists/etch/main/binary-i386/Packages.gz',0,'dpkg',1,1,1,2,NULL),(62,'Debian 4.0 i686 contrib sec','http://security.debian.org/debian-security/dists/etch/updates/contrib/binary-i386/Packages.gz',1,'dpkg',1,1,1,2,NULL);
/*!40000 ALTER TABLE `repositories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `os`
--

DROP TABLE IF EXISTS `os`;
CREATE TABLE `os` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `os` varchar(100) default NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `os` (`os`),
  KEY `os_index` (`os`(50))
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

--
-- Dumping data for table `os`
--

LOCK TABLES `os` WRITE;
/*!40000 ALTER TABLE `os` DISABLE KEYS */;
INSERT INTO `os` (`id`, `os`) VALUES (1,'Debian 4.0'),(2,'Scientific Linux CERN SLC release 4.8 (Beryllium)'),(3,'Debian 5.0.2'),(4,'Debian 5.0.3'),(5,'Ubuntu 8.04.3 LTS'),(6,'Debian 5.0.1'),(8,'Ubuntu 8.04.2'),(9,'Scientific Linux SL release 5.2 (Boron)'),(14,'Scientific Linux SL release 4.5 (Beryllium)'),(15,'Scientific Linux SL release 4.7 (Beryllium)'),(16,'Scientific Linux SL release 4.6 (Beryllium)'),(17,'Scientific Linux SL release 4.8 (Beryllium)'),(18,'Scientific Linux CERN SLC release 4.6 (Beryllium)'),(20,'Scientific Linux SL release 5.3 (Boron)'),(21,'Scientific Linux CERN SLC release 4.7 (Beryllium)'),(22,'Ubuntu 6.06 LTS'),(550,'SUSE LINUX 10.0 (X86-64)'),(551,'SUSE LINUX 9.3 (X86-64)'),(552,'Debian squeeze/sid'),(553,'openSUSE 11.0 (X86-64)'),(554,'SUSE LINUX 10.1 (X86-64)'),(555,'Scientific Linux SL release 3.0.9 (SL)'),(559,'Ubuntu 9.10'),(558,'Ubuntu 9.04'),(560,'Fedora release 12 (Constantine)'),(561,'SUSE Linux Enterprise Server 11 (x86_64)');
/*!40000 ALTER TABLE `os` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `arch`
--

DROP TABLE IF EXISTS `arch`;
CREATE TABLE `arch` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `arch` char(15) default NULL,
  PRIMARY KEY  (`id`),
  KEY `arch_index` (`arch`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

--
-- Dumping data for table `arch`
--

LOCK TABLES `arch` WRITE;
/*!40000 ALTER TABLE `arch` DISABLE KEYS */;
INSERT INTO `arch` (`id`, `arch`) VALUES (1,'i686'),(2,'x86_64'),(3,'unknown');
/*!40000 ALTER TABLE `arch` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `os_group`
--

DROP TABLE IF EXISTS `os_group`;
CREATE TABLE `os_group` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(128) NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

--
-- Dumping data for table `os_group`
--

LOCK TABLES `os_group` WRITE;
/*!40000 ALTER TABLE `os_group` DISABLE KEYS */;
INSERT INTO `os_group` (`id`, `name`) VALUES (8,'Debian 3.0'),(2,'Debian 4.0'),(5,'Debian 5.0'),(9,'SLC 4.8'),(10,'SLC 4.6'),(11,'SLC 4.7'),(12,'Ubuntu 8.04'),(13,'SL 5.2'),(14,'SL 5.3'),(15,'SuSE 11.0'),(16,'SL 4.5'),(17,'SL 4.7'),(18,'SL 4.6'),(19,'SL 4.8'),(20,'Ubuntu 6.06');
/*!40000 ALTER TABLE `os_group` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `oses_group`
--

DROP TABLE IF EXISTS `oses_group`;
CREATE TABLE `oses_group` (
  `os_group_id` int(10) unsigned NOT NULL,
  `os_id` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`os_group_id`,`os_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `oses_group`
--

LOCK TABLES `oses_group` WRITE;
/*!40000 ALTER TABLE `oses_group` DISABLE KEYS */;
INSERT INTO `oses_group` (`os_group_id`, `os_id`) VALUES (2,1),(5,3),(5,4),(5,6),(9,2),(10,18),(11,21),(12,5),(12,8),(13,9),(14,20),(15,13),(16,14),(17,15),(18,16),(19,17),(20,22);
/*!40000 ALTER TABLE `oses_group` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cves_os`
--

DROP TABLE IF EXISTS `cves_os`;
CREATE TABLE `cves_os` (
  `id` char(10) NOT NULL,
  `os_id` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`id`,`os_id`),
  KEY `os_id` (`os_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `cves_os`
--

LOCK TABLES `cves_os` WRITE;
/*!40000 ALTER TABLE `cves_os` DISABLE KEYS */;
INSERT INTO `cves_os` (`id`, `os_id`) VALUES ('rh_3',555),('rh_4',2),('rh_4',14),('rh_4',15),('rh_4',16),('rh_4',17),('rh_4',18),('rh_4',21),('rh_5',9),('rh_5',20);
/*!40000 ALTER TABLE `cves_os` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `settings`
--

DROP TABLE IF EXISTS `settings`;
CREATE TABLE `settings` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(1024) NOT NULL,
  `value` varchar(4096) NOT NULL,
  `value2` varchar(4096) default NULL,
  PRIMARY KEY  (`id`),
  KEY `name` (`name`(1000))
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

--
-- Dumping data for table `settings`
--

LOCK TABLES `settings` WRITE;
/*!40000 ALTER TABLE `settings` DISABLE KEYS */;
INSERT INTO `settings` (`id`, `name`, `value`, `value2`) VALUES (2,'RedHat Releases CVE','3',NULL),(3,'RedHat Releases CVE','4',NULL),(4,'RedHat Releases CVE','5',NULL),(19,'RedHat CVEs URL','https://www.redhat.com/security/data/oval/com.redhat.rhsa-2009.xml','1'),(18,'RedHat CVEs URL','https://www.redhat.com/security/data/oval/com.redhat.rhsa-2008.xml','0'),(17,'RedHat CVEs URL','https://www.redhat.com/security/data/oval/com.redhat.rhsa-2007.xml','0'),(16,'RedHat CVEs URL','https://www.redhat.com/security/data/oval/com.redhat.rhsa-2006.xml','0'),(15,'RedHat CVEs URL','https://www.redhat.com/security/data/oval/com.redhat.rhsa-2005.xml','0'),(14,'RedHat CVEs URL','https://www.redhat.com/security/data/oval/com.redhat.rhsa-2004.xml','0'),(13,'RedHat CVEs URL','https://www.redhat.com/security/data/oval/com.redhat.rhsa-2003.xml','0');
/*!40000 ALTER TABLE `settings` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2010-01-04 16:09:00
