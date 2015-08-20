<?php
/**
 * @package      MagicGallery
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2014 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

/**
 * It is Magic Gallery helper class
 */
class MagicGalleryHelper
{
    public static $extension = 'com_magicgallery';

    /**
     * Configure the Linkbar.
     *
     * @param    string  $vName  The name of the active view.
     *
     * @since    1.6
     */
    public static function addSubmenu($vName = 'dashboard')
    {
        JHtmlSidebar::addEntry(
            JText::_('COM_MAGICGALLERY_DASHBOARD'),
            'index.php?option=' . self::$extension . '&view=dashboard',
            $vName == 'dashboard'
        );

        JHtmlSidebar::addEntry(
            JText::_('COM_MAGICGALLERY_CATEGORIES'),
            'index.php?option=com_categories&extension=' . self::$extension,
            $vName == 'categories'
        );

        JHtmlSidebar::addEntry(
            JText::_('COM_MAGICGALLERY_GALLERIES'),
            'index.php?option=' . self::$extension . '&view=galleries',
            $vName == 'projects'
        );

    }

    /**
     * Prepare an image that will be used for meta data.
     *
     * @param object $category
     * @param MagicGallery\Resource\Resources  $resources
     * @param string  $mediaFolder
     *
     * @return NULL|string
     */
    public static function getIntroImage($category, $resources, $mediaFolder)
    {
        $uri = JUri::getInstance();

        $image = null;
        if (!$category->getImage() and 0 < count($resources)) {

            $image = reset($resources);
            
            if (!empty($image)) {

                if ($image->getThumbnail()) {
                    $image = $mediaFolder . "/" . $image->getThumbnail();
                } else {
                    $image = $mediaFolder . "/" . $image->getImage();
                }
            }

        } else {

            if ($category->getImage() and (0 !== strpos($category->getImage(), "http"))) {
                $image = $uri->toString(array("scheme", "host")) . "/" . $category->getImage();
            } else {
                $image = $category->getImage();
            }
        }

        return $image;
    }

    public static function getModalClass($modal)
    {
        switch ($modal) {

            case "nivo":
                $class = "js-com-nivo-modal";
                break;

            case "fancybox":
                $class = "js-com-fancybox-modal";
                break;

            case "magnific":
                $class = "js-com-magnific-modal";
                break;
            default:
                $class = "";
                break;
        }

        return $class;
    }

    /**
     * Get first found picture from a list with categories.
     *
     * @param array $categories
     *
     * @return null|string
     */
    public static function getCategoryImage($categories)
    {
        $result = null;

        $uri = JUri::getInstance();

        foreach ($categories as $category) {
            if (!empty($category->image)) {
                if (0 !== strpos($category->image, "http")) {
                    $result = $uri->toString(array("scheme", "host")) . "/" . $category->image;
                } else {
                    $result = $uri->toString(array("scheme", "host")) . "/". $category->image;
                }
            }
        }

        return $result;
    }
}
