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

class MagicGalleryViewCamera extends JViewLegacy
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

    /**
     * @var MagicGallery\Gallery\Galleries
     */
    protected $items = null;

    protected $pagination = null;

    protected $event = null;
    protected $option;
    protected $pageclass_sfx;

    /**
     * @var MagicGallery\Category\Category
     */
    protected $category;

    protected $categoryId;
    protected $gallery;
    protected $mediaUrl;

    public function __construct($config)
    {
        parent::__construct($config);
        $this->option = JFactory::getApplication()->input->get("option");
    }
    
    public function display($tpl = null)
    {
        $app = JFactory::getApplication();
        /** @var $app JApplicationSite */

        // Check for valid category
        $this->categoryId = $app->input->getInt("id");
        $this->category   = null;

        if (!empty($this->categoryId)) {
            $this->category = new MagicGallery\Category\Category(JFactory::getDbo());
            $this->category->load($this->categoryId);

            // Checking for published category
            if (!$this->category->getId() or !$this->category->isPublished()) {
                throw new Exception(JText::_("COM_MAGICGALLERY_ERROR_CATEGORY_DOES_NOT_EXIST"));
            }
        }

        // Initialise variables
        $this->state  = $this->get('State');
        $this->params = $this->state->get("params");

        $options  = array(
            "category_id"    => $this->category->getId(),
            "gallery_state"  => Prism\Constants::PUBLISHED,
            "load_resources" => true,
            "resource_state" => Prism\Constants::PUBLISHED
        );

        $this->items  = new MagicGallery\Gallery\Galleries(JFactory::getDbo());
        $this->items->load($options);

        $gallery   = $this->items->getFirst();
        $resources = (!empty($gallery)) ? $gallery->getResources() : null;

        // Prepare the path to media files;
        $this->mediaUrl = JURI::root() . $this->params->get("media_folder", "images/magicgallery");

        $this->prepareDocument();

        // Events
        JPluginHelper::importPlugin('content');
        $dispatcher  = JEventDispatcher::getInstance();
        $this->event = new stdClass();
        $offset      = 0;

        $item              = new stdClass();
        $item->title       = $this->document->getTitle();
        $item->link        = MagicGalleryHelperRoute::getCategoryViewRoute("camera", $this->categoryId);
        $item->image_intro = MagicGalleryHelper::getIntroImage($this->category, $resources, $this->mediaUrl);

        $results                             = $dispatcher->trigger('onContentAfterTitle', array('com_magicgallery.details', &$item, &$this->params, $offset));
        $this->event->onContentAfterTitle    = trim(implode("\n", $results));
        
        $results                             = $dispatcher->trigger('onContentBeforeDisplay', array('com_magicgallery.details', &$item, &$this->params, $offset));
        $this->event->onContentBeforeDisplay = trim(implode("\n", $results));

        $results                            = $dispatcher->trigger('onContentAfterDisplay', array('com_magicgallery.details', &$item, &$this->params, $offset));
        $this->event->onContentAfterDisplay = trim(implode("\n", $results));

        parent::display($tpl);
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
        if (!$this->params->get("page_heading")) {
            if (!empty($this->category)) {
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
            $title = $this->params->get("page_title");

            if (!$title) {
                $title = $app->get('sitename');
            }

        } else {

            $title = $this->category->getTitle();

            if (!$title) {
                // Get title from the page title option
                $title = $this->params->get("page_title");

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
            $this->document->setMetadata('keywords', $this->category->getMetaKeywords());
        }

        // Add the category name into breadcrumbs
        if ($this->params->get('category_breadcrumbs')) {

            if (!empty($this->category)) {
                $pathway = $app->getPathway();
                $pathway->addItem($this->category->getTitle());
            }
        }

        if ($this->items->provideResources()) {
            // Prepare the gallery.
            $this->gallery = new MagicGallery\Gallery\Camera($this->items, $this->params, $this->document);

            $this->gallery
                ->setMediaPath($this->mediaUrl)
                ->setSelector("js-mg-com-camera")
                ->addScriptDeclaration();
        }
    }
}
