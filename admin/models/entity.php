<?php
/**
 * @package      Magicgallery
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2016 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

jimport('Prism.libs.Flysystem.init');
jimport('Prism.libs.Aws.init');
jimport('Prism.libs.GuzzleHttp.init');

// Register Observers
JLoader::register('MagicgalleryObserverEntity', MAGICGALLERY_PATH_COMPONENT_ADMINISTRATOR .'/tables/observers/entity.php');
JObserverMapper::addObserverClassToClass('MagicgalleryObserverEntity', 'MagicgalleryTableEntity', array('typeAlias' => 'com_magicgallery.entity'));

use Joomla\Utilities\ArrayHelper;
use Joomla\String\StringHelper;

// no direct access
defined('_JEXEC') or die;

/**
 * It is a item model.
 */
class MagicgalleryModelEntity extends JModelAdmin
{
    /**
     * Returns a reference to the a Table object, always creating it.
     *
     * @param   string $type   The table type to instantiate
     * @param   string $prefix A prefix for the table class name. Optional.
     * @param   array  $config Configuration array for model. Optional.
     *
     * @return  JTable  A database object
     * @since   1.6
     */
    public function getTable($type = 'Entity', $prefix = 'MagicgalleryTable', $config = array())
    {
        return JTable::getInstance($type, $prefix, $config);
    }

    /**
     * Method to get the record form.
     *
     * @param   array   $data     An optional array of data for the form to interrogate.
     * @param   boolean $loadData True if the form is to load its own data (default case), false if not.
     *
     * @return  JForm|bool   A JForm object on success, false on failure
     * @since   1.6
     */
    public function getForm($data = array(), $loadData = true)
    {
        // Get the form.
        $form = $this->loadForm($this->option . '.entity', 'entity', array('control' => 'jform', 'load_data' => $loadData));
        if (!$form) {
            return false;
        }

        return $form;
    }

    /**
     * Method to get the data that should be injected in the form.
     *
     * @throws \Exception
     *
     * @return  mixed   The data for the form.
     * @since   1.6
     */
    protected function loadFormData()
    {
        // Check the session for previously entered form data.
        $app  = JFactory::getApplication();
        $data = $app->getUserState($this->option . '.edit.image.data', array());

        if (!$data) {
            $data = $this->getItem();

            // Get values that was used by the user
            $data->resize = array(
                'resize_image'   => $app->getUserState($this->option . '.gallery.resize_image', Prism\Constants::NO),
                'thumb_width'    => $app->getUserState($this->option . '.gallery.thumb_width', 300),
                'thumb_height'   => $app->getUserState($this->option . '.gallery.thumb_height', 300),
                'thumb_quality'  => $app->getUserState($this->option . '.gallery.thumb_quality', 80),
                'thumb_scale'    => $app->getUserState($this->option . '.gallery.thumb_scale', JImage::SCALE_INSIDE),
                'create_thumb'   => $app->getUserState($this->option . '.gallery.create_thumb', Prism\Constants::NO),
                'image_width'    => $app->getUserState($this->option . '.gallery.image_width', 500),
                'image_height'   => $app->getUserState($this->option . '.gallery.image_height', 500),
                'image_quality'  => $app->getUserState($this->option . '.gallery.image_quality', 80),
                'image_scale'    => $app->getUserState($this->option . '.gallery.image_scale', JImage::SCALE_INSIDE)
            );

            if (!$data->gallery_id) {
                $data->gallery_id = $app->getUserStateFromRequest('com_magicgallery.entities.filter.gallery_id', 'gid', 0, 'int');
            }
        }

        return $data;
    }

    /**
     * Save project data into the DB
     *
     * @param array $data The data about project
     *
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     * @throws \UnexpectedValueException
     * @throws \League\Flysystem\FileNotFoundException
     *
     * @return   int
     */
    public function save($data)
    {
        $id          = ArrayHelper::getValue($data, 'id', 0, 'int');
        $title       = ArrayHelper::getValue($data, 'title', '', 'string');
        $description = ArrayHelper::getValue($data, 'description', '', 'string');
        $published   = ArrayHelper::getValue($data, 'published', 0, 'int');
        $galleryId   = ArrayHelper::getValue($data, 'gallery_id', 0, 'int');

        // Load a record from the database
        $row = $this->getTable();
        $row->load($id);

        $row->set('title', $title);
        $row->set('description', $description);
        $row->set('published', $published);
        $row->set('gallery_id', $galleryId);
        $row->set('type', 'image');

        // Prepare the row for saving
        $this->prepareImages($row, $data);
        $this->prepareTable($row);

        $row->store(true);

        return $row->get('id');
    }

