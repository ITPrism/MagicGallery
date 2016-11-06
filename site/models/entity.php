<?php
/**
 * @package      Magicgallery
 * @subpackage   Component
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2016 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

use Joomla\Utilities\ArrayHelper;
use Joomla\String\StringHelper;

// No direct access
defined('_JEXEC') or die;

/**
 * Entity model Model
 *
 * @package        Magicgallery
 * @subpackage     Component
 */
class MagicgalleryModelEntity extends JModelLegacy
{
    /**
     * Remove an entity.
     *
     * @param int $itemId
     * @param Magicgallery\Gallery\Gallery $gallery
     *
     * @throws Exception
     *
     * @return array
     */
    public function remove($itemId, $gallery)
    {
        $db = $this->getDbo();

        $query = $db->getQuery(true);

        $query
            ->select('a.image, a.thumbnail')
            ->from($db->quoteName('#__magicgallery_entities', 'a'))
            ->where('a.id = ' . (int)$itemId)
            ->where('a.gallery_id = ' . (int)$gallery->getId());

        $db->setQuery($query, 0, 1);
        $result = (array)$db->loadAssoc();

        if (count($result) > 0) {
            jimport('Prism.libs.Flysystem.init');
            jimport('Prism.libs.Aws.init');
            jimport('Prism.libs.GuzzleHttp.init');

            // Magic Gallery global options.
            $params = JComponentHelper::getParams('com_magicgallery');

            // Get filesystem.
            $filesystemHelper = new Prism\Filesystem\Helper($params);
            $filesystem  = $filesystemHelper->getFilesystem();

            // Prepare media folder.
            $pathHelper       = new Magicgallery\Helper\Path($filesystemHelper);
            $mediaFolder = $pathHelper->getMediaFolder($gallery);
            
            $fileImage     = JPath::clean($mediaFolder .'/'. $result['image'], '/');
            $fileThumbnail = JPath::clean($mediaFolder .'/'. $result['thumbnail'], '/');

            if ($filesystem->has($fileImage)) {
                $filesystem->delete($fileImage);
            }

            if ($filesystem->has($fileThumbnail)) {
                $filesystem->delete($fileThumbnail);
            }

            // Remove the item record in database.
            $query = $db->getQuery(true);
            $query
                ->delete($db->quoteName('#__magicgallery_entities'))
                ->where($db->quoteName('id') . ' = ' . (int)$itemId)
                ->where($db->quoteName('gallery_id') . ' = ' . (int)$gallery->getId());

            $db->setQuery($query);
            $db->execute();
        }
    }

    /**
     * Upload the file. This method can create thumbnail or to resize the file.
     *
     * @param array $uploadedFileData
     * @param array $options
     * @param League\Flysystem\MountManager $filesystemManager
     * @param int $galleryId
     *
     * @throws Exception
     *
     * @return array
     */
    public function upload($uploadedFileData, $options, $filesystemManager, $galleryId)
    {
        $uploadedFile = ArrayHelper::getValue($uploadedFileData, 'tmp_name');
        $uploadedName = ArrayHelper::getValue($uploadedFileData, 'name');
        $errorCode    = ArrayHelper::getValue($uploadedFileData, 'error');

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
            $resizeOptions->set('width', ArrayHelper::getValue($options['resize'], 'image_width'));
            $resizeOptions->set('height', ArrayHelper::getValue($options['resize'], 'image_height'));
            $resizeOptions->set('scale', ArrayHelper::getValue($options['resize'], 'image_scale'));
            $resizeOptions->set('quality', ArrayHelper::getValue($options['resize'], 'image_quality', 80, 'int'));
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
            $resizeOptions->set('width', ArrayHelper::getValue($options['resize'], 'thumb_width'));
            $resizeOptions->set('height', ArrayHelper::getValue($options['resize'], 'thumb_height'));
            $resizeOptions->set('scale', ArrayHelper::getValue($options['resize'], 'thumb_scale'));
            $resizeOptions->set('quality', ArrayHelper::getValue($options['resize'], 'thumb_quality', 80, 'int'));
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

        // Prepare item data that will be returned.
        $itemData = array();
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

        // Store it as item.
        if (count($itemData) > 0) {
            $bindData = array();
            if (array_key_exists('image', $itemData) and count($itemData['image']) > 0) {
                $bindData = [
                    'gallery_id'     => (int)$galleryId,
                    'type'           => 'image',
                    'title'          => $itemData['image']['filename'],
                    'image'          => $itemData['image']['filename'],
                    'image_filesize' => $itemData['image']['filesize'],
                    'image_meta'     => [
                        'mime'     => $itemData['image']['mime'],
                        'filesize' => $itemData['image']['filesize'],
                        'width'    => $itemData['image']['attributes']['width'],
                        'height'   => $itemData['image']['attributes']['height']
                    ]
                ];
            }

            if (array_key_exists('thumbnail', $itemData) and count($itemData['thumbnail']) > 0) {
                $bindData['thumbnail']          = $itemData['thumbnail']['filename'];
                $bindData['thumbnail_filesize'] = $itemData['thumbnail']['filesize'];
                $bindData['thumbnail_meta'] = [
                    'mime'     => $itemData['thumbnail']['mime'],
                    'filesize' => $itemData['thumbnail']['filesize'],
                    'width'    => $itemData['thumbnail']['attributes']['width'],
                    'height'   => $itemData['thumbnail']['attributes']['height']
                ];
            }

            if (count($bindData) > 0) {
                $item = new Magicgallery\Entity\Entity(JFactory::getDbo());
                $item->bind($bindData);
                $item->setStatus($options['item']['default_item_status']);

                $item->store();
                $itemData['id'] = $item->getId();
            }
        }

        return $itemData;
    }
}
