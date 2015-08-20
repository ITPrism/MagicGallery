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

class MagicGalleryViewResource extends JViewLegacy
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
    protected $params;

    protected $state;
    protected $item;
    protected $form;

    protected $documentTitle;
    protected $option;

    protected $galleryId;

    public function __construct($config)
    {
        parent::__construct($config);

        $this->app    = JFactory::getApplication();
        $this->option = $this->app->input->get("option");
    }

    public function display($tpl = null)
    {
        $this->state = $this->get('State');
        $this->item  = $this->get('Item');
        $this->form  = $this->get('Form');

        $this->params = $this->state->get("params");

        $this->galleryId = (int)$this->app->getUserState("com_magicgallery.resources.filter.gallery_id");

        $this->addToolbar();
        $this->setDocument();

        parent::display($tpl);
    }

    /**
     * Add the page title and toolbar.
     *
     * @since   1.6
     */
    protected function addToolbar()
    {
        $this->app->input->set('hidemainmenu', true);

        $isNew               = ($this->item->id == 0);
        $this->documentTitle = $isNew ? JText::_('COM_MAGICGALLERY_RESOURCE_ADD') : JText::_('COM_MAGICGALLERY_RESOURCE_EDIT');

        JToolBarHelper::title($this->documentTitle);

        JToolBarHelper::apply('resource.apply');
        JToolBarHelper::save2new('resource.save2new');
        JToolBarHelper::save('resource.save');

        if (!$isNew) {
            JToolBarHelper::cancel('resource.cancel', 'JTOOLBAR_CANCEL');
        } else {
            JToolBarHelper::cancel('resource.cancel', 'JTOOLBAR_CLOSE');
        }
    }

    /**
     * Method to set up the document properties
     *
     * @return void
     */
    protected function setDocument()
    {
        $this->document->setTitle($this->documentTitle);

        // Load language string in JavaScript
        JText::script('COM_MAGICGALLERY_CHOOSE_FILE');
        JText::script('COM_MAGICGALLERY_REMOVE');

        // Script
        JHtml::_('behavior.tooltip');
        JHtml::_('behavior.keepalive');
        JHtml::_('behavior.formvalidation');

        JHtml::_('formbehavior.chosen', 'select');

        JHtml::_('Prism.ui.bootstrap2FileInput');

        $this->document->addScript('../media/' . $this->option . '/js/admin/' . Joomla\String\String::strtolower($this->getName()) . '.js');
    }
}
