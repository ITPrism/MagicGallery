<?php
/**
 * @package      MagicGallery
 * @subpackage   Component
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

class MagicGalleryViewTabbed extends JViewLegacy
{
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
    protected $state = null;

    protected $items = null;
    protected $pagination = null;

    protected $event = null;
    protected $option;
    protected $pageclass_sfx;

    protected $category;
    protected $categoryId;
    protected $galleries;
    protected $mediaUrl;
    protected $activeTab;
    protected $displayCaption;
    protected $openLink;
    protected $modal;
    protected $modalClass;
    protected $projectsView;

    public function __construct($config)
    {
        parent::__construct($config);
        $this->option = JFactory::getApplication()->input->get("option");
    }

    public function display($tpl = null)
    {
        $app = JFactory::getApplication();
        /** @var $app JApplicationSite */

        // Initialise variables
        $this->state  = $this->get('State');
        $this->items  = $this->get('Items');
        $this->params = $this->state->params;

        $this->projectsView = $app->input->get("projects_view", "tabbed", "string");

        // Parse parameters and collect categories ids in array
        $categoriesIds = array();
        foreach ($this->items as &$item) {
            $item->params = json_decode($item->params);
            if (!empty($item->params->image)) {
                $item->image = $item->params->image;
            }

            $categoriesIds[] = $item->id;
        }

        unset($item);

        $options  = array(
            "category_id"    => $categoriesIds,
            "gallery_state"  => Prism\Constants::PUBLISHED,
            "load_resources" => true,
            "resource_state" => Prism\Constants::PUBLISHED
        );

        $galleries_  = new MagicGallery\Gallery\Galleries(JFactory::getDbo());
        $galleries_->load($options);

        $galleries = array();
        foreach ($galleries_ as $gallery) {
            $galleries[$gallery->getCategoryId()][] = $gallery;
        }

        $this->galleries = $galleries;

        $this->mediaUrl       = JURI::root() . $this->params->get("media_folder", "images/magicgallery");
        $this->activeTab      = $this->params->get("active_tab");
        $this->displayCaption = false;

        // Open link target
        $this->openLink = 'target="' . $this->params->get("open_link", "_self") . '"';

        $this->prepareLightBox();
        $this->prepareDocument();

        // Events
        $offset = 0;

        $item              = new stdClass();
        $item->title       = $this->document->getTitle();
        $item->link        = MagicGalleryHelperRoute::getCategoriesViewRoute("tabbed");
        $item->image_intro = MagicGalleryHelper::getCategoryImage($this->items);

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
        $this->modal      = $this->params->get("modal");
        $this->modalClass = MagicGalleryHelper::getModalClass($this->modal);

        $this->setLayout($this->modal);

        switch ($this->modal) {

            case "fancybox":

                JHtml::_('jquery.framework');
                JHtml::_('MagicGallery.lightboxFancyBox');

                // Initialize lightbox
                $js = 'jQuery(document).ready(function(){
                        jQuery(".' . $this->modalClass . '").fancybox();
                });';
                $this->document->addScriptDeclaration($js);

                break;

            case "nivo": // Joomla! native

                JHtml::_('jquery.framework');
                JHtml::_('MagicGallery.lightboxNivo');

                // Initialize lightbox
                $js = '
                jQuery(document).ready(function(){
                    jQuery(".' . $this->modalClass . '").nivoLightbox();
                });';
                $this->document->addScriptDeclaration($js);
                break;
        }
    }

    /**
     * Prepare the document
     */
    protected function prepareDocument()
    {
        $app   = JFactory::getApplication();
        $menus = $app->getMenu();

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
        if (empty($title)) {
            $title = $app->get('sitename');
        } elseif ($app->get('sitename_pagetitles', 0)) {
            $title = JText::sprintf('JPAGETITLE', $app->get('sitename'), $title);
        }
        $this->document->setTitle($title);

        // Meta Description
        if ($this->params->get('menu-meta_description')) {
            $this->document->setDescription($this->params->get('menu-meta_description'));
        }

        // Meta keywords
        if ($this->params->get('menu-meta_keywords')) {
            $this->document->setMetadata('keywords', $this->params->get('menu-meta_keywords'));
        }


        // Scripts
        JHtml::_('jquery.framework');

        if ($this->params->get("display_tip", 0)) {
            JHtml::_('bootstrap.tooltip');
        }

        if ($this->params->get("caption_title", 0) or $this->params->get("caption_desc", 0) or $this->params->get("caption_url", 0)) {
            $this->displayCaption = true;
        }

        // Load captionjs script.
        if ($this->displayCaption) {
            JHtml::_('vipportfolio.jsquares');

            $js = '';

            $this->document->addScriptDeclaration($js);
        }

    }
}
