<?php
/**
 * @package         Magicgallery
 * @subpackage      Helpers
 * @author          Todor Iliev
 * @copyright       Copyright (C) 2016 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license         http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

namespace Magicgallery\Helper;

use Prism\Filesystem\Helper;
use Magicgallery\Gallery\Gallery;

defined('JPATH_PLATFORM') or die;

/**
 * This class provide functionality for preparing paths to media files.
 *
 * @package         Magicgallery
 * @subpackage      Helpers
 */
class Path
{
    /**
     * @var Helper
     */
    protected $filesystemHelper;

    public function __construct(Helper $filesystemHelper)
    {
        $this->filesystemHelper = $filesystemHelper;
    }

    /**
     * Prepare and return media folder.
     *
     * @param Gallery $gallery
     * @param int $id
     * @param string $folderName
     *
     * @throws \UnexpectedValueException
     * @return string
     */
    public function getMediaFolder(Gallery $gallery, $id = 0, $folderName = 'user')
    {
        $default     = $this->filesystemHelper->getMediaFolder($id, $folderName);
        $mediaFolder = $gallery->getParam('path');
        $mediaFolder = $mediaFolder ?: $default;

        return $mediaFolder ? \JPath::clean($mediaFolder, '/') : '';
    }

    /**
     * Prepare and return media URI.
     *
     * @param Gallery $gallery
     * @param int $id
     * @param string $folderName
     *
     * @return string
     */
    public function getMediaUri(Gallery $gallery, $id = 0, $folderName = 'user')
    {
        $mediaUri = (string)$gallery->getParam('uri');
        $mediaUri = $this->filesystemHelper->getMediaFolderUri($id, $folderName, $mediaUri);

        return $mediaUri ?: '';
    }
}
