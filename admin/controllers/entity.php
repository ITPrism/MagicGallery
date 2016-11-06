<?php
/**
 * @package      Magicgallery
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2016 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

use Joomla\Utilities\ArrayHelper;

// No direct access
defined('_JEXEC') or die;

/**
 * Entity controller class.
 *
 * @package        Magicgallery
 * @subpackage     Components
 * @since          1.6
 */
class MagicgalleryControllerEntity extends Prism\Controller\Form\Backend
{
    /**
     * Proxy method that returns model.
     *
     * @param string $name
     * @param string $prefix
     * @param array  $config
     *
     * @return MagicgalleryModelEntity
     */
    public function getModel($name = 'Entity', $prefix = 'MagicgalleryModel', $config = array('ignore_request' => true))
    {
        $model = parent::getModel($name, $prefix, $config);
        return $model;
    }
    
    public function save($key = null, $urlVar = null)
    {
        JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

        $app = JFactory::getApplication();
        /** @var $app JApplicationAdministrator */

        // Gets the data from the form
        $data   = $app->input->post->get('jform', array(), 'array');
        $itemId = ArrayHelper::getValue($data, 'id', 0, 'int');

        // Redirect options
        $redirectOptions = array(
            'task' => $this->getTask(),
            'id'   => $itemId
        );

        $model = $this->getModel();
        /** @var $model MagicgalleryModelEntity */

        $form = $model->getForm($data, false);
        /** @var $form JForm */

        if (!$form) {
            throw new Exception(JText::_('COM_MAGICGALLERY_ERROR_FORM_CANNOT_BE_LOADED'));
        }

        // Test for valid data.
        $validData = $model->validate($form, $data);

        // Check for validation errors.
        if ($validData === false) {
            $this->displayWarning($form->getErrors(), $redirectOptions);
            return;
        }

        try {
            // Get image
            $thumbFile = $app->input->files->get('jform', array(), 'array');
            $thumbFile = ArrayHelper::getValue($thumbFile, 'thumbnail');

            $imageFile = $app->input->files->get('jform', array(), 'array');
            $imageFile = ArrayHelper::getValue($imageFile, 'image');

            // Upload image
            if (!empty($imageFile['name']) or !empty($thumbFile['name'])) {
                // Magic Gallery global options.
                $params              = JComponentHelper::getParams('com_magicgallery');

                $gallery             = new Magicgallery\Gallery\Gallery(JFactory::getDbo());
                $gallery->load($validData['gallery_id']);

                $filesystemHelper    = new Prism\Filesystem\Helper($params);
                
                // Get media folder from gallery options.
                $pathHelper          = new Magicgallery\Helper\Path($filesystemHelper);
                $mediaFolder         = $pathHelper->getMediaFolder($gallery);
                if (!$mediaFolder) {
                    throw new RuntimeException(JText::_('COM_MAGICGALLERY_ERROR_INVALID_MEDIA_FOLDER'));
                }

                // Prepare temporary filesystem.
                $temporaryFolder     = JPath::clean($app->get('tmp_path'));
                $temporaryAdapter    = new League\Flysystem\Adapter\Local($temporaryFolder);
                $temporaryFilesystem = new League\Flysystem\Filesystem($temporaryAdapter);

                // Prepare storage filesystem.
                $storageFilesystem   = $filesystemHelper->getFilesystem();

                $filesystemManager = new League\Flysystem\MountManager([
                    'temporary' => $temporaryFilesystem,
                    'storage'   => $storageFilesystem
                ]);

                // Get resize options.
                $options     = ArrayHelper::getValue($validData, 'resize', array(), 'array');

                // Set option states.
                $resizeImage = ArrayHelper::getValue($options, 'resize_image', Prism\Constants::NO, 'int');
                $app->setUserState($this->option . '.gallery.resize_image', $resizeImage);
                $app->setUserState($this->option . '.gallery.image_width', ArrayHelper::getValue($options, 'image_width'));
                $app->setUserState($this->option . '.gallery.image_height', ArrayHelper::getValue($options, 'image_height'));
                $app->setUserState($this->option . '.gallery.image_scale', ArrayHelper::getValue($options, 'image_scale', \JImage::SCALE_INSIDE));
                $app->setUserState($this->option . '.gallery.image_quality', ArrayHelper::getValue($options, 'image_quality', 80));

                $app->setUserState($this->option . '.gallery.create_thumb', ArrayHelper::getValue($options, 'create_thumb', Prism\Constants::NO, 'int'));
                $app->setUserState($this->option . '.gallery.thumb_width', ArrayHelper::getValue($options, 'thumb_width', 200));
                $app->setUserState($this->option . '.gallery.thumb_height', ArrayHelper::getValue($options, 'thumb_width', 200));
                $app->setUserState($this->option . '.gallery.thumb_scale', ArrayHelper::getValue($options, 'thumb_width', \JImage::SCALE_INSIDE));
                $app->setUserState($this->option . '.gallery.thumb_quality', ArrayHelper::getValue($options, 'thumb_quality', 80));

                $uploadOptions = array(
                    'path' => array(
                        'temporary_folder' => $temporaryFolder, // Full path to temporary folder.
                        'media_folder'     => $mediaFolder // Relative path to media folder.
                    ),
                    'validation' => array(
                        'content_length'   => (int)$app->input->server->get('CONTENT_LENGTH'),
                        'upload_maxsize'   => (int)$params->get('max_size', 5) * (1024 * 1024),
                        'legal_types'      => $params->get('legal_types', 'image/jpeg, image/gif, image/png, image/bmp'),
                        'legal_extensions' => $params->get('legal_extensions', 'bmp, gif, jpg, jpeg, png'),
                        'image_width'      => (!$resizeImage) ? 0 : (int)$options['image_width'],
                        'image_height'     => (!$resizeImage) ? 0 : (int)$options['image_height']
                    ),
                    'resize' => $options
                );

                // Upload image
                if (!empty($imageFile['name'])) {
                    $result = $model->uploadImage($imageFile, $uploadOptions, $filesystemManager);

                    if (count($result) > 0) {
                        $validData = array_merge($validData, $result);
                    }
                }

                // Upload thumbnail
                if (!empty($thumbFile['name']) and empty($validData['thumbnail'])) {
                    $result = $model->uploadThumbnail($thumbFile, $uploadOptions, $filesystemManager);

                    if (count($result) > 0) {
                        $validData['thumbnail'] = $result;
                    }
                }

                // Set the media folder, where the system should look for files.
                $validData['media_folder'] = $mediaFolder;
            }

            $redirectOptions['id'] = $model->save($validData);

        } catch (RuntimeException $e) {
            $this->displayWarning($e->getMessage(), $redirectOptions);
            return;
        } catch (Exception $e) {
            JLog::add($e->getMessage(), JLog::ERROR, 'com_magicgallery');
            throw new Exception(JText::_('COM_MAGICGALLERY_ERROR_SYSTEM'));
        }

        $this->displayMessage(JText::_('COM_MAGICGALLERY_ITEM_SAVED'), $redirectOptions);
    }

