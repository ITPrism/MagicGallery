<?php
/**
 * @package      Magicgallery
 * @subpackage   Galleries
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2016 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace Magicgallery\Gallery;

use Prism;
use Joomla\Utilities\ArrayHelper;
use Magicgallery\Entity;
use Joomla\Registry\Registry;

defined('JPATH_PLATFORM') or die;

/**
 * This class contains methods that are used for managing a gallery.
 *
 * @package      Magicgallery
 * @subpackage   Galleries
 */
class Gallery extends Prism\Database\Table
{
    protected $id;
    protected $title;
    protected $alias;
    protected $description;
    protected $url;
    protected $catid = 0;
    protected $extension = '';
    protected $object_id = 0;
    protected $published = 0;
    protected $ordering = 0;
    protected $user_id = 0;
    protected $slug;
    protected $catslug;

    protected $entities;

    /**
     * Load gallery data from database.
     *
     * <code>
     * $galleryId = 1;
     *
     * $options = array(
     *     "load_entities" => true,
     *     "entity_state" => Prism\Constants::PUBLISHED
     * );
     *
     * $gallery   = new Magicgallery\Gallery\Gallery(\JFactory::getDbo());
     * $gallery->load($galleryId);
     * </code>
     *
     * @param int|array $keys
     * @param array $options
     *
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function load($keys, array $options = array())
    {
        $query = $this->db->getQuery(true);

        $query
            ->select(
                'a.id, a.title, a.alias, a.description, a.url, a.catid, a.extension, ' .
                'a.params, a.object_id, a.published, a.ordering, a.user_id, ' .
                $query->concatenate(array('a.id', 'a.alias'), ':') . ' AS slug,' .
                $query->concatenate(array('b.id', 'b.alias'), ':') . ' AS catslug'
            )
            ->from($this->db->quoteName('#__magicgallery_galleries', 'a'))
            ->leftJoin($this->db->quoteName('#__categories', 'b') . ' ON a.catid = b.id');

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

        // Load the items.
        $loadEntities = ArrayHelper::getValue($options, 'load_entities', false, 'bool');
        if ($loadEntities) {
            $itemState = ArrayHelper::getValue($options, 'entity_state', Prism\Constants::PUBLISHED, 'int');

            $option = array(
                'gallery_id' => (int)$this->id,
                'published'  => $itemState
            );

            $this->entities = new Entity\Entities(\JFactory::getDbo());
            $this->entities->load($option);
        }
    }

    /**
     * Store data to database.
     *
     * <code>
     * $data = array(
     *     "title" => "Title...",
     *     "description" => "Description..."
     * );
     *
     * $gallery    = new Magicgallery\Gallery\Gallery(\JFactory::getDbo());
     * $gallery->bind($data);
     * $gallery->store();
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
        $description = (!$this->description) ? 'NULL' : $this->db->quote($this->description);
        $url         = (!$this->url) ? 'NULL' : $this->db->quote($this->url);

        $params      = 'NULL';
        if (($this->params !== null) and ($this->params instanceof Registry) and ($this->params->count() > 0)) {
            $params  = $this->db->quote($this->params->toString());
        }

        $query = $this->db->getQuery(true);

        $query
            ->update($this->db->quoteName('#__magicgallery_galleries'))
            ->set($this->db->quoteName('title') . '=' . $this->db->quote($this->title))
            ->set($this->db->quoteName('alias') . '=' . $this->db->quote($this->alias))
            ->set($this->db->quoteName('description') . '=' . $description)
            ->set($this->db->quoteName('url') . '=' . $url)
            ->set($this->db->quoteName('catid') . '=' . (int)$this->catid)
            ->set($this->db->quoteName('extension') . '=' . $this->db->quote($this->extension))
            ->set($this->db->quoteName('object_id') . '=' . (int)$this->object_id)
            ->set($this->db->quoteName('published') . '=' . (int)$this->published)
            ->set($this->db->quoteName('ordering') . '=' . (int)$this->ordering)
            ->set($this->db->quoteName('user_id') . '=' . (int)$this->user_id)
            ->set($this->db->quoteName('params') . '=' . $params);

        $this->db->setQuery($query);
        $this->db->execute();
    }

    protected function insertObject()
    {
        // Prepare extra data value.
        $description = (!$this->description) ? 'NULL' : $this->db->quote($this->description);
        $url         = (!$this->url) ? 'NULL' : $this->db->quote($this->url);

        $params      = 'NULL';
        if (($this->params !== null) and ($this->params instanceof Registry) and ($this->params->count() > 0)) {
            $params  = $this->db->quote($this->params->toString());
        }

        if (!$this->alias) {
            $this->alias = \JApplicationHelper::stringURLSafe($this->title);
        }

        // Get last number of the ordering.
        $query = $this->db->getQuery(true);
        $query
            ->select('MAX('.$this->db->quoteName('ordering').')')
            ->from($this->db->quoteName('#__magicgallery_galleries'));

        $this->db->setQuery($query, 0, 1);
        $max = (int)$this->db->loadResult();
        $this->ordering = $max + 1;

        // Store the record
        $query = $this->db->getQuery(true);

        $query
            ->insert($this->db->quoteName('#__magicgallery_galleries'))
            ->set($this->db->quoteName('title') . '=' . $this->db->quote($this->title))
            ->set($this->db->quoteName('alias') . '=' . $this->db->quote($this->alias))
            ->set($this->db->quoteName('description') . '=' . $description)
            ->set($this->db->quoteName('url') . '=' . $url)
            ->set($this->db->quoteName('catid') . '=' . (int)$this->catid)
            ->set($this->db->quoteName('extension') . '=' . $this->db->quote($this->extension))
            ->set($this->db->quoteName('object_id') . '=' . (int)$this->object_id)
            ->set($this->db->quoteName('published') . '=' . (int)$this->published)
            ->set($this->db->quoteName('ordering') . '=' . (int)$this->ordering)
            ->set($this->db->quoteName('user_id') . '=' . (int)$this->user_id)
            ->set($this->db->quoteName('params') . '=' . $params);

        $this->db->setQuery($query);
        $this->db->execute();

        $this->id = $this->db->insertid();
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
     * $gallery    = new Magicgallery\Gallery\Gallery(\JFactory::getDbo());
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
        return (int)$this->id;
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
     * $gallery   = new Magicgallery\Gallery\Gallery(\JFactory::getDbo());
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
     * Set gallery title.
     *
     * <code>
     * $gallery   = new Magicgallery\Gallery\Gallery(\JFactory::getDbo());
     *
     * $gallery->setTitle("Title...");
     * </code>
     *
     * @param string $title
     *
     * @return self
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Return gallery alias.
     *
     * <code>
     * $galleryId = 1;
     *
     * $gallery   = new Magicgallery\Gallery\Gallery(\JFactory::getDbo());
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
     * $gallery   = new Magicgallery\Gallery\Gallery(\JFactory::getDbo());
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
     * $gallery   = new Magicgallery\Gallery\Gallery(\JFactory::getDbo());
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
     * $gallery   = new Magicgallery\Gallery\Gallery(\JFactory::getDbo());
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
     * Set gallery description.
     *
     * <code>
     * $gallery   = new Magicgallery\Gallery\Gallery(\JFactory::getDbo());
     *
     * $gallery->setDescription("Description...");
     * </code>
     *
     * @param string $description
     *
     * @return self
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Return URL.
     *
     * <code>
     * $galleryId = 1;
     *
     * $gallery   = new Magicgallery\Gallery\Gallery(\JFactory::getDbo());
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
     * $gallery   = new Magicgallery\Gallery\Gallery(\JFactory::getDbo());
     * $gallery->load($keys);
     *
     * $categoryId = $gallery->getCategoryId();
     * </code>
     *
     * @return int
     */
    public function getCategoryId()
    {
        return (int)$this->catid;
    }

