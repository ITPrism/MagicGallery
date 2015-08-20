CREATE TABLE IF NOT EXISTS `#__magicgallery_galleries` (
  `id` smallint(6) unsigned NOT NULL,
  `title` varchar(255) NOT NULL,
  `alias` varchar(255) NOT NULL DEFAULT '',
  `description` text,
  `url` varchar(255) DEFAULT NULL,
  `catid` smallint(6) unsigned NOT NULL DEFAULT '0',
  `extension` varchar(64) NOT NULL DEFAULT '' COMMENT 'Element name if it is assigned to an extension.',
  `object_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'Item ID if it is assigned to an extension.',
  `published` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `ordering` tinyint(4) unsigned NOT NULL DEFAULT '0',
  `user_id` int(10) unsigned NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__magicgallery_resources` (
  `id` smallint(6) unsigned NOT NULL,
  `title` varchar(128) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `image` varchar(24) NOT NULL,
  `thumbnail` varchar(24) NOT NULL,
  `size` int(11) DEFAULT NULL COMMENT 'Filesize in bytes.',
  `width` smallint(6) DEFAULT NULL,
  `height` smallint(6) DEFAULT NULL,
  `mime_type` varchar(64) DEFAULT NULL,
  `type` enum('image','video') NOT NULL DEFAULT 'image',
  `home` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `params` varchar(2048) DEFAULT NULL,
  `ordering` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `published` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `gallery_id` int(6) unsigned NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


ALTER TABLE `#__magicgallery_galleries`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_galleries_object_id` (`object_id`),
  ADD KEY `idx_galleries_extension` (`extension`) USING BTREE,
  ADD KEY `idx_galleries_catid` (`catid`) USING BTREE,
  ADD KEY `idx_galleries_user_id` (`user_id`);

ALTER TABLE `#__magicgallery_resources`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_itpvp_pi_id` (`gallery_id`),
  ADD KEY `idx_images_ordering` (`ordering`),
  ADD KEY `idx_images_state` (`published`),
  ADD KEY `idx_images_default` (`home`),
  ADD KEY `idx_resource_type` (`type`);


ALTER TABLE `#__magicgallery_galleries`
  MODIFY `id` smallint(6) unsigned NOT NULL AUTO_INCREMENT;
ALTER TABLE `#__magicgallery_resources`
  MODIFY `id` smallint(6) unsigned NOT NULL AUTO_INCREMENT;

