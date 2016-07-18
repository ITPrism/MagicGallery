<?php
/**
 * @package         Magicgallery
 * @subpackage      Projects
 * @author          Todor Iliev
 * @copyright       Copyright (C) 2016 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license         http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

namespace Magicgallery\Gallery;

use Prism;
use Joomla\Utilities\ArrayHelper;
use Magicgallery\Entity;

defined('JPATH_PLATFORM') or die;

/**
 * This class provide functionality for managing projects.
 *
 * @package         Magicgallery
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
     *     "load_entities" => true,
     *     "entity_state" => Prism\Constants::PUBLISHED
     * );
     *
     * $galleries   = new Magicgallery\Gallery\Galleries(\JFactory::getDbo());
     * $galleries->load($options);
     *
     * foreach( $galleries as $gallery ) {
     *     echo $gallery["title"];
     * }
     * </code>
     *
     * @param array $options
     *
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function load(array $options = array())
    {
        $query = $this->db->getQuery(true);
        $query
            ->select('a.id, a.title, a.alias, a.description, a.url, a.catid, a.extension, a.object_id, a.published, a.ordering, a.user_id')
            ->from($this->db->quoteName('#__magicgallery_galleries', 'a'));

        // Filter by ID.
        $ids = ArrayHelper::getValue($options, 'ids', array(), 'array');
        ArrayHelper::toInteger($ids);

        if (count($ids) > 0) {
            $query->where('a.id IN (' . implode(',', $ids) . ')');
        }

        // Filter by category ID.
        $categoryId = ArrayHelper::getValue($options, 'category_id');
        if ($categoryId !== null) {
            if (is_array($categoryId)) {
                ArrayHelper::toInteger($categoryId);
                if (count($categoryId) > 0) {
                    $query->where('a.catid IN (' . implode(',', $categoryId) . ')');
                }
            } else {
                $query->where('a.catid = ' . (int)$categoryId);
            }
        }

        // Filter by state.
        $published = ArrayHelper::getValue($options, 'gallery_state');
        if ($published !== null and is_numeric($published)) {
            if ($published) {
                $query->where('a.published = 1');
            } else {
                $query->where('a.published = 0');
            }
        }

        $query->order('a.ordering');
        $this->db->setQuery($query);

        $this->items = (array)$this->db->loadAssocList();

        // Load the images.
        $loadEntities = ArrayHelper::getValue($options, 'load_entities', false, 'bool');
        if ($loadEntities) {
            $this->loadEntities($options);
        }

        // Create Gallery objects and set the images.
        foreach ($this->items as $key => $item) {
            $gallery = new Gallery();
            $gallery->bind($item);

            if ($loadEntities) {
                $resources = $this->getEntities($item['id']);
                if ($resources !== null) {
                    $gallery->setEntities($resources);
                }
            }

            $this->items[$key] = $gallery;
        }
    }

    /**
     * Return the number of items.
     *
     * <code>
     * $ids = array(1,2,3,4);
     *
     * $galleries   = new Magicgallery\Gallery\Galleries(\JFactory::getDbo());
     * $number = $galleries->countEntities($options);
     *
     * foreach($galleries as $id => $number) {
     *     echo $number;
     * }
     * </code>
     *
     * @param array $ids
     *
     * @throws \RuntimeException
     *
     * @return array
     */
    public function countEntities(array $ids = array())
    {
        // Get the ids from current galleries
        // if the IDs are not provided as parameter.
        if (count($ids) === 0) {
            $ids = $this->getKeys();
        }

        ArrayHelper::toInteger($ids);
        $result = array();

        if (count($ids) > 0) {
            $query = $this->db->getQuery(true);
            $query
                ->select('a.gallery_id, COUNT(*) AS number')
                ->from($this->db->quoteName('#__magicgallery_entities', 'a'))
                ->where('a.gallery_id IN (' . implode(',', $ids) . ')')
                ->group('a.gallery_id');

            $this->db->setQuery($query);

            $result = (array)$this->db->loadAssocList('gallery_id');
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
     *     "load_entities" => true
     * );
     *
     * $galleries   = new Magicgallery\Gallery\Galleries(\JFactory::getDbo());
     * $galleries->load($options);
     *
     * $galleryId = 1;
     *
     * $resources = $galleries->getEntities($galleryId);
     * </code>
     *
     * @param int $galleryId
     * @param array $options
     *
     * @return null|Entity\Entities
     */
    public function getEntities($galleryId, array $options = array())
    {
        $result = null;
        $galleryId = (int)$galleryId;

        // Load the images.
        if ($this->resources === null) {
            $this->loadEntities($options);
        }

        if (is_array($this->resources) and array_key_exists($galleryId, $this->resources)) {
            $result = $this->resources[$galleryId];
        }

        return $result;
    }
    
    protected function loadEntities(array $options = array())
    {
        $galleriesIds = $this->getKeys();

        if (count($galleriesIds) > 0) {
            $query = $this->db->getQuery(true);
            $query
                ->select('a.id, a.title, a.description, a.thumbnail, a.image, a.home, a.ordering, a.published, a.gallery_id')
                ->from($this->db->quoteName('#__magicgallery_entities', 'a'))
                ->where('a.gallery_id IN (' . implode(',', $galleriesIds) . ')');

            // Filter by state.
            $published = ArrayHelper::getValue($options, 'entity_state');
            if ($published !== null and is_numeric($published)) {
                if ($published) {
                    $query->where('a.published = '. (int)Prism\Constants::PUBLISHED);
                } else {
                    $query->where('a.published = ' . (int)Prism\Constants::UNPUBLISHED);
                }
            } else {
                $query->where('a.published IN (1,0)');
            }

            $query->order('a.ordering');
            $this->db->setQuery($query);

            $results = (array)$this->db->loadAssocList();

            $this->resources = array();

            // Split the resources by galleries.
            $galleryResources = array();
            foreach ($results as $value) {
                $galleryResources[$value['gallery_id']][] = $value;
            }

            foreach ($galleryResources as $key => $items) {
                $resources = new Entity\Entities(\JFactory::getDbo());
                $resources->setEntities($items);

                $this->resources[$key] = $resources;
            }

            unset($results, $galleryResources);
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
     *     "load_entities" => true
     * );
     *
     * $galleries   = new Magicgallery\Gallery\Galleries(\JFactory::getDbo());
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
     *     "load_entities" => true
     * );
     *
     * $galleries   = new Magicgallery\Gallery\Galleries(\JFactory::getDbo());
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
            if ((int)$galleryId === (int)$item->getId()) {
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
     *     "load_entities" => true
     * );
     *
     * $galleries   = new Magicgallery\Gallery\Galleries(\JFactory::getDbo());
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
     *     "load_entities" => true
     * );
     *
     * $galleries   = new Magicgallery\Gallery\Galleries(\JFactory::getDbo());
     * $galleries->load($options);
     *
     * if (!$galleries->provideEntities()) {
     * ...
     * }
     * </code>
     *
     * @return bool
     */
    public function provideEntities()
    {
        return (bool)(is_array($this->resources) and count($this->resources) > 0);
    }
}
