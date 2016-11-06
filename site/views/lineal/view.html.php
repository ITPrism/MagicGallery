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

class MagicgalleryViewLineal extends JViewLegacy
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
    protected $resources;
    protected $mediaUrl;
    protected $openLink;
    protected $modal;
    protected $modalClass;

    /**
     * @var Magicgallery\Entity\Entity
     */
    protected $defaultResource;

    public function display($tpl = null)
    {
        $app = JFactory::getApplication();
        /** @var $app JApplicationSite */

        $this->option = $app->input->get('option');
        
        // Check for valid category
        $this->categoryId = $app->input->getInt('id');
        $this->category   = null;

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
//        $this->items      = $this->get('Items');
        $this->pagination = $this->get('Pagination');

        $this->params     = $this->state->get('params');
        /** @var  $params Joomla\Registry\Registry */

        $options  = array(
            'category_id'    => $this->category->getId(),
            'gallery_state'  => Prism\Constants::PUBLISHED,
            'load_resources' => true,
            'resource_state' => Prism\Constants::PUBLISHED,
            'start'          => $this->state->get('list.start', 0),
            'limit'          => $this->state->get('list.limit', 1)
        );

        $this->items      = new Magicgallery\Gallery\Galleries(JFactory::getDbo());
        $this->items->load($options);

        $filesystemHelper = new Prism\Filesystem\Helper($this->params);
        $pathHelper       = new Magicgallery\Helper\Path($filesystemHelper);

        // Prepare the resources that will be used to generate an intro image.
        $helperBus      = new Prism\Helper\HelperBus($this->items);
        $helperBus->addCommand(new Magicgallery\Helper\PrepareGalleriesUriHelper($pathHelper));
        $helperBus->handle();

        $this->item = $this->items->getFirst();
        if (!$this->item) {
            throw new Exception(JText::_('COM_MAGICGALLERY_ERROR_INVALID_GALLERY'));
        }

        // Get the default resource.
        $this->defaultResource = null;
        if (property_exists($this->item, 'entities') and ($this->item->entities instanceof \Magicgallery\Entity\Entities)) {
            $entities = $this->item->entities;
            /** @var \Magicgallery\Entity\Entities $entities */

            $this->defaultResource = $entities->getDefaultEntity();
        }

        // Open link target
        $this->openLink = 'target="' . $this->params->get('lineal_open_link', '_self') . '"';

        $this->prepareLightBox();
        $this->prepareDocument();

        // Events
        $offset            = $this->state->get('list.start', null);

        $item              = new stdClass();
        $item->title       = $this->document->getTitle();
        $item->link        = MagicgalleryHelperRoute::getGalleryViewRoute('lineal', $this->item->id, $this->categoryId, $offset);
        $item->image_intro = ($this->defaultResource !== null) ? $this->item->media_uri .'/'. $this->defaultResource->getThumbnail() : null;

        JPluginHelper::importPlugin('content');
        $dispatcher  = JEventDispatcher::getInstance();
        $this->event = new stdClass();

        $results                             = $dispatcher->trigger('onContentAfterTitle', array('com_magicgallery.details', &$item, &$this->params, $offset));
        $this->event->afterDisplayTitle      = trim(implode("\n", $results));

        $results                             = $dispatcher->trigger('onContentBeforeDisplay', array('com_magicgallery.details', &$item, &$this->params, $offset));
        $this->event->onContentBeforeDisplay = trim(implode("\n", $results));

        $results                             = $dispatcher->trigger('onContentAfterDisplay', array('com_magicgallery.details', &$item, &$this->params, $offset));
        $this->event->onContentAfterDisplay  = trim(implode("\n", $results));

        parent::display($tpl);
    }

    protected function prepareLightBox()
    {
        $this->modal      = $this->params->get('modal');
        $this->modalClass = MagicgalleryHelper::getModalClass($this->modal);

        JHtml::_('jquery.framework');

        switch ($this->modal) {
            case 'fancybox':
                JHtml::_('Magicgallery.lightboxFancybox');

                $js = 'jQuery(document).ready(function(){
                        jQuery(".' . $this->modalClass . '").fancybox();
                });';
                $this->document->addScriptDeclaration($js);

                break;

            case 'nivo': // Joomla! native
                JHtml::_('Magicgallery.lightboxNivo');

                $js = '
                jQuery(document).ready(function(){
                    jQuery(".' . $this->modalClass . '").nivoLightbox();
                });';
                $this->document->addScriptDeclaration($js);
                break;

            case 'magnific': // Joomla! native
                JHtml::_('Magicgallery.lightboxMagnific');

                $js = '
                jQuery(document).ready(function(){
                    jQuery(".' . $this->modalClass . '").magnificPopup({
                        type: "image",
                        gallery: {
                            enabled: true
                          }
                    });
                });';
                $this->document->addScriptDeclaration($js);
                break;
            case 'swipebox': // Joomla! native
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
            if ($this->item !== null) {
                $this->params->def('page_heading', $this->item->title);
            } else {
                if ($menu) {
                    $this->params->def('page_heading', $menu->title);
                } else {
                    $this->params->def('page_heading', JText::_('COM_MAGICGALLERY_DEFAULT_PAGE_TITLE'));
                }
            }
        }

        // Set page title
        if (!$this->item) { // Uncategorised
            // Get title from the page title option
            $title = $this->params->get('page_title');

            if (!$title) {
                $title = $app->get('sitename');
            }
        } else {
            $title = $this->item->title;

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

        // Add category name to page title.
        if ($this->params->get('category_in_title') and ($this->category !== null)) {
            $title .= ' | ' . $this->category->getTitle();
        }

        $this->document->setTitle($title);

        // Meta Description
        if (!$this->category) { // Uncategorised
            $this->document->setDescription($this->params->get('menu-meta_description'));
        } else {
            $metaDescription = JHtmlString::truncate($this->item->description, 160);
            $this->document->setDescription($metaDescription);
        }

        // Meta keywords
        if (!$this->category) { // Uncategorised
            $this->document->setDescription($this->params->get('menu-meta_keywords'));
        } else {
            $this->document->setMetaData('keywords', $this->category->getMetaKeywords());
        }

        // Add the category name into breadcrumbs
        $pathway = $app->getPathway();
        if ($this->params->get('category_breadcrumbs') and ($this->category !== null)) {
            $categoryLink = JRoute::_(MagicgalleryHelperRoute::getCategoryViewRoute('lineal', $this->categoryId));
            $pathway->addItem($this->category->getTitle(), $categoryLink);
        }

        $pathway->addItem($this->item->title);
    }
}
