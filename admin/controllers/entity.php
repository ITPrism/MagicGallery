<?php
/**
 * @package      Magicgallery
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2016 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

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
        $itemId = Joomla\Utilities\ArrayHelper::getValue($data, 'id', 0, 'int');

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
            $thumbFile = Joomla\Utilities\ArrayHelper::getValue($thumbFile, 'thumbnail');

            $imageFile = $app->input->files->get('jform', array(), 'array');
            $imageFile = Joomla\Utilities\ArrayHelper::getValue($imageFile, 'image');

            // Upload image
            if (!empty($imageFile['name']) or !empty($thumbFile['name'])) {

                // Magic Gallery global options.
                $params          = JComponentHelper::getParams('com_magicgallery');

                $gallery         = new Magicgallery\Gallery\Gallery(JFactory::getDbo());
                $gallery->load($validData['gallery_id']);

                $mediaFolder     = MagicgalleryHelper::getMediaFolder($params, $gallery);
                if (!$mediaFolder) {
                    throw new Exception(JText::_('COM_MAGICGALLERY_ERROR_INVALID_MEDIA_FOLDER'));
                }

                $mediaFolder = JPath::clean(JPATH_ROOT . '/' . $mediaFolder);

                // Get image options.
                $options = Joomla\Utilities\ArrayHelper::getValue($validData, 'resize', array(), 'array');

                // Set option states.
                $resizeImage = Joomla\Utilities\ArrayHelper::getValue($options, 'resize_image', 0, 'int');
                $app->setUserState($this->option . '.gallery.resize_image', $resizeImage);
                $app->setUserState($this->option . '.gallery.image_width', Joomla\Utilities\ArrayHelper::getValue($options, 'image_width'));
                $app->setUserState($this->option . '.gallery.image_height', Joomla\Utilities\ArrayHelper::getValue($options, 'image_height'));
                $app->setUserState($this->option . '.gallery.image_scale', Joomla\Utilities\ArrayHelper::getValue($options, 'image_scale', \JImage::SCALE_INSIDE));

                $app->setUserState($this->option . '.gallery.create_thumb', Joomla\Utilities\ArrayHelper::getValue($options, 'create_thumb', 0, 'int'));
                $app->setUserState($this->option . '.gallery.thumb_width', Joomla\Utilities\ArrayHelper::getValue($options, 'thumb_width', 200));
                $app->setUserState($this->option . '.gallery.thumb_height', Joomla\Utilities\ArrayHelper::getValue($options, 'thumb_width', 200));
                $app->setUserState($this->option . '.gallery.thumb_scale', Joomla\Utilities\ArrayHelper::getValue($options, 'thumb_width', \JImage::SCALE_INSIDE));

                $uploadOptions = array(
                    'path' => array(
                        'temporary_folder' => JPath::clean($app->get('tmp_path')),
                        'media_folder'     => $mediaFolder
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

                    $result = $model->uploadImage($imageFile, $uploadOptions);

                    if (count($result) > 0) {
                        $validData = array_merge($validData, $result);
                    }
                }

                // Upload thumbnail
                if (!empty($thumbFile['name']) and empty($validData['thumbnail'])) {

                    $result = $model->uploadThumbnail($thumbFile, $uploadOptions);

                    if (count($result) > 0) {
                        $validData['thumbnail'] = $result['filename'];
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

            JLog::add($e->getMessage());
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
                $params = JComponentHelper::getParams('com_magicgallery');

                $gallery         = new Magicgallery\Gallery\Gallery(JFactory::getDbo());
                $gallery->load($image->getGalleryId());

                $mediaFolder     = MagicgalleryHelper::getMediaFolder($params, $gallery);
                if (!$mediaFolder) {
                    throw new Exception(JText::_('COM_MAGICGALLERY_ERROR_INVALID_MEDIA_FOLDER'));
                }

                $mediaFolder = JPath::clean(JPATH_ROOT . '/' . $mediaFolder);

                $image->setMediaFolder($mediaFolder);
                $image->removeImage($type);
            }

        } catch (Exception $e) {
            JLog::add($e->getMessage());
            throw new Exception(JText::_('COM_MAGICGALLERY_ERROR_SYSTEM'));
        }

        // Display message
        if (strcmp('thumb', $type) === 0) {
            $msg = JText::_('COM_MAGICGALLERY_THUMB_DELETED');
        } else {
            $msg = JText::_('COM_MAGICGALLERY_IMAGE_DELETED');
        }

        $this->displayMessage($msg, $redirectOptions);
    }
}
