<?php
/**
 * @package      MagicGallery
 * @subpackage   Categories
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace MagicGallery\Category;

use Prism;

defined('JPATH_PLATFORM') or die;

/**
 * This class contains methods that are used for managing a category.
 *
 * @package      MagicGallery
 * @subpackage   Categories
 */
class Category extends Prism\Database\TableImmutable
{
    protected $id;
    protected $title;
    protected $alias;
    protected $slug;
    protected $description;
    protected $published;
    protected $params;
    protected $metadesc;
    protected $metakey;
    protected $image;

    /**
     * Load category data from database.
     *
     * <code>
     * $categoryId = 1;
     *
     * $category   = new MagicGallery\Category\Category(\JFactory::getDbo());
     * $category->load($categoryId);
     * </code>
     *
     * @param int|array $keys
     * @param array $options
     */
    public function load($keys, $options = array())
    {
        $query = $this->db->getQuery(true);

        $query
            ->select(
                "a.id, a.title, a.alias, a.description, a.published, a.params, a.metadesc, a.metakey, " .
                $query->concatenate(array("id", "alias"), ":") . " AS slug"
            )
            ->from($this->db->quoteName("#__categories", "a"));

        if (is_array($keys)) {
            foreach ($keys as $key => $value) {
                $query->where($this->db->quoteName($key) ." = " . $this->db->quote($value));
            }
        } else {
            $query->where("a.id = " . (int)$keys);
        }

        $this->db->setQuery($query);
        $result = (array)$this->db->loadAssoc();

        // Decode params and set the image.
        if (!empty($result["params"])) {
            $this->params = json_decode($result["params"], true);

            if (!empty($this->params["image"])) {
                $this->image = $this->params["image"];
            }
        }

        $this->bind($result, array("params"));
    }

    /**
     * Return category ID.
     *
     * <code>
     * $categoryId  = 1;
     *
     * $category    = new MagicGallery\Category\Category(\JFactory::getDbo());
     * $category->load($typeId);
     *
     * if (!$category->getId()) {
     * ....
     * }
     * </code>
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Return category title.
     *
     * <code>
     * $categoryId = 1;
     *
     * $category   = new MagicGallery\Category\Category(\JFactory::getDbo());
     * $category->load($categoryId);
     *
     * $title = $category->getTitle();
     * </code>
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Return category description.
     *
     * <code>
     * $categoryId = 1;
     *
     * $category   = new MagicGallery\Category\Category(\JFactory::getDbo());
     * $category->load($categoryId);
     *
     * $description = $category->getDescription();
     * </code>
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Return category alias.
     *
     * <code>
     * $categoryId = 1;
     *
     * $category   = new MagicGallery\Category\Category(\JFactory::getDbo());
     * $category->load($categoryId);
     *
     * $alias = $category->getAlias();
     * </code>
     *
     * @return string
     */
    public function getAlias()
    {
        return $this->alias;
    }

    /**
     * Return category slug.
     *
     * <code>
     * $categoryId = 1;
     *
     * $category   = new MagicGallery\Category\Category(\JFactory::getDbo());
     * $category->load($categoryId);
     *
     * $slug = $category->getSlug();
     * </code>
     *
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * Return category meta description.
     *
     * <code>
     * $categoryId = 1;
     *
     * $category   = new MagicGallery\Category\Category(\JFactory::getDbo());
     * $category->load($categoryId);
     *
     * echo $category->getMetaDescription();
     * </code>
     *
     * @return string
     */
    public function getMetaDescription()
    {
        return $this->metadesc;
    }

    /**
     * Return category meta keywords.
     *
     * <code>
     * $categoryId = 1;
     *
     * $category   = new MagicGallery\Category\Category(\JFactory::getDbo());
     * $category->load($categoryId);
     *
     * echo $category->getMetaKeywords();
     * </code>
     *
     * @return string
     */
    public function getMetaKeywords()
    {
        return $this->metakey;
    }

    /**
     * Return category image.
     *
     * <code>
     * $categoryId = 1;
     *
     * $category   = new MagicGallery\Category\Category(\JFactory::getDbo());
     * $category->load($categoryId);
     *
     * $image = $category->getImage();
     * </code>
     *
     * @return string
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * Check if it is published.
     *
     * <code>
     * $categoryId = 1;
     *
     * $category   = new MagicGallery\Category\Category(\JFactory::getDbo());
     * $category->load($categoryId);
     *
     * if ($category->isPublished()) {
     * ...
     * }
     * </code>
     *
     * @return bool
     */
    public function isPublished()
    {
        return (!$this->published) ? false : true;
    }

    /**
     * Return category parameter.
     *
     * <code>
     * $categoryId = 1;
     *
     * $category   = new MagicGallery\Category\Category(\JFactory::getDbo());
     * $category->load($categoryId);
     *
     * echo $category->getParam("image");
     * </code>
     *
     * @param string $key
     * @param mixed $default
     *
     * @return mixed
     */
    public function getParam($key, $default = null)
    {
        if (isset($this->params[$key])) {
            return $this->params[$key];
        }

        return $default;
    }
}
