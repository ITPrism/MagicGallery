<?php
/**
 * @package         MagicGallery
 * @subpackage      SlideGallery
 * @author          Todor Iliev
 * @copyright       Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license         http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

namespace MagicGallery\Gallery;

use Joomla\Registry\Registry;

defined('JPATH_PLATFORM') or die;

/**
 * This class provide functionality for managing Slide Gallery data.
 *
 * @package         MagicGallery
 * @subpackage      Galleries
 */
abstract class GalleryAbstract
{
    protected static $loaded = false;

    protected $items;
    protected $mediaPath;
    protected $selector;
    protected $document;

    /**
     * The gallery options.
     *
     * @var Registry
     */
    protected $options;

    /**
     * Initialize the object.
     *
     * <code>
     * $gallery = new MagicGallery\SlideGallery($items, $params);
     * </code>
     *
     * @param Galleries $items
     * @param Registry $options
     * @param \JDocument $document
     */
    public function __construct(Galleries $items, $options = null, \JDocument $document = null)
    {
        $this->items    = $items;
        $this->options  = ($options instanceof Registry) ? $options : new Registry;
        $this->document = $document;
    }

    /**
     * Set the element selector.
     *
     * <code>
     * $gallery = new MagicGallery\SlideGallery($items, $params);
     * $gallery->setSelector("#js-selector");
     * </code>
     *
     * @param string  $selector
     *
     * @return self
     */
    public function setSelector($selector)
    {
        $this->selector = $selector;

        return $this;
    }

    /**
     * Set a path to the pictures.
     *
     * <code>
     * $mediaPath = "../.../..../";
     *
     * $gallery = new MagicGallery\SlideGallery($items, $params);
     * $gallery->setImagesPath($mediaPath);
     * </code>
     *
     * @param string  $mediaPath
     *
     * @return self
     */
    public function setMediaPath($mediaPath)
    {
        $this->mediaPath = $mediaPath;

        return $this;
    }

    /**
     * Add script code to the document.
     *
     * <code>
     * $gallery = new MagicGallery\SlideGallery($items, $params, /JFactory::getDocument());
     * $gallery->addScriptDeclaration();
     * </code>
     *
     * @return self
     */
    abstract public function addScriptDeclaration();

    /**
     * Generate HTML code displaying thumbnails and images.
     *
     * <code>
     * $gallery = new MagicGallery\SlideGallery($items, $options, /JFactory::getDocument());
     * $gallery->setSelector("#vp-com-galleria");
     * $gallery->render();
     * </code>
     *
     * @return string
     */
    abstract public function render();
}
