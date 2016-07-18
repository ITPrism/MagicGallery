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

    protected $category;
    protected $categoryId;
    protected $galleries;
    protected $mediaUrl;
    protected $activeTab;
    protected $openLink;
    protected $modal;
    protected $modalClass;
    protected $projectsView;
    
    public function display($tpl = null)
    {
        $app = JFactory::getApplication();
        /** @var $app JApplicationSite */

        $this->option = $app->input->get('option');
        
        $this->state  = $this->get('State');
        $this->items  = $this->get('Items');
        $this->params = $this->state->params;

        $this->projectsView = $app->input->get('projects_view', 'tabbed', 'string');

        // Parse parameters and collect categories ids in array
        $categoriesIds = array();
        foreach ($this->items as &$item) {
            $item->params = json_decode($item->params);
            if ($item->params !== null and isset($item->params->image) and ($item->params->image !== '')) {
                $item->image = $item->params->image;
            }

            $categoriesIds[] = $item->id;
        }

        unset($item);

        $options  = array(
            'category_id'    => $categoriesIds,
            'gallery_state'  => Prism\Constants::PUBLISHED,
            'load_resources' => true,
            'resource_state' => Prism\Constants::PUBLISHED
        );

        $galleries_  = new Magicgallery\Gallery\Galleries(JFactory::getDbo());
        $galleries_->load($options);

        $galleries = array();
        foreach ($galleries_ as $gallery) {
            $galleries[$gallery->getCategoryId()][] = $gallery;
        }

        $this->galleries = $galleries;

        $this->mediaUrl       = JUri::root() . $this->params->get('media_folder', 'images/magicgallery');
        $this->activeTab      = $this->params->get('active_tab');

        // Open link target
        $this->openLink = 'target="' . $this->params->get('open_link', '_self') . '"';

        $this->prepareLightBox();
        $this->prepareDocument();

        // Events
        $offset = 0;

        $item              = new stdClass();
        $item->title       = $this->document->getTitle();
        $item->link        = MagicgalleryHelperRoute::getCategoriesViewRoute('tabbed');
        $item->image_intro = MagicgalleryHelper::getCategoryImage($this->items);

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

        switch ($this->modal) {
            case 'fancybox':
                JHtml::_('jquery.framework');
                JHtml::_('Magicgallery.lightboxFancybox');

                // Initialize lightbox
                $js = 'jQuery(document).ready(function(){
                        jQuery(".' . $this->modalClass . '").fancybox();
                });';
                $this->document->addScriptDeclaration($js);

                break;

            case 'nivo': // Joomla! native
                JHtml::_('jquery.framework');
                JHtml::_('Magicgallery.lightboxNivo');

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
     *
     * @throws \Exception
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
        if ($title !== '') {
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
            $this->document->setMetaData('keywords', $this->params->get('menu-meta_keywords'));
        }

        // Scripts
        JHtml::_('jquery.framework');

        if ($this->params->get('display_tip', 0)) {
            JHtml::_('bootstrap.tooltip');
        }
    }
}