    /**
     * Set gallery description.
     *
     * <code>
     * $categoryId = 1;
     *
     * $gallery   = new Magicgallery\Gallery\Gallery(\JFactory::getDbo());
     *
     * $gallery->setCategoryId($categoryId);
     * </code>
     *
     * @param int $categoryId
     *
     * @return self
     */
    public function setCategoryId($categoryId)
    {
        $this->catid = (int)$categoryId;

        return $this;
    }

    /**
     * Return extension name.
     *
     * <code>
     * $galleryId = 1;
     *
     * $gallery   = new Magicgallery\Gallery\Gallery(\JFactory::getDbo());
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
     * Set the extension option to which the gallery is assigned.
     *
     * <code>
     * $gallery   = new Magicgallery\Gallery\Gallery(\JFactory::getDbo());
     *
     * $gallery->setExtension("com_crowdfunding");
     * </code>
     *
     * @param string $extension
     *
     * @return self
     */
    public function setExtension($extension)
    {
        $this->extension = $extension;

        return $this;
    }

    /**
     * Return object ID.
     *
     * <code>
     * $galleryId = 1;
     *
     * $gallery   = new Magicgallery\Gallery\Gallery(\JFactory::getDbo());
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
     * Set the object ID where the gallery is assigned.
     *
     * <code>
     * $objectId = 1;
     *
     * $gallery   = new Magicgallery\Gallery\Gallery(\JFactory::getDbo());
     *
     * $gallery->setObjectId($objectId);
     * </code>
     *
     * @param int $objectId
     *
     * @return self
     */
    public function setObjectId($objectId)
    {
        $this->object_id = (int)$objectId;

        return $this;
    }

