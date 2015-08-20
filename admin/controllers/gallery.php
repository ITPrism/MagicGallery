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
class MagicGalleryControllerGallery extends Prism\Controller\Form\Backend
{
    public function getModel($name = 'Gallery', $prefix = 'MagicGalleryModel', $config = array('ignore_request' => true))
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

            $redirectOptions["id"] = $model->save($validData);

        } catch (RuntimeException $e) {
            $this->displayWarning($e->getMessage(), $redirectOptions);
        } catch (Exception $e) {

            JLog::add($e->getMessage());
            throw new Exception(JText::_('COM_MAGICGALLERY_ERROR_SYSTEM'));

        }

        $this->displayMessage(JText::_('COM_MAGICGALLERY_GALLERY_SAVED'), $redirectOptions);
    }

}
