<?php
/**
 * @package         Magicgallery
 * @subpackage      Galleries
 * @author          Todor Iliev
 * @copyright       Copyright (C) 2016 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license         http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

namespace Magicgallery\Gallery;

use Magicgallery\Entity\Entities;
use Magicgallery\Entity\Entity;

defined('JPATH_PLATFORM') or die;

/**
 * This class provide functionality for managing Slide Gallery data.
 *
 * @package         Magicgallery
 * @subpackage      Galleries
 */
class SlideGallery extends GalleryAbstract
{
    /**
     * Add script code to the document.
     *
     * <code>
     * $gallery = new Magicgallery\Gallery\SlideGallery($items, $params);
     * $js = $this->gallery
     *            ->setSelector('js-mg-com-slidegallery')
     *            ->prepareScriptDeclaration();
     *
     * $this->document->addScriptDeclaration($js);
     * </code>
     *
     * @throws \InvalidArgumentException
     *
     * @return string
     */
    public function prepareScriptDeclaration()
    {
        \JHtml::_('jquery.framework');
        \JHtml::_('Magicgallery.slidejs');

        $effects = $this->prepareEffects();
        $play    = $this->preparePlay();

        $js = '
jQuery(document).ready(function() {
	jQuery("#' . $this->selector . '").slidesjs({
        start: ' . $this->options->get('start', 1) . ',
        width: ' . $this->options->get('width', 600) . ',
        height: ' . $this->options->get('height', 400) . ',' .
            $effects . $play . '
    });
});';
        return $js;
    }

    /**
     * Generate HTML code displaying thumbnails and images.
     *
     * <code>
     * $gallery = new Magicgallery\Gallery\SlideGallery($items, $options, \JFactory::getDocument());
     * $gallery->setSelector("js-mg-com-galleria");
     *
     * echo $gallery->render();
     * </code>
     *
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
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

                if (!empty($resource->image)) {
                    $html[] = '<img src="' . $this->gallery->getMediaUri() . '/' . $resource->image . '" />';
                }
            }

            $html[] = '<a href="#" class="slidesjs-previous slidesjs-navigation"><i class="fa fa-chevron-left"></i></a>';
            $html[] = '<a href="#" class="slidesjs-next slidesjs-navigation"><i class="fa fa-chevron-right"></i></a>';

            $html[] = '</div>';
        }

        return implode("\n", $html);
    }

    private function prepareEffects()
    {
        $options = '';
        $effect  = $this->options->get('effect', 'fade');
        $speed   = $this->options->get('speed', 200);

        $navigation = $this->options->get('navigation', 0);
        $pagination = $this->options->get('pagination', 1);

        if (strcmp('slide', $effect) === 0) {
            $options = '
            	navigation: {
            		active: ' . $navigation . ',
        			effect: "slide"
    			},
    			pagination: {
            		active: ' . $pagination . ',
        			effect: "slide"
    			},
            	effect: {
                  slide: {
                    speed: ' . (int)$speed . '
                  }
                }
            ';
        } elseif (strcmp('fade', $effect) === 0) {
            $options = '
            	navigation: {
            		active: ' . $navigation . ',
        			effect: "fade"
    			},
    			pagination: {
            		active: ' . $pagination . ',
        			effect: "fade"
    			},
            	effect: {
                  fade: {
                    speed: ' . (int)$speed . ',
                    crossfade: false
                  }
                }
            ';
        } elseif (strcmp('fade-crossfade', $effect) === 0) {
            $options = '
            	navigation: {
            		active: ' . $navigation . ',
        			effect: "fade"
    			},
    			pagination: {
            		active: ' . $pagination . ',
        			effect: "fade"
    			},
            	effect: {
                  fade: {
                    speed: ' . (int)$speed . ',
                    crossfade: true
                  }
                }
            ';
        }

        return $options;
    }

    private function preparePlay()
    {
        $options  = '';
        $play     = (int)$this->options->get('play', 0);
        $effect   = $this->options->get('effect', 'fade');
        $interval = (int)$this->options->get('interval', 5000);
        $autoplay = (int)$this->options->get('autoplay', 0);
        $swap     = (int)$this->options->get('swap', 1);
        $pause    = (int)$this->options->get('pause', 0);
        $restart  = (int)$this->options->get('restart', 2500);

        if ($play > 0) {
            $options = ',
            	play: {
                  active: true,
                  effect: "' . $effect . '",
                  interval: ' . $interval . ',
                  auto: ' . $autoplay . ',
                  swap: ' . $swap . ',
                  pauseOnHover: ' . $pause . ',
                  restartDelay: ' . $restart . '
                }
            ';
        }

        return $options;
    }
}
