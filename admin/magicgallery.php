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

jimport("Prism.init");
jimport("MagicGallery.init");

// Get an instance of the controller
$controller = JControllerLegacy::getInstance("MagicGallery");

// Perform the request task
$controller->execute(JFactory::getApplication()->input->getCmd('task'));
$controller->redirect();
