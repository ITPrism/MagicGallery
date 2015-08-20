<?php
/**
 * @package      MagicGallery
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2014 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

class MagicGalleryViewGalleries extends JViewLegacy
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

    public function __construct($config)
    {
        parent::__construct($config);
        $this->option = JFactory::getApplication()->input->get("option");
    }

    public function display($tpl = null)
    {
        $this->state      = $this->get('State');
        $this->items      = $this->get('Items');
        $this->pagination = $this->get('Pagination');

        if (!empty($this->items)) {
            $ids = array();
            foreach ($this->items as $item) {
                $ids[] = $item->id;
            }

            Joomla\Utilities\ArrayHelper::toInteger($ids);
            $galleries = new MagicGallery\Gallery\Galleries(JFactory::getDbo());
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
        $this->saveOrder = (strcmp($this->listOrder, 'a.ordering') != 0) ? false : true;

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
        MagicGalleryHelper::addSubmenu($this->getName());
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
        JToolBarHelper::title(JText::_('COM_MAGICGALLERY_GALLERIES'));
        JToolBarHelper::addNew('gallery.add');
        JToolBarHelper::editList('gallery.edit');
        JToolBarHelper::divider();
        JToolBarHelper::publishList("galleries.publish");
        JToolBarHelper::unpublishList("galleries.unpublish");
        JToolBarHelper::divider();
        JToolBarHelper::deleteList(JText::_("COM_MAGICGALLERY_DELETE_ITEMS_QUESTION"), "galleries.delete");
        JToolBarHelper::divider();
        JToolBarHelper::custom('galleries.backToDashboard', "dashboard", "", JText::_("COM_MAGICGALLERY_DASHBOARD"), false);
    }

    /**
     * Method to set up the document properties
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
