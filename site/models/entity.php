<?php
/**
 * @package      MagicGallery
 * @subpackage   Component
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

/**
 * Entity model Model
 *
 * @package        MagicGallery
 * @subpackage     Component
 */
class MagicGalleryModelEntity extends JModelLegacy
{
    /**
     * Remove an entity.
     *
     * @param int $itemId
     * @param Magicgallery\Gallery\Gallery $gallery
     * @param string $mediaFolder
     *
     * @throws Exception
     *
     * @return array
     */
    public function remove($itemId, $gallery, $mediaFolder)
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
            $localAdapter  = new League\Flysystem\Adapter\Local(JPATH_ROOT);

            $filesystem    = new League\Flysystem\Filesystem($localAdapter);

            $fileImage     = JPath::clean($mediaFolder .'/'. $result['image']);
            $fileThumbnail = JPath::clean($mediaFolder .'/'. $result['thumbnail']);

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
     * @param array $file
     * @param array $options
     * @param int $galleryId
     *
     * @throws Exception
     *
     * @return array
     */
    public function upload($file, $options, $galleryId)
    {
        $itemData = array();

        jimport('Prism.libs.Flysystem.init');
        $temporaryAdapter    = new League\Flysystem\Adapter\Local($options['path']['temporary_folder']);
        $storageAdapter      = new League\Flysystem\Adapter\Local($options['path']['media_folder']);
        $temporaryFilesystem = new League\Flysystem\Filesystem($temporaryAdapter);
        $storageFilesystem   = new League\Flysystem\Filesystem($storageAdapter);

        $manager = new League\Flysystem\MountManager([
            'temporary' => $temporaryFilesystem,
            'storage'   => $storageFilesystem
        ]);

        $image = new Prism\File\Image($file, $options['path']['temporary_folder']);

        $uploadedFile = Joomla\Utilities\ArrayHelper::getValue($file, 'tmp_name');
        $uploadedName = Joomla\Utilities\ArrayHelper::getValue($file, 'name');
        $errorCode    = Joomla\Utilities\ArrayHelper::getValue($file, 'error');

        // Prepare file size validator
        $fileSizeValidator = new Prism\File\Validator\Size($options['validation']['content_length'], $options['validation']['upload_maxsize']);

        // Prepare server validator.
        $serverValidator = new Prism\File\Validator\Server($errorCode, array(UPLOAD_ERR_NO_FILE));

        // Prepare image validator.
        $imageValidator = new Prism\File\Validator\Image($uploadedFile, $uploadedName);

        // Get allowed mime types from media manager options
        $options['validation']['legal_types'] = \JString::trim($options['validation']['legal_types']);
        if ($options['validation']['legal_types']) {
            $mimeTypes = explode(',', $options['validation']['legal_types']);
            $mimeTypes = array_map('JString::trim', $mimeTypes);
            $imageValidator->setMimeTypes($mimeTypes);
        }

        // Get allowed image extensions from media manager options
        $options['validation']['legal_extensions'] = \JString::trim($options['validation']['legal_extensions']);
        if ($options['validation']['legal_extensions']) {
            $imageExtensions = explode(',', $options['validation']['legal_extensions']);
            $imageExtensions = array_map('JString::trim', $imageExtensions);
            $imageValidator->setImageExtensions($imageExtensions);
        }

        // Prepare image size validator.
        $imageSizeValidator = new Prism\File\Validator\Image\Size($uploadedFile);
        $imageSizeValidator->setMinWidth($options['validation']['image_width']);
        $imageSizeValidator->setMinHeight($options['validation']['image_height']);

        $image
            ->addValidator($fileSizeValidator)
            ->addValidator($serverValidator)
            ->addValidator($imageValidator)
            ->addValidator($imageSizeValidator);

        // Validate the file.
        if (!$image->isValid()) {
            throw new RuntimeException($image->getError());
        }

        // Upload the file.
        $image->upload();

        $mainImage = array();

        if (array_key_exists('resize_image', $options['resize']) and (int)$options['resize']['resize_image'] === 1) {
            $resizeOptions = array(
                'width'  => $options['resize']['image_width'],
                'height' => $options['resize']['image_height'],
                'scale'  => $options['resize']['image_scale']
            );

            $mainImage  = $image->resize($resizeOptions, Prism\Constants::REPLACE);
            $filename   = basename($mainImage['filename']);
            $manager->copy('temporary://'.$filename, 'storage://'.$filename);
        }

        // Generate thumbnail.
        $thumbnailData = array();
        if (array_key_exists('create_thumb', $options['resize']) and (int)$options['resize']['create_thumb'] === 1) {
            $resizeOptions = array(
                'width'  => $options['resize']['thumb_width'],
                'height' => $options['resize']['thumb_height'],
                'scale'  => $options['resize']['thumb_scale']
            );

            $thumbnailData   = $image->resize($resizeOptions, Prism\Constants::DO_NOT_REPLACE, 'thumb_');
            $filename        = basename($thumbnailData['filename']);
            $manager->move('temporary://'.$filename, 'storage://'.$filename);
        }

        // Remove the original file.
        $image->remove();

        // Store it as item.
        if (count($mainImage) > 0) {

            // Prepare data that will be stored as gallery item.
            $itemData = array(
                'image'    => $mainImage['filename'],
                'width'    => $mainImage['width'],
                'height'   => $mainImage['height'],
                'filesize' => $mainImage['filesize'],
                'mime'     => $mainImage['mime'],
                'type'     => 'image',
                'gallery_id' => (int)$galleryId
            );
            unset($mainImage);

            // Set the thumbnail name.
            if (count($thumbnailData) > 0) {
                $itemData['thumbnail'] = $thumbnailData['filename'];
            }

            $item = new Magicgallery\Entity\Entity(JFactory::getDbo());
            $item->bind($itemData);
            $item->setStatus($options['item']['default_item_status']);

            $item->store();

            $itemData['id'] = $item->getId();
        }

        return $itemData;
    }
}
