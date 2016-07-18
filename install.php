<?php
/**
 * @package      Magic Gallery
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2016 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

/**
 * Script file of Magic Gallery component.
 */
class pkg_magicgalleryInstallerScript
{
    /**
     * Method to install the component.
     *
     * @param $parent
     *
     * @return void
     */
    public function install($parent)
    {
    }

    /**
     * Method to uninstall the component.
     *
     * @param $parent
     *
     * @return void
     */
    public function uninstall($parent)
    {
    }

    /**
     * Method to update the component.
     *
     * @param $parent
     * @return void
     */
    public function update($parent)
    {
    }

    /**
     * Method to run before an install/update/uninstall method.
     *
     * @param $type
     * @param $parent
     *
     * @return void
     */
    public function preflight($type, $parent)
    {
    }

    /**
     * Method to run after an install/update/uninstall method.
     *
     * @param $type
     * @param $parent
     *
     * @return void
     */
    public function postflight($type, $parent)
    {
        if (!defined('MAGICGALLERY_PATH_COMPONENT_ADMINISTRATOR')) {
            define('MAGICGALLERY_PATH_COMPONENT_ADMINISTRATOR', JPATH_ADMINISTRATOR . '/components/com_magicgallery');
        }

        // Register Install Helper
        JLoader::register('MagicgalleryInstallHelper', MAGICGALLERY_PATH_COMPONENT_ADMINISTRATOR . '/helpers/install.php');

        jimport('Prism.init');
        jimport('Magicgallery.init');

        $params       = JComponentHelper::getParams('com_magicgallery');
        $imagesFolder = JFolder::makeSafe($params->get('media_folder', 'images/magicgallery'));
        $imagesPath   = JPath::clean(JPATH_SITE . DIRECTORY_SEPARATOR . $imagesFolder);

        // Create images folder
        if (!is_dir($imagesPath)) {
            MagicgalleryInstallHelper::createFolder($imagesPath);
        }

        // Start table with the information
        MagicgalleryInstallHelper::startTable();

        // Requirements
        MagicgalleryInstallHelper::addRowHeading(JText::_('COM_MAGICGALLERY_MINIMUM_REQUIREMENTS'));

        // Display result about verification for existing folder
        $title = JText::_('COM_MAGICGALLERY_IMAGE_FOLDER');
        $info  = $imagesFolder;
        if (!is_dir($imagesPath)) {
            $result = array('type' => 'important', 'text' => JText::_('JNO'));
        } else {
            $result = array('type' => 'success', 'text' => JText::_('JYES'));
        }
        MagicgalleryInstallHelper::addRow($title, $result, $info);

        // Display result about verification for writeable folder
        $title = JText::_('COM_MAGICGALLERY_WRITABLE_FOLDER');
        $info  = $imagesFolder;
        if (!is_writable($imagesPath)) {
            $result = array('type' => 'important', 'text' => JText::_('JNO'));
        } else {
            $result = array('type' => 'success', 'text' => JText::_('JYES'));
        }
        MagicgalleryInstallHelper::addRow($title, $result, $info);

        // Display result about verification for GD library
        $title = JText::_('COM_MAGICGALLERY_GD_LIBRARY');
        $info  = '';
        if (!extension_loaded('gd') and function_exists('gd_info')) {
            $result = array('type' => 'important', 'text' => JText::_('COM_MAGICGALLERY_WARNING'));
        } else {
            $result = array('type' => 'success', 'text' => JText::_('JYES'));
        }
        MagicgalleryInstallHelper::addRow($title, $result, $info);

        // Display result about verification for cURL library
        $title = JText::_('COM_MAGICGALLERY_CURL_LIBRARY');
        $info  = '';
        if (!extension_loaded('curl')) {
            $info   = JText::_('COM_MAGICGALLERY_CURL_INFO');
            $result = array('type' => 'important', 'text' => JText::_('COM_MAGICGALLERY_WARNING'));
        } else {
            $result = array('type' => 'success', 'text' => JText::_('JYES'));
        }
        MagicgalleryInstallHelper::addRow($title, $result, $info);

        // Display result about verification Magic Quotes
        $title = JText::_('COM_MAGICGALLERY_MAGIC_QUOTES');
        $info  = '';
        if (get_magic_quotes_gpc()) {
            $info   = JText::_('COM_MAGICGALLERY_MAGIC_QUOTES_INFO');
            $result = array('type' => 'important', 'text' => JText::_('JNO'));
        } else {
            $result = array('type' => 'success', 'text' => JText::_('JOFF'));
        }
        MagicgalleryInstallHelper::addRow($title, $result, $info);

        // Display result about verification PHP version.
        $title = JText::_('COM_MAGICGALLERY_PHP_VERSION');
        $info  = '';
        if (version_compare(PHP_VERSION, '5.5.19') < 0) {
            $result = array('type' => 'important', 'text' => JText::_('COM_MAGICGALLERY_WARNING'));
        } else {
            $result = array('type' => 'success', 'text' => JText::_('JYES'));
        }
        MagicgalleryInstallHelper::addRow($title, $result, $info);

        // Display result about MySQL Version.
        $title = JText::_('COM_MAGICGALLERY_MYSQL_VERSION');
        $info  = '';
        $dbVersion = JFactory::getDbo()->getVersion();
        if (version_compare($dbVersion, '5.5.3', '<')) {
            $result = array('type' => 'important', 'text' => JText::_('COM_MAGICGALLERY_WARNING'));
        } else {
            $result = array('type' => 'success', 'text' => JText::_('JYES'));
        }
        MagicgalleryInstallHelper::addRow($title, $result, $info);
        
        // Installed extensions
        MagicgalleryInstallHelper::addRowHeading(JText::_('COM_MAGICGALLERY_INSTALLED_EXTENSIONS'));

        // Display result about verification of installed Prism Library
        $info  = '';
        if (!class_exists('Prism\\Version')) {
            $title  = JText::_('COM_MAGICGALLERY_PRISM_LIBRARY');
            $info   = JText::_('COM_MAGICGALLERY_PRISM_LIBRARY_DOWNLOAD');
            $result = array('type' => 'important', 'text' => JText::_('JNO'));
        } else {
            $prismVersion   = new Prism\Version();
            $text           = JText::sprintf('COM_MAGICGALLERY_CURRENT_V_S', $prismVersion->getShortVersion());

            if (class_exists('Magicgallery\\Version')) {
                $componentVersion = new Magicgallery\Version();
                $title            = JText::sprintf('COM_MAGICGALLERY_PRISM_LIBRARY_S', $componentVersion->requiredPrismVersion);

                if (version_compare($prismVersion->getShortVersion(), $componentVersion->requiredPrismVersion, '<')) {
                    $info   = JText::_('COM_MAGICGALLERY_PRISM_LIBRARY_DOWNLOAD');
                    $result = array('type' => 'warning', 'text' => $text);
                }

            } else {
                $title  = JText::_('COM_MAGICGALLERY_PRISM_LIBRARY');
                $result = array('type' => 'success', 'text' => $text);
            }
        }
        MagicgalleryInstallHelper::addRow($title, $result, $info);

        // End table with the information
        MagicgalleryInstallHelper::endTable();

        echo JText::sprintf('COM_MAGICGALLERY_MESSAGE_REVIEW_SAVE_SETTINGS', JRoute::_('index.php?option=com_magicgallery'));

        if (!class_exists('Prism\\Version')) {
            echo JText::_('COM_MAGICGALLERY_MESSAGE_INSTALL_PRISM_LIBRARY');
        } else {
            if (class_exists('Magicgallery\\Version')) {
                $prismVersion        = new Prism\Version();
                $componentVersion    = new Magicgallery\Version();
                if (version_compare($prismVersion->getShortVersion(), $componentVersion->requiredPrismVersion, '<')) {
                    echo JText::_('COM_MAGICGALLERY_MESSAGE_INSTALL_PRISM_LIBRARY');
                }
            }
        }
    }
}
