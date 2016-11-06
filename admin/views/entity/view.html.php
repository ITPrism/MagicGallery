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

class MagicgalleryViewEntity extends JViewLegacy
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
    protected $gallery;
    protected $mediaUri;

    public function display($tpl = null)
    {
        $this->app    = JFactory::getApplication();
        $this->option = $this->app->input->get('option');
        
        $this->state = $this->get('State');
        $this->item  = $this->get('Item');

        if (!$this->item) {
            $this->app->redirect(JRoute::_('index.php?option=com_magicgallery&view=galleries', false));
            return;
        }

        $this->form       = $this->get('Form');
        $this->params     = $this->state->get('params');

        $this->galleryId  = (int)$this->app->getUserState('com_magicgallery.entities.filter.gallery_id');

        $this->gallery    = new Magicgallery\Gallery\Gallery(JFactory::getDbo());
        $this->gallery->load($this->galleryId);

        $filesystemHelper = new Prism\Filesystem\Helper($this->params);
        $pathHelper       = new Magicgallery\Helper\Path($filesystemHelper);

        $this->mediaUri   = $pathHelper->getMediaUri($this->gallery);
        if (!$this->mediaUri) {
            throw new Exception(JText::_('COM_MAGICGALLERY_ERROR_INVALID_MEDIA_FOLDER'));
        }

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

        $isNew               = ((int)$this->item->id === 0);
        $this->documentTitle = $isNew ? JText::_('COM_MAGICGALLERY_RESOURCE_ADD') : JText::_('COM_MAGICGALLERY_RESOURCE_EDIT');

        JToolbarHelper::title($this->documentTitle);

        JToolbarHelper::apply('entity.apply');
        JToolbarHelper::save2new('entity.save2new');
        JToolbarHelper::save('entity.save');

        if (!$isNew) {
            JToolbarHelper::cancel('entity.cancel', 'JTOOLBAR_CANCEL');
        } else {
            JToolbarHelper::cancel('entity.cancel', 'JTOOLBAR_CLOSE');
        }
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

        $this->document->addScript('../media/' . $this->option . '/js/admin/' . strtolower($this->getName()) . '.js');
    }
}
