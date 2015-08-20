<?php
/**
 * @package         MagicGallery
 * @subpackage      Galleries
 * @author          Todor Iliev
 * @copyright       Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license         http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

namespace MagicGallery\Gallery;

defined('JPATH_PLATFORM') or die;

/**
 * This class provide functionality for managing Gallery.
 *
 * @package         MagicGallery
 * @subpackage      Galleries
 */
class Galleria extends GalleryAbstract
{
    /**
     * Add script code to the document.
     *
     * <code>
     * $gallery = new MagicGallery\Gallery\Galleria($items, $params, \JFactory::getDocument());
     * $gallery->addScriptDeclaration($document);
     * </code>
     *
     * @return self
     */
    public function addScriptDeclaration()
    {
        \JHtml::_('jquery.framework');
        \JHtml::_('MagicGallery.galleria');

        $js = '
        jQuery(document).ready(function() {
            Galleria.run("#' . $this->selector . '", {
                autoplay: '.$this->options->get("autoplay", 0).',
                carousel: '.$this->options->get("carousel", 1).',
                carouselSpeed: '.$this->options->get("carousel_speed", 200).',
                carouselSteps: '.$this->options->get("carousel_steps", 2).',
                responsive: '.$this->options->get("responsive", 1).',
                height: '.$this->options->get("height", 1).',
            });
        });';

        $this->document->addScriptDeclaration($js);

        return $this;
    }

    /**
     * Generate HTML code displaying thumbnails and images.
     *
     * <code>
     * $gallery = new MagicGallery\Gallery\Galleria($items, $options, \JFactory::getDocument());
     * $gallery->setSelector("js-mg-com-galleria");
     * echo $gallery->render();
     * </code>
     *
     * @return string
     */
    public function render()
    {
        $html = array();

        if (!empty($this->items)) {

            $html[] = '<div id="' . $this->selector . '">';

            /** @var Gallery $item */
            foreach ($this->items as $item) {

                if (!$item->getId()) {
                    continue;
                }

                $media = $item->getDefaultResource($item->getId());
                /** @var Resource $media */

                if (!empty($media)) {
                    $html[] = '<a href="' . $this->mediaPath . "/" . $media->getImage() . '"><img src="' . $this->mediaPath . "/" . $media->getThumbnail() . '" width="200" height="200" /></a>';
                }
            }

            $html[] = '</div>';
        }

        return implode("\n", $html);
    }

    /**
     * Generate HTML code displaying only images.
     *
     * <code>
     * $gallery = new MagicGallery\Gallery\Galleria($items, $options, \JFactory::getDocument());
     * $gallery->setSelector("js-mg-com-galleria");
     *
     * echo $gallery->renderOnlyImages();
     * </code>
     *
     * @return string
     */
    public function renderOnlyImages()
    {
        $html = array();

        if (!empty($this->items)) {

            $html[] = '<div id="' . $this->selector . '">';

            foreach ($this->items as $item) {

                if (!$item->getId()) {
                    continue;
                }

                $media = $item->getDefaultResource($item->getId());

                $html[] = '<img src="' . $this->mediaPath . "/". $media->getImage() . '" />';
            }

            $html[] = '</div>';
        }

        return implode("\n", $html);
    }
}
