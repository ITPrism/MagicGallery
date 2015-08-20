<?php
/**
 * @package         MagicGallery
 * @subpackage      Projects
 * @author          Todor Iliev
 * @copyright       Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license         http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

namespace MagicGallery\Gallery;

use Prism;
use Joomla\Utilities\ArrayHelper;
use MagicGallery\Resource\Resources;

defined('JPATH_PLATFORM') or die;

/**
 * This class provide functionality for managing projects.
 *
 * @package         MagicGallery
 * @subpackage      Projects
 */
class Galleries extends Prism\Database\ArrayObject
{
    protected $resources;

    /**
     * Load the galleries.
     *
     * <code>
     * $options  = array(
     *     "ids" => array(1,2,3,4),
     *     "category_id" => array(1,2,3,4,5), // Can be integer or array that contains IDs.
     *     "gallery_state"   => Prism\Constants::PUBLISHED,
     *     "load_resources" => true,
     *     "resource_state" => Prism\Constants::PUBLISHED
     * );
     *
     * $galleries   = new MagicGallery\Gallery\Galleries(\JFactory::getDbo());
     * $galleries->load($options);
     *
     * foreach( $galleries as $gallery ) {
     *     echo $gallery["title"];
     * }
     * </code>
     *
     * @param array $options
     */
    public function load($options = array())
    {
        $query = $this->db->getQuery(true);
        $query
            ->select("a.id, a.title, a.alias, a.description, a.url, a.catid, a.extension, a.object_id, a.published, a.ordering, a.user_id")
            ->from($this->db->quoteName("#__magicgallery_galleries", "a"));

        // Filter by ID.
        $ids = ArrayHelper::getValue($options, "ids", array(), "array");
        ArrayHelper::toInteger($ids);

        if (!empty($ids)) {
            $query->where("a.id IN (" . implode(",", $ids) . ")");
        }

        // Filter by category ID.
        $categoryId = ArrayHelper::getValue($options, "category_id");
        if (!empty($categoryId)) {

            if (is_array($categoryId)) {
                ArrayHelper::toInteger($categoryId);
                if (!empty($categoryId)) {
                    $query->where("a.catid IN (" . implode(",", $categoryId) . ")");
                }
            } else {
                $query->where("a.catid = " . (int)$categoryId);
            }
        }

        // Filter by state.
        $published = ArrayHelper::getValue($options, "gallery_state");
        if (!is_null($published)) {
            if ($published) {
                $query->where("a.published = 1");
            } else {
                $query->where("a.published = 0");
            }
        }

        $query->order("a.ordering");
        $this->db->setQuery($query);

        $this->items = (array)$this->db->loadAssocList();

        // Load the images.
        $loadResources = ArrayHelper::getValue($options, "load_resources", false, "bool");
        if ($loadResources) {
            $this->loadResources($options);
        }

        // Create Gallery objects and set the images.
        foreach ($this->items as $key => $item) {
            $gallery = new Gallery();
            $gallery->bind($item);

            if ($loadResources) {
                $resources = $this->getResources($item["id"]);
                if (!is_null($resources)) {
                    $gallery->setResources($resources);
                }
            }

            $this->items[$key] = $gallery;
        }

    }

    /**
     * Count the resources.
     *
     * <code>
     * $ids = array(1,2,3,4);
     *
     * $galleries   = new MagicGallery\Gallery\Galleries(\JFactory::getDbo());
     * $number = $galleries->countResources($options);
     *
     * foreach($galleries as $id => $number) {
     *     echo $number;
     * }
     * </code>
     *
     * @param array $ids
     *
     * @return array
     */
    public function countResources($ids = array())
    {
        // Get the ids from current galleries
        // if the IDs are not provided as parameter.
        if (!$ids and !empty($this->items)) {
            $ids = $this->getKeys();
        }

        ArrayHelper::toInteger($ids);

        $result = array();

        if (!empty($ids)) {
            $query = $this->db->getQuery(true);
            $query
                ->select("a.gallery_id, COUNT(*) AS number")
                ->from($this->db->quoteName("#__magicgallery_resources", "a"))
                ->where("a.gallery_id IN (" . implode(",", $ids) . ")")
                ->group("a.gallery_id");

            $this->db->setQuery($query);

            $result = (array)$this->db->loadAssocList("gallery_id");
        }

        return $result;
    }

    /**
     * Return the resources for a gallery.
     *
     * <code>
     * $options  = array(
     *     "ids" => array(1,2,3,4),
     *     "category_id" => 1, // Can be integer or array that contains IDs.
     *     "gallery_state"   => Prism\Constants::PUBLISHED,
     *     "load_resources" => true
     * );
     *
     * $galleries   = new MagicGallery\Gallery\Galleries(\JFactory::getDbo());
     * $galleries->load($options);
     *
     * $galleryId = 1;
     *
     * $resources = $galleries->getResources($galleryId);
     * </code>
     *
     * @param int $galleryId
     * @param array $options
     *
     * @return null|Resources
     */
    public function getResources($galleryId, $options = array())
    {
        $result = null;

        // Load the images.
        if (is_null($this->resources)) {
            $this->loadResources($options);
        }

        if (isset($this->resources[$galleryId])) {
            $result = $this->resources[$galleryId];
        }

        return $result;
    }
    
