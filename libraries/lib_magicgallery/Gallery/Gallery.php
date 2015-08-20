<?php
/**
 * @package      MagicGallery
 * @subpackage   Galleries
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace MagicGallery\Gallery;

use Prism;
use Joomla\Utilities\ArrayHelper;
use MagicGallery\Resource\Resources;
use MagicGallery\Resource\Resource;

defined('JPATH_PLATFORM') or die;

/**
 * This class contains methods that are used for managing a gallery.
 *
 * @package      MagicGallery
 * @subpackage   Galleries
 */
class Gallery extends Prism\Database\TableImmutable
{
    protected $id;
    protected $title;
    protected $alias;
    protected $description;
    protected $url;
    protected $catid;
    protected $extension;
    protected $object_id;
    protected $published;
    protected $ordering;
    protected $user_id;
    protected $slug;
    protected $catslug;

    protected $resources;

    /**
     * Load gallery data from database.
     *
     * <code>
     * $galleryId = 1;
     *
     * $options = array(
     *     "load_resources" => true,
     *     "resource_state" => Prism\Constants::PUBLISHED
     * );
     *
     * $gallery   = new MagicGallery\Gallery(\JFactory::getDbo());
     * $gallery->load($galleryId);
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
                "a.id, a.title, a.alias, a.description, a.url, a.catid, " .
                "a.extension, a.object_id, a.published, a.ordering, a.user_id, " .
                $query->concatenate(array("a.id", "a.alias"), ":") . " AS slug," .
                $query->concatenate(array("b.id", "b.alias"), ":") . " AS catslug"
            )
            ->from($this->db->quoteName("#__magicgallery_galleries", "a"))
            ->leftJoin($this->db->quoteName("#__categories", "b") . " ON a.catid = b.id");

        if (is_array($keys)) {
            foreach ($keys as $key => $value) {
                $query->where($this->db->quoteName($key) ." = " . $this->db->quote($value));
            }
        } else {
            $query->where("a.id = " . (int)$keys);
        }

        $this->db->setQuery($query);
        $result = (array)$this->db->loadAssoc();

        $this->bind($result);

        // Load the resources.
        $loadResources = ArrayHelper::getValue($options, "load_resources", false, "bool");
        if ($loadResources) {

            $resourceState = ArrayHelper::getValue($options, "resource_state", Prism\Constants::PUBLISHED, "int");

            $option = array(
                "gallery_id" => (int)$this->id,
                "published"  => $resourceState
            );

            $this->resources = new Resources(\JFactory::getDbo());
            $this->resources->load($option);
        }
    }

    /**
     * Return gallery ID.
     *
     * <code>
     * $keys = array(
     *     "extension" => "com_crowdfunding",
     *     "object_id" => 1
     * );
     *
     * $gallery    = new MagicGallery\Gallery(\JFactory::getDbo());
     * $gallery->load($keys);
     *
     * if (!$gallery->getId()) {
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
     * Return gallery title.
     *
     * <code>
     * $keys = array(
     *    "extension" => "com_crowdfunding",
     *    "object_id" => 1
     * );
     *
     * $gallery   = new MagicGallery\Gallery(\JFactory::getDbo());
     * $gallery->load($keys);
     *
     * echo  $gallery->getTitle();
     * </code>
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Return gallery alias.
     *
     * <code>
     * $galleryId = 1;
     *
     * $gallery   = new MagicGallery\Gallery(\JFactory::getDbo());
     * $gallery->load($galleryId);
     *
     * echo $gallery->getAlias();
     * </code>
     *
     * @return string
     */
    public function getAlias()
    {
        return $this->alias;
    }

    /**
     * Return gallery slug.
     *
     * <code>
     * $galleryId = 1;
     *
     * $gallery   = new MagicGallery\Gallery(\JFactory::getDbo());
     * $gallery->load($galleryId);
     *
     * echo $gallery->getSlug();
     * </code>
     *
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * Return category slug.
     *
     * <code>
     * $galleryId = 1;
     *
     * $gallery   = new MagicGallery\Gallery(\JFactory::getDbo());
     * $gallery->load($galleryId);
     *
     * echo $gallery->getCatSlug();
     * </code>
     *
     * @return string
     */
    public function getCatSlug()
    {
        return $this->catslug;
    }

    /**
     * Return gallery description.
     *
     * <code>
     * $galleryId = 1;
     *
     * $gallery   = new MagicGallery\Gallery(\JFactory::getDbo());
     * $gallery->load($galleryId);
     *
     * echo $gallery->getDescription();
     * </code>
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Return URL.
     *
     * <code>
     * $galleryId = 1;
     *
     * $gallery   = new MagicGallery\Gallery(\JFactory::getDbo());
     * $gallery->load($galleryId);
     *
     * $url = $gallery->getUrl();
     * </code>
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Return category ID.
     *
     * <code>
     * $keys = array(
     *    "extension" => "com_crowdfunding",
     *    "object_id" => 1
     * );
     *
     * $gallery   = new MagicGallery\Gallery(\JFactory::getDbo());
     * $gallery->load($keys);
     *
     * $categoryId = $gallery->getCategoryId();
     * </code>
     *
     * @return string
     */
    public function getCategoryId()
    {
        return $this->catid;
    }

