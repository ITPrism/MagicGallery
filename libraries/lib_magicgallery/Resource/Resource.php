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
use Joomla\Registry\Registry;
use Joomla\String\String;

defined('JPATH_PLATFORM') or die;

/**
 * This class provide functionality for managing a resource.
 *
 * @package         MagicGallery
 * @subpackage      Resources
 */
class Resource extends Prism\Database\Table
{
    protected $id;
    protected $title;
    protected $description;
    protected $thumbnail;
    protected $image;
    protected $home;
    protected $type;
    protected $ordering;
    protected $published;
    protected $gallery_id;

    protected $params = array();

    /**
     * Folder where the media files are stored.
     *
     * @var string
     */
    protected $mediaFolder;

    /**
     * Load gallery data from database.
     *
     * <code>
     * $resourceId = 1;
     *
     * $resource   = new MagicGallery\Resource\Resource(\JFactory::getDbo());
     * $resource->load($resourceId);
     * </code>
     *
     * @param int|array $keys
     * @param array $options
     */
    public function load($keys, $options = array())
    {
        $query = $this->db->getQuery(true);

        $query
            ->select("a.id, a.title, a.description, a.thumbnail, a.image, a.home, a.ordering, a.published, a.gallery_id, a.params")
            ->from($this->db->quoteName("#__magicgallery_resources", "a"));

        if (is_array($keys)) {
            foreach ($keys as $key => $value) {
                $query->where($this->db->quoteName($key) ." = " . $this->db->quote($value));
            }
        } else {
            $query->where("a.id = " . (int)$keys);
        }

        $this->db->setQuery($query);
        $result = (array)$this->db->loadAssoc();

        $this->bind($result, array("params"));

        // Decode the parameters.
        if (!empty($result["params"])) {
            $this->params = json_decode($result["params"], true);
        }
    }

    public function store()
    {

    }

