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
 * It is a resource model.
 */
class MagicGalleryModelResource extends JModelAdmin
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
    public function getTable($type = 'Resource', $prefix = 'MagicGalleryTable', $config = array())
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
        $form = $this->loadForm($this->option . '.resource', 'resource', array('control' => 'jform', 'load_data' => $loadData));
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
        // Check the session for previously entered form data.
        $app  = JFactory::getApplication();
        $data = $app->getUserState($this->option . '.edit.image.data', array());

        if (empty($data)) {
            $data = $this->getItem();

            // Get values that was used by the user
            $data->resize = array(
                "resize_image" => $app->getUserState($this->option . ".gallery.resize_image", 0),
                "thumb_width"  => $app->getUserState($this->option . ".gallery.thumb_width", 300),
                "thumb_height" => $app->getUserState($this->option . ".gallery.thumb_height", 300),
                "thumb_scale"  => $app->getUserState($this->option . ".gallery.thumb_scale", JImage::SCALE_INSIDE),
                "create_thumb" => $app->getUserState($this->option . ".gallery.create_thumb", 0),
                "image_width"  => $app->getUserState($this->option . ".gallery.image_width", 500),
                "image_height" => $app->getUserState($this->option . ".gallery.image_height", 500),
                "image_scale"  => $app->getUserState($this->option . ".gallery.image_scale", JImage::SCALE_INSIDE)
            );
            
            if (!$data->gallery_id) {
                $data->gallery_id = (int)$app->getUserState("com_magicgallery.resources.filter.gallery_id");
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
        $id          = Joomla\Utilities\ArrayHelper::getValue($data, "id", 0, "int");
        $title       = Joomla\Utilities\ArrayHelper::getValue($data, "title", "", "string");
        $description = Joomla\Utilities\ArrayHelper::getValue($data, "description", "", "string");
        $published   = Joomla\Utilities\ArrayHelper::getValue($data, "published", 0, "int");
        $galleryId   = Joomla\Utilities\ArrayHelper::getValue($data, "gallery_id", 0, "int");

        $params      = Joomla\Utilities\ArrayHelper::getValue($data, "params", array(), "array");

        $mediaFolder = Joomla\Utilities\ArrayHelper::getValue($data, "media_folder", "", "string");

        if (!$description) {
            $description = null;
        }
        if (!$title) {
            $title = null;
        }

        // Load a record from the database
        $row = $this->getTable();
        $row->load($id);

        // Decode the parameters.
        $row->set("params", json_decode($row->get("params"), true));

        $row->set("title", $title);
        $row->set("description", $description);
        $row->set("published", $published);
        $row->set("gallery_id", $galleryId);

        // Prepare the row for saving
        $this->prepareImages($row, $data, $mediaFolder, $params);
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
                $db    = $this->getDbo();
                $query = $db->getQuery(true);
                $query
                    ->select("MAX(ordering)")
                    ->from($db->quoteName("#__magicgallery_resources"));

                $db->setQuery($query, 0, 1);
                $max = $db->loadResult();

                $table->set("ordering", $max + 1);
            }
        }

        // Fix magic quotes.
        if (get_magic_quotes_gpc()) {
            $table->set("title", stripcslashes($table->get("title")));
            $table->set("description", stripcslashes($table->get("description")));
        }

        // Set the image state to default if there are no other ones.
        $db    = $this->getDbo();
        $query = $db->getQuery(true);
        $query
            ->select("COUNT(*)")
            ->from($db->quoteName("#__magicgallery_resources", "a"))
            ->where("a.home = 1");

        $db->setQuery($query, 0, 1);
        $hasDefault = $db->loadResult();

        if (!$hasDefault) {
            $table->set("home", Prism\Constants::STATE_DEFAULT);
        }

        $table->set("params", json_encode((array)$table->get("params")));
    }

    /**
     * Prepare and sanitise the table prior to saving.
     *
     * @param JTable $table
     * @param array $data
     * @param string $mediaFolder
     * @param array $params
     *
     * @since    1.6
     */
    protected function prepareImages($table, $data, $mediaFolder, $params)
    {
        // Set the thumbnail
        if (!empty($data['thumbnail']) and !empty($mediaFolder)) {
            // Delete old image if I upload the new one
            if (!empty($table->thumbnail)) {
                // Remove an image from the filesystem
                $file = JPath::clean($mediaFolder . DIRECTORY_SEPARATOR . $table->thumbnail);
                if (JFile::exists($file)) {
                    JFile::delete($file);
                }
            }

            $table->set("thumbnail", $data['thumbnail']);

            // Prepare thumbnail params.
            $params_ = (array)$table->get("params");
            $params_["thumbnail"] = (!empty($params["thumbnail"])) ? $params["thumbnail"] : array();
            $table->set("params", $params_);
        }

        // Sets the images
        if (!empty($data['image']) and !empty($mediaFolder)) {
            // Delete old image if I upload the new one
            if (!empty($table->image)) {
                // Remove an image from the filesystem
                $file = JPath::clean($mediaFolder . DIRECTORY_SEPARATOR . $table->image);
                if (JFile::exists($file)) {
                    JFile::delete($file);
                }
            }

            $table->set("image", $data['image']);

            // Prepare image params.
            $params_ = (array)$table->get("params");
            $params_["image"] = (!empty($params["image"])) ? $params["image"] : array();
            $table->set("params", $params_);
        }
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
        $condition[] = 'gallery_id = ' . (int)$table->get("gallery_id");

        return $condition;
    }
}
