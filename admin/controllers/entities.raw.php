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
 * Entities controller
 *
 * @package     Magicgallery
 * @subpackage  Components
 */
class MagicgalleryControllerEntities extends JControllerAdmin
{
    public function getModel($name = 'Entity', $prefix = 'MagicgalleryModel', $config = array('ignore_request' => true))
    {
        $model = parent::getModel($name, $prefix, $config);

        return $model;
    }

    /**
     * Method to save the submitted ordering values for records via AJAX.
     *
     * @throws Exception
     * @return  void
     * @since   3.0
     */
    public function saveOrderAjax()
    {
        // Get the input
        $app   = JFactory::getApplication();
        $pks   = $app->input->post->get('cid', array(), 'array');
        $order = $app->input->post->get('order', array(), 'array');

        // Sanitize the input
        Joomla\Utilities\ArrayHelper::toInteger($pks);
        Joomla\Utilities\ArrayHelper::toInteger($order);

        // Get the model
        $model = $this->getModel();

        // Save the item
        try {
            $model->saveorder($pks, $order);
        } catch (Exception $e) {
            JLog::add($e->getMessage());
            throw new Exception(JText::_('COM_MAGICGALLERY_ERROR_SYSTEM'));
        }

        $response = array(
            'success' => true,
            'title'   => JText::_('COM_MAGICGALLERY_SUCCESS'),
            'text'    => JText::_('JLIB_APPLICATION_SUCCESS_ORDERING_SAVED'),
            'data'    => array()
        );

        echo json_encode($response);
        $app->close();
    }
}
