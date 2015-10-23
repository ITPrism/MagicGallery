<?php
/**
 * @package         MagicGallery
 * @subpackage      Resources
 * @author          Todor Iliev
 * @copyright       Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license         http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

namespace Magicgallery\Entity;

use Prism;
use Joomla\Utilities\ArrayHelper;

defined('JPATH_PLATFORM') or die;

/**
 * This class provide functionality for managing entities.
 *
 * @package         MagicGallery
 * @subpackage      Entities
 */
class Entities extends Prism\Database\ArrayObject
{
    protected $defaultEntity;

    /**
     * Load the resources.
     *
     * <code>
     * $options  = array(
     *     "ids" => array(1,2,3,4),
     *     "gallery_id" => 1, // Load the resources of a gallery.
     *     "published" => Prism\Constants::PUBLISHED
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
     */
    public function load($options = array())
    {
        $query = $this->db->getQuery(true);
        $query
            ->select('a.id, a.title, a.description, a.thumbnail, a.image, a.home, a.ordering, a.published, a.gallery_id')
            ->from($this->db->quoteName('#__magicgallery_entities', 'a'));

        // Filter by ID.
        $ids = ArrayHelper::getValue($options, 'ids', array(), 'array');
        ArrayHelper::toInteger($ids);
        if (count($ids) > 0) {
            $query->where('a.id IN (' . implode(',', $ids) . ')');
        }

        // Filter by gallery ID.
        $galleryId = ArrayHelper::getValue($options, 'gallery_id', 0, 'int');
        if ($galleryId > 0) {
            $query->where('a.gallery_id = ' . (int)$galleryId);
        }

        // Filter by state.
        $published = ArrayHelper::getValue($options, 'published');
        if ($published !== null and is_numeric($published)) {
            $query->where('a.published = ' . (int)$published);
        } else {
            $query->where('a.published IN (0, 1)');
        }

        $query->order('a.ordering ASC');
        $this->db->setQuery($query);

        $results = (array)$this->db->loadAssocList();

        foreach ($results as $key => $value) {
            $item = new Entity();
            $item->bind($value);

            $this->items[$key] = $item;
        }

        unset($results);

        // Prepare default resource.
        $this->prepareDefaultEntity();
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
     * @return Resource
     */
    public function getDefaultEntity()
    {
        if ($this->defaultEntity === null) {
            $this->prepareDefaultEntity();
        }

        return $this->defaultEntity;
    }

    /**
     * Set the resource items for this object.
     *
     * <code>
     * $items    = new Magicgallery\Entity\Entities(\JFactory::getDbo());
     * $items2   = array(...);
     *
     * $items->setEntities($items2);
     * </code>
     *
     * @param array $items
     *
     * @return self
     */
    public function setEntities(array $items)
    {
        foreach ($items as $item) {
            $image = new Entity();
            $image->bind($item);

            $this->items[] = $image;
        }

        $this->prepareDefaultEntity();

        return $this;
    }

    protected function prepareDefaultEntity()
    {
        /** @var Entity $item */
        foreach ($this->items as $item) {
            if ($item->isDefault()) {
                $this->defaultEntity = $item;
                break;
            }
        }

        // If there is no default image, get the first one.
        if (!$this->defaultEntity and count($this->items) > 0) {
            $item = reset($this->items);
            $this->defaultEntity = $item;
        }
    }
}
