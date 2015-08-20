<?php
/**
 * @package      MagicGallery
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2014 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

/**
 * Resource controller class.
 *
 * @package        MagicGallery
 * @subpackage     Components
 * @since          1.6
 */
class MagicGalleryControllerResource extends Prism\Controller\Form\Backend
{
    public function getModel($name = 'Resource', $prefix = 'MagicGalleryModel', $config = array('ignore_request' => true))
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
        $itemId = Joomla\Utilities\ArrayHelper::getValue($data, "id", 0, "int");

        // Redirect options
        $redirectOptions = array(
            "task" => $this->getTask(),
            "id"   => $itemId
        );

        $model = $this->getModel();
        /** @var $model MagicGalleryModelGallery */

        $form = $model->getForm($data, false);
        /** @var $form JForm */

        if (!$form) {
            throw new Exception(JText::_("COM_MAGICGALLERY_ERROR_FORM_CANNOT_BE_LOADED"));
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
            $thumbFile = Joomla\Utilities\ArrayHelper::getValue($thumbFile, "thumbnail");

            $imageFile = $app->input->files->get('jform', array(), 'array');
            $imageFile = Joomla\Utilities\ArrayHelper::getValue($imageFile, "image");

            // Upload image
            if (!empty($imageFile['name']) or !empty($thumbFile['name'])) {

                $params = JComponentHelper::getParams($this->option);

                // Get image options.
                $options = Joomla\Utilities\ArrayHelper::getValue($validData, "resize", array(), "array");
                $options["destination_folder"] = \JPath::clean(JPATH_ROOT . DIRECTORY_SEPARATOR . $params->get("media_folder", "images/magicgallery"));

                // Set option states.
                $app->setUserState($this->option . ".gallery.resize_image", Joomla\Utilities\ArrayHelper::getValue($options, "resize_image", 0, "int"));
                $app->setUserState($this->option . ".gallery.image_width", Joomla\Utilities\ArrayHelper::getValue($options, "image_width", 500));
                $app->setUserState($this->option . ".gallery.image_height", Joomla\Utilities\ArrayHelper::getValue($options, "image_height", 500));
                $app->setUserState($this->option . ".gallery.image_scale", Joomla\Utilities\ArrayHelper::getValue($options, "image_scale", \JImage::SCALE_INSIDE));

                $app->setUserState($this->option . ".gallery.create_thumb", Joomla\Utilities\ArrayHelper::getValue($options, "create_thumb", 0, "int"));
                $app->setUserState($this->option . ".gallery.thumb_width", Joomla\Utilities\ArrayHelper::getValue($options, "thumb_width", 200));
                $app->setUserState($this->option . ".gallery.thumb_height", Joomla\Utilities\ArrayHelper::getValue($options, "thumb_width", 300));
                $app->setUserState($this->option . ".gallery.thumb_scale", Joomla\Utilities\ArrayHelper::getValue($options, "thumb_width", \JImage::SCALE_INSIDE));

                // Upload image
                if (!empty($imageFile['name'])) {

                    // Create params.
                    if (!isset($validData["params"])) {
                        $validData["params"] = array("image" => array(), "thumbnail" => array());
                    }

                    $image = new MagicGallery\Resource\Resource();

                    $image->uploadImage($imageFile, $options);
                    if ($image->getImage()) {
                        $validData["image"]           = $image->getImage();
                        $validData["params"]["image"] = $image->getParam("image");
                    }

                    if ($image->getThumbnail()) {
                        $validData["thumbnail"]           = $image->getThumbnail();
                        $validData["params"]["thumbnail"] = $image->getParam("thumbnail");
                    }
                }

                // Upload thumbnail
                if (!empty($thumbFile['name']) and empty($validData["thumbnail"])) {

                    // Create params.
                    if (!isset($validData["params"])) {
                        $validData["params"] = array("image" => array(), "thumbnail" => array());
                    }

                    $image = new MagicGallery\Resource\Resource();

                    $image->uploadThumbnail($thumbFile, $options);
                    if ($image->getThumbnail()) {
                        $validData["thumbnail"]           = $image->getThumbnail();
                        $validData["params"]["thumbnail"] = $image->getParam("thumbnail");
                    }
                }

                // Set the media folder, where the system should look for files.
                $validData["media_folder"] = $options["destination_folder"];
            }

            $redirectOptions["id"] = $model->save($validData);

        } catch (RuntimeException $e) {
            $this->displayWarning($e->getMessage(), $redirectOptions);
        } catch (Exception $e) {

            JLog::add($e->getMessage());
            throw new Exception(JText::_('COM_MAGICGALLERY_ERROR_SYSTEM'));
        }

        $this->displayMessage(JText::_('COM_MAGICGALLERY_RESOURCE_SAVED'), $redirectOptions);
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
            "view" => "resource",
            "id"   => $itemId
        );

        try {

            $image = new MagicGallery\Resource\Resource(JFactory::getDbo());
            $image->load($itemId);

            if ($image->getId()) {
                $params = JComponentHelper::getParams($this->option);

                $image->setMediaFolder(JPath::clean(JPATH_ROOT .DIRECTORY_SEPARATOR. $params->get("media_folder", "images/magicgallery")));
                $image->removeImage($type);
            }

        } catch (Exception $e) {
            JLog::add($e->getMessage());
            throw new Exception(JText::_('COM_MAGICGALLERY_ERROR_SYSTEM'));
        }

        // Display message
        if (strcmp("thumb", $type) == 0) {
            $msg = JText::_('COM_MAGICGALLERY_THUMB_DELETED');
        } else {
            $msg = JText::_('COM_MAGICGALLERY_IMAGE_DELETED');
        }

        $this->displayMessage($msg, $redirectOptions);
    }
}
