<?php
/**
 * @package      Magicgallery
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2016 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

use Joomla\Utilities\ArrayHelper;

defined('JPATH_BASE') or die;

jimport('joomla.html.html');
jimport('joomla.form.formfield');
jimport('joomla.form.helper');
JFormHelper::loadFieldClass('list');

/**
 * Form Field class for the Joomla Framework.
 *
 * @package      Magicgallery
 * @subpackage   Components
 * @since        1.6
 */
class JFormFieldMgGalleryLayout extends JFormFieldList
{
    /**
     * The form field type.
     *
     * @var     string
     * @since   1.6
     */
    protected $type = 'MgGalleryLayout';

    /**
     * Method to get the field options.
     *
     * @return  array   The field option objects.
     * @since   1.6
     */
    protected function getOptions()
    {
        $published = $this->element['published'] ? (int)$this->element['published']: array(0, 1);

        $db    = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query
            ->select('a.alias AS value, a.title AS text')
            ->from($db->quoteName('#__magicgallery_galleries', 'a'))
            ->where('a.extension = ""');

        // Filter on the published state
        if (is_numeric($published)) {
            $query->where('a.published = ' . (int)$published);
        } elseif (is_array($published)) {
            $published = ArrayHelper::toInteger($published);
            $query->where('a.published IN (' . implode(',', $published) . ')');
        }

        $query->order('a.title ASC');

        // Get the options.
        $db->setQuery($query);

        try {
            $options = $db->loadObjectList();
        } catch (RuntimeException $e) {
            return array();
        }

        // Merge any additional options in the XML definition.
        $options = array_merge(parent::getOptions(), $options);

        return $options;
    }
}
