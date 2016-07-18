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

jimport("Prism.init");
jimport("Magicgallery.init");

// Get an instance of the controller
$controller = JControllerLegacy::getInstance("Magicgallery");

// Perform the request task
$controller->execute(JFactory::getApplication()->input->getCmd('task'));
$controller->redirect();
