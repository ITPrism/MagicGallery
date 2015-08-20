<?php
/**
 * @package      MagicGallery
 * @subpackage   Categories
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace MagicGallery\Category;

use Joomla\String\String;

defined('_JEXEC') or die;

/**
 * This class provide functionality for managing categories.
 *
 * @package         MagicGallery
 * @subpackage      Categories
 */
class Categories extends \JCategories
{
    /**
     * The property that contains categories.
     *
     * @var array
     */
    protected $data = array();

    /**
     * Database driver.
     *
     * @var \JDatabaseDriver
     */
    protected $db;

    /**
     * Initialize the object.
     *
     * <code>
     * $categories = new MagicGallery\Category\Categories($options);
     * </code>
     *
     * @param array  $options
     */
    public function __construct($options = array())
    {
        $options['table']     = '#__magicgallery_galleries';
        $options['extension'] = 'com_magicgallery';
        parent::__construct($options);
    }

    /**
     * Set database object.
     *
     * <code>
     * $categories   = new MagicGallery\Category\Categories();
     * $categories->setDb(\JFactory::getDbo());
     * </code>
     *
     * @param \JDatabaseDriver $db
     *
     * @return self
     */
    public function setDb(\JDatabaseDriver $db)
    {
        $this->db = $db;
        return $this;
    }

    /**
     * Load categories.
     *
     * <code>
     * $parentId = 2;
     *
     * $options = array(
     *    "offset" => 0,
     *    "limit" => 10,
     *    "order_by" => "a.name",
     *    "order_dir" => "DESC",
     * );
     *
     * $categories   = new MagicGallery\Category\Categories();
     * $categories->setDb(\JFactory::getDbo());
     *
     * $categories->load($parentId);
     * </code>
     *
     * @param null|int $parentId Parent ID or "root".
     * @param array $options
     */
    public function load($parentId = null, $options = array())
    {
        $offset    = (isset($options["offset"])) ? $options["offset"] : 0;
        $limit     = (isset($options["limit"])) ? $options["limit"] : 0;
        $orderBy   = (isset($options["order_by"])) ? $options["order_by"] : "a.title";
        $orderDir  = (isset($options["order_dir"])) ? $options["order_dir"] : "ASC";

        $orderDir = String::strtoupper($orderDir);

        if (!in_array($orderDir, array("ASC", "DESC"))) {
            $orderDir = "ASC";
        }

        $query = $this->db->getQuery(true);
        $query
            ->select(
                "a.id, a.title, a.alias, a.description, a.params, " .
                $query->concatenate(array("a.id", "a.alias"), ":") . " AS slug"
            )
            ->from($this->db->quoteName("#__categories", "a"))
            ->where("a.extension = ". $this->db->quote($this->_extension));

        if (!is_null($parentId)) {
            $query->where("a.parent_id = ". (int)$parentId);
        }

        $query->order($this->db->quoteName($orderBy) . " " . $orderDir);

        $this->db->setQuery($query, (int)$offset, (int)$limit);

        $this->data = (array)$this->db->loadAssocList("id");
    }

    /**
     * Return the elements as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return (array)$this->data;
    }
}