    /**
     * Upload resource to the media folder.
     *
     * <code>
     * $resource = array(
     *     "tmp_name" => "...",
     *     "name" => "...",
     *     "error" => "...",
     * );
     *
     * $options = array(
     *     "resize_image" => 1,
     *     "image_width" => 500,
     *     "image_height" => 500,
     *     "image_scale" => \JImage::SCALE_INSIDE,
     *
     *     "create_thumb" => 1,
     *     "thumb_width" => 300,
     *     "thumb_width" => 300,
     *     "thumb_scale" => \JImage::SCALE_INSIDE,
     *
     *     "destination_folder" => "/../.."
     * );
     *
     * $resource   = new MagicGallery\Resource\Resource(\JFactory::getDbo());
     *
     * $resources  = $resource->uploadImage($file, $options);
     * </code>
     *
     * @param array $mediaFile // Data that comes from server.
     * @param array $options
     *
     * @throws \Exception
     * @throws \RuntimeException
     */
    public function uploadImage($mediaFile, $options)
    {
        // Set the type to image.
        if (!$this->type) {
            $this->type = "image";
        }

        $app = \JFactory::getApplication();

        $uploadedFile  = ArrayHelper::getValue($mediaFile, 'tmp_name');
        $uploadedName  = ArrayHelper::getValue($mediaFile, 'name');
        $errorCode     = ArrayHelper::getValue($mediaFile, 'error');

        $destinationFolder   = ArrayHelper::getValue($options, 'destination_folder');

        // Joomla! media extension parameters
        $mediaParams   = \JComponentHelper::getParams("com_media");
        /** @var  $mediaParams Registry */

        $file          = new Prism\File\File();

        // Prepare size validator.
        $KB            = 1024 * 1024;
        $fileSize      = (int)$app->input->server->get('CONTENT_LENGTH');
        $uploadMaxSize = $mediaParams->get("upload_maxsize") * $KB;

        // Prepare file size validator
        $sizeValidator = new Prism\File\Validator\Size($fileSize, $uploadMaxSize);

        // Prepare server validator.
        $serverValidator = new Prism\File\Validator\Server($errorCode, array(UPLOAD_ERR_NO_FILE));

        // Prepare image validator.
        $resourceValidator = new Prism\File\Validator\Image($uploadedFile, $uploadedName);

        // Get allowed mime types from media manager options
        $mimeTypes = explode(",", $mediaParams->get("upload_mime"));
        $resourceValidator->setMimeTypes($mimeTypes);

        // Get allowed image extensions from media manager options
        $resourceExtensions = explode(",", $mediaParams->get("image_extensions"));
        $resourceValidator->setImageExtensions($resourceExtensions);

        $file
            ->addValidator($sizeValidator)
            ->addValidator($resourceValidator)
            ->addValidator($serverValidator);

        // Validate the file
        if (!$file->isValid()) {
            throw new \RuntimeException($file->getError());
        }

        // Generate temporary file name
        $ext = String::strtolower(\JFile::makeSafe(\JFile::getExt($mediaFile['name'])));

        $generatedName = new Prism\String();
        $generatedName->generateRandomString(6);

        $destinationFile = \JPath::clean($destinationFolder . DIRECTORY_SEPARATOR . "image_" . $generatedName . "." . $ext);

        // Prepare uploader object.
        $uploader = new Prism\File\Uploader\Local($uploadedFile);
        $uploader->setDestination($destinationFile);

        // Upload temporary file
        $file->setUploader($uploader);

        $file->upload();

        // Get file
        $sourceFile = $file->getFile();

        if (!is_file($sourceFile)) {
            throw new \Exception('LIB_MAGICGALLERY_ERROR_FILE_CANT_BE_UPLOADED');
        }

        $this->image = basename($sourceFile);

        // Resize image
        $resizeImage = ArrayHelper::getValue($options, "resize_image", 0, "int");
        if (!empty($resizeImage)) {

            $width       = ArrayHelper::getValue($options, "image_width", 500);
            $height      = ArrayHelper::getValue($options, "image_height", 500);
            $scale       = ArrayHelper::getValue($options, "image_scale", \JImage::SCALE_INSIDE);

            $this->resizeImage($sourceFile, $width, $height, $scale);
        }

        // Create thumbnail
        $createThumb = ArrayHelper::getValue($options, "create_thumb", 0, "int");
        if (!empty($createThumb)) {

            $width       = ArrayHelper::getValue($options, "thumb_width", 300);
            $height      = ArrayHelper::getValue($options, "thumb_height", 300);
            $scale       = ArrayHelper::getValue($options, "thumb_scale", \JImage::SCALE_INSIDE);

            $this->createThumbnail($sourceFile, $width, $height, "thumb_", $scale);
        }

        $this->params["image"] = $this->extractParams($sourceFile);
    }

