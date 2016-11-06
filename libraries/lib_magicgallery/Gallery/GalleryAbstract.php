<?php
/**
 * @package         Magicgallery
 * @subpackage      SlideGallery
 * @author          Todor Iliev
 * @copyright       Copyright (C) 2016 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license         http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

namespace Magicgallery\Gallery;

use Joomla\Registry\Registry;

defined('JPATH_PLATFORM') or die;

/**
 * This class provide functionality for managing Slide Gallery data.
 *
 * @package         Magicgallery
 * @subpackage      Galleries
 */
abstract class GalleryAbstract
{
    protected static $loaded = false;

    protected $gallery;
    protected $selector;

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
     * $gallery = new Magicgallery\Gallery\SlideGallery($item, $params);
     * </code>
     *
     * @param Gallery $gallery
     * @param Registry $options
     */
    public function __construct(Gallery $gallery, Registry $options)
    {
        $this->gallery  = $gallery;
        $this->options  = $options;
    }

    /**
     * Set the element selector.
     *
     * <code>
     * $gallery = new Magicgallery\Gallery\SlideGallery($item, $params);
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
     * Add script code to the document.
     *
     * <code>
     * $gallery = new Magicgallery\Gallery\SlideGallery($items, $options);
     * $js = $gallery->prepareScriptDeclaration();
     *
     * $document->addScriptDeclaration($js);
     * </code>
     *
     * @return self
     */
    abstract public function prepareScriptDeclaration();

    /**
     * Generate HTML code displaying thumbnails and images.
     *
     * <code>
     * $gallery = new Magicgallery\Gallery\SlideGallery($item, $options);
     * $gallery->setSelector("#vp-com-galleria");
     * $gallery->render();
     * </code>
     *
     * @return string
     */
    abstract public function render();
}
