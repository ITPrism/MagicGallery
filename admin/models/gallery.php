<?php
/**
 * @package      Magicgallery
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2016 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

use Joomla\Utilities\ArrayHelper;

jimport('Prism.libs.Flysystem.init');
jimport('Prism.libs.Aws.init');
jimport('Prism.libs.GuzzleHttp.init');

// no direct access
defined('_JEXEC') or die;

// Register Observers
JLoader::register('MagicgalleryObserverGallery', MAGICGALLERY_PATH_COMPONENT_ADMINISTRATOR .'/tables/observers/gallery.php');
JObserverMapper::addObserverClassToClass('MagicgalleryObserverGallery', 'MagicgalleryTableGallery', array('typeAlias' => 'com_magicgallery.gallery'));

class MagicgalleryModelGallery extends JModelAdmin
{
    /**
     * Returns a reference to the a Table object, always creating it.
     *
     * @param   string $type   The table type to instantiate
     * @param   string $prefix A prefix for the table class name. Optional.
     * @param   array  $config Configuration array for model. Optional.
     *
     * @return  MagicgalleryTableGallery|bool  A database object
     * @since   1.6
     */
    public function getTable($type = 'Gallery', $prefix = 'MagicgalleryTable', $config = array())
    {
        return JTable::getInstance($type, $prefix, $config);
    }

    /**
     * Method to get the record form.
     *
     * @param   array   $data     An optional array of data for the form to interrogate.
     * @param   boolean $loadData True if the form is to load its own data (default case), false if not.
     *
     * @return  JForm|bool   A JForm object on success, false on failure
     * @since   1.6
     */
    public function getForm($data = array(), $loadData = true)
    {
        // Get the form.
        $form = $this->loadForm($this->option . '.gallery', 'gallery', array('control' => 'jform', 'load_data' => $loadData));
        if (!$form) {
            return false;
        }

        return $form;
    }

    /**
     * Method to get the data that should be injected in the form.
     *
     * @return  mixed   The data for the form.
     * @since   1.6
     */
    protected function loadFormData()
    {
        $app  = JFactory::getApplication();

        // Check the session for previously entered form data.
        $data = $app->getUserState($this->option . '.edit.gallery.data', array());

        if ($data === null or (is_array($data) and count($data) === 0)) {
            $data = $this->getItem();

            // Prepare selected category.
            if ((int)$this->getState($this->getName() . '.id') === 0) {
                $data->set('catid', $app->input->getInt('catid', $app->getUserState($this->option . '.galleries.filter.category_id')));
            }
        }

        return $data;
    }

    /**
     * Save project data into the DB
     *
     * @param array $data The data about project
     *
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     * @throws \UnexpectedValueException
     *
     * @return   int
     */
    public function save($data)
    {
        $title       = ArrayHelper::getValue($data, 'title');
        $alias       = ArrayHelper::getValue($data, 'alias');
        $id          = ArrayHelper::getValue($data, 'id');
        $catid       = ArrayHelper::getValue($data, 'catid');
        $url         = ArrayHelper::getValue($data, 'url');
        $published   = ArrayHelper::getValue($data, 'published');
        $description = ArrayHelper::getValue($data, 'description');
        $userId      = ArrayHelper::getValue($data, 'user_id', 0, 'int');
        $metaDesc    = ArrayHelper::getValue($data, 'metadesc');
        $metaKeys    = ArrayHelper::getValue($data, 'metakeys');

        // Load a record from the database
        $row = $this->getTable();
        $row->load($id);

        $row->set('title', $title);
        $row->set('alias', $alias);
        $row->set('description', $description);
        $row->set('url', $url);
        $row->set('catid', $catid);
        $row->set('user_id', $userId);
        $row->set('published', $published);
        $row->set('metadesc', $metaDesc);
        $row->set('metakeys', $metaKeys);
        $row->set('params', null);

        // Encode parameters.
        if (array_key_exists('params', $data) and is_array($data['params']) and count($data) > 0) {
            $row->set('params', json_encode($data['params']));
        }

        // Prepare the row for saving
        $this->prepareTable($row);

        $row->store(true);

        return $row->get('id');
    }

    /**
     * Prepare and sanitise the table prior to saving.
     *
     * @param JTable $table
     *
     * @throws \RuntimeException
     * @since    1.6
     */
    protected function prepareTable($table)
    {
        // get maximum order number
        if (!$table->get('id') and !$table->get('ordering')) {
            // Set ordering to the last item if not set
            $db    = JFactory::getDbo();
            $query = $db->getQuery(true);
            $query
                ->select('MAX(ordering)')
                ->from('#__magicgallery_galleries');

            $db->setQuery($query, 0, 1);
            $max = $db->loadResult();

            $table->set('ordering', $max + 1);
        }

        if (!$table->get('description')) {
            $table->get('description', null);
        }

        if (!$table->get('url')) {
            $table->get('url', null);
        }

        if (!$table->get('metadesc')) {
            $table->set('metadesc', null);
        }

        if (!$table->get('metakeys')) {
            $table->set('metakeys', null);
        }

        // If does not exist alias, I will generate the new one from the title
        if (!$table->get('alias')) {
            $table->set('alias', $table->get('title'));
        }
        $table->set('alias', Prism\Utilities\StringHelper::stringUrlSafe($table->get('alias')));
    }

    /**
     * A protected method to get a set of ordering conditions.
     *
     * @param    object $table A record object.
     *
     * @return    array    An array of conditions to add to add to ordering queries.
     * @since    1.6
     */
    protected function getReorderConditions($table)
    {
        $condition   = array();
        $condition[] = 'catid = ' . (int)$table->get('catid');

        return $condition;
    }
}