    /**
     * Upload thumbnail to the media folder.
     *
     * <code>
     * $resource = array(
     *     "tmp_name" => "...",
     *     "name" => "...",
     *     "error" => "...",
     * );
     *
     * $options = array(
     *     "destination_folder" => "/../.."
     * );
     *
     * $resource   = new MagicGallery\Resource\Resource(\JFactory::getDbo());
     *
     * $resources  = $resource->uploadThumbnail($file, $options);
     * </code>
     *
     * @param array $mediaFile // Data that comes from server.
     * @param array $options
     *
     * @throws \RuntimeException
     *
     * @return array
     */
    public function uploadThumbnail($mediaFile, $options)
    {
        // Set the type to image.
        if (!$this->type) {
            $this->type = "image";
        }

        $app = \JFactory::getApplication();

        $uploadedFile = ArrayHelper::getValue($mediaFile, 'tmp_name');
        $uploadedName = ArrayHelper::getValue($mediaFile, 'name');
        $errorCode    = ArrayHelper::getValue($mediaFile, 'error');

        $destinationFolder   = ArrayHelper::getValue($options, 'destination_folder');

        // Joomla! media extension parameters
        $mediaParams = \JComponentHelper::getParams("com_media");
        /** @var  $mediaParams Registry */

        $file = new Prism\File\File();

        // Prepare size validator.
        $KB            = 1024 * 1024;
        $fileSize      = (int)$app->input->server->get('CONTENT_LENGTH');
        $uploadMaxSize = $mediaParams->get("upload_maxsize") * $KB;

        // Prepare file size validator
        $sizeValidator = new Prism\File\Validator\Size($fileSize, $uploadMaxSize);

        // Prepare server validator.
        $serverValidator = new Prism\File\Validator\Server($errorCode, array(UPLOAD_ERR_NO_FILE));

        // Prepare image validator.
        $resourceValidator = new Prism\File\Validator\Image($uploadedFile, $uploadedName);

        // Get allowed mime types from media manager options
        $mimeTypes = explode(",", $mediaParams->get("upload_mime"));
        $resourceValidator->setMimeTypes($mimeTypes);

        // Get allowed image extensions from media manager options
        $resourceExtensions = explode(",", $mediaParams->get("image_extensions"));
        $resourceValidator->setImageExtensions($resourceExtensions);

        $file
            ->addValidator($sizeValidator)
            ->addValidator($resourceValidator)
            ->addValidator($serverValidator);

        // Validate the file
        if (!$file->isValid()) {
            throw new \RuntimeException($file->getError());
        }

        // Generate temporary file name
        $ext = String::strtolower(\JFile::makeSafe(\JFile::getExt($mediaFile['name'])));

        $generatedName = new Prism\String();
        $generatedName->generateRandomString(6);

        $thumbName        = "thumb_" . $generatedName . "." . $ext;
        $destinationFile  = \JPath::clean($destinationFolder . DIRECTORY_SEPARATOR . $thumbName);

        // Prepare uploader object.
        $uploader = new Prism\File\Uploader\Local($uploadedFile);
        $uploader->setDestination($destinationFile);

        // Upload temporary file
        $file->setUploader($uploader);

        $file->upload();

        $this->thumbnail = $thumbName;

        // Get thumbnail parameters.
        $this->params["thumbnail"] = $this->extractParams($destinationFile);
    }

    protected function resizeImage($file, $width, $height, $scale = \JImage::SCALE_INSIDE)
    {
        // Make thumbnail
        $ext = String::strtolower(\JFile::getExt(\JFile::makeSafe($file)));

        $resource = new \JImage();

        $resource->loadFile($file);
        if (!$resource->isLoaded()) {
            throw new \Exception(\JText::sprintf('LIB_MAGICGALLERY_ERROR_FILE_NOT_FOUND', $file));
        }

        // Resize the file
        $resource->resize($width, $height, false, $scale);

        switch ($ext) {
            case "gif":
                $type = IMAGETYPE_GIF;
                break;

            case "png":
                $type = IMAGETYPE_PNG;
                break;

            case IMAGETYPE_JPEG:
            default:
                $type = IMAGETYPE_JPEG;
        }

        $resource->toFile($file, $type);
    }

    protected function createThumbnail($file, $width, $height, $prefix = "thumb_", $scale = \JImage::SCALE_INSIDE)
    {
        $destinationFolder = \JPath::clean(dirname($file));

        $ext = String::strtolower(\JFile::makeSafe(\JFile::getExt($file)));

        $resource = new \JImage();
        $resource->loadFile($file);
        if (!$resource->isLoaded()) {
            throw new \Exception(\JText::sprintf('LIB_MAGICGALLERY_ERROR_FILE_NOT_FOUND', $file));
        }

        // Resize the file as a new object
        $thumb = $resource->resize($width, $height, true, $scale);

        $generatedName = new Prism\String();
        $generatedName->generateRandomString(6);

        $thumbName = $prefix . $generatedName . "." . $ext;
        $thumbFile = \JPath::clean($destinationFolder . DIRECTORY_SEPARATOR . $thumbName);

        switch ($ext) {
            case "gif":
                $type = IMAGETYPE_GIF;
                break;

            case "png":
                $type = IMAGETYPE_PNG;
                break;

            case IMAGETYPE_JPEG:
            default:
                $type = IMAGETYPE_JPEG;
        }

        $thumb->toFile($thumbFile, $type);

        $this->thumbnail = $thumbName;

        // Get thumbnail parameters.
        $this->params["thumbnail"] = $this->extractParams($thumbFile);
    }

    protected function extractParams($file)
    {
        $properties = array();

        switch ($this->type) {

            case "image":
                $resource = new \JImage();
                $properties = $resource->getImageFileProperties($file);

                $properties = array(
                    "filesize" => $properties->filesize,
                    "width" => $properties->width,
                    "height" => $properties->height,
                    "mime_type" => $properties->mime,
                );

                break;

            case "video":

                break;
        }

        return $properties;
    }