    protected function loadResources($options = array())
    {
        $galleriesIds = $this->getKeys();

        if (!empty($galleriesIds)) {
            $query = $this->db->getQuery(true);
            $query
                ->select("a.id, a.title, a.description, a.thumbnail, a.image, a.home, a.ordering, a.published, a.gallery_id")
                ->from($this->db->quoteName("#__magicgallery_resources", "a"))
                ->where("a.gallery_id IN (" . implode(",", $galleriesIds) . ")");

            // Filter by state.
            $published = ArrayHelper::getValue($options, "resource_state");
            if (!is_null($published)) {
                if ($published) {
                    $query->where("a.published = ". (int)Prism\Constants::PUBLISHED);
                } else {
                    $query->where("a.published = " . (int)Prism\Constants::UNPUBLISHED);
                }
            } else {
                $query->where("a.published IN (1,0)");
            }

            $query->order("a.ordering");
            $this->db->setQuery($query);

            $results = (array)$this->db->loadAssocList();

            $this->resources = array();

            // Split the resources by galleries.
            $galleryResources = array();
            foreach ($results as $value) {
                $galleryResources[$value["gallery_id"]][] = $value;
            }

            foreach ($galleryResources as $key => $items) {
                $resources = new Resources(\JFactory::getDbo());
                $resources->setItems($items);

                $this->resources[$key] = $resources;
            }

            unset($results);
            unset($galleryResources);
        }

    }

    /**
     * Return first gallery from the list of items.
     *
     * <code>
     * $options  = array(
     *     "ids" => array(1,2,3,4),
     *     "category_id" => 1, // Can be integer or array that contains IDs.
     *     "gallery_state"   => Prism\Constants::PUBLISHED,
     *     "load_resources" => true
     * );
     *
     * $galleries   = new MagicGallery\Gallery\Galleries(\JFactory::getDbo());
     * $galleries->load($options);
     *
     * $galleryId = 1;
     *
     * $resources = $galleries->getFirst($galleryId);
     * </code>
     *
     * @return null|Gallery
     */
    public function getFirst()
    {
        $gallery = reset($this->items);

        return (!$gallery) ? null : $gallery;
    }

    /**
     * Return gallery from the list of items.
     *
     * <code>
     * $options  = array(
     *     "ids" => array(1,2,3,4),
     *     "category_id" => 1, // Can be integer or array that contains IDs.
     *     "gallery_state"   => Prism\Constants::PUBLISHED,
     *     "load_resources" => true
     * );
     *
     * $galleries   = new MagicGallery\Gallery\Galleries(\JFactory::getDbo());
     * $galleries->load($options);
     *
     * $galleryId = 1;
     *
     * $gallery = $galleries->getGallery($galleryId);
     * </code>
     *
     * @param int $galleryId
     * @return null|Gallery
     */
    public function getGallery($galleryId)
    {
        $gallery = null;

        /** @var Gallery $item */
        foreach ($this->items as $item) {
            if ($galleryId == $item->getId()) {
                $gallery = $item;
                break;
            }
        }

        return $gallery;
    }

    /**
     * Return the items as array.
     *
     * <code>
     * $options  = array(
     *     "ids" => array(1,2,3,4),
     *     "category_id" => 1, // Can be integer or array that contains IDs.
     *     "gallery_state"   => Prism\Constants::PUBLISHED,
     *     "load_resources" => true
     * );
     *
     * $galleries   = new MagicGallery\Gallery\Galleries(\JFactory::getDbo());
     * $galleries->load($options);
     *
     * $galleriesAsArray = $galleries->toArray();
     * </code>
     *
     * @return array
     */
    public function toArray()
    {
        $galleries = array();

        /** @var Gallery $item */
        foreach ($this->items as $item) {
            $galleries[] = $item->toArray();
        }

        return $galleries;
    }

    /**
     * Check for available resources.
     *
     * <code>
     * $options  = array(
     *     "ids" => array(1,2,3,4),
     *     "category_id" => 1, // Can be integer or array that contains IDs.
     *     "gallery_state"   => Prism\Constants::PUBLISHED,
     *     "load_resources" => true
     * );
     *
     * $galleries   = new MagicGallery\Gallery\Galleries(\JFactory::getDbo());
     * $galleries->load($options);
     *
     * if (!$galleries->provideResources()) {
     * ...
     * }
     * </code>
     *
     * @return array
     */
    public function provideResources()
    {
        return (!empty($this->resources)) ? true : false;
    }
}