    public function removeImage()
    {
        $app = JFactory::getApplication();
        /** @var $app JApplicationAdministrator */

        $itemId = $app->input->get->getInt('id', 0);
        $type   = $app->input->get->getCmd('type');
        if (!$itemId) {
            throw new Exception(JText::_('COM_MAGICGALLERY_ERROR_IMAGE_DOES_NOT_EXIST'));
        }

        // Redirect options
        $redirectOptions = array(
            'view' => 'entity',
            'id'   => $itemId
        );

        try {
            $image = new Magicgallery\Entity\Entity(JFactory::getDbo());
            $image->load($itemId);

            if ($image->getId()) {
                jimport('Prism.libs.Flysystem.init');
                jimport('Prism.libs.Aws.init');
                jimport('Prism.libs.GuzzleHttp.init');
                
                $gallery         = new Magicgallery\Gallery\Gallery(JFactory::getDbo());
                $gallery->load($image->getGalleryId());

                $params              = JComponentHelper::getParams('com_magicgallery');
                $filesystemHelper    = new Prism\Filesystem\Helper($params);

                // Get media folder from gallery options.
                $pathHelper          = new Magicgallery\Helper\Path($filesystemHelper);
                $mediaFolder         = $pathHelper->getMediaFolder($gallery);
                if (!$mediaFolder) {
                    throw new RuntimeException(JText::_('COM_MAGICGALLERY_ERROR_INVALID_MEDIA_FOLDER'));
                }

                // Prepare storage filesystem.
                $storageFilesystem   = $filesystemHelper->getFilesystem();

                // Remove the file from storage.
                $filename  = (strcmp('image', $type) === 0) ? $image->getImage() : $image->getThumbnail();
                $filepath  = JPath::clean($mediaFolder .'/'. $filename, '/');

                if ($storageFilesystem->has($filepath)) {
                    $storageFilesystem->delete($filepath);
                }

                // Remove the record of the file.
                $image->removeImage($type);
            }

        } catch (Exception $e) {
            JLog::add($e->getMessage(), JLog::ERROR, 'com_magicgallery');
            throw new Exception(JText::_('COM_MAGICGALLERY_ERROR_SYSTEM'));
        }

        // Display message.
        if (strcmp('thumb', $type) === 0) {
            $msg = JText::_('COM_MAGICGALLERY_THUMB_DELETED');
        } else {
            $msg = JText::_('COM_MAGICGALLERY_IMAGE_DELETED');
        }

        $this->displayMessage($msg, $redirectOptions);
    }
}
