<?php
/**
 * @package      MagicGallery
 * @subpackage   Component
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

jimport("MagicGallery.init");

/**
 * Method to build Route
 *
 * @param array $query
 *
 * @return string
 */
function MagicGalleryBuildRoute(&$query)
{
    $segments = array();

    // get a menu item based on Itemid or currently active
    $app  = JFactory::getApplication();
    $menu = $app->getMenu();

    // we need a menu item.  Either the one specified in the query, or the current active one if none specified
    if (empty($query['Itemid'])) {
        $menuItem      = $menu->getActive();
        $menuItemGiven = false;
    } else {
        $menuItem      = $menu->getItem($query['Itemid']);
        $menuItemGiven = (isset($menuItem->query)) ? true : false ;
    }

    // Check again
    if ($menuItemGiven and isset($menuItem) and strcmp("com_magicgallery", $menuItem->component) != 0) {
        $menuItemGiven = false;
        unset($query['Itemid']);
    }

    $mView   = (empty($menuItem->query['view'])) ? null : $menuItem->query['view'];
    $mId     = (empty($menuItem->query['id'])) ? null : $menuItem->query['id'];
//    $mOption = (empty($menuItem->query['option'])) ? null : $menuItem->query['option'];
//    $mCatid  = (empty($menuItem->query['catid'])) ? null : $menuItem->query['catid'];

    // If is set view and Itemid missing, we have to put the view to the segments
    if (isset($query['view'])) {
        $view = $query['view'];
    } else {
        return $segments;
    }

    // Are we dealing with a category that is attached to a menu item?
    if (($menuItem instanceof stdClass) and isset($view) and ($mView == $view) and (isset($query['id'])) and ($mId == (int)$query['id'])) {

        unset($query['view']);

        if (isset($query['catid'])) {
            unset($query['catid']);
        }

        if (isset($query['layout'])) {
            unset($query['layout']);
        }

        unset($query['id']);

        return $segments;
    }

    // Views
    if (isset($view)) {

        switch ($view) {

            // Category views.
            case "camera":
            case "galleria":
            case "slidegallery":
            case "list":
            case "lineal":

                if (!$menuItemGiven) {
                    $segments[] = $view;
                }

                if (isset($query['id'])) {
                    $categoryId = $query['id'];
                } else {
                    // We should have id set for this view.  If we don't, it is an error.
                    return $segments;
                }

                $segments = MagicGalleryHelperRoute::prepareCategoriesSegments($categoryId, $segments, $menuItem, $menuItemGiven);

                unset($query['id']);

                break;

            case "categories":
                if (isset($query['view'])) {
                    unset($query['view']);
                }

                if (isset($query['projects_view'])) {
                    unset($query['projects_view']);
                }

                if (isset($query['categories_ids'])) {
                    unset($query['categories_ids']);
                }
                break;

        }
    }

    // Layout
    if (isset($query['layout'])) {
        if ($menuItemGiven and isset($menuItem->query['layout'])) {
            if ($query['layout'] == $menuItem->query['layout']) {
                unset($query['layout']);
            }
        } else {
            if ($query['layout'] == 'default') {
                unset($query['layout']);
            }
        }
    }

    $total = count($segments);

    for ($i = 0; $i < $total; $i++) {
        $segments[$i] = str_replace(':', '-', $segments[$i]);
    }

    return $segments;
}

/**
 * Method to parse Route
 *
 * @param array $segments
 *
 * @return string
 */
function MagicGalleryParseRoute($segments)
{
    $total = count($segments);
    $vars = array();

    for ($i = 0; $i < $total; $i++) {
        $segments[$i] = preg_replace('/-/', ':', $segments[$i], 1);
    }

    //Get the active menu item.
    $app  = JFactory::getApplication();
    $menu = $app->getMenu();
    $menuItem = $menu->getActive();

    // Count route segments
    $count = count($segments);

    // Standard routing for articles.  If we don't pick up an Itemid then we get the view from the segments
    // the first segment is the view and the last segment is the id of the details, category or payment.
    if (!isset($menuItem)) {
        $vars['view']  = $segments[0];
        $vars['id']    = $segments[$count - 1];

        return $vars;
    }

    list($id, $alias) = explode(':', $segments[0], 2);
    $alias = str_replace(":", "-", $alias);

    // First we check if it is a category
    $category = JCategories::getInstance('Magicgallery')->get($id);
    if ($category && $category->alias == $alias) {

        // Get the category id from the menu item
        if (isset($menuItem->query['projects_view'])) {
            $vars['view'] = $menuItem->query['projects_view'];
        }
        $vars['id'] = $id;

        return $vars;
    }

    return $vars;
}
