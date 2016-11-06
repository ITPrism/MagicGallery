<?php
/**
 * @package      Magicgallery
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2016 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

use Magicgallery\Gallery\Gallery;

// no direct access
defined('_JEXEC') or die;

/**
 * It is Magic Gallery helper class
 */
class MagicgalleryHelper
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
            $vName === 'dashboard'
        );

        JHtmlSidebar::addEntry(
            JText::_('COM_MAGICGALLERY_CATEGORIES'),
            'index.php?option=com_categories&extension=' . self::$extension,
            $vName === 'categories'
        );

        JHtmlSidebar::addEntry(
            JText::_('COM_MAGICGALLERY_GALLERIES'),
            'index.php?option=' . self::$extension . '&view=galleries',
            $vName === 'galleries'
        );

        JHtmlSidebar::addEntry(
            JText::_('COM_MAGICGALLERY_RESOURCES'),
            'index.php?option=' . self::$extension . '&view=entities',
            $vName === 'entities'
        );
    }

    /**
     * Prepare an image that will be used for meta data.
     *
     * @param array $galleries
     *
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     *
     * @return null|string
     */
    public static function getIntroImageFromGalleries($galleries)
    {
        $imageUrl = null;

        /** @var Gallery $gallery */
        $gallery   = reset($galleries);

        $resource  = $gallery->getDefaultEntity();
        if ($resource !== null) {
            if ($resource->getThumbnail()) {
                $imageUrl = $gallery->getMediaUri() . '/' . $resource->getThumbnail();
            } else {
                $imageUrl = $gallery->getMediaUri() . '/' . $resource->getImage();
            }
        }

        return $imageUrl;
    }

    /**
     * Prepare an image that will be used for meta data.
     *
     * @param Magicgallery\Gallery\Gallery $gallery
     *
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     *
     * @return null|string
     */
    public static function getIntroImage($gallery)
    {
        $imageUrl = null;

        $resource = $gallery->getDefaultEntity();
        if ($resource !== null) {
            if ($resource->getThumbnail()) {
                $imageUrl = $gallery->getMediaUri() . '/' . $resource->getThumbnail();
            } else {
                $imageUrl = $gallery->getMediaUri() . '/' . $resource->getImage();
            }
        }

        return $imageUrl;
    }

    public static function getModalClass($modal)
    {
        switch ($modal) {
            case 'nivo':
                $class = 'js-com-nivo-modal';
                break;

            case 'fancybox':
                $class = 'js-com-fancybox-modal';
                break;

            case 'magnific':
                $class = 'js-com-magnific-modal';
                break;
            case 'swipebox':
                $class = 'js-com-swipebox-modal';
                break;

            default:
                $class = '';
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
                if (0 !== strpos($category->image, 'http')) {
                    $result = $uri->toString(array('scheme', 'host')) . '/' . $category->image;
                } else {
                    $result = $uri->toString(array('scheme', 'host')) . '/'. $category->image;
                }
            }
        }

        return $result;
    }
}
