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

class MagicgalleryViewSlideGallery extends JViewLegacy
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

    /**
     * @var Magicgallery\Gallery\Gallery
     */
    protected $item;

    protected $pagination;

    protected $event;
    protected $option;
    protected $pageclass_sfx;

    protected $galleryId;
    protected $gallery;
    protected $modal;
    protected $modalClass;
    protected $mediaUrl;

    public function display($tpl = null)
    {
        $this->app    = JFactory::getApplication();
        $this->option = $this->app->input->get('option');

        $this->galleryId = $this->app->input->getInt('id');
        if (!$this->galleryId) {
            throw new Exception(JText::_('COM_MAGICGALLERY_ERROR_GALLERY_DOES_NOT_EXIST'));
        }

        $this->state      = $this->get('State');
        $this->pagination = $this->get('Pagination');
        $this->params     = $this->app->getParams();

        $options  = array(
            'load_resources' => true,
            'resource_state' => Prism\Constants::PUBLISHED
        );

        $this->item  = new Magicgallery\Gallery\Gallery(JFactory::getDbo());
        $this->item->load($this->galleryId, $options);

        // Prepare the parameters of the galleries.
        $filesystemHelper = new Prism\Filesystem\Helper($this->params);
        $pathHelper       = new Magicgallery\Helper\Path($filesystemHelper);
        $mediaUri         = $pathHelper->getMediaUri($this->item);
        $this->item->setMediaUri($mediaUri);

        $this->prepareDocument();

        // Events
        JPluginHelper::importPlugin('content');
        $dispatcher = JEventDispatcher::getInstance();
        $offset     = 0;

        $item              = new stdClass();
        $item->title       = $this->document->getTitle();
        $item->link        = MagicgalleryHelperRoute::getGalleryViewRoute('slidegallery', $this->item->getSlug());
        $item->image_intro = MagicgalleryHelper::getIntroImage($this->item);

        $this->event                         = new stdClass();
        $results                             = $dispatcher->trigger('onContentBeforeDisplay', array('com_magicgallery.details', &$item, &$this->params, $offset));
        $this->event->onContentBeforeDisplay = trim(implode("\n", $results));

        $results                            = $dispatcher->trigger('onContentAfterDisplay', array('com_magicgallery.details', &$item, &$this->params, $offset));
        $this->event->onContentAfterDisplay = trim(implode("\n", $results));

        parent::display($tpl);
    }

    /**
     * Prepares the document.
     *
     * @throws \InvalidArgumentException
     */
    protected function prepareDocument()
    {
        $menus = $this->app->getMenu();
        // Because the application sets a default page title,
        // we need to get it from the menu item itself
        $menu = $menus->getActive();

        //Escape strings for HTML output
        $this->pageclass_sfx = htmlspecialchars($this->params->get('pageclass_sfx'));

        // Set page heading
        if (!$this->params->get('page_heading')) {
            if ($this->item !== null) {
                $this->params->def('page_heading', $this->item->getTitle());
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
                $title = $this->app->get('sitename');
            }
        } else {
            $title = $this->item->getTitle();

            if (!$title) {
                // Get title from the page title option
                $title = $this->params->get('page_title');

                if (!$title) {
                    $title = $this->app->get('sitename');
                }
            } elseif ($this->app->get('sitename_pagetitles', 0)) { // Set site name if it is necessary ( the option 'sitename' = 1 )
                $title = JText::sprintf('JPAGETITLE', $this->app->get('sitename'), $title);
            }
        }

        $this->document->setTitle($title);

        // Meta Description
        if (!$this->item) { // Uncategorised
            $this->document->setDescription($this->params->get('menu-meta_description'));
        } else {
            $this->document->setDescription($this->item->getMetaDescription());
        }

        // Meta keywords
        if (!$this->item) { // Uncategorised
            $this->document->setDescription($this->params->get('menu-meta_keywords'));
        } else {
            $this->document->setMetaData('keywords', $this->item->getMetaKeywords());
        }

        // Add the category name into breadcrumbs
        /*if ($this->params->get('category_breadcrumbs') and ($this->category !== null)) {
            $pathway = $this->app->getPathway();
            $pathway->addItem($this->category->getTitle());
        }*/

        // Prepare the gallery.
        if ($this->item !== null) {
            $this->gallery = new Magicgallery\Gallery\SlideGallery($this->item, $this->params);

            $js = $this->gallery
                ->setSelector('js-mg-com-slidegallery')
                ->prepareScriptDeclaration();

            $this->document->addScriptDeclaration($js);
        }
    }
}
