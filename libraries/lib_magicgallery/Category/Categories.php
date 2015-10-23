<?php
/**
 * @package      MagicGallery
 * @subpackage   Categories
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace Magicgallery\Category;

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
     * $categories = new Magicgallery\Category\Categories($options);
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
     * $categories   = new Magicgallery\Category\Categories();
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
     * $categories   = new Magicgallery\Category\Categories();
     * $categories->setDb(\JFactory::getDbo());
     *
     * $categories->load($parentId);
     * </code>
     *
     * @param array $options
     */
    public function load(array $options = array())
    {
        $offset    = (array_key_exists('offset', $options)) ? (int)$options['offset'] : 0;
        $limit     = (array_key_exists('limit', $options)) ? (int)$options['limit'] : 0;
        $orderBy   = (array_key_exists('order_by', $options)) ? $options['order_by'] : 'a.title';
        $orderDir  = (array_key_exists('order_dir', $options)) ? $options['order_dir'] : 'ASC';
        $parentId  = (array_key_exists('parent_id', $options)) ? (int)$options['parent_id'] : 0;

        $orderDir = \JString::strtoupper($orderDir);

        if (!in_array($orderDir, array('ASC', 'DESC'), true)) {
            $orderDir = 'ASC';
        }

        $query = $this->db->getQuery(true);
        $query
            ->select(
                'a.id, a.title, a.alias, a.description, a.params, ' .
                $query->concatenate(array('a.id', 'a.alias'), ':') . ' AS slug'
            )
            ->from($this->db->quoteName('#__categories', 'a'))
            ->where('a.extension = '. $this->db->quote($this->_extension));

        if ($parentId > 0) {
            $query->where('a.parent_id = '. (int)$parentId);
        }

        $query->order($this->db->quoteName($orderBy) . ' ' . $orderDir);

        $this->db->setQuery($query, (int)$offset, (int)$limit);

        $this->data = (array)$this->db->loadAssocList('id');
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
