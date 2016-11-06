<?php
/**
 * @package         Magicgallery
 * @subpackage      Galleries
 * @author          Todor Iliev
 * @copyright       Copyright (C) 2016 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license         http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

namespace Magicgallery\Gallery;

use Joomla\Registry\Registry;
use Magicgallery\Entity\Entities;
use Magicgallery\Entity\Entity;

defined('JPATH_PLATFORM') or die;

/**
 * This class provide functionality for managing Gallery.
 *
 * @package         Magicgallery
 * @subpackage      Galleries
 */
class Galleria extends GalleryAbstract
{
    /**
     * Add script code to the document.
     *
     * <code>
     * $resource = new Magicgallery\Gallery\Galleria($items, $params);
     * $js = $this->gallery
     *            ->setSelector('js-mg-com-galleria')
     *            ->prepareScriptDeclaration();
     *
     * $this->document->addScriptDeclaration($js);
     * </code>
     *
     * @throws \InvalidArgumentException
     * @return string
     */
    public function prepareScriptDeclaration()
    {
        \JHtml::_('jquery.framework');
        \JHtml::_('Magicgallery.galleria');

        $js = '
        jQuery(document).ready(function() {
            Galleria.run("#' . $this->selector . '", {
                autoplay: '.$this->options->get('autoplay', 0).',
                carousel: '.$this->options->get('carousel', 1).',
                carouselSpeed: '.$this->options->get('carousel_speed', 200).',
                carouselSteps: '.$this->options->get('carousel_steps', 2).',
                responsive: '.$this->options->get('responsive', 1).',
                height: '.$this->options->get('height', 1).',
            });
        });';

        return $js;
    }

    /**
     * Generate HTML code displaying thumbnails and images.
     *
     * <code>
     * $resource = new Magicgallery\Gallery\Galleria($items, $options, \JFactory::getDocument());
     * $resource->setSelector("js-mg-com-galleria");
     * echo $resource->render();
     * </code>
     *
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     *
     * @return string
     */
    public function render()
    {
        $html = array();

        if ($this->gallery !== null) {
            $html[] = '<div id="' . $this->selector . '">';

            $resources = $this->gallery->getEntities();
            foreach ($resources as $resource) {
                if ($resource === null or !$resource->id) {
                    continue;
                }

                if (!empty($resource->image) and !empty($resource->thumbnail)) {
                    if (empty($resource->image_meta)) {
                        $resource->image_meta = '{}';
                    }

                    $meta   = new Registry($resource->image_meta);
                    $width  = $meta->get('width', 200);
                    $height = $meta->get('height', 200);

                    $html[] = '<a href="' . $this->gallery->getMediaUri() . '/' . $resource->image . '"><img src="' . $this->gallery->getMediaUri() . '/' . $resource->thumbnail . '" width="'.$width.'" height="'.$height.'" /></a>';
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
     * $resource = new Magicgallery\Gallery\Galleria($items, $options, \JFactory::getDocument());
     * $resource->setSelector("js-mg-com-galleria");
     *
     * echo $resource->renderOnlyImages();
     * </code>
     *
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     *
     * @return string
     */
    public function renderOnlyImages()
    {
        $html = array();

        if ($this->gallery !== null) {
            $html[] = '<div id="' . $this->selector . '">';

            $resources = $this->gallery->getEntities();
            foreach ($resources as $resource) {
                if ($resource === null or !$resource->id) {
                    continue;
                }

                if ($resource->image !== null and $resource->image !== '') {
                    if (empty($resource->image_meta)) {
                        $resource->image_meta = '{}';
                    }

                    $meta   = new Registry($resource->image_meta);
                    $width  = $meta->get('width', 200);
                    $height = $meta->get('height', 200);

                    $html[] = '<img src="' . $this->gallery->getMediaUri() .'/'. $resource->image . '" width="'.$width.'" height="'.$height.'" />';
                }
            }

            $html[] = '</div>';
        }

        return implode("\n", $html);
    }
}
