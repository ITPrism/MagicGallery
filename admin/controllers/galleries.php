<?php
/**
 * @package      MagicGallery
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2014 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

/**
 * Magic Gallery Galleries Controller
 *
 * @package     MagicGallery
 * @subpackage  Components
 */
class MagicGalleryControllerGalleries extends Prism\Controller\Admin
{
    public function getModel($name = 'Gallery', $prefix = 'MagicGalleryModel', $config = array('ignore_request' => true))
    {
        $model = parent::getModel($name, $prefix, $config);
        return $model;
    }
}
