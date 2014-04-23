DROP TABLE IF EXISTS `repositories`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
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
SET character_set_client = @saved_cs_client;
INSERT INTO `repositories` VALUES (1,'SL 4.5 os','http://ftp.scientificlinux.org/linux/scientific/45/x86_64/SL/RPMS/repodata/primary.xml.gz',0,'rpm',1,1,1,3,NULL),(2,'SL 4.5 os','http://ftp.scientificlinux.org/linux/scientific/45/i386/SL/RPMS/repodata/primary.xml.gz',0,'rpm',1,1,2,3,NULL),(3,'SL 4.4 os','http://ftp.scientificlinux.org/linux/scientific/44/i386/SL/RPMS/repodata/primary.xml.gz',0,'rpm',1,1,2,2,NULL),(4,'SL 4.4 os','http://ftp.scientificlinux.org/linux/scientific/44/x86_64/SL/RPMS/repodata/primary.xml.gz',0,'rpm',1,1,1,2,NULL),(7,'SL 4.5 updates','http://ftp.scientificlinux.org/linux/scientific/45/x86_64/errata/SL/RPMS/repodata/primary.xml.gz',1,'rpm',1,1,1,3,NULL),(6,'SL 4.5 updates','http://ftp.scientificlinux.org/linux/scientific/45/i386/errata/SL/RPMS/repodata/primary.xml.gz',1,'rpm',1,1,2,3,NULL),(8,'SL 4.4 updates','http://ftp.scientificlinux.org/linux/scientific/44/i386/errata/SL/RPMS/repodata/primary.xml.gz',1,'rpm',1,1,2,2,NULL),(9,'SL 4.4 updates','http://ftp.scientificlinux.org/linux/scientific/44/x86_64/errata/SL/RPMS/repodata/primary.xml.gz',1,'rpm',1,1,1,2,NULL),(10,'SL 4.6 os','http://ftp.scientificlinux.org/linux/scientific/46/i386/SL/RPMS/repodata/primary.xml.gz',0,'rpm',1,1,2,4,NULL),(11,'SL 4.6 os','http://ftp.scientificlinux.org/linux/scientific/46/x86_64/SL/RPMS/repodata/primary.xml.gz',0,'rpm',1,1,1,4,NULL),(12,'SL 4.6 updates','http://ftp.scientificlinux.org/linux/scientific/46/i386/errata/SL/RPMS/repodata/primary.xml.gz',1,'rpm',1,1,2,4,NULL),(13,'SL 4.6 updates','http://ftp.scientificlinux.org/linux/scientific/46/x86_64/errata/SL/RPMS/repodata/primary.xml.gz',1,'rpm',1,1,1,4,NULL),(14,'SL 4.7 os','http://ftp.scientificlinux.org/linux/scientific/47/i386/SL/RPMS/repodata/primary.xml.gz',0,'rpm',1,1,2,5,NULL),(15,'SL 4.7 os','http://ftp.scientificlinux.org/linux/scientific/47/x86_64/SL/RPMS/repodata/primary.xml.gz',0,'rpm',1,1,1,5,NULL),(16,'SL 4.7 updates','http://ftp.scientificlinux.org/linux/scientific/47/i386/errata/SL/RPMS/repodata/primary.xml.gz',1,'rpm',1,1,2,5,NULL),(17,'SL 4.7 updates','http://ftp.scientificlinux.org/linux/scientific/47/x86_64/errata/SL/RPMS/repodata/primary.xml.gz',1,'rpm',1,1,1,5,NULL),(18,'SL 4.8 os','http://ftp.scientificlinux.org/linux/scientific/48/i386/SL/RPMS/repodata/primary.xml.gz',0,'rpm',1,1,2,6,NULL),(19,'SL 4.8 os','http://ftp.scientificlinux.org/linux/scientific/48/x86_64/SL/RPMS/repodata/primary.xml.gz',0,'rpm',1,1,1,6,NULL),(20,'SL 4.8 updates','http://ftp.scientificlinux.org/linux/scientific/48/i386/errata/SL/RPMS/repodata/primary.xml.gz',1,'rpm',1,1,2,6,NULL),(21,'SL 4.8 updates','http://ftp.scientificlinux.org/linux/scientific/48/x86_64/errata/SL/RPMS/repodata/primary.xml.gz',1,'rpm',1,1,1,6,NULL),(22,'SL 5.2 os','http://ftp.scientificlinux.org/linux/scientific/52/i386/SL/repodata/primary.xml.gz',0,'rpm',1,1,2,7,NULL),(23,'SL 5.2 os','http://ftp.scientificlinux.org/linux/scientific/52/x86_64/SL/repodata/primary.xml.gz',0,'rpm',1,1,1,7,NULL),(24,'SL 5.2 updates','http://ftp.scientificlinux.org/linux/scientific/52/i386/updates/security/repodata/primary.xml.gz',1,'rpm',1,1,2,7,NULL),(25,'SL 5.2 updates','http://ftp.scientificlinux.org/linux/scientific/52/x86_64/updates/security/repodata/primary.xml.gz',1,'rpm',1,1,1,7,NULL),(26,'SL 5.3 os','http://ftp.scientificlinux.org/linux/scientific/53/i386/SL/repodata/primary.xml.gz',0,'rpm',1,1,2,1,NULL),(27,'SL 5.3 os','http://ftp.scientificlinux.org/linux/scientific/53/x86_64/SL/repodata/primary.xml.gz',0,'rpm',1,1,1,1,NULL),(28,'SL 5.3 updates','http://ftp.scientificlinux.org/linux/scientific/53/i386/updates/security/repodata/primary.xml.gz',1,'rpm',1,1,2,1,NULL),(29,'SL 5.3 updates','http://ftp.scientificlinux.org/linux/scientific/53/x86_64/updates/security/repodata/primary.xml.gz',1,'rpm',1,1,1,1,NULL),(30,'SL 5.4 os','http://ftp.scientificlinux.org/linux/scientific/54/i386/SL/repodata/primary.xml.gz',0,'rpm',1,1,2,8,NULL),(31,'SL 5.4 os','http://ftp.scientificlinux.org/linux/scientific/54/x86_64/SL/repodata/primary.xml.gz',0,'rpm',1,1,1,8,NULL),(32,'SL 5.4 updates','http://ftp.scientificlinux.org/linux/scientific/54/i386/updates/security/repodata/primary.xml.gz',1,'rpm',1,1,2,8,NULL),(33,'SL 5.4 updates','http://ftp.scientificlinux.org/linux/scientific/54/x86_64/updates/security/repodata/primary.xml.gz',1,'rpm',1,1,1,8,NULL),(35,'SLC 4.7 os','http://linuxsoft.cern.ch/cern/slc47/i386/yum/os/repodata/primary.xml.gz',0,'rpm',1,1,2,11,NULL),(36,'SLC 4.7 os','http://linuxsoft.cern.ch/cern/slc47/x86_64/yum/os/repodata/primary.xml.gz',0,'rpm',1,1,1,11,NULL),(37,'SLC 4.7 updates','http://linuxsoft.cern.ch/cern/slc47/i386/yum/updates/repodata/primary.xml.gz',1,'rpm',1,1,2,11,NULL),(38,'SLC 4.7 updates','http://linuxsoft.cern.ch/cern/slc47/x86_64/yum/updates/repodata/primary.xml.gz',1,'rpm',1,1,1,11,NULL),(39,'SLC 4.8 os','http://linuxsoft.cern.ch/cern/slc48/i386/yum/os/repodata/primary.xml.gz',0,'rpm',1,1,2,9,NULL),(40,'SLC 4.8 os','http://linuxsoft.cern.ch/cern/slc48/x86_64/yum/os/repodata/primary.xml.gz',0,'rpm',1,1,1,9,NULL),(41,'SLC 4.8 updates','http://linuxsoft.cern.ch/cern/slc48/i386/yum/updates/repodata/primary.xml.gz',1,'rpm',1,1,2,9,NULL),(42,'SLC 4.8 updates','http://linuxsoft.cern.ch/cern/slc48/x86_64/yum/updates/repodata/primary.xml.gz',1,'rpm',1,1,1,9,NULL),(43,'SLC 5.4 os','http://linuxsoft.cern.ch/cern/slc54/i386/yum/os/repodata/primary.xml.gz',0,'rpm',1,1,2,10,NULL),(44,'SLC 5.4 os','http://linuxsoft.cern.ch/cern/slc54/x86_64/yum/os/repodata/primary.xml.gz',0,'rpm',1,1,1,10,NULL),(45,'SLC 5.4 updates','http://linuxsoft.cern.ch/cern/slc54/i386/yum/updates/repodata/primary.xml.gz',1,'rpm',1,1,2,10,NULL),(46,'SLC 5.4 updates','http://linuxsoft.cern.ch/cern/slc54/x86_64/yum/updates/repodata/primary.xml.gz',1,'rpm',1,1,1,10,NULL),(47,'CentOS 4 Sec','ftp://centos.arcticnetwork.ca/pub/centos/4/updates/i386/repodata/primary.xml.gz',1,'rpm',1,1,2,12,NULL),(48,'CentOS 4 Sec','ftp://centos.arcticnetwork.ca/pub/centos/4/updates/x86_64/repodata/primary.xml.gz',1,'rpm',1,1,1,12,NULL),(49,'CentOS 4 os','ftp://centos.arcticnetwork.ca/pub/centos/4/os/i386/repodata/primary.xml.gz',0,'rpm',1,1,2,12,NULL),(50,'CentOS 4 os','ftp://centos.arcticnetwork.ca/pub/centos/4/os/x86_64/repodata/primary.xml.gz',0,'rpm',1,1,1,12,NULL),(51,'CentOS 5 sec','ftp://centos.arcticnetwork.ca/pub/centos/5/updates/i386/repodata/primary.xml.gz',1,'rpm',1,1,2,13,NULL),(52,'CentOS 5 sec','ftp://centos.arcticnetwork.ca/pub/centos/5/updates/x86_64/repodata/primary.xml.gz',1,'rpm',1,1,1,13,NULL),(53,'CentOS 5 os','ftp://centos.arcticnetwork.ca/pub/centos/5/os/i386/repodata/primary.xml.gz',0,'rpm',1,1,2,13,NULL),(54,'CentOS 5 os','ftp://centos.arcticnetwork.ca/pub/centos/5/os/x86_64/repodata/primary.xml.gz',0,'rpm',1,1,1,13,NULL),(55,'SLC 4.6 os','http://linuxsoft.cern.ch/cern/slc46/i386/yum/os/repodata/primary.xml.gz',0,'rpm',1,1,2,16,NULL),(56,'SLC 4.6 os','http://linuxsoft.cern.ch/cern/slc46/x86_64/yum/os/repodata/primary.xml.gz',0,'rpm',1,1,1,16,NULL),(57,'SLC 4.6 updates','http://linuxsoft.cern.ch/cern/slc46/x86_64/yum/updates/repodata/primary.xml.gz',1,'rpm',1,1,1,16,NULL),(58,'SLC 4.6 updates','http://linuxsoft.cern.ch/cern/slc46/i386/yum/updates/repodata/primary.xml.gz',1,'rpm',1,1,2,16,NULL),(59,'SLC 4.5 os','http://linuxsoft.cern.ch/cern/slc45/i386/yum/os/repodata/primary.xml.gz',0,'rpm',1,1,2,15,NULL),(60,'SLC 4.5 os','http://linuxsoft.cern.ch/cern/slc45/x86_64/yum/os/repodata/primary.xml.gz',0,'rpm',1,1,1,15,NULL),(61,'SLC 4.5 updates','http://linuxsoft.cern.ch/cern/slc45/i386/yum/updates/repodata/primary.xml.gz',1,'rpm',1,1,2,15,NULL),(62,'SLC 4.5 updates','http://linuxsoft.cern.ch/cern/slc45/x86_64/yum/updates/repodata/primary.xml.gz',1,'rpm',1,1,1,15,NULL),(63,'SLC 4.4 os','http://linuxsoft.cern.ch/cern/slc44/i386/yum/os/repodata/primary.xml.gz',0,'rpm',1,1,2,14,NULL),(64,'SLC 4.4 os','http://linuxsoft.cern.ch/cern/slc44/x86_64/yum/os/repodata/primary.xml.gz',0,'rpm',1,1,1,14,NULL),(65,'SLC 4.4 updates','http://linuxsoft.cern.ch/cern/slc44/i386/yum/updates/repodata/primary.xml.gz',1,'rpm',1,1,2,14,NULL),(66,'SLC 4.4 updates','http://linuxsoft.cern.ch/cern/slc44/x86_64/yum/updates/repodata/primary.xml.gz',1,'rpm',1,1,1,14,NULL),(67,'SLC 5.3 os','http://linuxsoft.cern.ch/cern/slc53/i386/yum/os/repodata/primary.xml.gz',0,'rpm',1,1,2,17,NULL),(68,'SLC 5.3 os','http://linuxsoft.cern.ch/cern/slc53/x86_64/yum/os/repodata/primary.xml.gz',0,'rpm',1,1,1,17,NULL),(69,'SLC 5.3 updates','http://linuxsoft.cern.ch/cern/slc53/i386/yum/updates/repodata/primary.xml.gz',1,'rpm',1,1,2,17,NULL),(70,'SLC 5.3 updates','http://linuxsoft.cern.ch/cern/slc53/x86_64/yum/updates/repodata/primary.xml.gz',1,'rpm',1,1,1,17,NULL),(71,'SL 4.3 os','http://ftp.scientificlinux.org/linux/scientific/43/i386/SL/RPMS/repodata/primary.xml.gz',0,'rpm',1,1,2,19,NULL),(72,'SL 4.3 os','http://ftp.scientificlinux.org/linux/scientific/43/x86_64/SL/RPMS/repodata/primary.xml.gz',0,'rpm',1,1,1,19,NULL),(73,'SL 4.3 updates','http://ftp.scientificlinux.org/linux/scientific/43/i386/errata/SL/RPMS/repodata/primary.xml.gz',1,'rpm',1,1,2,19,NULL),(74,'SL 4.3 updates','http://ftp.scientificlinux.org/linux/scientific/43/x86_64/errata/SL/RPMS/repodata/primary.xml.gz',1,'rpm',1,1,1,19,NULL),(75,'SL 5.0 os','http://ftp.scientificlinux.org/linux/scientific/50/i386/SL/repodata/primary.xml.gz',0,'rpm',1,1,2,18,NULL),(76,'SL 5.0 os','http://ftp.scientificlinux.org/linux/scientific/50/x86_64/SL/repodata/primary.xml.gz',0,'rpm',1,1,1,18,NULL),(77,'SL 5.0 updates','http://ftp.scientificlinux.org/linux/scientific/50/i386/updates/security/repodata/primary.xml.gz',1,'rpm',1,1,2,18,NULL),(78,'SL 5.0 updates','http://ftp.scientificlinux.org/linux/scientific/50/x86_64/updates/security/repodata/primary.xml.gz',1,'rpm',1,1,1,18,NULL),(79,'openSUSE 10.3','http://download.opensuse.org/update/10.3/repodata/primary.xml.gz',0,'rpm',1,1,1,20,NULL);
DROP TABLE IF EXISTS `os`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `os` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `os` varchar(100) default NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `os` (`os`),
  KEY `os_index` (`os`(50))
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;
INSERT INTO `os` VALUES (1,'Scientific Linux SL 5.3'),(2,'Scientific Linux SL 4.5'),(3,'Scientific Linux SL 4.7'),(4,'Scientific Linux SL 5.2'),(5,'Scientific Linux 5.3'),(6,'Scientific Linux SLC 4.8'),(7,'Scientific Linux SL 4.8'),(8,'Scientific Linux SLC 5.4'),(9,'Scientific Linux SL 4.4'),(10,'Scientific Linux SL 5.4'),(11,'Scientific Linux SL release 4.5 (Beryllium)'),(12,'Scientific Linux SL 4.6'),(13,'SuSe Linux 10'),(14,'Scientific Linux SLC 4.7'),(15,'Scientific Linux 4.6'),(16,'CentOS release 5.4 (Final)'),(17,'CentOS release 4.8 (Final)'),(18,'Scientific Linux SLC 4.6'),(19,'Scientific Linux SLC 4.5'),(20,'SUSE Linux Enterprise Server 10 (x86_64)'),(21,'Scientific Linux SL 5.0'),(22,'Scientific Linux SLC 5.3'),(23,'Ubuntu 8.04.3 LTS'),(24,'CentOS release 4.5 (Final)'),(25,'CentOS release 4.6 (Final)'),(26,'CentOS release 5.3 (Final)'),(27,'Red Hat Enterprise Linux AS release 4 (Nahant Update 5)'),(28,'Red Hat Enterprise Linux ES release 4.8 (Final)'),(29,'CentOS Linux 4'),(30,'Scientific Linux 4.5'),(31,'openSUSE 10.3 (X86-64)'),(32,'Scientific Linux SLC 4.4'),(33,'Red Hat Enterprise Linux ES release 4 (Nahant Update 7)'),(34,'Scientific Linux SL release 5.4 (Boron)'),(35,'Scientific Linux CERN SLC release 4.8 (Beryllium)'),(36,'Scientific Linux SL release 4.4 (Beryllium)'),(37,'Scientific Linux SL release 4.7 (Beryllium)'),(38,'Scientific Linux SL release 4.8 (Beryllium)'),(39,'Red Hat Enterprise Linux WS release 4 (Nahant Update 8)'),(40,'Scientific Linux SL release 5.3 (Boron)'),(41,'Scientific Linux SL release 5.0 (Boron)'),(42,'Scientific Linux SL release 4.6 (Beryllium)'),(43,'Scientific Linux CERN SLC release 5.4 (Boron)'),(44,'Scientific Linux CERN SLC release 4.7 (Beryllium)'),(45,'Red Hat Enterprise Linux AS release 4 (Nahant Update 4)'),(46,'Scientific Linux CERN SLC release 4.6 (Beryllium)'),(47,'Scientific Linux CERN SLC release 4.4 (Beryllium)'),(48,'Scientific Linux SL release 5.2 (Boron)'),(49,'Scientific Linux CERN SLC release 4.5 (Beryllium)'),(50,'Red Hat Enterprise Linux release 4.6 (Beryllium)'),(51,'Red Hat Enterprise Linux AS Release 5.3'),(52,'Scientific Linux SL release 4.3 (Beryllium)'),(53,'Redhat Enterprise Release Linux AS 5.3 (SLC)'),(54,'Scientific Linux CERN SLC release 5.3 (Boron)'),(55,'Redhat Enterprise Linux AS 4.7 (SL)'),(56,'Red Hat Enterprise Linux Server release 5.3 (Tikanga)'),(57,'Red Hat Enterprise Linux AS release 4 (Nahant Update 2)'),(58,'Debian 4.0'),(59,'Scientific Linux 4.8'),(60,'Scientific Linux SL release 4.8 (Nahant)'),(61,'Scientific Linux SL release 5.3 (Tikanga)');
DROP TABLE IF EXISTS `arch`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `arch` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `arch` char(15) default NULL,
  PRIMARY KEY  (`id`),
  KEY `arch_index` (`arch`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;
INSERT INTO `arch` VALUES (1,'x86_64'),(2,'i686'),(3,'ia64'),(4,'unknown');
DROP TABLE IF EXISTS `os_group`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `os_group` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(128) NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;
INSERT INTO `os_group` VALUES (1,'SL 5.3'),(2,'SL 4.4'),(3,'SL 4.5'),(4,'SL 4.6'),(5,'SL 4.7'),(6,'SL 4.8'),(7,'SL 5.2'),(8,'SL 5.4'),(9,'SLC 4.8'),(10,'SLC 5.4'),(11,'SLC 4.7'),(12,'CentOS 4'),(13,'CentOS 5'),(14,'SLC 4.4'),(15,'SLC 4.5'),(16,'SLC 4.6'),(17,'SLC 5.3'),(18,'SL 5.0'),(19,'SL 4.3'),(20,'OpenSuSE 10.3');
DROP TABLE IF EXISTS `oses_group`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `oses_group` (
  `os_group_id` int(10) unsigned NOT NULL,
  `os_id` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`os_group_id`,`os_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;
INSERT INTO `oses_group` VALUES (1,1),(1,5),(1,40),(1,61),(2,9),(2,36),(3,2),(3,11),(3,30),(4,12),(4,15),(4,42),(5,3),(5,37),(6,7),(6,38),(6,59),(6,60),(7,4),(7,48),(8,10),(8,34),(9,6),(9,35),(10,8),(10,43),(11,14),(11,44),(12,17),(12,24),(12,25),(12,29),(13,16),(13,26),(14,32),(14,47),(15,19),(15,49),(16,18),(16,46),(17,22),(17,54),(18,21),(18,41),(19,52),(20,31);
DROP TABLE IF EXISTS `cves_os`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `cves_os` (
  `id` char(10) NOT NULL,
  `os_id` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`id`,`os_id`),
  KEY `os_id` (`os_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;
INSERT INTO `cves_os` VALUES ('rh_4',2),('rh_4',3),('rh_4',6),('rh_4',7),('rh_4',9),('rh_4',11),('rh_4',12),('rh_4',14),('rh_4',15),('rh_4',17),('rh_4',18),('rh_4',19),('rh_4',24),('rh_4',25),('rh_4',27),('rh_4',28),('rh_4',29),('rh_4',30),('rh_4',32),('rh_4',33),('rh_4',35),('rh_4',36),('rh_4',37),('rh_4',38),('rh_4',39),('rh_4',42),('rh_4',44),('rh_4',45),('rh_4',46),('rh_4',47),('rh_4',49),('rh_4',50),('rh_4',52),('rh_4',55),('rh_4',57),('rh_4',59),('rh_5',1),('rh_5',4),('rh_5',5),('rh_5',8),('rh_5',10),('rh_5',16),('rh_5',21),('rh_5',22),('rh_5',26),('rh_5',34),('rh_5',40),('rh_5',41),('rh_5',43),('rh_5',48),('rh_5',51),('rh_5',53),('rh_5',54),('rh_5',56);
DROP TABLE IF EXISTS `settings`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `settings` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(1024) NOT NULL,
  `value` varchar(4096) NOT NULL,
  `value2` varchar(4096) default NULL,
  PRIMARY KEY  (`id`),
  KEY `name` (`name`(1000))
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;
INSERT INTO `settings` VALUES (1,'RedHat CVEs URL','https://www.redhat.com/security/data/oval/com.redhat.rhsa-2003.xml','0'),(2,'RedHat CVEs URL','https://www.redhat.com/security/data/oval/com.redhat.rhsa-2004.xml','0'),(3,'RedHat CVEs URL','https://www.redhat.com/security/data/oval/com.redhat.rhsa-2005.xml','0'),(4,'RedHat CVEs URL','https://www.redhat.com/security/data/oval/com.redhat.rhsa-2006.xml','0'),(5,'RedHat CVEs URL','https://www.redhat.com/security/data/oval/com.redhat.rhsa-2007.xml','1'),(6,'RedHat CVEs URL','https://www.redhat.com/security/data/oval/com.redhat.rhsa-2008.xml','1'),(7,'RedHat CVEs URL','https://www.redhat.com/security/data/oval/com.redhat.rhsa-2009.xml','1'),(8,'RedHat Releases CVE','3',NULL),(9,'RedHat Releases CVE','4',NULL),(10,'RedHat Releases CVE','5',NULL);