    /**
     * Prepare and sanitise the table prior to saving.
     *
     * @param JTable $table
     *
     * @throws \RuntimeException
     * @since    1.6
     */
    protected function prepareTable($table)
    {
        // get maximum order number
        if (!$table->get('id') and !$table->get('ordering')) {
            // Set ordering to the last item if not set
            $db    = $this->getDbo();
            $query = $db->getQuery(true);
            $query
                ->select('MAX(ordering)')
                ->from($db->quoteName('#__magicgallery_entities'));

            $db->setQuery($query, 0, 1);
            $max = $db->loadResult();

            $table->set('ordering', $max + 1);
        }

        if (!$table->get('title')) {
            $table->set('title', null);
        }

        if (!$table->get('description')) {
            $table->set('description', null);
        }

        // Set the image state to default if there are no other ones.
        $db    = $this->getDbo();
        $query = $db->getQuery(true);
        $query
            ->select('COUNT(*)')
            ->from($db->quoteName('#__magicgallery_entities', 'a'))
            ->where('a.home = 1');

        $db->setQuery($query, 0, 1);
        $hasDefault = $db->loadResult();

        if (!$hasDefault) {
            $table->set('home', Prism\Constants::STATE_DEFAULT);
        }
    }

    /**
     * Prepare and sanitise the table prior to saving.
     *
     * @param JTable $table
     * @param array $data
     *
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     * @throws \UnexpectedValueException
     * @throws \League\Flysystem\FileNotFoundException
     *
     * @since    1.6
     */
    protected function prepareImages($table, $data)
    {
        $mediaFolder = ArrayHelper::getValue($data, 'media_folder', '', 'string');

        // Set the thumbnail
        if (!empty($data['thumbnail']) and $mediaFolder !== '') {
            // Delete old image if I upload the new one
            if ($table->get('thumbnail')) {
                $params           = JComponentHelper::getParams('com_magicgallery');

                $filesystemHelper = new Prism\Filesystem\Helper($params);
                $filesystem       = $filesystemHelper->getFilesystem();

                $fileThumbnail = JPath::clean($mediaFolder .'/'. $table->get('thumbnail'), '/');
                if ($filesystem->has($fileThumbnail)) {
                    $filesystem->delete($fileThumbnail);
                }
            }

            $fileMeta = $this->prepareFileMeta($data['thumbnail']);

            $table->set('thumbnail_filesize', $data['thumbnail']['filesize']);
            $table->set('thumbnail_meta', json_encode($fileMeta));
            $table->set('thumbnail', $data['thumbnail']['filename']);
        }

        // Sets the images
        if (!empty($data['image']) and $mediaFolder !== '') {
            // Delete old image if I upload a new one.
            if ($table->get('image')) {
                $params           = JComponentHelper::getParams('com_magicgallery');

                $filesystemHelper = new Prism\Filesystem\Helper($params);
                $filesystem       = $filesystemHelper->getFilesystem();

                $fileImage = JPath::clean($mediaFolder .'/'. $table->get('image'), '/');
                if ($filesystem->has($fileImage)) {
                    $filesystem->delete($fileImage);
                }
            }

            $fileMeta = $this->prepareFileMeta($data['image']);

            $table->set('image_filesize', $data['image']['filesize']);
            $table->set('image_meta', json_encode($fileMeta));
            $table->set('image', $data['image']['filename']);
        }
    }

    protected function prepareFileMeta(array $data)
    {
        $fileMeta = array();

        $fileMeta['mime'] = ArrayHelper::getValue($data, 'mime');

        $attributes = ArrayHelper::getValue($data, 'attributes');
        if ($attributes !== null) {
            $fileMeta['width']  = ArrayHelper::getValue($attributes, 'width');
            $fileMeta['height'] = ArrayHelper::getValue($attributes, 'height');
        }

        return $fileMeta;
    }

    /**
     * A protected method to get a set of ordering conditions.
     *
     * @param    JTable $table A record object.
     *
     * @return   array    An array of conditions to add to add to ordering queries.
     * @since    1.6
     */
    protected function getReorderConditions($table)
    {
        $condition   = array();
        $condition[] = 'gallery_id = ' . (int)$table->get('gallery_id');

        return $condition;
    }

