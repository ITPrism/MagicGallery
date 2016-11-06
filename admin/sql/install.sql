CREATE TABLE IF NOT EXISTS `#__magicgallery_entities` (
  `id` smallint(6) UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` varchar(128) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `image` varchar(32) NOT NULL,
  `thumbnail` varchar(32) NOT NULL,
  `image_filesize` int(11) NOT NULL DEFAULT '0',
  `thumbnail_filesize` int(11) NOT NULL DEFAULT '0',
  `image_meta` varchar(255) NOT NULL DEFAULT '{}',
  `thumbnail_meta` varchar(255) NOT NULL DEFAULT '{}',
  `type` enum('image','video') NOT NULL DEFAULT 'image',
  `home` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
  `ordering` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
  `published` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
  `gallery_id` int(6) UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_itpvp_pi_id` (`gallery_id`),
  KEY `idx_images_ordering` (`ordering`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__magicgallery_galleries` (
  `id` smallint(6) UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `alias` varchar(255) NOT NULL DEFAULT '',
  `description` text,
  `url` varchar(255) DEFAULT NULL,
  `catid` smallint(6) UNSIGNED NOT NULL DEFAULT '0',
  `extension` varchar(64) NOT NULL DEFAULT '' COMMENT 'Element name if it is assigned to an extension.',
  `object_id` int(11) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Item ID if it is assigned to an extension.',
  `params` varchar(255) DEFAULT NULL,
  `metadesc` varchar(255) DEFAULT NULL,
  `metakeys` varchar(255) DEFAULT NULL,
  `published` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
  `ordering` tinyint(4) UNSIGNED NOT NULL DEFAULT '0',
  `user_id` int(10) UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_galleries_catid` (`catid`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
