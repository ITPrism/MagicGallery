<?php
/**
 * @package      MagicGallery
 * @subpackage   Initializator
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

if (!defined('MAGICGALLERY_PATH_COMPONENT_ADMINISTRATOR')) {
    define('MAGICGALLERY_PATH_COMPONENT_ADMINISTRATOR', JPATH_ADMINISTRATOR . '/components/com_magicgallery');
}

if (!defined('MAGICGALLERY_PATH_COMPONENT_SITE')) {
    define('MAGICGALLERY_PATH_COMPONENT_SITE', JPATH_SITE . '/components/com_magicgallery');
}

if (!defined('MAGICGALLERY_PATH_LIBRARY')) {
    define('MAGICGALLERY_PATH_LIBRARY', JPATH_LIBRARIES . '/Magicgallery');
}

JLoader::registerNamespace('Magicgallery', JPATH_LIBRARIES);

// Register libraries and helpers
JLoader::register('MagicGalleryHelper', MAGICGALLERY_PATH_COMPONENT_ADMINISTRATOR . '/helpers/magicgallery.php');
JLoader::register('MagicGalleryHelperRoute', MAGICGALLERY_PATH_COMPONENT_SITE . '/helpers/route.php');

// Register some helpers
JHtml::addIncludePath(MAGICGALLERY_PATH_COMPONENT_SITE . '/helpers/html');

// Load library language
$lang = JFactory::getLanguage();
$lang->load('lib_magicgallery', MAGICGALLERY_PATH_LIBRARY);

// Register class aliases.
JLoader::registerAlias('MagicgalleryCategories', '\\Magicgallery\\Category\\Categories');
