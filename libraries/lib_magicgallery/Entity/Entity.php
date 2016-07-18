<?php
/**
 * @package         Magicgallery
 * @subpackage      Entities
 * @author          Todor Iliev
 * @copyright       Copyright (C) 2016 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license         http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

namespace Magicgallery\Entity;

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
    protected $width;
    protected $height;
    protected $mime;
    protected $type;
    protected $home;
    protected $filesize;
    protected $ordering;
    protected $published;
    protected $gallery_id;

    /**
     * The folder where the media files are stored.
     *
     * @var string
     */
    protected $mediaFolder;

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
     * @param int|array $keys
     * @param array $options
     *
     * @throws \RuntimeException
     */
    public function load($keys, array $options = array())
    {
        $query = $this->db->getQuery(true);

        $query
            ->select('a.id, a.title, a.description, a.image, a.thumbnail, a.width, a.height, a.mime, a.type, a.home, a.filesize, a.ordering, a.published, a.gallery_id')
            ->from($this->db->quoteName('#__magicgallery_entities', 'a'));

        if (is_array($keys)) {
            foreach ($keys as $key => $value) {
                $query->where($this->db->quoteName('a.'.$key) .' = ' . $this->db->quote($value));
            }
        } else {
            $query->where('a.id = ' . (int)$keys);
        }

        $this->db->setQuery($query);
        $result = (array)$this->db->loadAssoc();

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
        $filesize    = (!$this->filesize) ? 'NULL' : $this->db->quote($this->filesize);
        $width    = (!$this->width) ? 'NULL' : (int)$this->width;
        $height    = (!$this->height) ? 'NULL' : (int)$this->height;
        $mime    = (!$this->mime) ? 'NULL' : $this->db->quote($this->mime);
        $type    = (!$this->type) ? 'image' : $this->db->quote($this->type);

        $query     = $this->db->getQuery(true);

        $query
            ->update($this->db->quoteName('#__magicgallery_entities'))
            ->set($this->db->quoteName('title') . '=' . $title)
            ->set($this->db->quoteName('description') . '=' . $description)
            ->set($this->db->quoteName('image') . '=' . $this->db->quote($this->image))
            ->set($this->db->quoteName('thumbnail') . '=' . $this->db->quote($this->thumbnail))
            ->set($this->db->quoteName('filesize') . '=' . $filesize)
            ->set($this->db->quoteName('width') . '=' . $width)
            ->set($this->db->quoteName('height') . '=' . $height)
            ->set($this->db->quoteName('mime') . '=' . $mime)
            ->set($this->db->quoteName('type') . '=' . $type)
            ->set($this->db->quoteName('home') . '=' . (int)$this->home)
            ->set($this->db->quoteName('ordering') . '=' . (int)$this->ordering)
            ->set($this->db->quoteName('published') . '=' . (int)$this->published)
            ->set($this->db->quoteName('gallery_id') . '=' . (int)$this->gallery_id)
            ->where($this->db->quoteName('id') .'='. (int)$this->id);

        $this->db->setQuery($query);
        $this->db->execute();
    }

    protected function insertObject()
    {
        if (!(int)$this->gallery_id) {
            throw new \RuntimeException(\JText::_('LIB_MAGICGALLERY_ERROR_MISSING_GALLERY_ID'));
        }
        
        // Prepare extra data value.
        $title       = (!$this->title) ? 'NULL' : $this->db->quote($this->title);
        $description = (!$this->description) ? 'NULL' : $this->db->quote($this->description);
        $filesize    = (!$this->filesize) ? 'NULL' : $this->db->quote($this->filesize);
        $width       = (!$this->width) ? 'NULL' : (int)$this->width;
        $height      = (!$this->height) ? 'NULL' : (int)$this->height;
        $mime        = (!$this->mime) ? 'NULL' : $this->db->quote($this->mime);
        $type        = (!$this->type) ? 'image' : $this->db->quote($this->type);

        // Get last number of the ordering.
        $query = $this->db->getQuery(true);
        $query
            ->select('MAX('.$this->db->quoteName('ordering').')')
            ->from($this->db->quoteName('#__magicgallery_entities'))
            ->where($this->db->quoteName('gallery_id') .'='.(int)$this->gallery_id);

        $this->db->setQuery($query, 0, 1);
        $max = (int)$this->db->loadResult();
        $this->ordering = $max + 1;

        // Count default entities for this gallery.
        $query = $this->db->getQuery(true);
        $query
            ->select('COUNT(*)')
            ->from($this->db->quoteName('#__magicgallery_entities'))
            ->where($this->db->quoteName('gallery_id') .'='.(int)$this->gallery_id)
            ->where($this->db->quoteName('home') .' = '. (int)Constants::STATE_DEFAULT);

        $this->db->setQuery($query, 0, 1);
        $hasDefault = (bool)$this->db->loadResult();

        // If there is no default entity, set this as default one.
        if (!$hasDefault) {
            $this->home = Constants::STATE_DEFAULT;
        }

        $query       = $this->db->getQuery(true);

        $query
            ->insert($this->db->quoteName('#__magicgallery_entities'))
            ->set($this->db->quoteName('title') . '=' . $title)
            ->set($this->db->quoteName('description') . '=' . $description)
            ->set($this->db->quoteName('image') . '=' . $this->db->quote($this->image))
            ->set($this->db->quoteName('thumbnail') . '=' . $this->db->quote($this->thumbnail))
            ->set($this->db->quoteName('filesize') . '=' . $filesize)
            ->set($this->db->quoteName('width') . '=' . $width)
            ->set($this->db->quoteName('height') . '=' . $height)
            ->set($this->db->quoteName('mime') . '=' . $mime)
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
     * @param string  $type
     *
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     * @throws \UnexpectedValueException
     *
     * @return self
     */
    public function removeImage($type)
    {
        if (!$this->mediaFolder) {
            throw new \RuntimeException(\JText::_('LIB_MAGICGALLERY_ERROR_MISSING_MEDIA_FOLDER'));
        }

        if ((int)$this->id > 0) {
            switch ($type) {
                case 'thumbnail':
                    // Remove an image from the filesystem
                    $file = \JPath::clean($this->mediaFolder . DIRECTORY_SEPARATOR . $this->thumbnail);
                    if (\JFile::exists($file)) {
                        \JFile::delete($file);
                    }

                    // Remove the image from the DB
                    $query = $this->db->getQuery(true);
                    $query
                        ->update($this->db->quoteName('#__magicgallery_entities'))
                        ->set($this->db->quoteName('thumbnail') . ' = "" ')
                        ->where($this->db->quoteName('id') . ' = ' . (int)$this->id);

                    $this->db->setQuery($query);
                    $this->db->execute();

                    break;

                case 'image':
                    // Remove an image from the filesystem
                    $file = \JPath::clean($this->mediaFolder . DIRECTORY_SEPARATOR . $this->image);
                    if (\JFile::exists($file)) {
                        \JFile::delete($file);
                    }

                    // Remove the image from the DB
                    $query = $this->db->getQuery(true);
                    $query
                        ->update($this->db->quoteName('#__magicgallery_entities'))
                        ->set($this->db->quoteName('image') . ' = "" ')
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
        if (!$this->mediaFolder) {
            throw new \RuntimeException(\JText::_('LIB_MAGICGALLERY_ERROR_MISSING_MEDIA_FOLDER'));
        }

        if ($this->id > 0) {
            // Remove the thumbnail from the filesystem.
            $file = \JPath::clean($this->mediaFolder . DIRECTORY_SEPARATOR . $this->thumbnail);
            if (\JFile::exists($file)) {
                \JFile::delete($file);
            }

            // Remove an image from the filesystem.
            $file = \JPath::clean($this->mediaFolder . DIRECTORY_SEPARATOR . $this->image);
            if (\JFile::exists($file)) {
                \JFile::delete($file);
            }

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
     * Return the path to the media folder.
     *
     * <code>
     * $itemId = 1;
     *
     * $item   = new Magicgallery\Entity\Entity(\JFactory::getDbo());
     * $item->load($itemId);
     *
     * echo $item->getMediaFolder();
     * </code>
     *
     * @return string
     */
    public function getMediaFolder()
    {
        return $this->mediaFolder;
    }

    /**
     * Set the path to the media folder.
     *
     * <code>
     * $itemId = 1;
     * $mediaFolder = "/.../..";
     *
     * $item   = new Magicgallery\Entity\Entity(\JFactory::getDbo());
     * $item->load($itemId);
     *
     * $item->setMediaFolder($mediaFolder);
     * </code>
     *
     * @param string $mediaFolder
     *
     * @return self
     */
    public function setMediaFolder($mediaFolder)
    {
        $this->mediaFolder = $mediaFolder;

        return $this;
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
}
