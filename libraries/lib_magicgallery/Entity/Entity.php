<?php
/**
 * @package         Magicgallery
 * @subpackage      Entities
 * @author          Todor Iliev
 * @copyright       Copyright (C) 2016 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license         http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

namespace Magicgallery\Entity;

use Joomla\Registry\Registry;
use Prism\Database;
use Prism\Constants;

defined('JPATH_PLATFORM') or die;

/**
 * This class provide functionality for managing an entity.
 *
 * @package         Magicgallery
 * @subpackage      Entities
 */
class Entity extends Database\Table
{
    protected $id;
    protected $title;
    protected $description;
    protected $image;
    protected $thumbnail;
    protected $image_filesize;
    protected $thumbnail_filesize;
    protected $type;
    protected $home;
    protected $ordering;
    protected $published;
    protected $gallery_id;

    /**
     * @var Registry
     */
    protected $image_meta;
    protected $thumbnail_meta;

    /**
     * Load gallery data from database.
     *
     * <code>
     * $itemId = 1;
     *
     * $item   = new Magicgallery\Entity\Entity(\JFactory::getDbo());
     * $item->load($itemId);
     * </code>
     *
     * @param int||array $keys
     * @param array     $options
     *
     * @throws \RuntimeException
     */
    public function load($keys, array $options = array())
    {
        $query = $this->db->getQuery(true);

        $query
            ->select(
                'a.id, a.title, a.description, a.image, a.thumbnail, a.image_filesize, a.thumbnail_filesize, ' .
                'a.image_meta, a.thumbnail_meta, a.type, a.home, a.ordering, a.published, a.gallery_id'
            )
            ->from($this->db->quoteName('#__magicgallery_entities', 'a'));

        if (is_array($keys)) {
            foreach ($keys as $key => $value) {
                $query->where($this->db->quoteName('a.' . $key) . ' = ' . $this->db->quote($value));
            }
        } else {
            $query->where('a.id = ' . (int)$keys);
        }

        $this->db->setQuery($query);
        $result = $this->db->loadObject();

        $this->bind($result);
    }

    /**
     * Store data to database.
     *
     * <code>
     * $data = array(
     *    "title" => "...",
     *    "description" => "..."
     * );
     *
     * $item    = new Magicgallery\Entity\Entity(\JFactory::getDbo());
     * $item->bind($data);
     * $item->store();
     * </code>
     */
    public function store()
    {
        if (!$this->id) { // Insert
            $this->insertObject();
        } else { // Update
            $this->updateObject();
        }
    }

    protected function updateObject()
    {
        // Prepare extra data value.
        $title       = (!$this->title) ? 'NULL' : $this->db->quote($this->title);
        $description = (!$this->description) ? 'NULL' : $this->db->quote($this->description);
        $imageMeta   = ($this->image_meta instanceof Registry) ? $this->db->quote($this->image_meta->toString()) : $this->db->quote('{}');
        $thumbMeta   = ($this->thumbnail_meta instanceof Registry) ? $this->db->quote($this->thumbnail_meta->toString()) : $this->db->quote('{}');
        $type        = (!$this->type) ? 'image' : $this->db->quote($this->type);

        $query = $this->db->getQuery(true);

        $query
            ->update($this->db->quoteName('#__magicgallery_entities'))
            ->set($this->db->quoteName('title') . '=' . $title)
            ->set($this->db->quoteName('description') . '=' . $description)
            ->set($this->db->quoteName('image') . '=' . $this->db->quote($this->image))
            ->set($this->db->quoteName('thumbnail') . '=' . $this->db->quote($this->thumbnail))
            ->set($this->db->quoteName('image_filesize') . '=' . (int)$this->image_filesize)
            ->set($this->db->quoteName('thumbnail_filesize') . '=' . (int)$this->thumbnail_filesize)
            ->set($this->db->quoteName('image_meta') . '=' . $imageMeta)
            ->set($this->db->quoteName('thumbnail_meta') . '=' . $thumbMeta)
            ->set($this->db->quoteName('type') . '=' . $type)
            ->set($this->db->quoteName('home') . '=' . (int)$this->home)
            ->set($this->db->quoteName('ordering') . '=' . (int)$this->ordering)
            ->set($this->db->quoteName('published') . '=' . (int)$this->published)
            ->set($this->db->quoteName('gallery_id') . '=' . (int)$this->gallery_id)
            ->where($this->db->quoteName('id') . '=' . (int)$this->id);

        $this->db->setQuery($query);
        $this->db->execute();
    }

