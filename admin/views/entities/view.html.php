<?php
/**
 * @package      Magicgallery
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2016 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

class MagicgalleryViewEntities extends JViewLegacy
{
    /**
     * @var JApplicationAdministrator
     */
    public $app;

    /**
     * @var JDocumentHtml
     */
    public $document;

    /**
     * @var Joomla\Registry\Registry
     */
    protected $state;

    /**
     * @var Joomla\Registry\Registry
     */
    protected $params;

    protected $items;
    protected $pagination;

    protected $option;

    protected $listOrder;
    protected $listDirn;
    protected $saveOrder;
    protected $saveOrderingUrl;

    public $filterForm;

    protected $sidebar;

    protected $galleryId;
    protected $mediaUri;

    /**
     * @var Magicgallery\Category\Category
     */
    protected $gallery;

    public function display($tpl = null)
    {
        $this->app        = JFactory::getApplication();
        $this->option     = JFactory::getApplication()->input->get('option');

        $this->state      = $this->get('State');
        $this->items      = $this->get('Items');
        $this->pagination = $this->get('Pagination');

        $this->params     = $this->state->get('params');

        $this->galleryId  = (int)$this->state->get('filter.gallery_id');
        if (!$this->galleryId) {
            $this->app->redirect(JRoute::_('index.php?option=com_magicgallery&view=galleries', false));
            return;
        }

        $this->gallery    = new Magicgallery\Gallery\Gallery(JFactory::getDbo());
        $this->gallery->load($this->galleryId);

        $filesystemHelper = new Prism\Filesystem\Helper($this->params);
        $pathHelper       = new Magicgallery\Helper\Path($filesystemHelper);

        $this->mediaUri   = $pathHelper->getMediaUri($this->gallery);
        if (!$this->mediaUri) {
            throw new Exception(JText::_('COM_MAGICGALLERY_ERROR_INVALID_MEDIA_FOLDER'));
        }

        // Prepare sorting data
        $this->prepareSorting();

        // Prepare actions
        $this->addToolbar();
        $this->addSidebar();
        $this->setDocument();

        parent::display($tpl);
    }

    /**
     * Prepare sortable fields, sort values and filters.
     */
    protected function prepareSorting()
    {
        // Prepare filters
        $this->listOrder = $this->escape($this->state->get('list.ordering'));
        $this->listDirn  = $this->escape($this->state->get('list.direction'));
        $this->saveOrder = (strcmp($this->listOrder, 'a.ordering') === 0);

        if ($this->saveOrder) {
            $this->saveOrderingUrl = 'index.php?option=' . $this->option . '&task=' . $this->getName() . '.saveOrderAjax&format=raw';
            JHtml::_('sortablelist.sortable', $this->getName() . 'List', 'adminForm', strtolower($this->listDirn), $this->saveOrderingUrl);
        }

        $this->filterForm    = $this->get('FilterForm');
    }

    /**
     * Add a menu on the sidebar of page
     */
    protected function addSidebar()
    {
        MagicgalleryHelper::addSubmenu($this->getName());
        $this->sidebar = JHtmlSidebar::render();
    }

    /**
     * Add the page title and toolbar.
     *
     * @since   1.6
     */
    protected function addToolbar()
    {
        // Set toolbar items for the page
        JToolbarHelper::title(JText::sprintf('COM_MAGICGALLERY_RESOURCES_TITLE_S', $this->gallery->getTitle()));
        JToolbarHelper::addNew('entity.add');
        JToolbarHelper::editList('entity.edit');
        JToolbarHelper::divider();
        JToolbarHelper::publishList('entities.publish');
        JToolbarHelper::unpublishList('entities.unpublish');
        JToolbarHelper::divider();
        JToolbarHelper::deleteList(JText::_('COM_MAGICGALLERY_DELETE_ITEMS_QUESTION'), 'entities.delete');
        JToolbarHelper::divider();
        JToolbarHelper::custom('entities.backToDashboard', 'dashboard', '', JText::_('COM_MAGICGALLERY_DASHBOARD'), false);
    }

    /**
     * Method to set up the document properties.
     *
     * @throws \InvalidArgumentException
     *
     * @return void
     */
    protected function setDocument()
    {
        $this->document->setTitle(JText::sprintf('COM_MAGICGALLERY_RESOURCES_TITLE_S', $this->gallery->getTitle()));

        // Scripts
        JHtml::_('bootstrap.tooltip');
        JHtml::_('behavior.multiselect');
        JHtml::_('formbehavior.chosen', 'select');

        JHtml::_('magicgallery.lightboxNivo');

        $this->document->addScript('../media/' . $this->option . '/js/admin/' . strtolower($this->getName()) . '.js');
    }
}
