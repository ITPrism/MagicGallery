<?php
/**
 * @package      Magic Gallery
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2014 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

/**
 * Script file of Magic Gallery component.
 */
class pkg_magicGalleryInstallerScript
{
    private $imagesFolder = "";
    private $imagesPath = "";

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
        if (!defined("MAGICGALLERY_PATH_COMPONENT_ADMINISTRATOR")) {
            define("MAGICGALLERY_PATH_COMPONENT_ADMINISTRATOR", JPATH_ADMINISTRATOR . "/components/com_magicgallery");
        }

        // Register Install Helper
        JLoader::register("MagicGalleryInstallHelper", MAGICGALLERY_PATH_COMPONENT_ADMINISTRATOR . "/helpers/install.php");

        jimport('joomla.filesystem.path');
        jimport('joomla.filesystem.folder');
        jimport('joomla.filesystem.file');
        jimport('Prism.init');
        jimport('Magicgallery.init');

        $params             = JComponentHelper::getParams("com_magicgallery");
        $this->imagesFolder = JFolder::makeSafe($params->get("media_folder", "images/magicgallery"));
        $this->imagesPath   = JPath::clean(JPATH_SITE . DIRECTORY_SEPARATOR . $this->imagesFolder);

        // Create images folder
        if (!is_dir($this->imagesPath)) {
            MagicGalleryInstallHelper::createFolder($this->imagesPath);
        }

        // Start table with the information
        MagicGalleryInstallHelper::startTable();

        // Requirements
        MagicGalleryInstallHelper::addRowHeading(JText::_("COM_MAGICGALLERY_MINIMUM_REQUIREMENTS"));

        // Display result about verification for existing folder
        $title = JText::_("COM_MAGICGALLERY_IMAGE_FOLDER");
        $info  = $this->imagesFolder;
        if (!is_dir($this->imagesPath)) {
            $result = array("type" => "important", "text" => JText::_("JNO"));
        } else {
            $result = array("type" => "success", "text" => JText::_("JYES"));
        }
        MagicGalleryInstallHelper::addRow($title, $result, $info);

        // Display result about verification for writeable folder
        $title = JText::_("COM_MAGICGALLERY_WRITABLE_FOLDER");
        $info  = $this->imagesFolder;
        if (!is_writable($this->imagesPath)) {
            $result = array("type" => "important", "text" => JText::_("JNO"));
        } else {
            $result = array("type" => "success", "text" => JText::_("JYES"));
        }
        MagicGalleryInstallHelper::addRow($title, $result, $info);

        // Display result about verification for GD library
        $title = JText::_("COM_MAGICGALLERY_GD_LIBRARY");
        $info  = "";
        if (!extension_loaded('gd') and function_exists('gd_info')) {
            $result = array("type" => "important", "text" => JText::_("COM_MAGICGALLERY_WARNING"));
        } else {
            $result = array("type" => "success", "text" => JText::_("JYES"));
        }
        MagicGalleryInstallHelper::addRow($title, $result, $info);

        // Display result about verification for cURL library
        $title = JText::_("COM_MAGICGALLERY_CURL_LIBRARY");
        $info  = "";
        if (!extension_loaded('curl')) {
            $info   = JText::_("COM_MAGICGALLERY_CURL_INFO");
            $result = array("type" => "important", "text" => JText::_("COM_MAGICGALLERY_WARNING"));
        } else {
            $result = array("type" => "success", "text" => JText::_("JYES"));
        }
        MagicGalleryInstallHelper::addRow($title, $result, $info);

        // Display result about verification Magic Quotes
        $title = JText::_("COM_MAGICGALLERY_MAGIC_QUOTES");
        $info  = "";
        if (get_magic_quotes_gpc()) {
            $info   = JText::_("COM_MAGICGALLERY_MAGIC_QUOTES_INFO");
            $result = array("type" => "important", "text" => JText::_("JNO"));
        } else {
            $result = array("type" => "success", "text" => JText::_("JOFF"));
        }
        MagicGalleryInstallHelper::addRow($title, $result, $info);

        // Display result about verification PHP version.
        $title = JText::_("COM_MAGICGALLERY_PHP_VERSION");
        $info  = "";
        if (version_compare(PHP_VERSION, '5.3.0') < 0) {
            $result = array("type" => "important", "text" => JText::_("COM_MAGICGALLERY_WARNING"));
        } else {
            $result = array("type" => "success", "text" => JText::_("JYES"));
        }
        MagicGalleryInstallHelper::addRow($title, $result, $info);

        // Installed extensions
        MagicGalleryInstallHelper::addRowHeading(JText::_("COM_MAGICGALLERY_INSTALLED_EXTENSIONS"));

        // Display result about verification of installed Prism Library
        $title = JText::_("COM_MAGICGALLERY_PRISM_LIBRARY");
        $info  = "";
        if (!class_exists("Prism\\Version")) {
            $info   = JText::_("COM_MAGICGALLERY_PRISM_LIBRARY_DOWNLOAD");
            $result = array("type" => "important", "text" => JText::_("JNO"));
        } else {
            $result = array("type" => "success", "text" => JText::_("JYES"));
        }
        MagicGalleryInstallHelper::addRow($title, $result, $info);

        // End table with the information
        MagicGalleryInstallHelper::endTable();

        echo JText::sprintf("COM_MAGICGALLERY_MESSAGE_REVIEW_SAVE_SETTINGS", JRoute::_("index.php?option=com_magicgallery"));

        if (!class_exists("Prism\\Version")) {
            echo JText::_("COM_MAGICGALLERY_MESSAGE_INSTALL_PRISM_LIBRARY");
        } else {

            if (class_exists("MagicGallery\\Version")) {
                $prismVersion        = new Prism\Version();
                $componentVersion    = new MagicGallery\Version();
                if (version_compare($prismVersion->getShortVersion(), $componentVersion->requiredPrismVersion, "<")) {
                    echo JText::_("COM_MAGICGALLERY_MESSAGE_INSTALL_PRISM_LIBRARY");
                }
            }
        }
    }
}
