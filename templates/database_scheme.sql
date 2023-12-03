-- MariaDB dump 10.19  Distrib 10.6.12-MariaDB, for debian-linux-gnu (aarch64)
--
-- Host: localhost    Database: pressdo
-- ------------------------------------------------------
-- Server version	10.6.12-MariaDB-0ubuntu0.22.04.1

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `BlockHistory`
--

DROP TABLE IF EXISTS `BlockHistory`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `BlockHistory` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `executor` varchar(128) DEFAULT NULL,
  `from_ip` varchar(64) DEFAULT NULL,
  `to_ip` varchar(128) DEFAULT NULL,
  `display_ip` varchar(128) DEFAULT NULL,
  `ipver` varchar(4) DEFAULT NULL,
  `target_member` varchar(128) DEFAULT NULL,
  `target_aclgroup` varchar(512) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `target_id` int(11) DEFAULT NULL,
  `comment` varchar(190) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `datetime` bigint(20) NOT NULL,
  `until` bigint(20) DEFAULT NULL,
  `action` varchar(32) NOT NULL,
  `granted` varchar(512) DEFAULT NULL,
  `removed` varchar(1024) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `acl_document`
--

DROP TABLE IF EXISTS `acl_document`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `acl_document` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `docid` int(11) NOT NULL,
  `access` varchar(32) NOT NULL,
  `condition` varchar(256) NOT NULL,
  `action` varchar(32) NOT NULL,
  `until` bigint(20) NOT NULL,
  `deleted` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `docid` (`docid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `acl_namespace`
--

DROP TABLE IF EXISTS `acl_namespace`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `acl_namespace` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `namespace` varchar(128) NOT NULL,
  `access` varchar(64) NOT NULL,
  `condition` varchar(256) NOT NULL,
  `action` varchar(32) NOT NULL,
  `until` bigint(20) NOT NULL,
  `deleted` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `aclgroups`
--

DROP TABLE IF EXISTS `aclgroups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aclgroups` (
  `groupid` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(128) NOT NULL,
  `admin` varchar(5) NOT NULL,
  `perms` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  PRIMARY KEY (`groupid`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `backlinks`
--

DROP TABLE IF EXISTS `backlinks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `backlinks` (
  `target_ns` varchar(128) NOT NULL,
  `target_name` varchar(1024) NOT NULL,
  `from_did` int(11) NOT NULL,
  `method` varchar(16) NOT NULL,
  KEY `from_did` (`from_did`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `document`
--

DROP TABLE IF EXISTS `document`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `document` (
  `docid` int(11) NOT NULL,
  `content` mediumtext NOT NULL,
  `length` bigint(20) NOT NULL,
  `comment` varchar(190) NOT NULL,
  `datetime` bigint(20) NOT NULL,
  `action` varchar(64) NOT NULL,
  `rev` int(11) NOT NULL,
  `count` bigint(20) NOT NULL,
  `reverted_version` int(11) DEFAULT NULL,
  `contributor` varchar(128) NOT NULL,
  `edit_request_uri` varchar(64) DEFAULT NULL,
  `acl_changed` varchar(1024) DEFAULT NULL,
  `moved_from` varchar(1024) DEFAULT NULL,
  `moved_to` varchar(1024) DEFAULT NULL,
  `is_hidden` varchar(5) NOT NULL DEFAULT 'false',
  `is_latest` varchar(5) NOT NULL DEFAULT 'true'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `editrequest`
--

DROP TABLE IF EXISTS `editrequest`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `editrequest` (
  `urlstr` varchar(128) NOT NULL,
  `docid` int(11) NOT NULL,
  `status` varchar(16) NOT NULL,
  `comment` varchar(190) DEFAULT NULL,
  `content` mediumtext NOT NULL,
  `contributor_m` varchar(128) DEFAULT NULL,
  `contributor_i` varchar(64) DEFAULT NULL,
  `base_revision` int(11) NOT NULL,
  `datetime` bigint(20) NOT NULL,
  `lastedit` bigint(20) NOT NULL,
  `accepted` varchar(128) DEFAULT NULL COMMENT '승인 편집자 아이디',
  `acceptrev` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `live_document_list`
--

DROP TABLE IF EXISTS `live_document_list`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `live_document_list` (
  `docid` int(11) NOT NULL AUTO_INCREMENT,
  `namespace` varchar(256) NOT NULL,
  `title` varchar(1024) NOT NULL,
  `deleted` varchar(5) DEFAULT NULL,
  PRIMARY KEY (`docid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `login_history`
--

DROP TABLE IF EXISTS `login_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `login_history` (
  `username` varchar(128) NOT NULL,
  `ip` varchar(64) NOT NULL,
  `datetime` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `member`
--

DROP TABLE IF EXISTS `member`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `member` (
  `username` varchar(128) NOT NULL,
  `password` varchar(256) NOT NULL,
  `email` varchar(256) NOT NULL,
  `gravatar_url` varchar(256) NOT NULL,
  `skin` varchar(256) NOT NULL,
  `perm` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `last_login_ua` varchar(256) NOT NULL,
  `registered` bigint(20) NOT NULL,
  PRIMARY KEY (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `reverse_index`
--

DROP TABLE IF EXISTS `reverse_index`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `reverse_index` (
  `word` varchar(256) NOT NULL,
  `documents` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `starred`
--

DROP TABLE IF EXISTS `starred`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `starred` (
  `docid` int(11) NOT NULL,
  `user` varchar(128) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  PRIMARY KEY (`docid`,`user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `thread`
--

DROP TABLE IF EXISTS `thread`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `thread` (
  `urlstr` varchar(128) NOT NULL,
  `namespace` varchar(128) NOT NULL,
  `title` varchar(512) NOT NULL,
  `topic` varchar(512) NOT NULL,
  `status` varchar(16) NOT NULL,
  `last_comment` varchar(16) NOT NULL,
  PRIMARY KEY (`urlstr`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `thread_content`
--

DROP TABLE IF EXISTS `thread_content`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `thread_content` (
  `urlstr` varchar(128) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `no` int(11) NOT NULL,
  `contributor_m` varchar(128) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `contributor_i` varchar(64) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `type` varchar(64) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `content` mediumtext NOT NULL,
  `datetime` bigint(20) NOT NULL,
  `blind` varchar(128) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT '숨긴 사람'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `uploader_license_category`
--

DROP TABLE IF EXISTS `uploader_license_category`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `uploader_license_category` (
  `type` varchar(50) NOT NULL COMMENT '종류',
  `namespace` varchar(50) NOT NULL,
  `display_name` varchar(128) NOT NULL,
  `name` varchar(128) NOT NULL COMMENT '표시될 이름'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user_agent`
--

DROP TABLE IF EXISTS `user_agent`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_agent` (
  `username` varchar(128) NOT NULL,
  `useragent` varchar(256) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2023-12-03 12:06:53