    /**
     * Return user ID.
     *
     * <code>
     * $galleryId = 1;
     *
     * $gallery   = new Magicgallery\Gallery\Gallery(\JFactory::getDbo());
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
     * Set the user ID.
     *
     * <code>
     * $userId = 1;
     *
     * $gallery   = new Magicgallery\Gallery\Gallery(\JFactory::getDbo());
     *
     * $gallery->setUserId($userId);
     * </code>
     *
     * @param int $userId
     *
     * @return self
     */
    public function setUserId($userId)
    {
        $this->user_id = (int)$userId;

        return $this;
    }

    /**
     * Return ordering number.
     *
     * <code>
     * $galleryId = 1;
     *
     * $gallery   = new Magicgallery\Gallery\Gallery(\JFactory::getDbo());
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
     * Set the status of the gallery.
     *
     * <code>
     * $gallery   = new Magicgallery\Gallery\Gallery(\JFactory::getDbo());
     *
     * $gallery->setStatus(Prism\Constants::PUBLISHED);
     * </code>
     *
     * @param int $status The status : 1 - published; 0 - unpublished.
     *
     * @return self
     */
    public function setStatus($status)
    {
        $this->published = (int)$status;

        return $this;
    }

    /**
     * Check if the item is published.
     *
     * <code>
     * $galleryId = 1;
     *
     * $gallery   = new Magicgallery\Image(\JFactory::getDbo());
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
        return (bool)($this->published === Prism\Constants::PUBLISHED);
    }

    /**
     * Return default item.
     *
     * <code>
     * $galleryId = 1;
     *
     * $gallery   = new Magicgallery\Gallery\Gallery(\JFactory::getDbo());
     * $gallery->load($galleryId);
     *
     * $image = $gallery->getDefaultEntity();
     * </code>
     *
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     *
     * @return Entity\Entities
     */
    public function getDefaultEntity()
    {
        if ($this->entities === null) {
            $option = array(
                'gallery_id' => (int)$this->id,
                'published'  => Prism\Constants::PUBLISHED
            );

            $this->entities = new Entity\Entities(\JFactory::getDbo());
            $this->entities->load($option);
        }

        return $this->entities->getDefaultEntity();
    }

    /**
     * Return the items provided by the gallery.
     *
     * <code>
     * $galleryId = 1;
     *
     * $gallery   = new Magicgallery\Gallery\Gallery(\JFactory::getDbo());
     * $gallery->load($galleryId);
     *
     * $items = $gallery->getEntities();
     * </code>
     *
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     *
     * @return Entity\Entities
     */
    public function getEntities()
    {
        if ($this->entities === null) {
            $options = array(
                'gallery_id' => (int)$this->id,
                'published'  => Prism\Constants::PUBLISHED
            );

            $this->entities = new Entity\Entities(\JFactory::getDbo());
            $this->entities->load($options);
        }

        return $this->entities;
    }

    /**
     * Set the entities to the gallery.
     *
     * <code>
     * $items = array(...);
     * $galleryId = 1;
     *
     * $gallery   = new Magicgallery\Gallery\Gallery(\JFactory::getDbo());
     * $gallery->load($galleryId);
     *
     * $gallery->setEntities($items);
     * </code>
     *
     * @param Entity\Entities $entities
     *
     * @return self
     */
    public function setEntities(Entity\Entities $entities)
    {
        $this->entities = $entities;

        return $this;
    }

    /**
     * Return the object properties as array.
     *
     * <code>
     * $galleryId = 1;
     *
     * $gallery   = new Magicgallery\Gallery\Gallery(\JFactory::getDbo());
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

        $items = array();

        /** @var Entity\Entity $item */
        foreach ($this->entities as $item) {
            $items[] = $item->getProperties();
        }

        $gallery['items'] = $items;

        return $gallery;
    }
}