    /**
     * Return extension name.
     *
     * <code>
     * $galleryId = 1;
     *
     * $gallery   = new MagicGallery\Gallery(\JFactory::getDbo());
     * $gallery->load($galleryId);
     *
     * echo $gallery->getExtension();
     * </code>
     *
     * @return string
     */
    public function getExtension()
    {
        return $this->extension;
    }

    /**
     * Return object ID.
     *
     * <code>
     * $galleryId = 1;
     *
     * $gallery   = new MagicGallery\Gallery(\JFactory::getDbo());
     * $gallery->load($galleryId);
     *
     * $objectId = $gallery->getObjectId();
     * </code>
     *
     * @return int
     */
    public function getObjectId()
    {
        return (int)$this->object_id;
    }

    /**
     * Return user ID.
     *
     * <code>
     * $galleryId = 1;
     *
     * $gallery   = new MagicGallery\Gallery(\JFactory::getDbo());
     * $gallery->load($galleryId);
     *
     * $userId = $gallery->getUserId();
     * </code>
     *
     * @return int
     */
    public function getUserId()
    {
        return (int)$this->user_id;
    }

    /**
     * Return ordering number.
     *
     * <code>
     * $galleryId = 1;
     *
     * $gallery   = new MagicGallery\Gallery(\JFactory::getDbo());
     * $gallery->load($galleryId);
     *
     * $ordering = $gallery->getOrdering();
     * </code>
     *
     * @return int
     */
    public function getOrdering()
    {
        return (int)$this->ordering;
    }

    /**
     * Check if the item is published.
     *
     * <code>
     * $galleryId = 1;
     *
     * $gallery   = new MagicGallery\Image(\JFactory::getDbo());
     * $gallery->load($galleryId);
     *
     * if ($gallery->isPublished()) {
     * ...
     * }
     * </code>
     *
     * @return bool
     */
    public function isPublished()
    {
        return ($this->published == Prism\Constants::PUBLISHED) ? true : false;
    }

    /**
     * Return default resource.
     *
     * <code>
     * $galleryId = 1;
     *
     * $gallery   = new MagicGallery\Gallery(\JFactory::getDbo());
     * $gallery->load($galleryId);
     *
     * $image = $gallery->getDefaultResource();
     * </code>
     *
     * @return Resource
     */
    public function getDefaultResource()
    {
        if (is_null($this->resources)) {
            $option = array(
                "gallery_id" => (int)$this->id,
                "published"  => Prism\Constants::PUBLISHED
            );

            $this->resources = new Resources(\JFactory::getDbo());
            $this->resources->load($option);
        }

        return $this->resources->getDefaultResource();
    }

    /**
     * Return the resources provided by the gallery.
     *
     * <code>
     * $galleryId = 1;
     *
     * $gallery   = new MagicGallery\Gallery(\JFactory::getDbo());
     * $gallery->load($galleryId);
     *
     * $resources = $gallery->getResources();
     * </code>
     *
     * @return Resources
     */
    public function getResources()
    {
        if (is_null($this->resources)) {
            $option = array(
                "gallery_id" => (int)$this->id,
                "published"  => Prism\Constants::PUBLISHED
            );

            $this->resources = new Resources(\JFactory::getDbo());
            $this->resources->load($option);
        }

        return $this->resources;
    }

    /**
     * Set the resources to the gallery.
     *
     * <code>
     * $resources = array(...);
     * $galleryId = 1;
     *
     * $gallery   = new MagicGallery\Gallery(\JFactory::getDbo());
     * $gallery->load($galleryId);
     *
     * $gallery->setResources($resources);
     * </code>
     *
     * @param Resources $resources
     *
     * @return self
     */
    public function setResources(Resources $resources)
    {
        $this->resources = $resources;

        return $this;
    }

    /**
     * Return the object properties as array.
     *
     * <code>
     * $resources = array(...);
     * $galleryId = 1;
     *
     * $gallery   = new MagicGallery\Gallery(\JFactory::getDbo());
     * $gallery->load($galleryId);
     *
     * $galleryAsArray = $gallery->toArray();
     * </code>
     *
     * @return array
     */
    public function toArray()
    {
        $gallery = $this->getProperties();

        $resources = array();

        /** @var Resource $resource */
        foreach ($this->resources as $resource) {
            $resources[] = $resource->toArray();
        }

        $gallery["resources"] = $resources;

        return $gallery;
    }
}
