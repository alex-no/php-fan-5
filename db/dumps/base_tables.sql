-- phpMyAdmin SQL Dump
-- version 4.0.9
-- http://www.phpmyadmin.net
--
-- Хост: 127.0.0.1
-- Время создания: Дек 31 2013 г., 00:33
-- Версия сервера: 5.5.20
-- Версия PHP: 5.3.19

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
-- Структура таблицы `city_area`
--

CREATE TABLE IF NOT EXISTS `city_area` (
  `id_city_area` int(11) NOT NULL AUTO_INCREMENT,
  `id_country` int(11) NOT NULL,
  `id_city_area_kind` mediumint(8) unsigned NOT NULL,
  `id_area_center` int(11) DEFAULT NULL,
  `name_ru` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `name_en` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id_city_area`),
  KEY `city_FKIndex1` (`id_country`),
  KEY `fk_city_area_n1_idx` (`id_city_area_kind`),
  KEY `fk_city_area_n2_idx` (`id_area_center`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci PACK_KEYS=0 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Структура таблицы `city_area_kind`
--

CREATE TABLE IF NOT EXISTS `city_area_kind` (
  `id_city_area_kind` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `name_ru` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `name_en` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL,
  `short_name_ru` varchar(16) COLLATE utf8_unicode_ci NOT NULL,
  `short_name_en` varchar(16) COLLATE utf8_unicode_ci DEFAULT NULL,
  `data_type` enum('area','city') COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id_city_area_kind`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Структура таблицы `country`
--

CREATE TABLE IF NOT EXISTS `country` (
  `id_country` int(11) NOT NULL AUTO_INCREMENT,
  `full_name_ru` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `full_name_en` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `short_name` varchar(3) COLLATE utf8_unicode_ci DEFAULT NULL,
  `show_it` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_country`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci PACK_KEYS=0 AUTO_INCREMENT=1 ;

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
-- Структура таблицы `internal_news`
--

CREATE TABLE IF NOT EXISTS `internal_news` (
  `id_internal_news` int(11) NOT NULL AUTO_INCREMENT,
  `news_date` date DEFAULT NULL,
  `header_ru` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `header_en` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `announcement_ru` text COLLATE utf8_unicode_ci,
  `announcement_en` text COLLATE utf8_unicode_ci,
  `content_ru` text COLLATE utf8_unicode_ci,
  `content_en` text COLLATE utf8_unicode_ci,
  `is_complete` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id_internal_news`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci PACK_KEYS=0 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Структура таблицы `internal_news_has_image`
--

CREATE TABLE IF NOT EXISTS `internal_news_has_image` (
  `id_internal_news` int(11) NOT NULL,
  `id_file_data` bigint(20) unsigned NOT NULL,
  `order_num` mediumint(8) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`id_internal_news`,`id_file_data`),
  KEY `internal_news_has_image_FKIndex1` (`id_internal_news`),
  KEY `internal_news_has_image_FKIndex2` (`id_file_data`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci PACK_KEYS=0;

-- --------------------------------------------------------

--
-- Структура таблицы `member`
--

CREATE TABLE IF NOT EXISTS `member` (
  `id_member` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_city_area` int(11) NOT NULL,
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
  KEY `member_FKIndex1` (`id_city_area`),
  KEY `member_FKIndex2` (`id_site_language`)
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
-- Структура таблицы `menu_element`
--

CREATE TABLE IF NOT EXISTS `menu_element` (
  `id_menu_element` int(11) NOT NULL AUTO_INCREMENT,
  `group_key` varchar(16) COLLATE utf8_unicode_ci NOT NULL,
  `id_menu_element_parent` int(11) DEFAULT NULL,
  `id_site_url` int(11) DEFAULT NULL,
  `order_key` smallint(5) unsigned DEFAULT NULL,
  `menu_key` varchar(48) COLLATE utf8_unicode_ci DEFAULT NULL,
  `condition_key` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  `target` enum('self','top','blank') COLLATE utf8_unicode_ci DEFAULT 'self',
  `menu_type` enum('local','adjacent','foreign','dummy') COLLATE utf8_unicode_ci DEFAULT 'local',
  PRIMARY KEY (`id_menu_element`),
  KEY `menu_element_FKIndex1` (`group_key`),
  KEY `menu_element_FKIndex2` (`id_site_url`),
  KEY `menu_element_FKIndex3` (`id_menu_element_parent`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci PACK_KEYS=0 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Структура таблицы `menu_element_language`
--

CREATE TABLE IF NOT EXISTS `menu_element_language` (
  `id_menu_element_language` int(11) NOT NULL AUTO_INCREMENT,
  `id_menu_element` int(11) NOT NULL,
  `id_site_language` smallint(5) unsigned DEFAULT NULL,
  `menu_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `foreign_url` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  `show_it` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_menu_element_language`),
  KEY `menu_element_language_FKIndex1` (`id_menu_element`),
  KEY `menu_element_language_FKIndex2` (`id_site_language`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci PACK_KEYS=0 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Структура таблицы `menu_group`
--

CREATE TABLE IF NOT EXISTS `menu_group` (
  `group_key` varchar(16) COLLATE utf8_unicode_ci NOT NULL,
  `group_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `is_editable` enum('no','yes','not_new') COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`group_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci PACK_KEYS=0;

-- --------------------------------------------------------

--
-- Структура таблицы `remind_enter_password`
--

CREATE TABLE IF NOT EXISTS `remind_enter_password` (
  `id_remind_enter_password` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_member` int(10) unsigned NOT NULL,
  `request_date` datetime DEFAULT NULL,
  `change_key` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id_remind_enter_password`),
  KEY `remind_enter_password_FKIndex1` (`id_member`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci PACK_KEYS=0 AUTO_INCREMENT=1 ;

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
  KEY `fk_role_embed_role_n2_idx` (`id_embedded_role`),
  KEY `fk_role_embed_role_n1_idx` (`id_container_role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

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
-- Структура таблицы `site_url`
--

CREATE TABLE IF NOT EXISTS `site_url` (
  `id_site_url` int(11) NOT NULL AUTO_INCREMENT,
  `id_site_url_parent` int(11) DEFAULT NULL,
  `url_value` varchar(80) COLLATE utf8_unicode_ci DEFAULT NULL,
  `url_status` enum('dynamic','static','blocked') COLLATE utf8_unicode_ci DEFAULT 'static',
  `protocol` enum('http','https') COLLATE utf8_unicode_ci DEFAULT NULL,
  `url_role` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  `url_key1` varchar(48) COLLATE utf8_unicode_ci DEFAULT NULL,
  `url_key2` varchar(48) COLLATE utf8_unicode_ci DEFAULT NULL,
  `url_css` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `url_js` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id_site_url`),
  UNIQUE KEY `site_url_unique_url` (`url_value`),
  KEY `site_url_FKIndex1` (`id_site_url_parent`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci PACK_KEYS=0 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Структура таблицы `site_url_attribute`
--

CREATE TABLE IF NOT EXISTS `site_url_attribute` (
  `id_site_url_attribute` int(11) NOT NULL AUTO_INCREMENT,
  `id_site_url` int(11) NOT NULL,
  `id_site_language` smallint(5) unsigned DEFAULT NULL,
  `header` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `title` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `keywords` text COLLATE utf8_unicode_ci,
  `description` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id_site_url_attribute`),
  KEY `site_url_attribute_FKIndex1` (`id_site_url`),
  KEY `site_url_attribute_FKIndex2` (`id_site_language`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci PACK_KEYS=0 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Структура таблицы `site_url_has_static_content`
--

CREATE TABLE IF NOT EXISTS `site_url_has_static_content` (
  `id_site_url` int(11) NOT NULL,
  `id_static_content` int(11) NOT NULL,
  PRIMARY KEY (`id_site_url`,`id_static_content`),
  KEY `site_url_has_static_content_FKIndex1` (`id_site_url`),
  KEY `site_url_has_static_content_FKIndex2` (`id_static_content`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci PACK_KEYS=0;

-- --------------------------------------------------------

--
-- Структура таблицы `static_content`
--

CREATE TABLE IF NOT EXISTS `static_content` (
  `id_static_content` int(11) NOT NULL AUTO_INCREMENT,
  `id_site_language` smallint(5) unsigned DEFAULT NULL,
  `content_key` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL,
  `content_data` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id_static_content`),
  KEY `static_content_FKIndex1` (`id_site_language`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci PACK_KEYS=0 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Структура таблицы `static_content_has_image`
--

CREATE TABLE IF NOT EXISTS `static_content_has_image` (
  `id_static_content` int(11) NOT NULL,
  `id_file_data` bigint(20) unsigned NOT NULL,
  `order_num` mediumint(8) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`id_static_content`,`id_file_data`),
  KEY `static_content_has_image_FKIndex1` (`id_static_content`),
  KEY `static_content_has_image_FKIndex2` (`id_file_data`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci PACK_KEYS=0;

--
-- Триггеры `static_content_has_image`
--
DROP TRIGGER IF EXISTS `static_content_has_image_before_ins_tr`;
DELIMITER //
CREATE TRIGGER `static_content_has_image_before_ins_tr` BEFORE INSERT ON `static_content_has_image`
 FOR EACH ROW BEGIN
	DECLARE iCnum MEDIUMINT DEFAULT 1;
	SELECT MAX(`order_num`) INTO iCnum FROM `static_content_has_image` WHERE `id_static_content` = NEW.`id_static_content`;
	if iCnum IS NULL THEN
		SET NEW.`order_num` = 1;
	ELSE
		SET NEW.`order_num` = iCnum + 1;
	END IF;
END
//
DELIMITER ;

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
  ADD CONSTRAINT `fk_{0D2DABB4-6546-428D-A614-6C033E288200}` FOREIGN KEY (`id_administrator`) REFERENCES `administrator` (`id_administrator`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_{35C549DA-DE73-4429-94A9-84F58D502FF6}` FOREIGN KEY (`id_role`) REFERENCES `role` (`id_role`) ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `city_area`
--
ALTER TABLE `city_area`
  ADD CONSTRAINT `fk_city_area_n1` FOREIGN KEY (`id_city_area_kind`) REFERENCES `city_area_kind` (`id_city_area_kind`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_city_area_n2` FOREIGN KEY (`id_area_center`) REFERENCES `city_area` (`id_city_area`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_{0DE5939B-1992-42B3-AEBF-F01F3D6A32DA}` FOREIGN KEY (`id_country`) REFERENCES `country` (`id_country`) ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `dynamic_meta_array`
--
ALTER TABLE `dynamic_meta_array`
  ADD CONSTRAINT `fk_{6E1ED857-8AF8-4446-B6AE-12712A8F268A}` FOREIGN KEY (`data_key`) REFERENCES `dynamic_meta` (`data_key`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `dynamic_meta_has_site_language`
--
ALTER TABLE `dynamic_meta_has_site_language`
  ADD CONSTRAINT `fk_{10A40977-4914-4DE1-AB02-C77211FDBAC3}` FOREIGN KEY (`data_key`) REFERENCES `dynamic_meta` (`data_key`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_{5609ACDB-A25A-437B-9D79-709261059EE3}` FOREIGN KEY (`id_site_language`) REFERENCES `site_language` (`id_site_language`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `file_data`
--
ALTER TABLE `file_data`
  ADD CONSTRAINT `fk_{E108EFBD-287A-4DD1-A7BA-665A114040F1}` FOREIGN KEY (`id_file_access_type`) REFERENCES `file_access_type` (`id_file_access_type`) ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `file_personal_access`
--
ALTER TABLE `file_personal_access`
  ADD CONSTRAINT `fk_{471576AD-D7BB-40BE-8E4C-7AD60AFE5927}` FOREIGN KEY (`id_file_data`) REFERENCES `file_data` (`id_file_data`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_{E68106DE-0E89-49F9-9511-C4EF01D1C1E3}` FOREIGN KEY (`id_member`) REFERENCES `member` (`id_member`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `flash`
--
ALTER TABLE `flash`
  ADD CONSTRAINT `fk_{BAB50417-09A4-4839-8C04-AABFB37CB860}` FOREIGN KEY (`id_file_data`) REFERENCES `file_data` (`id_file_data`) ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `image`
--
ALTER TABLE `image`
  ADD CONSTRAINT `fk_{865FD68B-F7B4-4044-9B16-92F48A5C7089}` FOREIGN KEY (`id_file_data`) REFERENCES `file_data` (`id_file_data`) ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `internal_news_has_image`
--
ALTER TABLE `internal_news_has_image`
  ADD CONSTRAINT `fk_{493D042E-C374-45D5-997E-83936F6C2558}` FOREIGN KEY (`id_internal_news`) REFERENCES `internal_news` (`id_internal_news`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_{E2CDEA36-703A-43E4-8C0D-E957D04453FE}` FOREIGN KEY (`id_file_data`) REFERENCES `image` (`id_file_data`) ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `member`
--
ALTER TABLE `member`
  ADD CONSTRAINT `fk_{1795D324-0F7E-47E5-A3C4-1084E6111BED}` FOREIGN KEY (`id_city_area`) REFERENCES `city_area` (`id_city_area`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_{A269C6C2-3808-4293-80CA-89134A9B579D}` FOREIGN KEY (`id_site_language`) REFERENCES `site_language` (`id_site_language`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `member_has_role`
--
ALTER TABLE `member_has_role`
  ADD CONSTRAINT `fk_{C37DBA92-0142-493F-A108-25ADA8546A59}` FOREIGN KEY (`id_member`) REFERENCES `member` (`id_member`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_{EF52055D-BFC0-4AD8-9126-FEC53624E51F}` FOREIGN KEY (`id_role`) REFERENCES `role` (`id_role`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `menu_element`
--
ALTER TABLE `menu_element`
  ADD CONSTRAINT `fk_{A6915A40-ABF4-4311-AFDB-A543D826C900}` FOREIGN KEY (`group_key`) REFERENCES `menu_group` (`group_key`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_{482E2002-637F-4050-B8E0-7287400B75C7}` FOREIGN KEY (`id_site_url`) REFERENCES `site_url` (`id_site_url`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_{297271C4-6C6F-4C69-8768-0C0420EEBFDC}` FOREIGN KEY (`id_menu_element_parent`) REFERENCES `menu_element` (`id_menu_element`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `menu_element_language`
--
ALTER TABLE `menu_element_language`
  ADD CONSTRAINT `fk_{BDD4968C-D915-4DEC-93AF-0C23F4F29670}` FOREIGN KEY (`id_menu_element`) REFERENCES `menu_element` (`id_menu_element`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_{15A47C15-183A-491D-A4F1-33CBA57DFCA5}` FOREIGN KEY (`id_site_language`) REFERENCES `site_language` (`id_site_language`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `remind_enter_password`
--
ALTER TABLE `remind_enter_password`
  ADD CONSTRAINT `fk_{C69B7A27-9BD7-4276-B024-E17535CC1DE1}` FOREIGN KEY (`id_member`) REFERENCES `member` (`id_member`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `role`
--
ALTER TABLE `role`
  ADD CONSTRAINT `fk_{FCDC5772-5DAB-4FF0-9697-1762E77674B0}` FOREIGN KEY (`id_role_group`) REFERENCES `role_group` (`id_role_group`) ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `role_embed_role`
--
ALTER TABLE `role_embed_role`
  ADD CONSTRAINT `fk_role_embed_role_n1` FOREIGN KEY (`id_container_role`) REFERENCES `role` (`id_role`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_role_embed_role_n2` FOREIGN KEY (`id_embedded_role`) REFERENCES `role` (`id_role`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `site_url`
--
ALTER TABLE `site_url`
  ADD CONSTRAINT `fk_{7E1B5B98-93F6-485A-955A-70877A3BF020}` FOREIGN KEY (`id_site_url_parent`) REFERENCES `site_url` (`id_site_url`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `site_url_attribute`
--
ALTER TABLE `site_url_attribute`
  ADD CONSTRAINT `fk_{D84836DB-8F6C-4025-8CC5-1D0F696824F3}` FOREIGN KEY (`id_site_url`) REFERENCES `site_url` (`id_site_url`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_{45249651-5368-42D0-A1B1-661E57756F48}` FOREIGN KEY (`id_site_language`) REFERENCES `site_language` (`id_site_language`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `site_url_has_static_content`
--
ALTER TABLE `site_url_has_static_content`
  ADD CONSTRAINT `fk_{2D1977A3-F301-4C12-8035-278CD86A5083}` FOREIGN KEY (`id_site_url`) REFERENCES `site_url` (`id_site_url`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_{37878F10-B34D-4D23-8E2B-3FB3B49D88F2}` FOREIGN KEY (`id_static_content`) REFERENCES `static_content` (`id_static_content`) ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `static_content`
--
ALTER TABLE `static_content`
  ADD CONSTRAINT `fk_{5E885F54-D18A-476C-A20D-14286AAA8F29}` FOREIGN KEY (`id_site_language`) REFERENCES `site_language` (`id_site_language`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `static_content_has_image`
--
ALTER TABLE `static_content_has_image`
  ADD CONSTRAINT `fk_{C587361D-9509-4A99-92DA-B8F21E27C34B}` FOREIGN KEY (`id_static_content`) REFERENCES `static_content` (`id_static_content`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_{E20C8579-1262-4E4B-8361-CC4C3A997CD0}` FOREIGN KEY (`id_file_data`) REFERENCES `image` (`id_file_data`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `video`
--
ALTER TABLE `video`
  ADD CONSTRAINT `fk_{D8DB9EAB-4B9D-4E6E-8B9B-2DB29DCA39EF}` FOREIGN KEY (`id_file_data`) REFERENCES `file_data` (`id_file_data`) ON DELETE CASCADE ON UPDATE CASCADE;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
