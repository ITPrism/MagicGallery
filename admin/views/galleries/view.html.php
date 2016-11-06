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

class MagicgalleryViewGalleries extends JViewLegacy
{
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
    protected $numberOfResources = array();
    
    public function display($tpl = null)
    {
        $this->option = JFactory::getApplication()->input->get('option');
        
        $this->state      = $this->get('State');
        $this->items      = $this->get('Items');
        $this->pagination = $this->get('Pagination');

        if (is_array($this->items) and count($this->items) > 0) {
            $ids = array();
            foreach ($this->items as $item) {
                $ids[] = $item->id;
            }

            $ids = Joomla\Utilities\ArrayHelper::toInteger($ids);
            $galleries = new Magicgallery\Gallery\Galleries(JFactory::getDbo());
            $this->numberOfResources = $galleries->countResources($ids);
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
        JToolbarHelper::title(JText::_('COM_MAGICGALLERY_GALLERIES'));
        JToolbarHelper::addNew('gallery.add');
        JToolbarHelper::editList('gallery.edit');
        JToolbarHelper::divider();
        JToolbarHelper::publishList('galleries.publish');
        JToolbarHelper::unpublishList('galleries.unpublish');
        JToolbarHelper::divider();
        JToolbarHelper::deleteList(JText::_('COM_MAGICGALLERY_DELETE_ITEMS_QUESTION'), 'galleries.delete');
        JToolbarHelper::divider();
        JToolbarHelper::custom('galleries.backToDashboard', 'dashboard', '', JText::_('COM_MAGICGALLERY_DASHBOARD'), false);
    }

    /**
     * Method to set up the document properties
     *
     * @throws \InvalidArgumentException
     *
     * @return void
     */
    protected function setDocument()
    {
        $this->document->setTitle(JText::_('COM_MAGICGALLERY_GALLERIES'));

        // Scripts
        JHtml::_('bootstrap.tooltip');
        JHtml::_('behavior.multiselect');
        JHtml::_('formbehavior.chosen', 'select');
    }
}
