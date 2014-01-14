SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- База данных: `php_fan_test`
--
CREATE DATABASE IF NOT EXISTS `php_fan_test` DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;
USE `php_fan_test`;

-- --------------------------------------------------------

--
-- Структура таблицы `administrator`
--

CREATE TABLE IF NOT EXISTS `administrator` (
  `id_administrator` int(11) NOT NULL AUTO_INCREMENT,
  `login` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `admin_password` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `adm_type` enum('root','manager') COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id_administrator`),
  UNIQUE KEY `administrator_unique_loqin` (`login`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci PACK_KEYS=0 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Структура таблицы `administrator_has_role`
--

CREATE TABLE IF NOT EXISTS `administrator_has_role` (
  `id_administrator` int(11) NOT NULL,
  `id_role` mediumint(8) unsigned NOT NULL,
  `expired_date` datetime DEFAULT NULL,
  PRIMARY KEY (`id_administrator`,`id_role`),
  KEY `administrator_has_role_FKIndex1` (`id_administrator`),
  KEY `administrator_has_role_FKIndex2` (`id_role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci PACK_KEYS=0;

-- --------------------------------------------------------

--
-- Структура таблицы `dynamic_meta`
--

CREATE TABLE IF NOT EXISTS `dynamic_meta` (
  `data_key` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `data_name` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  `scalar_value` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `data_type` enum('scalar','array') COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`data_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci PACK_KEYS=0;

-- --------------------------------------------------------

--
-- Структура таблицы `dynamic_meta_array`
--

CREATE TABLE IF NOT EXISTS `dynamic_meta_array` (
  `id_dynamic_meta_array` int(11) NOT NULL AUTO_INCREMENT,
  `data_key` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `ns_data_key` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ns_data_value` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id_dynamic_meta_array`),
  KEY `dynamic_meta_array_FKIndex1` (`data_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci PACK_KEYS=0 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Структура таблицы `dynamic_meta_has_site_language`
--

CREATE TABLE IF NOT EXISTS `dynamic_meta_has_site_language` (
  `data_key` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `id_site_language` smallint(5) unsigned NOT NULL,
  PRIMARY KEY (`data_key`,`id_site_language`),
  KEY `dynamic_meta_has_site_language_FKIndex1` (`data_key`),
  KEY `dynamic_meta_has_site_language_FKIndex2` (`id_site_language`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci PACK_KEYS=0;

-- --------------------------------------------------------

--
-- Структура таблицы `file_access_type`
--

CREATE TABLE IF NOT EXISTS `file_access_type` (
  `id_file_access_type` int(11) NOT NULL AUTO_INCREMENT,
  `access_type` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `access_rule` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id_file_access_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci PACK_KEYS=0 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Структура таблицы `file_data`
--

CREATE TABLE IF NOT EXISTS `file_data` (
  `id_file_data` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id_file_access_type` int(11) DEFAULT NULL,
  `src_name` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `mime_type` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `description` text COLLATE utf8_unicode_ci NOT NULL,
  `file_type` enum('image','flash','video','other') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'other',
  `is_accessible` tinyint(1) NOT NULL DEFAULT '1',
  `is_deleted` tinyint(1) NOT NULL DEFAULT '0',
  `create_date` datetime DEFAULT NULL,
  `update_date` datetime DEFAULT NULL,
  PRIMARY KEY (`id_file_data`),
  KEY `file_data_FKIndex1` (`id_file_access_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci PACK_KEYS=0 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Структура таблицы `file_personal_access`
--

CREATE TABLE IF NOT EXISTS `file_personal_access` (
  `id_file_personal_access` bigint(19) unsigned NOT NULL AUTO_INCREMENT,
  `id_file_data` bigint(20) unsigned NOT NULL,
  `id_member` int(10) unsigned NOT NULL,
  `member_type` enum('owner','guest') COLLATE utf8_unicode_ci DEFAULT NULL,
  `expire_data` datetime DEFAULT NULL,
  `access_qtt` int(11) DEFAULT '-1',
  PRIMARY KEY (`id_file_personal_access`),
  KEY `file_personal_access_FKIndex1` (`id_file_data`),
  KEY `file_personal_access_FKIndex2` (`id_member`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci PACK_KEYS=0 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Структура таблицы `flash`
--

CREATE TABLE IF NOT EXISTS `flash` (
  `id_file_data` bigint(20) unsigned NOT NULL,
  `width` smallint(5) unsigned DEFAULT NULL,
  `height` smallint(5) unsigned DEFAULT NULL,
  `bgcolor` varchar(6) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id_file_data`),
  KEY `flash_FKIndex1` (`id_file_data`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci PACK_KEYS=0;

-- --------------------------------------------------------

--
-- Структура таблицы `image`
--

CREATE TABLE IF NOT EXISTS `image` (
  `id_file_data` bigint(20) unsigned NOT NULL,
  `width` smallint(5) unsigned NOT NULL,
  `height` smallint(5) unsigned NOT NULL,
  `img_type` smallint(5) unsigned NOT NULL,
  `alt` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id_file_data`),
  KEY `image_FKIndex1` (`id_file_data`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci PACK_KEYS=0;

-- --------------------------------------------------------

--
-- Структура таблицы `member`
--

CREATE TABLE IF NOT EXISTS `member` (
  `id_member` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_site_language` smallint(5) unsigned DEFAULT NULL,
  `login` varchar(48) COLLATE utf8_unicode_ci NOT NULL,
  `password` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `firstname` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `patronymic` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `surname` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `mobile_phone` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  `email` varchar(80) COLLATE utf8_unicode_ci DEFAULT NULL,
  `postal_code` varchar(16) COLLATE utf8_unicode_ci DEFAULT NULL,
  `address` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `member_status` enum('new','confirmed','active','block') COLLATE utf8_unicode_ci DEFAULT NULL,
  `join_date` datetime DEFAULT NULL,
  `last_visit_date` datetime DEFAULT NULL,
  PRIMARY KEY (`id_member`),
  UNIQUE KEY `unic_login` (`login`),
  KEY `fk_member_n1_idx` (`id_site_language`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci PACK_KEYS=0 COMMENT='Main member''s data' AUTO_INCREMENT=20 ;

-- --------------------------------------------------------

--
-- Структура таблицы `member_has_role`
--

CREATE TABLE IF NOT EXISTS `member_has_role` (
  `id_member` int(10) unsigned NOT NULL,
  `id_role` mediumint(8) unsigned NOT NULL,
  `expired_date` datetime DEFAULT NULL,
  PRIMARY KEY (`id_member`,`id_role`),
  KEY `member_has_role_FKIndex1` (`id_member`),
  KEY `member_has_role_FKIndex2` (`id_role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci PACK_KEYS=0;

-- --------------------------------------------------------

--
-- Структура таблицы `role`
--

CREATE TABLE IF NOT EXISTS `role` (
  `id_role` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `id_role_group` int(11) NOT NULL,
  `role_name` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id_role`),
  UNIQUE KEY `role_unique_name` (`role_name`),
  KEY `role_FKIndex1` (`id_role_group`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci PACK_KEYS=0 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Структура таблицы `role_embed_role`
--

CREATE TABLE IF NOT EXISTS `role_embed_role` (
  `id_container_role` mediumint(8) unsigned NOT NULL,
  `id_embedded_role` mediumint(8) unsigned NOT NULL,
  PRIMARY KEY (`id_container_role`,`id_embedded_role`),
  KEY `fk_role_embed_role_n2_idx` (`id_embedded_role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci PACK_KEYS=0;

-- --------------------------------------------------------

--
-- Структура таблицы `role_group`
--

CREATE TABLE IF NOT EXISTS `role_group` (
  `id_role_group` int(11) NOT NULL AUTO_INCREMENT,
  `group_name` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id_role_group`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci PACK_KEYS=0 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Структура таблицы `site_language`
--

CREATE TABLE IF NOT EXISTS `site_language` (
  `id_site_language` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `short_name` varchar(2) COLLATE utf8_unicode_ci DEFAULT NULL,
  `full_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id_site_language`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci PACK_KEYS=0 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Структура таблицы `test_primary`
--

CREATE TABLE IF NOT EXISTS `test_primary` (
  `id_test_primary` int(11) NOT NULL AUTO_INCREMENT,
  `date` date DEFAULT NULL,
  `header` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `content` text COLLATE utf8_unicode_ci,
  `is_complete` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id_test_primary`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci PACK_KEYS=0 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Структура таблицы `test_subtable`
--

CREATE TABLE IF NOT EXISTS `test_subtable` (
  `id_test_subtable` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_test_primary` int(11) NOT NULL,
  `sub_content` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id_test_subtable`),
  KEY `fk_test_subtable_n1_idx` (`id_test_primary`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 COMMENT = 'Some table description';

-- --------------------------------------------------------

--
-- Структура таблицы `timer_program`
--

CREATE TABLE IF NOT EXISTS `timer_program` (
  `id_process` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `start_time` datetime DEFAULT NULL,
  `class_name` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `method_name` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `parameters` text COLLATE utf8_unicode_ci,
  `period` int(11) NOT NULL DEFAULT '0',
  `overcall_limit` tinyint(4) NOT NULL DEFAULT '0',
  `overcall_qtt` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '0',
  `last_start` datetime DEFAULT NULL,
  PRIMARY KEY (`id_process`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci PACK_KEYS=0;

-- --------------------------------------------------------

--
-- Структура таблицы `video`
--

CREATE TABLE IF NOT EXISTS `video` (
  `id_file_data` bigint(20) unsigned NOT NULL,
  `width` smallint(5) unsigned DEFAULT NULL,
  `height` smallint(5) unsigned DEFAULT NULL,
  `duration` mediumint(8) unsigned DEFAULT NULL,
  `sound` enum('none','mono','stereo') COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id_file_data`),
  KEY `video_FKIndex1` (`id_file_data`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci PACK_KEYS=0;

--
-- Ограничения внешнего ключа сохраненных таблиц
--

--
-- Ограничения внешнего ключа таблицы `administrator_has_role`
--
ALTER TABLE `administrator_has_role`
  ADD CONSTRAINT `fk_administrator_has_role_n1` FOREIGN KEY (`id_administrator`) REFERENCES `administrator` (`id_administrator`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_administrator_has_role_n2` FOREIGN KEY (`id_role`) REFERENCES `role` (`id_role`) ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `dynamic_meta_array`
--
ALTER TABLE `dynamic_meta_array`
  ADD CONSTRAINT `fk_dynamic_meta_array_n1` FOREIGN KEY (`data_key`) REFERENCES `dynamic_meta` (`data_key`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `dynamic_meta_has_site_language`
--
ALTER TABLE `dynamic_meta_has_site_language`
  ADD CONSTRAINT `fk_dynamic_meta_has_site_language_n1` FOREIGN KEY (`data_key`) REFERENCES `dynamic_meta` (`data_key`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_dynamic_meta_has_site_language_n2` FOREIGN KEY (`id_site_language`) REFERENCES `site_language` (`id_site_language`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `file_data`
--
ALTER TABLE `file_data`
  ADD CONSTRAINT `fk_file_data_n1` FOREIGN KEY (`id_file_access_type`) REFERENCES `file_access_type` (`id_file_access_type`) ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `file_personal_access`
--
ALTER TABLE `file_personal_access`
  ADD CONSTRAINT `fk_file_personal_access_n1` FOREIGN KEY (`id_file_data`) REFERENCES `file_data` (`id_file_data`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_file_personal_access_n2` FOREIGN KEY (`id_member`) REFERENCES `member` (`id_member`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `flash`
--
ALTER TABLE `flash`
  ADD CONSTRAINT `fk_flash_n1` FOREIGN KEY (`id_file_data`) REFERENCES `file_data` (`id_file_data`) ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `image`
--
ALTER TABLE `image`
  ADD CONSTRAINT `fk_image_n1` FOREIGN KEY (`id_file_data`) REFERENCES `file_data` (`id_file_data`) ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `member`
--
ALTER TABLE `member`
  ADD CONSTRAINT `fk_member_n1` FOREIGN KEY (`id_site_language`) REFERENCES `site_language` (`id_site_language`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `member_has_role`
--
ALTER TABLE `member_has_role`
  ADD CONSTRAINT `fk_member_has_role_n1` FOREIGN KEY (`id_member`) REFERENCES `member` (`id_member`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_member_has_role_n2` FOREIGN KEY (`id_role`) REFERENCES `role` (`id_role`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `role`
--
ALTER TABLE `role`
  ADD CONSTRAINT `fk_role_n1` FOREIGN KEY (`id_role_group`) REFERENCES `role_group` (`id_role_group`) ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `role_embed_role`
--
ALTER TABLE `role_embed_role`
  ADD CONSTRAINT `fk_role_embed_role_n1` FOREIGN KEY (`id_container_role`) REFERENCES `role` (`id_role`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_role_embed_role_n2` FOREIGN KEY (`id_embedded_role`) REFERENCES `role` (`id_role`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `test_subtable`
--
ALTER TABLE `test_subtable`
  ADD CONSTRAINT `fk_test_subtable_n1` FOREIGN KEY (`id_test_primary`) REFERENCES `test_primary` (`id_test_primary`) ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `video`
--
ALTER TABLE `video`
  ADD CONSTRAINT `fk_video_n1` FOREIGN KEY (`id_file_data`) REFERENCES `file_data` (`id_file_data`) ON DELETE CASCADE ON UPDATE CASCADE;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