    /**
     * Upload the file. This method can create thumbnail or to resize the file.
     *
     * @param array $uploadedFileData
     * @param array $options
     * @param League\Flysystem\MountManager $filesystemManager
     *
     * @throws Exception
     *
     * @return array
     */
    public function uploadImage($uploadedFileData, $options, $filesystemManager)
    {
        $itemData     = ['image' => array(), 'thumbnail' => array()];

        $uploadedFile = Joomla\Utilities\ArrayHelper::getValue($uploadedFileData, 'tmp_name');
        $uploadedName = Joomla\Utilities\ArrayHelper::getValue($uploadedFileData, 'name');
        $errorCode    = Joomla\Utilities\ArrayHelper::getValue($uploadedFileData, 'error');

        // Prepare file size validator
        $fileSizeValidator = new Prism\File\Validator\Size($options['validation']['content_length'], $options['validation']['upload_maxsize']);

        // Prepare server validator.
        $serverValidator   = new Prism\File\Validator\Server($errorCode, array(UPLOAD_ERR_NO_FILE));

        // Prepare image validator.
        $imageValidator    = new Prism\File\Validator\Image($uploadedFile, $uploadedName);

        // Get allowed mime types from media manager options
        $options['validation']['legal_types'] = StringHelper::trim($options['validation']['legal_types']);
        if ($options['validation']['legal_types']) {
            $mimeTypes = explode(',', $options['validation']['legal_types']);
            $mimeTypes = array_map('JString::trim', $mimeTypes);
            $imageValidator->setMimeTypes($mimeTypes);
        }

        // Get allowed image extensions from media manager options
        $options['validation']['legal_extensions'] = StringHelper::trim($options['validation']['legal_extensions']);
        if ($options['validation']['legal_extensions']) {
            $imageExtensions = explode(',', $options['validation']['legal_extensions']);
            $imageExtensions = array_map('JString::trim', $imageExtensions);
            $imageValidator->setImageExtensions($imageExtensions);
        }

        // Prepare image size validator.
        $imageSizeValidator = new Prism\File\Validator\Image\Size($uploadedFile);
        $imageSizeValidator->setMinWidth($options['validation']['image_width']);
        $imageSizeValidator->setMinHeight($options['validation']['image_height']);

        $file = new Prism\File\File($uploadedFile);
        $file
            ->addValidator($fileSizeValidator)
            ->addValidator($serverValidator)
            ->addValidator($imageValidator)
            ->addValidator($imageSizeValidator);

        // Validate the file.
        if (!$file->isValid()) {
            throw new RuntimeException($file->getError());
        }

        // Upload the file in temporary folder.
        $filesystemLocal = new \Prism\Filesystem\Adapter\Local($options['path']['temporary_folder']);
        $filePath        = $filesystemLocal->upload($uploadedFileData);

        // Resize the image.
        if (array_key_exists('resize_image', $options['resize']) and (int)$options['resize']['resize_image'] === Prism\Constants::OK) {
            $resizeOptions = new \Joomla\Registry\Registry;
            $resizeOptions->set('width', $options['resize']['image_width']);
            $resizeOptions->set('height', $options['resize']['image_height']);
            $resizeOptions->set('scale', $options['resize']['image_scale']);
            $resizeOptions->set('quality', $options['resize']['image_quality']);
            $resizeOptions->set('filename_length', 16);
            $resizeOptions->set('create_new', Prism\Constants::NO);

            $image      = new Prism\File\Image($filePath);
            $fileData   = $image->resize($options['path']['temporary_folder'], $resizeOptions);

            // Remove the original file.
            if (JFile::exists($filePath)) {
                JFile::delete($filePath);
            }

            // Set resized file as original. I will use it to create a thumbnail, if it is allowed.
            $filePath = $fileData['filepath'];
            unset($fileData['filepath']);
        } else {
            // Prepare meta data about the file if it is not resized.
            $file     = new Prism\File\File($filePath);
            $fileData = $file->extractFileData();
        }

        // Copy the file to storage.
        $filesystemManager->copy('temporary://'.$fileData['filename'], 'storage://'. JPath::clean($options['path']['media_folder'].'/'.$fileData['filename'], '/'));

        // Generate thumbnail.
        $thumbnailData = array();
        if (array_key_exists('create_thumb', $options['resize']) and (int)$options['resize']['create_thumb'] === Prism\Constants::OK) {
            $resizeOptions = new \Joomla\Registry\Registry;
            $resizeOptions->set('width', $options['resize']['thumb_width']);
            $resizeOptions->set('height', $options['resize']['thumb_height']);
            $resizeOptions->set('scale', $options['resize']['thumb_scale']);
            $resizeOptions->set('quality', $options['resize']['thumb_quality']);
            $resizeOptions->set('filename_length', 16);
            $resizeOptions->set('create_new', Prism\Constants::NO);
            $resizeOptions->set('prefix', 'thumb_');

            $image                 = new Prism\File\Image($filePath);
            $thumbnailData         = $image->resize($options['path']['temporary_folder'], $resizeOptions);
            unset($thumbnailData['filepath']);

            $filesystemManager->move('temporary://'.$thumbnailData['filename'], 'storage://'. JPath::clean($options['path']['media_folder'].'/'.$thumbnailData['filename'], '/'));
        }

        // Remove the original file.
        if (JFile::exists($filePath)) {
            JFile::delete($filePath);
        }

        // Store it as item.
        if (count($fileData) > 0) {
            // Prepare data that will be stored as gallery item.
            $itemData['image'] = $fileData;
            unset($fileData);

            // Set the thumbnail name.
            if (count($thumbnailData) > 0) {
                $itemData['thumbnail'] = $thumbnailData;
                unset($thumbnailData);
            }
        }

        return $itemData;
    }

