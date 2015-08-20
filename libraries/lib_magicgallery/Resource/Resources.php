<?php
/**
 * @package         MagicGallery
 * @subpackage      Resources
 * @author          Todor Iliev
 * @copyright       Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license         http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

namespace MagicGallery\Resource;

use Prism;
use Joomla\Utilities\ArrayHelper;

defined('JPATH_PLATFORM') or die;

/**
 * This class provide functionality for managing resources.
 *
 * @package         MagicGallery
 * @subpackage      Resources
 */
class Resources extends Prism\Database\ArrayObject
{
    protected $defaultResource = null;

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
     * $resources   = new MagicGallery\Resource\Resources(\JFactory::getDbo());
     * $resources->load($options);
     *
     * foreach($resources as $resource) {
     *     echo $resource["title"];
     * }
     * </code>
     *
     * @param array $options
     */
    public function load($options = array())
    {
        $query = $this->db->getQuery(true);
        $query
            ->select("a.id, a.title, a.description, a.thumbnail, a.image, a.home, a.ordering, a.published, a.gallery_id")
            ->from($this->db->quoteName("#__magicgallery_resources", "a"));

        // Filter by ID.
        $ids = ArrayHelper::getValue($options, "ids", array(), "array");
        ArrayHelper::toInteger($ids);

        if (!empty($ids)) {
            $query->where("a.id IN (" . implode(",", $ids) . ")");
        }

        // Filter by gallery ID.
        $galleryId = ArrayHelper::getValue($options, "gallery_id", 0, "int");
        if (!empty($galleryId)) {
            $query->where("a.gallery_id = " . (int)$galleryId);
        }

        // Filter by state.
        $published = ArrayHelper::getValue($options, "published");
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

        foreach ($this->items as $key => $item) {
            $resource = new Resource();
            $resource->bind($item);

            $this->items[$key] = $resource;
        }

        // Prepare default resource.
        $this->prepareDefaultResource();
    }

    /**
     * Return the default resource for a gallery.
     * 
     * <code>
     * $options  = array(
     *     "gallery_id" => 1,
     *     "published" => Prism\Constants::PUBLISHED,
     * );
     *
     * $resources   = new MagicGallery\Resource\Resources(\JFactory::getDbo());
     * $resources->load($options);
     * 
     * $defaultResource = $resources->getDefaultResource();
     * </code>
     * 
     * @return Resource
     */
    public function getDefaultResource()
    {
        if (is_null($this->defaultResource)) {
            $this->prepareDefaultResource();
        }

        return $this->defaultResource;
    }

    /**
     * Set the resource items for this object.
     *
     * <code>
     * $resources    = new MagicGallery\Resource\Resources(\JFactory::getDbo());
     * $resources2   = array(...);
     *
     * $resources->setItems($resources2);
     * </code>
     *
     * @param array $items
     *
     * @return self
     */
    public function setItems(array $items)
    {
        foreach ($items as $item) {
            $image = new Resource();
            $image->bind($item);

            $this->items[] = $image;
        }

        $this->prepareDefaultResource();

        return $this;
    }

    protected function prepareDefaultResource()
    {
        /** @var Resource $resource */
        foreach ($this->items as $resource) {
            if ($resource->isDefault()) {
                $this->defaultResource = $resource;
                break;
            }
        }

        // If there is no default image, get the first one.
        if (!$this->defaultResource and !empty($this->items)) {
            $resource = reset($this->items);
            $this->defaultResource = $resource;
        }
    }
}
