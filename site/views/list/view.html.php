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

class MagicGalleryViewList extends JViewLegacy
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

    /**
     * @var Magicgallery\Category\Category
     */
    protected $category;

    protected $categoryId;
    protected $item;
    protected $images;
    protected $mediaUrl;
    protected $openLink;
    protected $modal;
    protected $modalClass;

    public function display($tpl = null)
    {
        $app = JFactory::getApplication();
        /** @var $app JApplicationSite */

        $this->option = $app->input->get('option');
        
        // Check for valid category
        $this->categoryId = $app->input->getInt('id');

        if ($this->categoryId > 0) {
            $this->category = new Magicgallery\Category\Category(JFactory::getDbo());
            $this->category->load($this->categoryId);

            // Checking for published category
            if (!$this->category->getId() or !$this->category->isPublished()) {
                throw new Exception(JText::_('COM_MAGICGALLERY_ERROR_CATEGORY_DOES_NOT_EXIST'));
            }
        }
        
        // Initialise variables
        $this->state      = $this->get('State');
        $this->items      = $this->get('Items');
        $this->pagination = $this->get('Pagination');

        $this->params = $this->state->get('params');

        // Get items IDs.
        $ids = array();
        foreach ($this->items as $item) {
            $ids[] = $item->id;
        }

        if (count($ids) > 0) {
            $model        = $this->getModel();
            $this->images = $model->getImages($ids);
        }

        // Prepare the resources that will be used to generate an intro image.
        $resources = null;
        if (count($this->images) > 0) {
            $resources = new Magicgallery\Entity\Entities();
            $resources->setEntities(reset($this->images));
        }

        // Open link target
        $this->openLink = 'target="' . $this->params->get('open_link', '_self') . '"';

        // Prepare the path to media files;
        $this->mediaUrl = JURI::root() . $this->params->get('media_folder', 'images/magicgallery');

        $this->prepareLightBox();
        $this->prepareDocument();

        // Events
        JPluginHelper::importPlugin('content');
        $dispatcher  = JEventDispatcher::getInstance();
        $this->event = new stdClass();
        $offset      = 0;

        $item              = new stdClass();
        $item->title       = $this->document->getTitle();
        $item->link        = MagicGalleryHelperRoute::getCategoryViewRoute('list', $this->categoryId);
        $item->image_intro = (count($this->images) > 0) ? MagicGalleryHelper::getIntroImage($this->category, $resources, $this->mediaUrl) : null;

        $results                             = $dispatcher->trigger('onContentAfterTitle', array('com_magicgallery.details', &$item, &$this->params, $offset));
        $this->event->onContentAfterTitle    = trim(implode("\n", $results));

        $results                             = $dispatcher->trigger('onContentBeforeDisplay', array('com_magicgallery.details', &$item, &$this->params, $offset));
        $this->event->onContentBeforeDisplay = trim(implode("\n", $results));

        $results                            = $dispatcher->trigger('onContentAfterDisplay', array('com_magicgallery.details', &$item, &$this->params, $offset));
        $this->event->onContentAfterDisplay = trim(implode("\n", $results));

        parent::display($tpl);
    }

    protected function prepareLightBox()
    {
        $this->modal      = $this->params->get('modal');
        $this->modalClass = MagicGalleryHelper::getModalClass($this->modal);

        switch ($this->modal) {

            case 'fancybox':

                JHtml::_('jquery.framework');
                JHtml::_('MagicGallery.lightboxFancybox');

                // Initialize lightbox
                $js = 'jQuery(document).ready(function(){
                        jQuery(".' . $this->modalClass . '").fancybox();
                });';
                $this->document->addScriptDeclaration($js);

                break;

            case 'nivo': // Joomla! native

                JHtml::_('jquery.framework');
                JHtml::_('MagicGallery.lightboxNivo');

                // Initialize lightbox
                $js = '
                jQuery(document).ready(function(){
                    jQuery(".' . $this->modalClass . '").nivoLightbox();
                });';
                $this->document->addScriptDeclaration($js);
                break;

            case 'magnific': // Joomla! native

                JHtml::_('jquery.framework');
                JHtml::_('MagicGallery.lightboxMagnific');

                // Initialize lightbox
                $js = '
                jQuery(document).ready(function(){
                    jQuery(".' . $this->modalClass . '").magnificPopup({
                        type: "image",
                        gallery: {
                            enabled:true
                          }
                    });
                });';
                $this->document->addScriptDeclaration($js);
                break;
        }

    }


    /**
     * Prepares the document
     */
    protected function prepareDocument()
    {
        $app = JFactory::getApplication();
        /** @var $app JApplicationSite */

        $menus = $app->getMenu();
        // Because the application sets a default page title,
        // we need to get it from the menu item itself
        $menu = $menus->getActive();

        //Escape strings for HTML output
        $this->pageclass_sfx = htmlspecialchars($this->params->get('pageclass_sfx'));

        // Set page heading
        if (!$this->params->get('page_heading')) {
            if ($this->category !== null) {
                $this->params->def('page_heading', $this->category->getTitle());
            } else {
                if ($menu) {
                    $this->params->def('page_heading', $menu->title);
                } else {
                    $this->params->def('page_heading', JText::_('COM_MAGICGALLERY_DEFAULT_PAGE_TITLE'));
                }
            }
        }

        // Set page title
        if (!$this->category) { // Uncategorised
            // Get title from the page title option
            $title = $this->params->get('page_title');

            if (!$title) {
                $title = $app->get('sitename');
            }

        } else {

            $title = $this->category->getTitle();

            if (!$title) {
                // Get title from the page title option
                $title = $this->params->get('page_title');

                if (!$title) {
                    $title = $app->get('sitename');
                }

            } elseif ($app->get('sitename_pagetitles', 0)) { // Set site name if it is necessary ( the option 'sitename' = 1 )
                $title = JText::sprintf('JPAGETITLE', $app->get('sitename'), $title);

            }

        }

        $this->document->setTitle($title);

        // Meta Description
        if (!$this->category) { // Uncategorised
            $this->document->setDescription($this->params->get('menu-meta_description'));
        } else {
            $this->document->setDescription($this->category->getMetaDescription());
        }

        // Meta keywords
        if (!$this->category) { // Uncategorised
            $this->document->setDescription($this->params->get('menu-meta_keywords'));
        } else {
            $this->document->setMetaData('keywords', $this->category->getMetaKeywords());
        }

        // Add the category name into breadcrumbs
        if ($this->params->get('category_breadcrumbs') and ($this->category !== null)) {
            $pathway = $app->getPathway();
            $pathway->addItem($this->category->getTitle());
        }

    }
}
