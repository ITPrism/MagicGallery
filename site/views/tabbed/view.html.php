<?php
/**
 * @package      Magicgallery
 * @subpackage   Component
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2016 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

class MagicgalleryViewTabbed extends JViewLegacy
{
    /**
     * @var JApplicationSite
     */
    public $app;
    
    /**
     * @var JDocumentHtml
     */
    public $document;

    /**
     * @var Joomla\Registry\Registry
     */
    protected $params;

    /**
     * @var Joomla\Registry\Registry
     */
    protected $state;

    protected $items;
    protected $pagination;

    protected $event;
    protected $option;
    protected $pageclass_sfx;

    protected $galleries;
    protected $mediaUrl;
    protected $activeTab;
    protected $openLink;
    protected $modal;
    protected $modalClass;
    protected $projectsView;
    
    public function display($tpl = null)
    {
        $this->app    = JFactory::getApplication();
        $this->option = $this->app->input->get('option');
        
        $this->state  = $this->get('State');
//        $this->items  = $this->get('Items');
        $this->params = $this->state->params;

        $this->projectsView = $this->app->input->get('projects_view', 'tabbed', 'string');

        $requestId  = $this->app->input->getCmd('id');

        $galleryIds = $this->params->get('gallery_id');
        $galleryIds = Joomla\Utilities\ArrayHelper::toInteger($galleryIds);
        $options    = array(
            'ids'            => $galleryIds,
            'gallery_state'  => Prism\Constants::PUBLISHED,
            'load_resources' => true,
            'resource_state' => Prism\Constants::PUBLISHED
        );

        $galleries  = new Magicgallery\Gallery\Galleries(JFactory::getDbo());
        $galleries->load($options);

        // Prepare the categories parameters and images.
        $filesystemHelper = new Prism\Filesystem\Helper($this->params);
        $pathHelper       = new Magicgallery\Helper\Path($filesystemHelper);
        
        // Prepare the media URL of the galleries.
        $helperBus      = new Prism\Helper\HelperBus($galleries);
        $helperBus->addCommand(new Magicgallery\Helper\PrepareGalleriesUriHelper($pathHelper));
        $helperBus->handle();

        $this->items     = $galleries->getGalleries();

        $this->activeTab = $this->params->get('active_tab');

        // Open link target
        $this->openLink = 'target="' . $this->params->get('open_link', '_self') . '"';

        $this->prepareLightBox();
        $this->prepareDocument();

        // Events
        $offset = 0;

        $item              = new stdClass();
        $item->title       = $this->document->getTitle();
        $item->link        = MagicgalleryHelperRoute::getGalleryViewRoute('tabbed', $requestId);
        $item->image_intro = MagicgalleryHelper::getIntroImageFromGalleries($this->items);

        JPluginHelper::importPlugin('content');
        $dispatcher  = JEventDispatcher::getInstance();
        $this->event = new stdClass();

        $results                             = $dispatcher->trigger('onContentBeforeDisplay', array('com_magicgallery.details', &$item, &$this->params, $offset));
        $this->event->onContentBeforeDisplay = trim(implode("\n", $results));

        $results                            = $dispatcher->trigger('onContentAfterDisplay', array('com_magicgallery.details', &$item, &$this->params, $offset));
        $this->event->onContentAfterDisplay = trim(implode("\n", $results));

        parent::display($tpl);
    }

    protected function prepareLightBox()
    {
        $this->modal      = $this->params->get('modal');
        $this->modalClass = MagicgalleryHelper::getModalClass($this->modal);

        $this->setLayout($this->modal);

        JHtml::_('jquery.framework');

        switch ($this->modal) {
            case 'fancybox':
                JHtml::_('Magicgallery.lightboxFancybox');

                // Initialize lightbox
                $js = 'jQuery(document).ready(function(){
                        jQuery(".' . $this->modalClass . '").fancybox();
                });';
                $this->document->addScriptDeclaration($js);

                break;

            case 'nivo': // Joomla! native
                JHtml::_('Magicgallery.lightboxNivo');

                // Initialize lightbox
                $js = '
                jQuery(document).ready(function(){
                    jQuery(".' . $this->modalClass . '").nivoLightbox();
                });';
                $this->document->addScriptDeclaration($js);
                break;

            case 'swipebox':
                JHtml::_('Magicgallery.lightboxSwipebox');

                // Initialize lightbox
                $js = '
                jQuery(document).ready(function(){
                    jQuery(".' . $this->modalClass . '").swipebox();
                });';
                $this->document->addScriptDeclaration($js);
                break;
        }
    }

    /**
     * Prepare the document
     *
     * @throws \Exception
     */
    protected function prepareDocument()
    {
        $menus = $this->app->getMenu();

        //Escape strings for HTML output
        $this->pageclass_sfx = htmlspecialchars($this->params->get('pageclass_sfx'));

        // Because the application sets a default page title,
        // we need to get it from the menu item itself
        $menu = $menus->getActive();
        if ($menu) {
            $this->params->def('page_heading', $this->params->get('page_title', $menu->title));
        } else {
            $this->params->def('page_heading', JText::_('COM_MAGICGALLERY_CATEGORIES_DEFAULT_PAGE_TITLE'));
        }

        // Set page title
        $title = $this->params->get('page_title', '');
        if ($title !== '') {
            $title = $this->app->get('sitename');
        } elseif ($this->app->get('sitename_pagetitles', 0)) {
            $title = JText::sprintf('JPAGETITLE', $this->app->get('sitename'), $title);
        }
        $this->document->setTitle($title);

        // Meta Description
        if ($this->params->get('menu-meta_description')) {
            $this->document->setDescription($this->params->get('menu-meta_description'));
        }

        // Meta keywords
        if ($this->params->get('menu-meta_keywords')) {
            $this->document->setMetaData('keywords', $this->params->get('menu-meta_keywords'));
        }

        // Scripts
        JHtml::_('jquery.framework');

        if ($this->params->get('display_tip', 0)) {
            JHtml::_('bootstrap.tooltip');
        }
    }
}
