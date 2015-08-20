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
 * Gallery controller class.
 *
 * @package        MagicGallery
 * @subpackage     Components
 * @since          1.6
 */
class MagicGalleryControllerGallery extends JControllerLegacy
{
    public function getModel($name = 'Gallery', $prefix = 'MagicGalleryModel', $config = array('ignore_request' => true))
    {
        $model = parent::getModel($name, $prefix, $config);

        // Load the component parameters.
        $params = JComponentHelper::getParams("com_magicgallery");
        /** @var  $params Joomla\Registry\Registry */

        // Set images folder
        $model->setImagesFolder(JPATH_ROOT . DIRECTORY_SEPARATOR . $params->get("media_folder", "images/magicgallery"));
        $model->setImagesUri("../" . $params->get("media_folder", "images/magicgallery") . "/");

        return $model;
    }

    /**
     * Deletes Extra Image
     */
    public function removeExtraImage()
    {
        // Initialize variables
        $itemId = $this->input->post->get("id");

        $response = new Prism\Response\Json();

        try {

            // Get the model
            $model = $this->getModel();
            $model->removeExtraImage($itemId);

        } catch (Exception $e) {
            JLog::add($e->getMessage());
            throw new Exception($e->getMessage());
        }

        $response
            ->setTitle(JText::_('COM_MAGICGALLERY_SUCCESS'))
            ->setText(JText::_('COM_MAGICGALLERY_IMAGE_DELETED'))
            ->setData(array("item_id" => $itemId))
            ->success();

        echo $response;
        JFactory::getApplication()->close();
    }

    public function addExtraImage()
    {
        $response = new Prism\Response\Json();

        // Initialize variables
        $itemId = $this->input->post->get("id");

        // Prepare the size of additional thumbnails
        $thumbWidth  = $this->input->post->get("thumb_width", 50);
        $thumbHeight = $this->input->post->get("thumb_height", 50);
        if ($thumbWidth < 25 or $thumbHeight < 25) {
            $thumbWidth  = 50;
            $thumbHeight = 50;
        }

        $scale = $this->input->post->get("thumb_scale", JImage::SCALE_INSIDE);

        $files = $this->input->files->get("files");
        if (!$files) {

            $response
                ->setTitle(JText::_('COM_MAGICGALLERY_FAIL'))
                ->setText(JText::_('COM_MAGICGALLERY_ERROR_FILE_UPLOAD'))
                ->failure();

            echo $response;
            JFactory::getApplication()->close();
        }

        try {

            // Get the model
            $model  = $this->getModel();
            $images = $model->uploadExtraImages($files, $thumbWidth, $thumbHeight, $scale);
            $images = $model->storeExtraImage($images, $itemId);

        } catch (Exception $e) {
            JLog::add($e->getMessage());
            throw new Exception($e->getMessage());
        }

        $response
            ->setTitle(JText::_('COM_MAGICGALLERY_SUCCESS'))
            ->setText(JText::_('COM_MAGICGALLERY_IMAGE_SAVED'))
            ->setData($images)
            ->success();

        echo $response;
        JFactory::getApplication()->close();
    }
}
