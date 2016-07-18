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

/**
 * Main controller
 *
 * @package        Magicgallery
 * @subpackage     Components
 */
class MagicgalleryController extends JControllerLegacy
{
    public function display($cachable = false, $urlparams = false)
    {
        $document = JFactory::getDocument();
        /** @var $document JDocumentHtml */

        // Add component style
        $document->addStyleSheet('../media/com_magicgallery/css/backend.style.css');

        $viewName = $this->input->getCmd('view', 'dashboard');
        $this->input->set('view', $viewName);

        parent::display();

        return $this;
    }
}
