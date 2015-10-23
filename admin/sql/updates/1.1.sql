ALTER TABLE `#__magicgallery_galleries` DROP INDEX `idx_galleries_user_id`;
ALTER TABLE `#__magicgallery_galleries` DROP INDEX `idx_galleries_extension`;
ALTER TABLE `#__magicgallery_galleries` DROP INDEX `idx_galleries_object_id`;

ALTER TABLE `#__magicgallery_galleries` ADD `params` VARCHAR(255) NULL DEFAULT NULL AFTER `object_id`;

ALTER TABLE `#__magicgallery_resources` DROP INDEX `idx_resource_type`;
ALTER TABLE `#__magicgallery_resources` DROP INDEX `idx_images_default`;
ALTER TABLE `#__magicgallery_resources` DROP INDEX `idx_images_state`;

ALTER TABLE `#__magicgallery_resources` CHANGE `mime_type` `mime` VARCHAR(64) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;
ALTER TABLE `#__magicgallery_resources` CHANGE `size` `filesize` INT(11) NULL DEFAULT NULL COMMENT 'Filesize in bytes.';

ALTER TABLE `#__magicgallery_resources` CHANGE `image` `image` VARCHAR(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;
ALTER TABLE `#__magicgallery_resources` CHANGE `thumbnail` `thumbnail` VARCHAR(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;

ALTER TABLE `#__magicgallery_resources` DROP `params`;

ALTER TABLE `#__magicgallery_resources` RENAME `#__magicgallery_entities`;