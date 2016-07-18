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
 * Entity RAW controller class.
 *
 * @package        Magicgallery
 * @subpackage     Components
 * @since          1.6
 */
class MagicgalleryControllerEntity extends JControllerLegacy
{
    /**
     * Return the model of the item.
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

    /**
     * Upload an item.
     *
     * @throws Exception
     */
    public function upload()
    {
        // Check for request forgeries.
        JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

        $app = JFactory::getApplication();
        /** @var $app JApplicationSite */

        $response = new Prism\Response\Json();

        $userId   = JFactory::getUser()->get('id');

        // Check for authorized user.
        if (!$userId) {
            $response
                ->setTitle(JText::_('COM_MAGICGALLERY_FAIL'))
                ->setText(JText::_('COM_MAGICGALLERY_ERROR_NOT_LOG_IN'))
                ->failure();

            echo $response;
            $app->close();
        }

        $objectId   = $this->input->post->getInt('object_id');
        $categoryId = $this->input->post->getInt('category_id');
        $extension  = $this->input->post->getCmd('extension');

        $keys = array(
            'object_id'     => $objectId,
            'extension'     => $extension,
            'user_id'       => $userId,
            'catid'         => $categoryId
        );

        $gallery = new Magicgallery\Gallery\Gallery(JFactory::getDbo());
        $gallery->load($keys);

        // Check for valid gallery.
        if (!$gallery->getId()) {
            $response
                ->setTitle(JText::_('COM_MAGICGALLERY_FAIL'))
                ->setText(JText::_('COM_MAGICGALLERY_ERROR_INVALID_GALLERY'))
                ->failure();

            echo $response;
            $app->close();
        }

        $category = new Magicgallery\Category\Category(JFactory::getDbo());
        $category->load($categoryId);

        // Check for valid category.
        if (!$category->getId()) {
            $response
                ->setTitle(JText::_('COM_MAGICGALLERY_FAIL'))
                ->setText(JText::_('COM_MAGICGALLERY_ERROR_INVALID_CATEGORY'))
                ->failure();

            echo $response;
            $app->close();
        }

        $file = $this->input->files->get('media', array(), 'array');
        if ((count($file) === 0) or (JString::strlen($file['name']) === 0)) {
            $response
                ->setTitle(JText::_('COM_MAGICGALLERY_FAIL'))
                ->setText(JText::_('COM_MAGICGALLERY_ERROR_FILES_CANT_BE_UPLOADED'))
                ->failure();

            echo $response;
            $app->close();
        }

        // Magic Gallery global options.
        $params          = JComponentHelper::getParams('com_magicgallery');

        $result          = array();

        $mediaFolder     = JPath::clean($gallery->getParam('path'));
        $mediaUri        = $gallery->getParam('uri');

        if (!$mediaFolder) {
            $mediaFolder = JPath::clean($params->get('media_folder', 'images/magicgallery'));
        }

        if (!$mediaUri) {
            $mediaUri = $params->get('media_folder', 'images/magicgallery');
        }

        if (!$mediaFolder or !$mediaUri) {
            $response
                ->setTitle(JText::_('COM_MAGICGALLERY_FAIL'))
                ->setText(JText::_('COM_MAGICGALLERY_ERROR_INVALID_MEDIA_FOLDER'))
                ->failure();

            echo $response;
            $app->close();
        }

        $mediaFolder     = JPath::clean(JPATH_ROOT . DIRECTORY_SEPARATOR . $mediaFolder);
        $mediaUri        = JUri::root() . $mediaUri;

        $options = array(
            'path' => array(
                'temporary_folder' => JPath::clean($app->get('tmp_path')),
                'media_folder'     => $mediaFolder,
            ),
            'validation' => array(
                'content_length'   => (int)$app->input->server->get('CONTENT_LENGTH'),
                'upload_maxsize'   => (int)$params->get('max_size', 5) * (1024 * 1024),
                'legal_types'      => $params->get('legal_types', 'image/jpeg, image/gif, image/png, image/bmp'),
                'legal_extensions' => $params->get('legal_extensions', 'bmp, gif, jpg, jpeg, png'),
                'image_width'      => (int)$category->getParam('image_width'),
                'image_height'     => (int)$category->getParam('image_height')
            ),
            'resize' => $category->getParams(),
            'item'   => array (
                'default_item_status' => $category->getParam('default_item_status', Prism\Constants::UNPUBLISHED)
            )
        );

        try {

            $model = $this->getModel();

            $itemData = $model->upload($file, $options, $gallery->getId());

            $result = array (
                'id' => $itemData['id'],
                'title' => $itemData['image'],
                'link_image'  => $mediaUri .'/'. $itemData['image'],
                'link_thumbnail'  => $mediaUri .'/'. $itemData['thumbnail'],
            );

        } catch (RuntimeException $e) {

            $response
                ->setTitle(JText::_('COM_MAGICGALLERY_FAIL'))
                ->setText($e->getMessage())
                ->failure();

            echo $response;
            $app->close();

        } catch (Exception $e) {
            JLog::add($e->getMessage());

            $response
                ->setTitle(JText::_('COM_MAGICGALLERY_FAIL'))
                ->setText(JText::_('COM_MAGICGALLERY_SYSTEM_ERROR'))
                ->failure();

            echo $response;
            $app->close();
        }

        $response
            ->setTitle(JText::_('COM_MAGICGALLERY_SUCCESS'))
            ->setText(JText::_('COM_MAGICGALLERY_UPLOADING_MEDIA_COMPLETED'))
            ->setData($result)
            ->success();

        echo $response;
        $app->close();
    }
}
