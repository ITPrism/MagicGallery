<?php
/**
 * @package         Magicgallery
 * @subpackage      Resources
 * @author          Todor Iliev
 * @copyright       Copyright (C) 2016 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license         http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

namespace Magicgallery\Entity;

use Prism\Database\Collection;
use Joomla\Utilities\ArrayHelper;

defined('JPATH_PLATFORM') or die;

/**
 * This class provide functionality for managing entities.
 *
 * @package         Magicgallery
 * @subpackage      Entities
 */
class Entities extends Collection
{
    /**
     * Load the resources.
     *
     * <code>
     * $options  = array(
     *     "ids" => array(1,2,3,4),
     *     "gallery_id" => 1, // Can be integer or array that contains IDs.
     *     "state" => Prism\Constants::PUBLISHED
     * );
     *
     * $items   = new Magicgallery\Entity\Entities(\JFactory::getDbo());
     * $items->load($options);
     *
     * foreach($items as $item) {
     *     echo $item["title"];
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
            ->select(
                'a.id, a.title, a.description, a.image, a.thumbnail, a.image_meta, a.thumbnail_meta, ' .
                'a.home, a.ordering, a.published, a.gallery_id, a.image_filesize, a.thumbnail_filesize'
            )
            ->from($this->db->quoteName('#__magicgallery_entities', 'a'));

        // Filter by ID.
        $ids = $this->getOptionIds($options);
        if (count($ids) > 0) {
            $query->where('a.id IN (' . implode(',', $ids) . ')');
        }

        // Filter by gallery IDs.
        $galleryId = ArrayHelper::getValue($options, 'gallery_id');
        if ($galleryId !== null) {
            if (is_array($galleryId)) {
                $galleryId = ArrayHelper::toInteger($galleryId);
                if (count($galleryId) > 0) {
                    $query->where('a.gallery_id IN (' . implode(',', $galleryId) . ')');
                }
            } else {
                $query->where('a.gallery_id = ' . (int)$galleryId);
            }
        }

        // Filter by state.
        $published = ArrayHelper::getValue($options, 'state');
        if ($published !== null and is_numeric($published)) {
            $query->where('a.published = ' . (int)$published);
        } else {
            $query->where('a.published IN (0, 1)');
        }

        $query->order('a.ordering ASC');
        $this->db->setQuery($query);

        $this->items = (array)$this->db->loadObjectList();
    }

    /**
     * Set the resource items for this object.
     *
     * <code>
     * $resourceId  = 1;
     * $items       = new Magicgallery\Entity\Entities(\JFactory::getDbo());
     *
     * $items->getEntity($resourceId);
     * </code>
     *
     * @param int $id
     *
     * @return Entity|null
     */
    public function getEntity($id)
    {
        $resource = null;
        foreach ($this->items as $item) {
            if ((int)$item->id === $id) {
                $resource = new Entity();
                $resource->bind($item);
                break;
            }
        }

        return $resource;
    }

    /**
     * Get an array with objects Entity.
     *
     * <code>
     * $items    = new Magicgallery\Entity\Entities(\JFactory::getDbo());
     *
     * $items->setEntities('gallery_id');
     * </code>
     *
     * @param string $key
     *
     * @return array
     */
    public function getEntities($key = '')
    {
        $items = array();
        foreach ($this->items as $item) {
            $resource = new Entity();
            $resource->bind($item);

            if ($key !== '' and property_exists($item, $key)) {
                if (!$resource->isDefault()) {
                    $index = (string)$item->$key;
                    $items[$index][] = $resource;
                } else {
                    $index = (string)$item->$key;
                    $items[$index]['default'] = $resource;
                }
            } else {
                if (!$resource->isDefault()) {
                    $items[] = $resource;
                } else {
                    $items['default'] = $resource;
                }
            }
        }

        return $items;
    }

    /**
     * Return the default item for a gallery.
     *
     * <code>
     * $options  = array(
     *     "gallery_id" => 1,
     *     "published" => Prism\Constants::PUBLISHED
     * );
     *
     * $items   = new Magicgallery\Entity\Entities(\JFactory::getDbo());
     * $items->load($options);
     *
     * $defaultResource = $items->getDefaultEntity();
     * </code>
     *
     * @param int $galleryId
     *
     * @return Entity
     */
    public function getDefaultEntity($galleryId = 0)
    {
        $resource      = null;
        $defaultEntity = null;

        /** @var \stdClass $item */
        foreach ($this->items as $item) {
            if ($galleryId > 0 and $item->gallery_id !== $galleryId) {
                continue;
            }

            if ((int)$item->home === 1) {
                $defaultEntity = $item;
                break;
            }
        }

        // If there is no default image, get the first one.
        if (!$defaultEntity and count($this->items) > 0) {
            $item = reset($this->items);
            $defaultEntity = $item;
        }

        if ($defaultEntity !== null) {
            $resource = new Entity();
            $resource->bind($defaultEntity);
        }

        return $resource;
    }
}