    protected function insertObject()
    {
        if (!(int)$this->gallery_id) {
            throw new \RuntimeException('Missing gallery ID.');
        }

        // Prepare extra data value.
        $title       = (!$this->title) ? 'NULL' : $this->db->quote($this->title);
        $description = (!$this->description) ? 'NULL' : $this->db->quote($this->description);
        $imageMeta   = ($this->image_meta instanceof Registry) ? $this->db->quote($this->image_meta->toString()) : $this->db->quote('{}');
        $thumbMeta   = ($this->thumbnail_meta instanceof Registry) ? $this->db->quote($this->thumbnail_meta->toString()) : $this->db->quote('{}');
        $type        = (!$this->type) ? 'image' : $this->db->quote($this->type);

        // Get last number of the ordering.
        $query = $this->db->getQuery(true);
        $query
            ->select('MAX(' . $this->db->quoteName('ordering') . ')')
            ->from($this->db->quoteName('#__magicgallery_entities'))
            ->where($this->db->quoteName('gallery_id') . '=' . (int)$this->gallery_id);

        $this->db->setQuery($query, 0, 1);
        $max            = (int)$this->db->loadResult();
        $this->ordering = $max + 1;

        // Count default entities for this gallery.
        $query = $this->db->getQuery(true);
        $query
            ->select('COUNT(*)')
            ->from($this->db->quoteName('#__magicgallery_entities'))
            ->where($this->db->quoteName('gallery_id') . '=' . (int)$this->gallery_id)
            ->where($this->db->quoteName('home') . ' = ' . (int)Constants::STATE_DEFAULT);

        $this->db->setQuery($query, 0, 1);
        $hasDefault = (bool)$this->db->loadResult();

        // If there is no default entity, set this as default one.
        if (!$hasDefault) {
            $this->home = Constants::STATE_DEFAULT;
        }

        $query = $this->db->getQuery(true);

        $query
            ->insert($this->db->quoteName('#__magicgallery_entities'))
            ->set($this->db->quoteName('title') . '=' . $title)
            ->set($this->db->quoteName('description') . '=' . $description)
            ->set($this->db->quoteName('image') . '=' . $this->db->quote($this->image))
            ->set($this->db->quoteName('thumbnail') . '=' . $this->db->quote($this->thumbnail))
            ->set($this->db->quoteName('image_filesize') . '=' . (int)$this->image_filesize)
            ->set($this->db->quoteName('thumbnail_filesize') . '=' . (int)$this->thumbnail_filesize)
            ->set($this->db->quoteName('image_meta') . '=' . $imageMeta)
            ->set($this->db->quoteName('thumbnail_meta') . '=' . $thumbMeta)
            ->set($this->db->quoteName('type') . '=' . $type)
            ->set($this->db->quoteName('home') . '=' . (int)$this->home)
            ->set($this->db->quoteName('ordering') . '=' . (int)$this->ordering)
            ->set($this->db->quoteName('published') . '=' . (int)$this->published)
            ->set($this->db->quoteName('gallery_id') . '=' . (int)$this->gallery_id);

        $this->db->setQuery($query);
        $this->db->execute();

        $this->id = $this->db->insertid();
    }

    /**
     * Delete the thumbnail or the image.
     *
     * <code>
     * $itemId = 1;
     *
     * $type       = "thumbnail"; // It is image type - thumbnail or image.
     *
     * $item   = new Magicgallery\Entity\Entity(\JFactory::getDbo());
     * $item->load($itemId);
     *
     * $item->removeImage($type);
     * </code>
     *
     * @param string $type
     *
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     * @throws \UnexpectedValueException
     *
     * @return self
     */
    public function removeImage($type)
    {
        if ((int)$this->id > 0) {
            switch ($type) {
                case 'thumbnail':
                    // Remove the thumbnail from the DB
                    $query = $this->db->getQuery(true);
                    $query
                        ->update($this->db->quoteName('#__magicgallery_entities'))
                        ->set($this->db->quoteName('thumbnail') . ' = ""')
                        ->set($this->db->quoteName('thumbnail_filesize') . ' = 0')
                        ->set($this->db->quoteName('thumbnail_meta') . ' = ' . $this->db->quote('{}'))
                        ->where($this->db->quoteName('id') . ' = ' . (int)$this->id);

                    $this->db->setQuery($query);
                    $this->db->execute();
                    break;

                case 'image':
                    // Remove the image from the DB
                    $query = $this->db->getQuery(true);
                    $query
                        ->update($this->db->quoteName('#__magicgallery_entities'))
                        ->set($this->db->quoteName('image') . ' = ""')
                        ->set($this->db->quoteName('image_filesize') . ' = 0')
                        ->set($this->db->quoteName('image_meta') . ' = ' . $this->db->quote('{}'))
                        ->where($this->db->quoteName('id') . ' = ' . (int)$this->id);

                    $this->db->setQuery($query);
                    $this->db->execute();

                    break;
            }
        }

        return $this;
    }

