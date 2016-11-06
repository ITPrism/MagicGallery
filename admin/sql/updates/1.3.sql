ALTER TABLE `#__magicgallery_entities` DROP `filesize` ;
ALTER TABLE `#__magicgallery_entities` DROP `width`;
ALTER TABLE `#__magicgallery_entities` DROP `height`;
ALTER TABLE `#__magicgallery_entities` DROP `mime`;

ALTER TABLE `#__magicgallery_entities` ADD `image_filesize` INT(11) UNSIGNED NOT NULL DEFAULT '0' AFTER `thumbnail`;
ALTER TABLE `#__magicgallery_entities` ADD `thumbnail_filesize` INT(11) UNSIGNED NOT NULL  DEFAULT '0' AFTER `image_filesize`;
ALTER TABLE `#__magicgallery_entities` ADD `image_meta` VARCHAR(255) NOT NULL DEFAULT '{}' AFTER `thumbnail_filesize`;
ALTER TABLE `#__magicgallery_entities` ADD `thumbnail_meta` VARCHAR(255) NOT NULL DEFAULT '{}' AFTER `image_meta`;

ALTER TABLE `#__magicgallery_galleries` ADD `metadesc` VARCHAR(255) NULL DEFAULT NULL AFTER `params`, ADD `metakeys` VARCHAR(255) NULL DEFAULT NULL AFTER `metadesc`;