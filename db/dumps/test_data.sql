SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";

--
-- База данных: `php_fan_test`
--
USE `php_fan_test`;

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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci PACK_KEYS=0 AUTO_INCREMENT=1;

--
-- Дамп данных таблицы `test_primary`
--

INSERT INTO `test_primary` (`id_test_primary`, `date`, `header`, `content`, `is_complete`) VALUES
(1, '2014-01-01', 'Lorem ipsum dolor', 'Lorem ipsum dolor sit amet, usu vero bonorum ad. Simul praesent vulputate ei pri.\nHis omnis libris possit ex, dissentias liberavisse mel ex, cum debet clita voluptatibus in.\nFalli expetenda ne eum, purto aeque verterem ut vel, ut sit elitr aliquip.\nPri at molestie delicatissimi, eos tractatos torquatos no, no mel dolore integre minimum.', 0),
(5, '2014-01-02', 'Debitis definiebas te quo', 'Debitis definiebas te quo, in maiorum appellantur concludaturque vis.\nTale iudicabit reprimique id qui, ea expetendis interpretaris usu, his ne porro paulo exerci.\nAn eum utamur gubergren, diam legimus an quo. Mea atomorum disputando ut.\nDuo ne tritani malorum consulatu, pro nullam debitis te, sit vero legimus consequuntur in.', 1),
(16, '2013-12-23', 'Mea ea augue iriure', 'Mea ea augue iriure, at ullum graecis dissentias has, dolorem consectetuer in mel.\nElitr dicant persius ut eum, nec paulo epicurei ad. Accusata definitionem ei est.\nCu quo diam aeque, ea pro doming menandri, ad usu senserit postulant constituam', 0),
(18, '2014-01-07', 'Sed id essent iriure, nam dolorem civibus', 'Sed id essent iriure, nam dolorem civibus mnesarchum te. Ea utinam constituto eos, eam insolens consequat reprehendunt at.\r\nEligendi efficiantur et vis, vix et invidunt neglegentur. Feugait placerat oportere cum id.\r\nAlia graecis an mea, mel fuisset scripserit scribentur ut.', 0),
(19, '2014-01-08', 'Ei probatus percipitur sadipscing his', 'Ei probatus percipitur sadipscing his.\nEos ea omnes delicata vituperatoribus, pro purto tamquam id, nec option molestiae cu.\nSea eu ipsum paulo persecuti, in utroque voluptua recusabo ius, quando animal qui ad.\nTe falli exerci usu. Stet perfecto eam ex.\nDolorum accumsan volutpat vis cu, suscipit efficiantur ex mel.', 1);

-- --------------------------------------------------------

--
-- Структура таблицы `test_subtable`
--

CREATE TABLE IF NOT EXISTS `test_subtable` (
  `id_test_subtable` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_test_primary` int(11) NOT NULL,
  `sub_content` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id_test_subtable`),
  KEY `fk_test_subtable_n1_idx` (`id_test_primary`),
  CONSTRAINT `fk_test_subtable_n1` FOREIGN KEY (`id_test_primary`) REFERENCES `test_primary` (`id_test_primary`) ON UPDATE CASCADE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1;

--
-- Дамп данных таблицы `test_subtable`
--

INSERT INTO `test_subtable` (`id_test_subtable`, `id_test_primary`, `sub_content`) VALUES
(1, 1, 'Mollis gubergren reformidans'),
(2, 1, 'Eam ut everti impetus pertinacia'),
(3, 16, 'Eam ei facer ludus delicatissimi'),
(4, 5, 'Ad est assum fierent'),
(5, 18, 'Justo possit pri ad'),
(6, 19, 'Denique quaerendum in est'),
(7, 19, 'Ea his harum veritus');
