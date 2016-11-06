<?php
/**
 * @package      Magicgallery
 * @subpackage   Initializator
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2016 Todor Iliev <todor@itprism.com>. All rights reserved.
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
JLoader::register('MagicgalleryHelper', MAGICGALLERY_PATH_COMPONENT_ADMINISTRATOR . '/helpers/magicgallery.php');
JLoader::register('MagicgalleryHelperRoute', MAGICGALLERY_PATH_COMPONENT_SITE . '/helpers/route.php');

// Register some helpers
JHtml::addIncludePath(MAGICGALLERY_PATH_COMPONENT_SITE . '/helpers/html');

// Load library language
$lang = JFactory::getLanguage();
$lang->load('lib_magicgallery', MAGICGALLERY_PATH_COMPONENT_SITE);

// Register class aliases.
JLoader::registerAlias('MagicgalleryCategories', '\\Magicgallery\\Category\\Categories');

JLog::addLogger(
    array(
        // Sets file name
        'text_file' => 'com_magicgallery.errors.php'
    ),
    // Sets messages of all log levels to be sent to the file
    JLog::CRITICAL + JLog::EMERGENCY + JLog::ALERT + JLog::ERROR + JLog::WARNING,
    // The log category/categories which should be recorded in this file
    // In this case, it's just the one category from our extension, still
    // we need to put it inside an array
    array('com_magicgallery')
);
