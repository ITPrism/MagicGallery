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

class MagicGalleryViewGallery extends JViewLegacy
{
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

    protected $extraImages;

    public function __construct($config)
    {
        parent::__construct($config);
        $this->option = JFactory::getApplication()->input->get("option");
    }

    public function display($tpl = null)
    {
        $this->state = $this->get('State');
        $this->item  = $this->get('Item');
        $this->form  = $this->get('Form');

        $this->params = $this->state->get("params");

        $this->addToolbar();
        $this->setDocument();

        parent::display($tpl);
    }

    protected function addToolbar()
    {
        JFactory::getApplication()->input->set('hidemainmenu', true);

        $isNew               = ($this->item->id == 0);
        $this->documentTitle = $isNew ? JText::_('COM_MAGICGALLERY_GALLERY_ADD') : JText::_('COM_MAGICGALLERY_GALLERY_EDIT');

        JToolBarHelper::title($this->documentTitle);

        JToolBarHelper::apply('gallery.apply');
        JToolBarHelper::save2new('gallery.save2new');
        JToolBarHelper::save('gallery.save');

        if (!$isNew) {
            JToolBarHelper::cancel('gallery.cancel', 'JTOOLBAR_CANCEL');
        } else {
            JToolBarHelper::cancel('gallery.cancel', 'JTOOLBAR_CLOSE');
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

        // Script
        JHtml::_('behavior.tooltip');
        JHtml::_('behavior.keepalive');
        JHtml::_('behavior.formvalidation');

        JHtml::_('formbehavior.chosen', 'select');

        $this->document->addScript('../media/' . $this->option . '/js/admin/' . Joomla\String\String::strtolower($this->getName()) . '.js');

    }
}
