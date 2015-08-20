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

/**
 * Main controller
 *
 * @package        MagicGallery
 * @subpackage     Components
 */
class MagicGalleryController extends JControllerLegacy
{
    protected $option;

    public function __construct($config = array())
    {
        parent::__construct($config);
        $this->option = JFactory::getApplication()->input->getCmd("option");
    }

    public function display($cachable = false, $urlparams = false)
    {
        $document = JFactory::getDocument();
        /** @var $document JDocumentHtml */

        // Add component style
        $document->addStyleSheet('../media/' . $this->option . '/css/backend.style.css');

        $viewName = JFactory::getApplication()->input->getCmd('view', 'dashboard');
        JFactory::getApplication()->input->set("view", $viewName);

        parent::display();

        return $this;
    }
}
