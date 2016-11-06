<?php
/**
 * @package         Magicgallery
 * @subpackage      Projects
 * @author          Todor Iliev
 * @copyright       Copyright (C) 2016 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license         http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

namespace Magicgallery\Gallery;

use Joomla\Utilities\ArrayHelper;
use Prism\Database\Collection;
use Magicgallery\Entity\Entities;

defined('JPATH_PLATFORM') or die;

/**
 * This class provide functionality for managing projects.
 *
 * @package         Magicgallery
 * @subpackage      Projects
 */
class Galleries extends Collection
{
    /**
     * Load the galleries.
     *
     * <code>
     * $options  = array(
     *     "ids"            => array(1,2,3,4),
     *     "category_id"    => array(1,2,3,4,5), // Can be integer or array that contains IDs.
     *     "gallery_state"  => Prism\Constants::PUBLISHED,
     *     "load_resources" => true,
     *     "resource_state" => Prism\Constants::PUBLISHED
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
            ->select('a.id, a.title, a.alias, a.description, a.url, a.catid, a.extension, a.object_id, a.published, a.ordering, a.user_id, a.params')
            ->from($this->db->quoteName('#__magicgallery_galleries', 'a'));

        // Filter by ID.
        $ids = $this->getOptionIds($options);
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
            $published = $published ? 1 : 0;
            $query->where('a.published = ' . (int)$published);
        } else {
            $query->where('a.published = (0,1)');
        }

        $query->order('a.ordering');

        $start = $this->getOptionStart($options);
        $limit = $this->getOptionLimit($options);
        $this->db->setQuery($query, $start, $limit);

        $this->items = (array)$this->db->loadObjectList();
        $this->prepareParameters();

        // Load the images.
        $loadResources = ArrayHelper::getValue($options, 'load_resources', false, 'bool');
        if ($loadResources) {
            $this->loadResources($options);
        }
    }

    /**
     * Return the number of items.
     *
     * <code>
     * $ids         = array(1,2,3,4);
     *
     * $galleries   = new Magicgallery\Gallery\Galleries(\JFactory::getDbo());
     * $number      = $galleries->countResources($options);
     *
     * foreach($galleries as $id => $number) {
     *     echo $number;
     * }
     * </code>
     *
     * @param array $galleriesIds
     *
     * @throws \RuntimeException
     *
     * @return array
     */
    public function countResources(array $galleriesIds = array())
    {
        // Get the ids from current galleries
        // if the IDs are not provided as parameter.
        if (count($galleriesIds) === 0) {
            $galleriesIds = $this->getValues('id');
        }

        $result = array();

        $galleriesIds = ArrayHelper::toInteger($galleriesIds);
        if (count($galleriesIds) > 0) {
            $query = $this->db->getQuery(true);
            $query
                ->select('a.gallery_id, COUNT(*) AS number')
                ->from($this->db->quoteName('#__magicgallery_entities', 'a'))
                ->where('a.gallery_id IN (' . implode(',', $galleriesIds) . ')')
                ->group('a.gallery_id');

            $this->db->setQuery($query);
            $result = (array)$this->db->loadAssocList('gallery_id');
        }

        return $result;
    }
    
    protected function loadResources(array $options = array())
    {
        $galleriesIds = $this->getValues('id');
        $galleriesIds = ArrayHelper::toInteger($galleriesIds);

        if (count($galleriesIds) > 0) {
            $published = ArrayHelper::getValue($options, 'resource_state');
            $options = array(
                'gallery_id' => $galleriesIds,
                'state'      => $published
            );
            
            $resources = new Entities($this->db);
            $resources->load($options);

            // Set the resources to the galleries.
            $resources_ = array();
            foreach ($this->items as $item) {
                $entities   = new Entities();
                $resources_ = $resources->findAll($item->id, 'gallery_id');

                $entities->setItems($resources_);

                $item->entities = $entities;
            }

            unset($resources, $resources_, $galleriesIds);
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
     * $galleries   = new Magicgallery\Gallery\Galleries(\JFactory::getDbo());
     * $galleries->load($options);
     *
     * $galleryId = 1;
     *
     * $resources = $galleries->getFirst($galleryId);
     * </code>
     *
     * @return null|\stdClass
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

        foreach ($this->items as $item) {
            if ((int)$galleryId === (int)$item['id']) {
                $gallery = new Gallery($this->db);
                $gallery->bind($item);
                break;
            }
        }

        return $gallery;
    }

    /**
     * Get an array with objects Gallery.
     *
     * <code>
     * $items    = new Magicgallery\Gallery\Galleries(\JFactory::getDbo());
     *
     * $items->getGalleries('catid');
     * </code>
     *
     * @param string $key
     *
     * @return array
     */
    public function getGalleries($key = '')
    {
        $items = array();
        
        foreach ($this->items as $item) {
            $gallery = new Gallery($this->db);
            $gallery->bind($item);

            if ($key !== '' and property_exists($item, $key)) {
                $index           = (string)$item->$key;
                $items[$index][] = $gallery;
            } else {
                $items[] = $gallery;
            }
        }

        return $items;
    }
}
