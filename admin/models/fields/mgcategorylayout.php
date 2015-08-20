<?php
/**
 * @package      MagicGallery
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2014 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

jimport('joomla.html.html');
jimport('joomla.form.formfield');
jimport('joomla.form.helper');
JFormHelper::loadFieldClass('list');

/**
 * Form Field class for the Joomla Framework.
 *
 * @package      MagicGallery
 * @subpackage   Components
 * @since        1.6
 */
class JFormFieldMgCategoryLayout extends JFormFieldList
{
    /**
     * The form field type.
     *
     * @var     string
     * @since   1.6
     */
    protected $type = 'MgCategoryLayout';

    /**
     * Method to get the field options.
     *
     * @return  array   The field option objects.
     * @since   1.6
     */
    protected function getOptions()
    {
        $db    = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query
            ->select("a.alias as value, a.title as text")
            ->from($db->quoteName("#__categories", "a"))
            ->where("a.extension =" . $db->quote("com_magicgallery"))
            ->where("a.published = 1");

        $db->setQuery($query);
        $options = $db->loadAssocList();

        // Merge any additional options in the XML definition.
        $options = array_merge(parent::getOptions(), $options);

        return $options;
    }
}
