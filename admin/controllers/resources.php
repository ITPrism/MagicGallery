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
 * Magic Gallery Resources Controller
 *
 * @package     MagicGallery
 * @subpackage  Components
 */
class MagicGalleryControllerResources extends Prism\Controller\Admin
{
    public function __construct($config = array())
    {
        parent::__construct($config);
        $this->registerTask('unsetDefault',	'setDefault');
    }

    public function getModel($name = 'Resource', $prefix = 'MagicGalleryModel', $config = array('ignore_request' => true))
    {
        $model = parent::getModel($name, $prefix, $config);

        return $model;
    }

    /**
     * Method to set the home property for a list of items.
     *
     * @throws  Exception
     * @return  void
     */
    public function setDefault()
    {
        // Check for request forgeries
        JSession::checkToken() or die(JText::_('JINVALID_TOKEN'));

        // Get items to publish from the request.
        $cid   = $this->input->get('cid', array(), 'array');
        Joomla\Utilities\ArrayHelper::toInteger($cid);

        // Get item ID.
        $itemId    = array_shift($cid);

        $data  = array('setDefault' => 1, 'unsetDefault' => 0);

        $task  = $this->getTask();
        $value = Joomla\Utilities\ArrayHelper::getValue($data, $task, 0, 'int');

        // Redirect options
        $redirectOptions = array(
            "view" => "resources",
            "gid"  => $this->input->getInt('gid')
        );

        try {

            $image = new MagicGallery\Resource\Resource(JFactory::getDbo());
            $image->load($itemId);

            if ($image->getId()) {
                $image->changeDefaultState($value);
            }

        } catch (Exception $e) {

            JLog::add($e->getMessage());
            throw new Exception(JText::_('COM_MAGICGALLERY_ERROR_SYSTEM'));

        }

        $this->displayMessage(JText::_('COM_MAGICGALLERY_STATE_DEFAULT_CHANGED'), $redirectOptions);
    }
}
