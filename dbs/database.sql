SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


CREATE TABLE IF NOT EXISTS `category` (
`id` int(11) NOT NULL,
  `name` varchar(8) COLLATE utf8_unicode_ci NOT NULL,
  `mute` tinyint(4) NOT NULL DEFAULT '0',
  `hide` tinyint(4) NOT NULL DEFAULT '0',
  `theme` varchar(8) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `category` (`id`, `name`, `mute`, `hide`, `theme`) VALUES
(1, '兄贵', 0, 0, 'red'),
(2, '卖萌', 0, 0, 'orange'),
(3, '搞基', 0, 0, 'pink'),
(4, '天空', 0, 0, 'green'),
(5, '百合', 0, 0, 'blue'),
(6, '种子', 1, 1, 'teal');

CREATE TABLE IF NOT EXISTS `content` (
`id` int(11) NOT NULL,
  `author` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `title` text COLLATE utf8_unicode_ci,
  `content` text COLLATE utf8_unicode_ci NOT NULL,
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `active_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `img` tinytext COLLATE utf8_unicode_ci,
  `upid` int(11) NOT NULL,
  `sage` tinyint(4) NOT NULL DEFAULT '0',
  `category` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE `category`
 ADD PRIMARY KEY (`id`);

ALTER TABLE `content`
 ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `id_UNIQUE` (`id`), ADD FULLTEXT KEY `index_content` (`title`,`content`);

ALTER TABLE `category`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=7;

ALTER TABLE `content`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;