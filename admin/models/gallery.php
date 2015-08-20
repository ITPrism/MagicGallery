<?php
/**
 * @package      MagicGallery
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2014 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

/**
 * It is a gallery model.
 */
class MagicGalleryModelGallery extends JModelAdmin
{
    /**
     * Returns a reference to the a Table object, always creating it.
     *
     * @param   string $type   The table type to instantiate
     * @param   string $prefix A prefix for the table class name. Optional.
     * @param   array  $config Configuration array for model. Optional.
     *
     * @return  JTable  A database object
     * @since   1.6
     */
    public function getTable($type = 'Gallery', $prefix = 'MagicGalleryTable', $config = array())
    {
        return JTable::getInstance($type, $prefix, $config);
    }

    /**
     * Method to get the record form.
     *
     * @param   array   $data     An optional array of data for the form to interrogate.
     * @param   boolean $loadData True if the form is to load its own data (default case), false if not.
     *
     * @return  JForm   A JForm object on success, false on failure
     * @since   1.6
     */
    public function getForm($data = array(), $loadData = true)
    {
        // Get the form.
        $form = $this->loadForm($this->option . '.gallery', 'gallery', array('control' => 'jform', 'load_data' => $loadData));
        if (empty($form)) {
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

        if (empty($data)) {
            $data = $this->getItem();

            // Prepare selected category.
            if ($this->getState($this->getName() . '.id') == 0) {
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
     * @return   int
     */
    public function save($data)
    {
        $title       = Joomla\Utilities\ArrayHelper::getValue($data, "title");
        $alias       = Joomla\Utilities\ArrayHelper::getValue($data, "alias");
        $id          = Joomla\Utilities\ArrayHelper::getValue($data, "id");
        $catid       = Joomla\Utilities\ArrayHelper::getValue($data, "catid");
        $url         = Joomla\Utilities\ArrayHelper::getValue($data, "url");
        $published   = Joomla\Utilities\ArrayHelper::getValue($data, "published");
        $description = Joomla\Utilities\ArrayHelper::getValue($data, "description");
        $userId      = Joomla\Utilities\ArrayHelper::getValue($data, "user_id", 0, "int");

        if (!$description) {
            $description = null;
        }

        if (!$url) {
            $url = null;
        }

        // Load a record from the database
        $row = $this->getTable();
        $row->load($id);

        $row->set("title", $title);
        $row->set("alias", $alias);
        $row->set("description", $description);
        $row->set("url", $url);
        $row->set("catid", $catid);
        $row->set("user_id", $userId);
        $row->set("published", $published);

        // Prepare the row for saving
        $this->prepareTable($row);

        $row->store(true);

        return $row->get("id");
    }

    /**
     * Prepare and sanitise the table prior to saving.
     *
     * @param JTable $table
     *
     * @since    1.6
     */
    protected function prepareTable($table)
    {
        // get maximum order number
        if (!$table->get("id")) {

            // Set ordering to the last item if not set
            if (!$table->get("ordering")) {
                $db    = JFactory::getDbo();
                $query = $db->getQuery(true);
                $query
                    ->select("MAX(ordering)")
                    ->from("#__magicgallery_galleries");

                $db->setQuery($query, 0, 1);
                $max = $db->loadResult();

                $table->set("ordering", $max + 1);
            }
        }

        // Fix magic quotes.
        if (get_magic_quotes_gpc()) {
            $table->set("title", stripcslashes($table->get("title")));
            $table->set("description", stripcslashes($table->get("description")));
            $table->set("url", stripcslashes($table->get("url")));
        }

        // If does not exist alias, I will generate the new one from the title
        if (!$table->get("alias")) {
            $table->set("alias", $table->get("alias"));
        }

        $table->set("alias", JApplicationHelper::stringURLSafe($table->get("alias")));
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
        $condition[] = 'catid = ' . (int)$table->get("catid");

        return $condition;
    }
}
