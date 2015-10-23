<?php
/**
 * @package         MagicGallery
 * @subpackage      Galleries
 * @author          Todor Iliev
 * @copyright       Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license         http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

namespace Magicgallery\Gallery;

use MagicGallery\Entity\Entity;

defined('JPATH_PLATFORM') or die;

/**
 * This class provide functionality for managing Camera.
 *
 * @package         MagicGallery
 * @subpackage      Galleries
 */
class Camera extends GalleryAbstract
{
    protected $linkable = 0;
    protected $link_target = '_blank';

    protected $alignment = 'center';
    protected $auto_advance = 1;
    protected $bar_direction = 'leftToRight';
    protected $bar_position = 'bottom';
    protected $effect = 'random';
    protected $navigation = 1;
    protected $navigation_hover = 1;
    protected $pagination = 1;
    protected $play_pause = 1;
    protected $pause_on_click = 1;
    protected $time = 7000;
    protected $trans_period = 1500;
    protected $thumbnails = 0;

    /**
     * Add script code to the document.
     *
     * <code>
     * $gallery = new Magicgallery\Gallery\Camera($items, $params, \JFactory::getDocument());
     * $gallery->addScriptDeclaration();
     * </code>
     *
     * @return self
     */
    public function addScriptDeclaration()
    {
        \JHtml::_('jquery.framework');
        \JHtml::_('MagicGallery.camera');

        $js = '
jQuery(document).ready(function() {
        
	jQuery("#' . $this->selector . '").camera({
        alignment : "' . $this->options->get('alignment', 'center', $this->alignment) . '",
        autoAdvance : ' . $this->options->get('auto_advance', $this->auto_advance) . ',
        barDirection : "' . $this->options->get('bar_direction', $this->bar_direction) . '",
        barPosition : "' . $this->options->get('bar_position', $this->bar_position) . '",
        fx : "' . $this->options->get('effect', $this->effect) . '",
        navigation : ' . $this->options->get('navigation', $this->navigation) . ',
        navigationHover : ' . $this->options->get('navigation_hover', $this->navigation_hover) . ',
        pagination : ' . $this->options->get('pagination', $this->pagination) . ',
        playPause : ' . $this->options->get('play_pause', $this->play_pause) . ',
        pauseOnClick : ' . $this->options->get('pause_click', $this->pause_on_click) . ',
        time : ' . $this->options->get('time', $this->time) . ',
        transPeriod : ' . $this->options->get('trans_period', $this->trans_period) . ',
        thumbnails : ' . $this->options->get('thumbnails', $this->thumbnails) . '
    });
        
});';
        $this->document->addScriptDeclaration($js);

        return $this;
    }

    /**
     * Render the HTML code.
     *
     * <code>
     * $gallery = new Magicgallery\Gallery\Camera($items, $params, \JFactory::getDocument());
     * echo $gallery->render();
     * </code>
     *
     * @return string
     */
    public function render()
    {
        $html = array();

        if (count($this->items) > 0) {

            $html[] = '<div id="' . $this->selector . '">';

            /** @var Gallery $item */
            foreach ($this->items as $item) {
                
                if (!$item->getId()) {
                    continue;
                }

                // Set a link
                $dataLink   = '';
                $dataTarget = '';
                if ($this->options->get('linkable', $this->linkable) and $item->getUrl()) {

                    $dataLink = ' data-link="' . $item->getUrl() . '"';

                    // Set a link target
                    $dataTarget = ' data-target="' . $this->options->get('link_target', '_blank') . '"';

                }

                $media = $item->getDefaultEntity($item->getId());
                /** @var Entity $media */

                if ($media !== null and ($media instanceof Entity)) {
                    // Set thumbnails
                    $dataThumb = '';
                    if ($this->options->get('thumbnails', $this->thumbnails) and $media->getThumbnail()) {
                        $dataThumb = ' data-thumb="' . $this->mediaPath . '/' . $media->getThumbnail() . '"';
                    }

                    $html[] = '<div data-src="' . $this->mediaPath . '/' . $media->getImage() . '" ' . $dataLink . $dataTarget . $dataThumb . '></div>';
                }
            }
            $html[] = '</div>';
        }

        return implode("\n", $html);
    }
}