    /**
     * Delete the record from database and reset the object.
     *
     * <code>
     * $itemId = 1;
     *
     * $item   = new Magicgallery\Entity\Entity(\JFactory::getDbo());
     * $item->load($itemId);
     *
     * $item->remove();
     * </code>
     *
     * @throws \RuntimeException
     * @throws \UnexpectedValueException
     *
     * @return self
     */
    public function remove()
    {
        if ($this->id > 0) {
            // Remove the record from the DB
            $query = $this->db->getQuery(true);
            $query
                ->delete($this->db->quoteName('#__magicgallery_entities'))
                ->where($this->db->quoteName('id') . ' = ' . (int)$this->id);

            $this->db->setQuery($query);
            $this->db->execute();

            // Reset parameters.
            $this->reset();
        }

        return $this;
    }

    /**
     * Change entity state to default or to not default.
     *
     * <code>
     * $itemId = 1;
     *
     * $item   = new Magicgallery\Entity\Entity(\JFactory::getDbo());
     * $item->load($itemId);
     *
     * $item->changeDefaultState(Prism\Constants::STATE_DEFAULT);
     * </code>
     *
     * @param int $state State : 1 = default; 0 = not default;
     *
     * @throws \RuntimeException
     *
     * @return self
     */
    public function changeDefaultState($state)
    {
        if ((int)$this->id > 0) {
            // Reset the states of all entities.
            if ((int)$state === Constants::STATE_DEFAULT) {
                $query = $this->db->getQuery(true);
                $query
                    ->update($this->db->quoteName('#__magicgallery_entities'))
                    ->set($this->db->quoteName('home') . ' = ' . (int)Constants::STATE_NOT_DEFAULT)
                    ->where($this->db->quoteName('gallery_id') . ' = ' . (int)$this->gallery_id);

                $this->db->setQuery($query);
                $this->db->execute();
            }

            // Set the new state.
            $query = $this->db->getQuery(true);
            $query
                ->update($this->db->quoteName('#__magicgallery_entities'))
                ->set($this->db->quoteName('home') . ' = ' . (int)$state)
                ->where($this->db->quoteName('id') . ' = ' . (int)$this->id);

            $this->db->setQuery($query);
            $this->db->execute();
        }

        return $this;
    }

    /**
     * Return entity ID.
     *
     * <code>
     * $itemId = 1;
     *
     * $item   = new Magicgallery\Entity\Entity(\JFactory::getDbo());
     * $item->load($itemId);
     *
     * if (!$item->getId()) {
     * ...
     * }
     * </code>
     *
     * @return int
     */
    public function getId()
    {
        return (int)$this->id;
    }

    /**
     * Return entity title.
     *
     * <code>
     * $itemId = 1;
     *
     * $item   = new Magicgallery\Entity\Entity(\JFactory::getDbo());
     * $item->load($itemId);
     *
     * echo $item->getTitle();
     * </code>
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Return entity description.
     *
     * <code>
     * $itemId = 1;
     *
     * $item   = new Magicgallery\Entity\Entity(\JFactory::getDbo());
     * $item->load($itemId);
     *
     * echo $item->getDescription();
     * </code>
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Return the filename of the thumbnail.
     *
     * <code>
     * $itemId = 1;
     *
     * $item   = new Magicgallery\Entity\Entity(\JFactory::getDbo());
     * $item->load($itemId);
     *
     * echo $item->getThumbnail();
     * </code>
     *
     * @return string
     */
    public function getThumbnail()
    {
        return $this->thumbnail;
    }

    /**
     * Return the filename of the image.
     *
     * <code>
     * $itemId = 1;
     *
     * $item   = new Magicgallery\Entity\Entity(\JFactory::getDbo());
     * $item->load($itemId);
     *
     * echo $item->getImage();
     * </code>
     *
     * @return mixed
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * Check if it is default entity.
     *
     * <code>
     * $itemId = 1;
     *
     * $item   = new Magicgallery\Entity\Entity(\JFactory::getDbo());
     * $item->load($itemId);
     *
     * if ($item->isDefault()) {
     * ...
     * }
     * </code>
     *
     * @return bool
     */
    public function isDefault()
    {
        return ((int)$this->home === Constants::STATE_DEFAULT);
    }

    /**
     * Check if the entity is published.
     *
     * <code>
     * $itemId = 1;
     *
     * $item   = new Magicgallery\Entity\Entity(\JFactory::getDbo());
     * $item->load($itemId);
     *
     * if ($item->isPublished()) {
     * ...
     * }
     * </code>
     *
     * @return bool
     */
    public function isPublished()
    {
        return ((int)$this->published === Constants::PUBLISHED);
    }

