<?php
/**
 * @package      Magicgallery
 * @subpackage   Helpers
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2016 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace Magicgallery\Helper;

use Joomla\Utilities\ArrayHelper;
use Magicgallery\Gallery\Gallery;
use Prism\Helper\HelperInterface;
use Magicgallery\Helper\Path as PathHelper;

defined('JPATH_PLATFORM') or die;

/**
 * This class provides functionality to prepare the URI of the galleries.
 *
 * @package      Magicgallery
 * @subpackage   Helpers
 */
class PrepareGalleriesUriHelper implements HelperInterface
{
    /**
     * @var PathHelper
     */
    protected $pathHelper;

    /**
     * PrepareGalleriesUriHelper constructor.
     *
     * @param PathHelper $helper
     */
    public function __construct($helper)
    {
        $this->pathHelper = $helper;
    }

    /**
     * Prepare an item status.
     *
     * @param array $data
     * @param array $options
     */
    public function handle(&$data, array $options = array())
    {
        foreach ($data as $key => $item) {
            $item_ = new Gallery();
            $item_->bind($item);

            $item->media_uri = $this->pathHelper->getMediaUri($item_);
            unset($item_);
        }
    }
}
