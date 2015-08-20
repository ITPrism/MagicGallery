<?php
/**
 * @package      MagicGallery
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

jimport('MagicGallery.init');

/**
 * Component Route Helper that help to find a menu item.
 * IMPORTANT: It help us to find right MENU ITEM.
 *
 * Use router ...BuildRoute to build a link
 *
 * @static
 * @package        MagicGallery
 * @subpackage     Components
 * @since          1.5
 */
abstract class MagicGalleryHelperRoute
{
    protected static $lookup;

    /**
     * Routing a link to gallery view.
     *
     * @param string $view
     * @param int $catid
     * @param int $galleryId
     * @param int $offset
     *
     * @return string
     */
    public static function getGalleryViewRoute($view, $catid, $galleryId, $offset = null)
    {
        if ($catid instanceof JCategoryNode) {
            $id       = $catid->id;
            $category = $catid;
        } else {
            $id       = (int)$catid;
            $category = JCategories::getInstance('MagicGallery')->get($id);
        }

        if ($id < 1) {
            $link = '';
        } else {
            $needles = array(
                $view => array($galleryId)
            );

            // Get menu item ( Itemid )
            if ($item = self::findItem($needles)) {
                $link = 'index.php?Itemid=' . $item;
            } else { // Continue to search and deep inside

                // Create the link
                $link = 'index.php?option=com_magicgallery&view=' . $view . '&catid=' . $id . "&id=".$galleryId;

                if ($category) {
                    $catids = array_reverse($category->getPath());

                    $needles = array(
                        $view => $catids
                    );

                    // Looking for menu item (Itemid)
                    if ($item = self::findItem($needles)) {
                        $link .= '&Itemid=' . $item;
                    } elseif ($item = self::findItem()) { // Get the menu item (Itemid) from the active (current) item.
                        $link .= '&Itemid=' . $item;
                    }
                }
            }
        }

        if (!is_null($offset)) {
            $link .= "&start=" . (int)$offset;
        }

        return $link;
    }

    /**
     * Routing a link to a category view.
     *
     * @param string $view
     * @param int $catid
     *
     * @return string
     */
    public static function getCategoryViewRoute($view, $catid)
    {
        if ($catid instanceof JCategoryNode) {
            $id       = $catid->id;
            $category = $catid;
        } else {
            $id       = (int)$catid;
            $category = JCategories::getInstance('MagicGallery')->get($id);
        }

        if ($id < 1) {
            $link = '';
        } else {
            $needles = array(
                $view => array($id)
            );

            // Get menu item ( Itemid )
            if ($item = self::findItem($needles)) {
                $link = 'index.php?Itemid=' . $item;
            } else { // Continue to search and deep inside

                // Create the link
                $link = 'index.php?option=com_magicgallery&view=' . $view . '&id=' . $id;

                if ($category) {
                    $catids = array_reverse($category->getPath());

                    $needles = array(
                        $view => $catids
                    );

                    // Looking for menu item (Itemid)
                    if ($item = self::findItem($needles)) {
                        $link .= '&Itemid=' . $item;
                    } elseif ($item = self::findItem()) { // Get the menu item (Itemid) from the active (current) item.
                        $link .= '&Itemid=' . $item;
                    }
                }
            }
        }

        return $link;
    }

    /**
     * Routing a link to categories view.
     *
     * @param string $view
     *
     * @return string
     */
    public static function getCategoriesViewRoute($view)
    {
        /**
         *
         * # category
         * We will check for view category first. If find a menu item with view "category" and "id" eqallity of the key,
         * we will get that menu item ( Itemid ).
         *
         * # categories view
         * If miss a menu item with view "category" we continue with searchin but now for view "categories".
         * It is assumed view "categories" will be in the first level of the menu.
         * The view "categories" won't contain category ID so it has to contain 0 for ID key.
         */
        $needles = array(
            $view => array(0)
        );

        //Create the link
        $link = 'index.php?option=com_magicgallery&view='.$view;

        // Looking for menu item (Itemid)
        if ($item = self::findItem($needles)) {
            $link .= '&Itemid=' . $item;
        } elseif ($item = self::findItem()) { // Get the menu item (Itemid) from the active (current) item.
            $link .= '&Itemid=' . $item;
        }

        return $link;
    }

    protected static function findItem($needles = null)
    {
        $app   = JFactory::getApplication();
        $menus = $app->getMenu('site');

        // Prepare the reverse lookup array.
        // Collect all menu items and creat an array that contains
        // the ID from the query string of the menu item as a key,
        // and the menu item id (Itemid) as a value
        // Example:
        // array( "category" =>
        //     1(catid) => 100 (Itemid),
        //     2(catid) => 101 (Itemid)
        // );
        if (self::$lookup === null) {
            self::$lookup = array();

            $component = JComponentHelper::getComponent('com_magicgallery');
            $items     = $menus->getItems('component_id', $component->id);

            if ($items) {
                foreach ($items as $item) {
                    if (isset($item->query) && isset($item->query['view'])) {
                        $view = $item->query['view'];

                        if (!isset(self::$lookup[$view])) {
                            self::$lookup[$view] = array();
                        }

                        if (isset($item->query['id'])) {
                            self::$lookup[$view][$item->query['id']] = $item->id;
                        } else { // If it is a root element that have no a request parameter ID ( categories, authors ), we set 0 for an key
                            self::$lookup[$view][0] = $item->id;
                        }
                    }
                }
            }
        }

        if ($needles) {

            foreach ($needles as $view => $ids) {
                if (isset(self::$lookup[$view])) {

                    foreach ($ids as $id) {
                        if (isset(self::$lookup[$view][(int)$id])) {
                            return self::$lookup[$view][(int)$id];
                        }
                    }

                }
            }

        } else {
            $active = $menus->getActive();
            if ($active) {
                return $active->id;
            }
        }

        return null;
    }

    /**
     * Prepare categories path to the segments.
     * We use this method in the router "MagicGalleryParseRoute".
     *
     * @param integer $categoryId Category Id
     * @param array   $segments
     * @param object  $menuItem
     * @param bool    $menuItemGiven
     *
     * @return array
     */
    public static function prepareCategoriesSegments($categoryId, $segments, $menuItem, $menuItemGiven)
    {
        if ($menuItemGiven and isset($menuItem->query['id'])) {
            $menuCategoryId = $menuItem->query['id'];
        } else {
            $menuCategoryId = 0;
        }

        $categories = MagicGallery\Category\Categories::getInstance('MagicGallery');
        $category   = $categories->get($categoryId);

        if (!$category) {
            // We couldn't find the category we were given.
            return $segments;
        }

        $path = array_reverse($category->getPath());

        $array = array();

        // If a category ID match with an ID in a menu item,
        // we cannot generate an array with subcategories (aliases).
        foreach ($path as $id) {

            // Is an ID match with an ID in a menu item?
            if ((int)$id == (int)$menuCategoryId) {
                break;
            }

            // Add the item to the array with category aliases.
            /*list($tmp, $id) = explode(':', $id, 2);
            $array[] = $id;*/

            $array[] = str_replace(":", "-", $id);
        }

        $array = array_reverse($array);

        $segments = array_merge($segments, $array);

        return $segments;
    }
}