    /**
     * Delete the thumbnail or the image.
     *
     * <code>
     * $resourceId = 1;
     *
     * $type       = "thumbnail"; // It is image type - thumbnail or image.
     *
     * $resource   = new MagicGallery\Resource\Resource(\JFactory::getDbo());
     * $resource->load($resourceId);
     *
     * $resource->removeImage($type);
     * </code>
     *
     * @param string  $type
     *
     * @return self
     */
    public function removeImage($type)
    {
        if (!$this->mediaFolder) {
            throw new \RuntimeException(\JText::_("LIB_MAGICGALLERY_ERROR_MISSING_MEDIA_FOLDER"));
        }

        if (!empty($this->id)) {

            switch ($type) {

                case "thumbnail":

                    // Remove an image from the filesystem
                    $file = \JPath::clean($this->mediaFolder . DIRECTORY_SEPARATOR . $this->thumbnail);
                    if (\JFile::exists($file)) {
//                        \JFile::delete($file);
                    }

                    // Reset params.
                    $this->setParam("thumbnail", array());
                    $params = json_encode($this->params);

                    // Remove the image from the DB
                    $query = $this->db->getQuery(true);
                    $query
                        ->update($this->db->quoteName("#__magicgallery_resources"))
                        ->set($this->db->quoteName("thumbnail") . ' = "" ')
                        ->set($this->db->quoteName("params") . ' = ' . $this->db->quote($params))
                        ->where($this->db->quoteName("id") . ' = ' . (int)$this->id);

                    $this->db->setQuery($query);
                    $this->db->execute();

                    break;

                case "image":

                    // Remove an image from the filesystem
                    $file = \JPath::clean($this->mediaFolder . DIRECTORY_SEPARATOR . $this->image);
                    if (\JFile::exists($file)) {
                        \JFile::delete($file);
                    }

                    // Reset params.
                    $this->setParam("image", array());
                    $params = json_encode($this->params);

                    // Remove the image from the DB
                    $query = $this->db->getQuery(true);
                    $query
                        ->update($this->db->quoteName("#__magicgallery_resources"))
                        ->set($this->db->quoteName("image") . ' = "" ')
                        ->set($this->db->quoteName("params") . ' = ' . $this->db->quote($params))
                        ->where($this->db->quoteName("id") . ' = ' . (int)$this->id);

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
     * $resourceId = 1;
     *
     * $resource   = new MagicGallery\Resource\Resource(\JFactory::getDbo());
     * $resource->load($resourceId);
     *
     * $resource->remove();
     * </code>
     *
     * @return self
     */
    public function remove()
    {
        if (!$this->mediaFolder) {
            throw new \RuntimeException(\JText::_("LIB_MAGICGALLERY_ERROR_MISSING_MEDIA_FOLDER"));
        }

        if (!empty($this->id)) {

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
                ->delete($this->db->quoteName("#__magicgallery_resources"))
                ->where($this->db->quoteName("id") . ' = ' . (int)$this->id);

            $this->db->setQuery($query);
            $this->db->execute();

            // Reset parameters.
            $this->reset();
        }

        return $this;
    }

    /**
     * Change item state to default or to not default.
     *
     * <code>
     * $resourceId = 1;
     *
     * $resource   = new MagicGallery\Resource\Resource(\JFactory::getDbo());
     * $resource->load($resourceId);
     *
     * $resource->changeDefaultState();
     * </code>
     *
     * @param int $state State : 1 = default; 0 = not default;
     *
     * @return self
     */
    public function changeDefaultState($state)
    {
        if (!empty($this->id)) {

            // Reset the states of all resources.
            if ($state == Prism\Constants::STATE_DEFAULT) {
                $query = $this->db->getQuery(true);
                $query
                    ->update($this->db->quoteName("#__magicgallery_resources"))
                    ->set($this->db->quoteName("home") . ' = ' . (int)Prism\Constants::STATE_NOT_DEFAULT)
                    ->where($this->db->quoteName("gallery_id") . ' = ' . (int)$this->gallery_id);

                $this->db->setQuery($query);
                $this->db->execute();
            }

            // Set the new state.
            $query = $this->db->getQuery(true);
            $query
                ->update($this->db->quoteName("#__magicgallery_resources"))
                ->set($this->db->quoteName("home") . ' = ' . (int)$state)
                ->where($this->db->quoteName("id") . ' = ' . (int)$this->id);

            $this->db->setQuery($query);
            $this->db->execute();
        }

        return $this;

    }

    /**
     * Return item ID.
     *
     * <code>
     * $resourceId = 1;
     *
     * $resource   = new MagicGallery\Resource\Resource(\JFactory::getDbo());
     * $resource->load($resourceId);
     *
     * if (!$resource->getId()) {
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
     * Return item title.
     *
     * <code>
     * $resourceId = 1;
     *
     * $resource   = new MagicGallery\Resource\Resource(\JFactory::getDbo());
     * $resource->load($resourceId);
     *
     * echo $resource->getTitle();
     * </code>
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Return item description.
     *
     * <code>
     * $resourceId = 1;
     *
     * $resource   = new MagicGallery\Resource\Resource(\JFactory::getDbo());
     * $resource->load($resourceId);
     *
     * echo $resource->getDescription();
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
     * $resourceId = 1;
     *
     * $resource   = new MagicGallery\Resource\Resource(\JFactory::getDbo());
     * $resource->load($resourceId);
     *
     * echo $resource->getThumbnail();
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
     * $resourceId = 1;
     *
     * $resource   = new MagicGallery\Resource\Resource(\JFactory::getDbo());
     * $resource->load($resourceId);
     *
     * echo $resource->getImage();
     * </code>
     *
     * @return mixed
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * Check if it is default item.
     *
     * <code>
     * $resourceId = 1;
     *
     * $resource   = new MagicGallery\Resource\Resource(\JFactory::getDbo());
     * $resource->load($resourceId);
     *
     * if ($resource->isDefault()) {
     * ...
     * }
     * </code>
     *
     * @return bool
     */
    public function isDefault()
    {
        return ($this->home == Prism\Constants::STATE_DEFAULT) ? true : false;
    }

    /**
     * Check if the item is published.
     *
     * <code>
     * $resourceId = 1;
     *
     * $resource   = new MagicGallery\Resource\Resource(\JFactory::getDbo());
     * $resource->load($resourceId);
     *
     * if ($resource->isPublished()) {
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
     * Return the gallery ID where this image item belongs.
     *
     * <code>
     * $resourceId = 1;
     *
     * $resource   = new MagicGallery\Resource\Resource(\JFactory::getDbo());
     * $resource->load($resourceId);
     *
     * echo $resource->getGalleryId();
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
     * $resourceId = 1;
     *
     * $resource   = new MagicGallery\Resource\Resource(\JFactory::getDbo());
     * $resource->load($resourceId);
     *
     * echo $resource->getMediaFolder();
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
     * $resourceId = 1;
     * $mediaFolder = "/.../..";
     *
     * $resource   = new MagicGallery\Resource\Resource(\JFactory::getDbo());
     * $resource->load($resourceId);
     *
     * $resource->setMediaFolder($mediaFolder);
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
     * Return the path to the image. If there is thumbnail, it will return it.
     * If there is not thumbnail, it will return the large image.
     *
     * <code>
     * $resourceId = 1;
     *
     * $resource   = new MagicGallery\Resource\Resource(\JFactory::getDbo());
     * $resource->load($resourceId);
     *
     * echo $resource;
     * </code>
     *
     * @return string
     */
    public function __toString()
    {
        return (!empty($this->thumbnail)) ? (string)$this->thumbnail : (string)$this->image;
    }

    /**
     * Return the object properties as array.
     *
     * <code>
     * $resourceId = 1;
     *
     * $resource   = new MagicGallery\Resource\Resource(\JFactory::getDbo());
     * $resource->load($resourceId);
     *
     * $resourceAsArray = $resource->toArray();
     * </code>
     *
     * @return array
     */
    public function toArray()
    {
        return $this->getProperties();
    }
}
