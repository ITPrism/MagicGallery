<?php
/**
 * @package      Magicgallery
 * @subpackage   Helpers
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2016 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace Magicgallery\Helper;

use Joomla\Registry\Registry;
use Prism\Helper\HelperInterface;

defined('JPATH_PLATFORM') or die;

/**
 * This class provides functionality to prepare categories parameters.
 *
 * @package      Magicgallery
 * @subpackage   Helpers
 */
class PrepareCategoriesParamsHelper implements HelperInterface
{
    /**
     * Prepare an item parameters.
     *
     * @param array $data
     * @param array $options
     */
    public function handle(&$data, array $options = array())
    {
        if (count($data) > 0) {
            foreach ($data as $key => $item) {
                if ($item->params === null or $item->params === '') {
                    $item->params = '{}';
                }

                if (is_string($item->params) and $item->params !== '') {
                    $params = new Registry;
                    $params->loadString($item->params);
                    $item->params = $params;
                }

                if ($item->params->get('image')) {
                    $item->image = $item->params->get('image');
                }
            }
        }
    }
}
