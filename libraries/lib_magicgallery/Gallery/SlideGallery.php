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
 * This class provide functionality for managing Slide Gallery data.
 *
 * @package         MagicGallery
 * @subpackage      Galleries
 */
class SlideGallery extends GalleryAbstract
{
    /**
     * Add script code to the document.
     *
     * <code>
     * $gallery = new MagicGallery\Gallery\SlideGallery($items, $params, \JFactory::getDocument());
     * $gallery->addScriptDeclaration();
     * </code>
     *
     * @return self
     */
    public function addScriptDeclaration()
    {
        \JHtml::_('jquery.framework');
        \JHtml::_('MagicGallery.slidejs');

        $effects = $this->prepareEffects();
        $play    = $this->preparePlay();

        $js = '
jQuery(document).ready(function() {
	jQuery("#' . $this->selector . '").slidesjs({
        start: ' . $this->options->get("start", 1) . ',
        width: ' . $this->options->get("width", 600) . ',
        height: ' . $this->options->get("height", 400) . ',' .
            $effects . $play . '
    });
});';
        $this->document->addScriptDeclaration($js);

        return $this;
    }

    /**
     * Generate HTML code displaying thumbnails and images.
     *
     * <code>
     * $gallery = new MagicGallery\Gallery\SlideGallery($items, $options, \JFactory::getDocument());
     * $gallery->setSelector("js-mg-com-galleria");
     *
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
                    $html[] = '<img src="' . $this->mediaPath . "/" . $media->getImage() . '" />';
                }
            }

            $html[] = '<a href="#" class="slidesjs-previous slidesjs-navigation"><i class="glyphicon glyphicon-chevron-left"></i></a>';
            $html[] = '<a href="#" class="slidesjs-next slidesjs-navigation"><i class="glyphicon glyphicon-chevron-right"></i></a>';

            $html[] = '</div>';
        }

        return implode("\n", $html);
    }

    private function prepareEffects()
    {
        $options = "";
        $effect  = $this->options->get("effect", "fade");
        $speed   = $this->options->get("speed", 200);

        $navigation = $this->options->get("navigation", 0);
        $pagination = $this->options->get("pagination", 1);

        if (strcmp("slide", $effect) == 0) {

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

        } elseif (strcmp("fade", $effect) == 0) {

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

        } elseif (strcmp("fade-crossfade", $effect) == 0) {
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
        $options  = "";
        $play     = $this->options->get("play", 0);
        $effect   = $this->options->get("effect", "fade");
        $interval = $this->options->get("interval", 5000);
        $autoplay = $this->options->get("autoplay", 0);
        $swap     = $this->options->get("swap", 1);
        $pause    = $this->options->get("pause", 0);
        $restart  = $this->options->get("restart", 2500);

        if (!empty($play)) {
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