    /**
     * Upload the file. This method can create thumbnail or to resize the file.
     *
     * @param array $uploadedFileData
     * @param array $options
     * @param League\Flysystem\MountManager $filesystemManager
     *
     * @throws Exception
     *
     * @return array
     */
    public function uploadThumbnail($uploadedFileData, $options, $filesystemManager)
    {
        $uploadedFile = Joomla\Utilities\ArrayHelper::getValue($uploadedFileData, 'tmp_name');
        $uploadedName = Joomla\Utilities\ArrayHelper::getValue($uploadedFileData, 'name');
        $errorCode    = Joomla\Utilities\ArrayHelper::getValue($uploadedFileData, 'error');

        // Prepare file size validator
        $fileSizeValidator = new Prism\File\Validator\Size($options['validation']['content_length'], $options['validation']['upload_maxsize']);

        // Prepare server validator.
        $serverValidator = new Prism\File\Validator\Server($errorCode, array(UPLOAD_ERR_NO_FILE));

        // Prepare image validator.
        $imageValidator = new Prism\File\Validator\Image($uploadedFile, $uploadedName);

        // Get allowed mime types from media manager options
        $options['validation']['legal_types'] = StringHelper::trim($options['validation']['legal_types']);
        if ($options['validation']['legal_types']) {
            $mimeTypes = explode(',', $options['validation']['legal_types']);
            $mimeTypes = array_map('JString::trim', $mimeTypes);
            $imageValidator->setMimeTypes($mimeTypes);
        }

        // Get allowed image extensions from media manager options
        $options['validation']['legal_extensions'] = StringHelper::trim($options['validation']['legal_extensions']);
        if ($options['validation']['legal_extensions']) {
            $imageExtensions = explode(',', $options['validation']['legal_extensions']);
            $imageExtensions = array_map('JString::trim', $imageExtensions);
            $imageValidator->setImageExtensions($imageExtensions);
        }

        $file = new Prism\File\File($uploadedFile);
        $file
            ->addValidator($fileSizeValidator)
            ->addValidator($serverValidator)
            ->addValidator($imageValidator);

        // Validate the file.
        if (!$file->isValid()) {
            JLog::add($file->getErrorAdditionalInformation(), JLog::ERROR, 'com_magicgallery');
            throw new RuntimeException($file->getError());
        }

        // Upload the file in temporary folder.
        $filesystemLocal = new Prism\Filesystem\Adapter\Local($options['path']['temporary_folder']);
        $filePath        = $filesystemLocal->upload($uploadedFileData);

        // Prepare meta data about the file if it is not resized.
        $file     = new Prism\File\File($filePath);
        $fileData = $file->extractFileData();

        // Copy the file to storage.
        $sourceFilename       = $fileData['filename'];
        $fileData['filename'] = 'thumb_'.$fileData['filename'];

        // Copy the file to storage.
        $filesystemManager->copy('temporary://'.$sourceFilename, 'storage://'. JPath::clean($options['path']['media_folder'].'/'.$fileData['filename'], '/'));

        // Remove the original file.
        if (JFile::exists($filePath)) {
            JFile::delete($filePath);
        }
        
        return $fileData;
    }
}
