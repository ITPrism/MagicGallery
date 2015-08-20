<?php
/**
 * @package      MagicGallery
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

/**
 * Magic Gallery Html Helper
 *
 * @package        MagicGallery
 * @subpackage     Components
 * @since          1.6
 */
abstract class JHtmlMagicGallery
{
    protected static $extension = "com_magicgallery";

    /**
     * @var   array   array containing information for loaded files
     */
    protected static $loaded = array();

    /**
     * Include jQuery Nivo Light Box library.
     *
     * <code>
     * JHtml::addIncludePath(MAGICGALLERY_PATH_COMPONENT_SITE . '/helpers/html');
     *
     * JHtml::_('MagicGallery.lightboxNivo');
     * </code>
     *
     * @link http://docs.dev7studios.com/jquery-plugins/nivo-lightbox
     */
    public static function lightboxNivo()
    {
        // Only load once
        if (!empty(self::$loaded[__METHOD__])) {
            return;
        }

        $document = JFactory::getDocument();

        $document->addStylesheet(JUri::root() . 'media/' . self::$extension . '/js/nivo/nivo-lightbox.css');
        $document->addStylesheet(JUri::root() . 'media/' . self::$extension . '/js/nivo/themes/default/default.css');
        $document->addScript(JUri::root() . 'media/' . self::$extension . '/js/nivo/nivo-lightbox.min.js');

        self::$loaded[__METHOD__] = true;
    }

    /**
     * Include jQuery Duncan's Light Box library.
     *
     * <code>
     * JHtml::addIncludePath(MAGICGALLERY_PATH_COMPONENT_SITE . '/helpers/html');
     *
     * JHtml::_('MagicGallery.lightboxDuncan');
     * </code>
     *
     * @link https://github.com/duncanmcdougall/Responsive-Lightbox
     */
    public static function lightboxDuncan()
    {
        // Only load once
        if (!empty(self::$loaded[__METHOD__])) {
            return;
        }

        $document = JFactory::getDocument();

        $document->addStylesheet(JUri::root() . 'media/' . self::$extension . '/js/duncan/jquery.lightbox.min.css');
        $document->addScript(JUri::root() . 'media/' . self::$extension . '/js/duncan/jquery.lightbox.min.js');

        self::$loaded[__METHOD__] = true;
    }

    /**
     * Include jQuery FancyBox library.
     *
     * <code>
     * JHtml::addIncludePath(PRISM_PATH_LIBRARY .'/ui/helpers');
     *
     * JHtml::_('MagicGallery.ui.lightboxFancyBox');
     * </code>
     *
     * @link http://fancyapps.com/fancybox/ FancyBox documentation
     */
    public static function lightboxFancyBox()
    {
        // Only load once
        if (!empty(self::$loaded[__METHOD__])) {
            return;
        }

        $document = JFactory::getDocument();

        $document->addScript(JUri::root() . 'media/' . self::$extension . '/js/fancybox/jquery.fancybox.pack.js');
        $document->addStyleSheet(JUri::root() . 'media/' . self::$extension . '/js/fancybox/jquery.fancybox.css');

        self::$loaded[__METHOD__] = true;
    }

    /**
     * Include jQuery Magnific library.
     *
     * <code>
     * JHtml::addIncludePath(PRISM_PATH_LIBRARY .'/ui/helpers');
     *
     * JHtml::_('MagicGallery.ui.lightboxMagnific');
     * </code>
     *
     * @link http://dimsemenov.com/plugins/magnific-popup/ Magnific documentation
     */
    public static function lightboxMagnific()
    {
        // Only load once
        if (!empty(self::$loaded[__METHOD__])) {
            return;
        }

        $document = JFactory::getDocument();

        $document->addScript(JUri::root() . 'media/' . self::$extension . '/js/magnific/jquery.magnific-popup.min.js');
        $document->addStyleSheet(JUri::root() . 'media/' . self::$extension . '/js/magnific/magnific-popup.css');

        self::$loaded[__METHOD__] = true;
    }
    
    /**
     * Include jQuery Galleria library.
     *
     * <code>
     * JHtml::addIncludePath(MAGICGALLERY_PATH_COMPONENT_SITE . '/helpers/html');
     *
     * JHtml::_('MagicGallery.galleria');
     * </code>
     *
     * @link https://github.com/duncanmcdougall/Responsive-Lightbox
     */
    public static function galleria()
    {
        // Only load once
        if (!empty(self::$loaded[__METHOD__])) {
            return;
        }

        $document = JFactory::getDocument();

        $document->addStyleSheet('media/' . self::$extension . '/js/galleria/themes/classic/galleria.classic.css');

        $document->addScript('media/' . self::$extension . '/js/galleria/galleria.min.js');
        $document->addScript('media/' . self::$extension . '/js/galleria/themes/classic/galleria.classic.min.js');

        self::$loaded[__METHOD__] = true;
    }

    /**
     * Include jQuery Camera library.
     *
     * <code>
     * JHtml::addIncludePath(MAGICGALLERY_PATH_COMPONENT_SITE . '/helpers/html');
     *
     * JHtml::_('MagicGallery.camera');
     * </code>
     *
     * @link http://www.pixedelic.com/plugins/camera/
     */
    public static function camera()
    {
        // Only load once
        if (!empty(self::$loaded[__METHOD__])) {
            return;
        }

        $document = JFactory::getDocument();

        $document->addStyleSheet('media/' . self::$extension . '/js/camera/css/camera.css');
        $document->addScript('media/' . self::$extension . '/js/camera/camera.min.js');
        $document->addScript('media/' . self::$extension . '/js/camera/jquery.easing.1.3.js');

        self::$loaded[__METHOD__] = true;
    }