    /**
     * Return the gallery ID where this image entity belongs.
     *
     * <code>
     * $itemId = 1;
     *
     * $item   = new Magicgallery\Entity\Entity(\JFactory::getDbo());
     * $item->load($itemId);
     *
     * echo $item->getGalleryId();
     * </code>
     *
     * @return int
     */
    public function getGalleryId()
    {
        return (int)$this->gallery_id;
    }

    /**
     * Return the filesize of the image.
     *
     * <code>
     * $itemId = 1;
     *
     * $item   = new Magicgallery\Entity\Entity(\JFactory::getDbo());
     * $item->load($itemId);
     *
     * echo $item->getImageFilesize();
     * </code>
     *
     * @return int
     */
    public function getImageFilesize()
    {
        return (int)$this->image_filesize;
    }

    /**
     * Return the filesize of the thumbnail.
     *
     * <code>
     * $itemId = 1;
     *
     * $item   = new Magicgallery\Entity\Entity(\JFactory::getDbo());
     * $item->load($itemId);
     *
     * echo $item->getThumbnailFilesize();
     * </code>
     *
     * @return int
     */
    public function getThumbnailFilesize()
    {
        return (int)$this->thumbnail_filesize;
    }

    /**
     * Return the meta data of the image.
     *
     * <code>
     * $itemId = 1;
     *
     * $item   = new Magicgallery\Entity\Entity(\JFactory::getDbo());
     * $item->load($itemId);
     *
     * echo $item->getImageMeta()->get('width');
     * </code>
     *
     * @return Registry
     */
    public function getImageMeta()
    {
        return $this->image_meta;
    }

    /**
     * Return the meta data of the thumbnail.
     *
     * <code>
     * $itemId = 1;
     *
     * $item   = new Magicgallery\Entity\Entity(\JFactory::getDbo());
     * $item->load($itemId);
     *
     * echo $item->getThumbnailMeta()->get('width');
     * </code>
     *
     * @return Registry
     */
    public function getThumbnailMeta()
    {
        return $this->thumbnail_meta;
    }

    /**
     * Set the path to the media folder.
     *
     * <code>
     * $itemId = 1;
     *
     * $item   = new Magicgallery\Entity\Entity(\JFactory::getDbo());
     * $item->load($itemId);
     *
     * $item->setStatus(Prism\Constants::PUBLISHED);
     * </code>
     *
     * @param int $status
     *
     * @return self
     */
    public function setStatus($status)
    {
        $this->published = (!is_numeric($status)) ? 0 : (int)$status;

        return $this;
    }

    /**
     * Return the path to the image. If there is thumbnail, it will return it.
     * If there is not thumbnail, it will return the large image.
     *
     * <code>
     * $itemId = 1;
     *
     * $item   = new Magicgallery\Entity\Entity(\JFactory::getDbo());
     * $item->load($itemId);
     *
     * echo $item;
     * </code>
     *
     * @return string
     */
    public function __toString()
    {
        return (strlen($this->thumbnail) > 0) ? (string)$this->thumbnail : (string)$this->image;
    }

    /**
     * Set notification data to object parameters.
     *
     * <code>
     * $data = new stdClass;
     *
     * $resource   = new Magicgallery\Entity\Entity(\JFactory::getDbo());
     * $resource->bind($data);
     * </code>
     *
     * @param array||\stdClass $data
     * @param array $ignored
     */
    public function bind($data, array $ignored = array())
    {
        if (is_array($data)) { // Array
            // Parse parameters of the object if they exists.
            if (array_key_exists('params', $data) and !in_array('params', $ignored, true)) {
                $this->params = new Registry($data['params']);
                unset($data['params']);
            }

            if (array_key_exists('image_meta', $data)) {
                $this->image_meta = new Registry($data['image_meta']);
                unset($data['image_meta']);
            }

            if (array_key_exists('thumbnail_meta', $data)) {
                $this->thumbnail_meta = new Registry($data['thumbnail_meta']);
                unset($data['thumbnail_meta']);
            }

            foreach ($data as $key => $value) {
                if (!in_array($key, $ignored, true)) {
                    $this->$key = $value;
                }
            }

        } elseif (is_object($data)) { // Object
            // Parse parameters of the object if they exists.
            if (property_exists($data, 'params') and !in_array('params', $ignored, true)) {
                $this->params = new Registry($data->params);
                unset($data->params);
            }

            if (property_exists($data, 'image_meta')) {
                $this->image_meta = new Registry($data->image_meta);
                unset($data->image_meta);
            }

            if (property_exists($data, 'thumbnail_meta')) {
                $this->thumbnail_meta = new Registry($data->thumbnail_meta);
                unset($data->thumbnail_meta);
            }

            $data_ = get_object_vars($data);
            foreach ($data_ as $key => $value) {
                if (!in_array($key, $ignored, true)) {
                    $this->$key = $value;
                }
            }
        }
    }
}
