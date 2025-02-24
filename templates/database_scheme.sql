-- Adminer 4.8.1 MySQL 10.6.18-MariaDB-0ubuntu0.22.04.1 dump

SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

DELIMITER ;;

DROP FUNCTION IF EXISTS `get_cho`;;

DROP FUNCTION IF EXISTS `mask_to_bin`;;
CREATE FUNCTION `mask_to_bin`(`mask` int) RETURNS varbinary(16)
    DETERMINISTIC
BEGIN
    -- IPv4의 경우 (mask ≤ 32)
    IF mask <= 32 THEN
        RETURN UNHEX(LPAD(HEX((~((1 << (32 - mask)) - 1)) & 0xFFFFFFFF), 8, '0'));
    END IF;

    -- IPv6의 경우 (mask > 32)
    RETURN UNHEX(LPAD(REPEAT('f', mask DIV 4), 32, '0'));
END;;

DELIMITER ;

SET NAMES utf8mb4;

DROP TABLE IF EXISTS `aclgroups`;
CREATE TABLE `aclgroups` (
  `groupid` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(128) NOT NULL,
  PRIMARY KEY (`groupid`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


DROP TABLE IF EXISTS `acl_document`;
CREATE TABLE `acl_document` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uuid` binary(16) NOT NULL,
  `access` varchar(32) NOT NULL,
  `condition` varchar(256) NOT NULL,
  `action` varchar(16) NOT NULL,
  `until` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `uuid` (`uuid`),
  CONSTRAINT `acl_document_ibfk_1` FOREIGN KEY (`uuid`) REFERENCES `document` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;


DROP TABLE IF EXISTS `acl_namespace`;
CREATE TABLE `acl_namespace` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `namespace` varchar(128) NOT NULL,
  `access` varchar(64) NOT NULL,
  `condition` varchar(256) NOT NULL,
  `action` varchar(32) NOT NULL,
  `until` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;


DROP TABLE IF EXISTS `BlockHistory`;
CREATE TABLE `BlockHistory` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `executor_m` binary(16) DEFAULT NULL,
  `executor_i` varbinary(16) DEFAULT NULL,
  `target_ip` varbinary(16) DEFAULT NULL,
  `mask` smallint(6) DEFAULT NULL,
  `target_member` binary(16) DEFAULT NULL,
  `target_ip_uuid` binary(16) DEFAULT NULL,
  `target_aclgroup` varchar(64) DEFAULT NULL,
  `comment` varchar(512) DEFAULT NULL,
  `datetime` bigint(20) NOT NULL DEFAULT unix_timestamp(),
  `until` bigint(20) DEFAULT NULL,
  `action` varchar(32) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `granted` varchar(512) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  KEY `executor` (`executor_m`),
  KEY `id` (`id`),
  KEY `target_member` (`target_member`),
  KEY `target_ip_uuid` (`target_ip_uuid`),
  CONSTRAINT `BlockHistory_ibfk_1` FOREIGN KEY (`target_member`) REFERENCES `member` (`uuid`),
  CONSTRAINT `BlockHistory_ibfk_3` FOREIGN KEY (`executor_m`) REFERENCES `member` (`uuid`),
  CONSTRAINT `BlockHistory_ibfk_4` FOREIGN KEY (`target_member`) REFERENCES `member` (`uuid`),
  CONSTRAINT `BlockHistory_ibfk_5` FOREIGN KEY (`target_ip_uuid`) REFERENCES `ip` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


DROP TABLE IF EXISTS `config`;
CREATE TABLE `config` (
  `key` varchar(128) NOT NULL,
  `value` longtext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


DROP TABLE IF EXISTS `document`;
CREATE TABLE `document` (
  `uuid` binary(16) NOT NULL,
  `namespace` varchar(256) NOT NULL,
  `title` varchar(1024) NOT NULL,
  `status` varchar(6) NOT NULL DEFAULT 'normal',
  `backlink_updated` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


DROP TABLE IF EXISTS `editrequest`;
CREATE TABLE `editrequest` (
  `urlstr` varchar(128) NOT NULL,
  `document` binary(16) NOT NULL,
  `status` varchar(16) NOT NULL,
  `comment` varchar(190) DEFAULT NULL,
  `content` mediumtext NOT NULL,
  `contributor_m` binary(16) DEFAULT NULL,
  `contributor_i` binary(16) DEFAULT NULL,
  `base_revision` int(11) NOT NULL,
  `datetime` bigint(20) NOT NULL DEFAULT unix_timestamp(),
  `lastedit` bigint(20) NOT NULL,
  `accepted` varchar(128) DEFAULT NULL COMMENT '승인 편집자 아이디',
  `acceptrev` int(11) DEFAULT NULL,
  PRIMARY KEY (`urlstr`),
  KEY `document` (`document`),
  KEY `contributor_m` (`contributor_m`),
  KEY `contributor_i` (`contributor_i`),
  CONSTRAINT `editrequest_ibfk_1` FOREIGN KEY (`document`) REFERENCES `document` (`uuid`),
  CONSTRAINT `editrequest_ibfk_2` FOREIGN KEY (`contributor_m`) REFERENCES `member` (`uuid`),
  CONSTRAINT `editrequest_ibfk_3` FOREIGN KEY (`contributor_i`) REFERENCES `ip` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


DROP TABLE IF EXISTS `files`;
CREATE TABLE `files` (
  `uuid` binary(16) NOT NULL,
  `hash` binary(32) NOT NULL,
  UNIQUE KEY `hash` (`hash`),
  KEY `uuid` (`uuid`),
  CONSTRAINT `files_ibfk_1` FOREIGN KEY (`uuid`) REFERENCES `document` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


DROP TABLE IF EXISTS `history`;
CREATE TABLE `history` (
  `uuid` binary(16) NOT NULL,
  `document` binary(16) NOT NULL,
  `rev` int(11) NOT NULL DEFAULT 1,
  `content` mediumtext DEFAULT NULL,
  `comment` varchar(190) NOT NULL DEFAULT '',
  `datetime` bigint(20) NOT NULL DEFAULT unix_timestamp(),
  `action` varchar(64) NOT NULL,
  `count` int(11) NOT NULL DEFAULT 0,
  `reverted_version` int(11) DEFAULT NULL,
  `contributor_m` binary(16) DEFAULT NULL,
  `contributor_i` binary(16) DEFAULT NULL,
  `edit_request_uri` varchar(64) DEFAULT NULL,
  `acl_changed` varchar(1024) DEFAULT NULL,
  `moved_from` varchar(1024) DEFAULT NULL,
  `moved_to` varchar(1024) DEFAULT NULL,
  `is_hidden` varchar(5) NOT NULL DEFAULT 'false',
  PRIMARY KEY (`uuid`),
  KEY `document` (`document`),
  KEY `contributor_i` (`contributor_i`),
  KEY `contributor_m` (`contributor_m`),
  CONSTRAINT `history_ibfk_1` FOREIGN KEY (`document`) REFERENCES `document` (`uuid`),
  CONSTRAINT `history_ibfk_2` FOREIGN KEY (`contributor_i`) REFERENCES `ip` (`uuid`),
  CONSTRAINT `history_ibfk_3` FOREIGN KEY (`contributor_m`) REFERENCES `member` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


DROP TABLE IF EXISTS `ip`;
CREATE TABLE `ip` (
  `uuid` binary(16) NOT NULL,
  `ip` varbinary(16) DEFAULT NULL,
  PRIMARY KEY (`uuid`),
  UNIQUE KEY `ipv6` (`ip`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


DROP TABLE IF EXISTS `links`;
CREATE TABLE `links` (
  `namespace` varchar(128) NOT NULL,
  `title` varchar(1024) NOT NULL,
  `from_uuid` binary(16) NOT NULL,
  `type` varchar(16) NOT NULL,
  UNIQUE KEY `namespace_title_from_uuid_type` (`namespace`,`title`,`from_uuid`,`type`) USING HASH,
  KEY `from_uuid` (`from_uuid`),
  CONSTRAINT `links_ibfk_1` FOREIGN KEY (`from_uuid`) REFERENCES `document` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


DROP TABLE IF EXISTS `login_history`;
CREATE TABLE `login_history` (
  `uuid` binary(16) NOT NULL,
  `ip` varchar(64) NOT NULL,
  `datetime` bigint(20) NOT NULL DEFAULT unix_timestamp(),
  KEY `uuid` (`uuid`),
  CONSTRAINT `login_history_ibfk_1` FOREIGN KEY (`uuid`) REFERENCES `member` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;


DROP TABLE IF EXISTS `member`;
CREATE TABLE `member` (
  `uuid` binary(16) NOT NULL,
  `username` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `password` varchar(256) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `email` varchar(256) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `last_login_ua` varchar(256) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `skin` varchar(256) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `registered` bigint(20) NOT NULL DEFAULT unix_timestamp(),
  `registered_ip` varbinary(16) DEFAULT NULL,
  `totp_secret` varchar(16) DEFAULT NULL,
  `perm` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `settings` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`uuid`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;


DROP TABLE IF EXISTS `remember_login`;
CREATE TABLE `remember_login` (
  `user` binary(16) NOT NULL,
  `ip` varbinary(16) NOT NULL,
  `useragent` varchar(256) NOT NULL,
  KEY `user` (`user`),
  CONSTRAINT `remember_login_ibfk_1` FOREIGN KEY (`user`) REFERENCES `member` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;


DROP TABLE IF EXISTS `starred`;
CREATE TABLE `starred` (
  `document` binary(16) NOT NULL,
  `user` binary(16) NOT NULL,
  PRIMARY KEY (`document`,`user`),
  KEY `user` (`user`),
  CONSTRAINT `starred_ibfk_1` FOREIGN KEY (`document`) REFERENCES `document` (`uuid`),
  CONSTRAINT `starred_ibfk_2` FOREIGN KEY (`user`) REFERENCES `member` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


DROP TABLE IF EXISTS `thread`;
CREATE TABLE `thread` (
  `urlstr` varchar(128) NOT NULL,
  `document` binary(16) NOT NULL,
  `topic` varchar(512) NOT NULL,
  `status` varchar(16) NOT NULL,
  PRIMARY KEY (`urlstr`),
  KEY `document` (`document`),
  CONSTRAINT `thread_ibfk_1` FOREIGN KEY (`document`) REFERENCES `document` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


DROP TABLE IF EXISTS `thread_content`;
CREATE TABLE `thread_content` (
  `urlstr` varchar(128) NOT NULL,
  `no` int(11) NOT NULL,
  `contributor_m` binary(16) DEFAULT NULL,
  `contributor_i` binary(16) DEFAULT NULL,
  `type` varchar(64) DEFAULT NULL,
  `content` mediumtext NOT NULL,
  `datetime` bigint(20) NOT NULL DEFAULT unix_timestamp(),
  `blind` varchar(128) DEFAULT NULL COMMENT '숨긴 사람',
  KEY `urlstr` (`urlstr`),
  KEY `contributor_u` (`contributor_i`),
  KEY `contributor_u_2` (`contributor_m`),
  CONSTRAINT `thread_content_ibfk_1` FOREIGN KEY (`urlstr`) REFERENCES `thread` (`urlstr`),
  CONSTRAINT `thread_content_ibfk_2` FOREIGN KEY (`contributor_i`) REFERENCES `ip` (`uuid`),
  CONSTRAINT `thread_content_ibfk_3` FOREIGN KEY (`contributor_m`) REFERENCES `member` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


DROP TABLE IF EXISTS `webauthn`;
CREATE TABLE `webauthn` (
  `uuid` binary(16) NOT NULL,
  `name` varchar(64) NOT NULL,
  `registered` bigint(20) NOT NULL DEFAULT unix_timestamp(),
  `lastuse` int(11) DEFAULT NULL,
  `client_data` varchar(1024) NOT NULL,
  UNIQUE KEY `uuid_name` (`uuid`,`name`),
  CONSTRAINT `webauthn_ibfk_1` FOREIGN KEY (`uuid`) REFERENCES `member` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- 2025-02-17 13:45:03