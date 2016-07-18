<?php
/**
 * @package      Magicgallery
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2016 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

/**
 * Magic Gallery Galleries Controller
 *
 * @package     Magicgallery
 * @subpackage  Components
 */
class MagicgalleryControllerGalleries extends Prism\Controller\Admin
{
    public function getModel($name = 'Gallery', $prefix = 'MagicgalleryModel', $config = array('ignore_request' => true))
    {
        $model = parent::getModel($name, $prefix, $config);
        return $model;
    }
}