    /**
     * Include jQuery SlideJS library.
     *
     * <code>
     * JHtml::addIncludePath(MAGICGALLERY_PATH_COMPONENT_SITE . '/helpers/html');
     *
     * JHtml::_('MagicGallery.slidejs');
     * </code>
     *
     * @link http://slidesjs.com/
     */
    public static function slidejs()
    {
        // Only load once
        if (!empty(self::$loaded[__METHOD__])) {
            return;
        }

        $document = JFactory::getDocument();

        $document->addStyleSheet('media/' . self::$extension . '/js/slidesjs/font-awesome.min.css');
        $document->addStyleSheet('media/' . self::$extension . '/js/slidesjs/jquery.slides.css');
        $document->addScript('media/' . self::$extension . '/js/slidesjs/jquery.slides.min.js');

        self::$loaded[__METHOD__] = true;
    }

    /**
     * Display the file size.
     *
     * <code>
     * JHtml::addIncludePath(MAGICGALLERY_PATH_COMPONENT_SITE . '/helpers/html');
     *
     * JHtml::_('MagicGallery.fileSize', $params);
     * </code>
     *
     * @param array $params
     *
     * @return string
     */
    public static function fileSize($params)
    {
        $result = "";
        $value  = (!empty($params["filesize"])) ? abs($params["filesize"]) : 0;

        if ($value > 0) {
            $value = $value / 1000;

            if ($value > 1000) {
                $value = $value / 1000;

                $result  = '<div class="small">';
                $result .= JText::sprintf("COM_MAGICGALLERY_FILESIZE_MB_D", $value);
                $result .= '</div>';
            } else {
                $result  = '<div class="small">';
                $result .= JText::sprintf("COM_MAGICGALLERY_FILESIZE_KB_D", $value);
                $result .= '</div>';
            }
        }

        return $result;
    }

    /**
     * Display the image size.
     *
     * <code>
     * JHtml::addIncludePath(MAGICGALLERY_PATH_COMPONENT_SITE . '/helpers/html');
     *
     * JHtml::_('MagicGallery.imageSize', $params);
     * </code>
     *
     * @param array $params
     *
     * @return string
     */
    public static function imageSize($params)
    {
        $result = "";

        $width   = (!empty($params["width"])) ? abs($params["width"]) : 0;
        $height  = (!empty($params["height"])) ? abs($params["height"]) : 0;

        if (!empty($width) and !empty($height)) {
            $result  = '<div class="small">';
            $result .= JText::sprintf("COM_MAGICGALLERY_IMAGE_SIZE_S", $width ."x". $height);
            $result .= '</div>';
        }

        return $result;
    }

    /**
     * Display the image mime type.
     *
     * <code>
     * JHtml::addIncludePath(MAGICGALLERY_PATH_COMPONENT_SITE . '/helpers/html');
     *
     * JHtml::_('MagicGallery.mimeType', $params);
     * </code>
     *
     * @param array $params
     *
     * @return string
     */
    public static function mimeType($params)
    {
        $result = "";

        if (!empty($params["mime_type"])) {
            $result  = '<i class="icon-info btn btn-mini hasTooltip" title="'.JText::sprintf("COM_MAGICGALLERY_MIMETYPE_S", htmlentities($params["mime_type"], ENT_QUOTES, "UTF-8")) .'"></i>';
        }

        return $result;
    }

    /**
     * Display information about the file.
     *
     * <code>
     * JHtml::addIncludePath(MAGICGALLERY_PATH_COMPONENT_SITE . '/helpers/html');
     *
     * JHtml::_('MagicGallery.fileInfo', $params);
     * </code>
     *
     * @param array $params
     *
     * @return string
     */
    public static function fileInfo($params)
    {
        $result = "";
        $title = array();

        // Image mime type.
        if (!empty($params["mime_type"])) {
            $title[] = JText::sprintf("COM_MAGICGALLERY_MIMETYPE_S", htmlentities($params["mime_type"], ENT_QUOTES, "UTF-8"));
        }

        // Image size.
        $width   = (!empty($params["width"])) ? abs($params["width"]) : 0;
        $height  = (!empty($params["height"])) ? abs($params["height"]) : 0;

        if (!empty($width) and !empty($height)) {
            $title[] = JText::sprintf("COM_MAGICGALLERY_IMAGE_SIZE_S", $width ."x". $height);
        }

        // Filesize
        $value  = (!empty($params["filesize"])) ? abs($params["filesize"]) : 0;

        if ($value > 0) {
            $value = $value / 1000;

            if ($value > 1000) {
                $value = $value / 1000;

                $title[] = JText::sprintf("COM_MAGICGALLERY_FILESIZE_MB_D", $value);
            } else {
                $title[]= JText::sprintf("COM_MAGICGALLERY_FILESIZE_KB_D", $value);
            }
        }

        if (!empty($title)) {
            $result = ' <i class="icon-info btn btn-mini hasTooltip" title="'.implode('<br />', $title).'"></i>';
        }

        return $result;
    }
}
