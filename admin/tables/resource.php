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

class MagicGalleryTableResource extends JTable
{
    /**
     * Initialize the object.
     *
     * @param JDatabaseDriver $db
     */
    public function __construct($db)
    {
        parent::__construct('#__magicgallery_resources', 'id', $db);
    }
}
