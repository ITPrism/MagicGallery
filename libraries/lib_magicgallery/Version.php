<?php
/**
 * @package      MagicGallery
 * @subpackage   Version
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace MagicGallery;

defined('JPATH_BASE') or die;

/**
 * Magic Gallery version class
 *
 * @package     MagicGallery
 * @subpackage  Version
 */
class Version
{
    /**
     * Extension name
     *
     * @var string
     */
    public $product = 'Magic Gallery';

    /**
     * Main Release Level
     *
     * @var integer
     */
    public $release = '1';

    /**
     * Sub Release Level
     *
     * @var integer
     */
    public $devLevel = '0';

    /**
     * Release Type
     *
     * @var integer
     */
    public $releaseType = 'Pro';

    /**
     * Development Status
     *
     * @var string
     */
    public $devStatus = 'Stable';

    /**
     * Date
     *
     * @var string
     */
    public $releaseDate = '07 August, 2015';

    /**
     * License
     *
     * @var string
     */
    public $license = '<a href="http://www.gnu.org/copyleft/gpl.html" target="_blank">GNU/GPL</a>';

    /**
     * Copyright Text
     *
     * @var string
     */
    public $copyright = '&copy; 2015 ITPrism. All rights reserved.';

    /**
     * URL
     *
     * @var string
     */
    public $url = '<a href="http://itprism.com/free-joomla-extensions/others/portfolio-presentation-gallery" target="_blank">Magic Gallery</a>';

    /**
     * Backlink
     *
     * @var string
     */
    public $backlink = '<div style="width:100%; text-align: left; font-size: xx-small; margin-top: 10px;"><a href="http://itprism.com/free-joomla-extensions/others/portfolio-presentation-gallery" target="_blank">Joomla! portfolio</a></div>';

    /**
     * Developer
     *
     * @var string
     */
    public $developer = '<a href="http://itprism.com" target="_blank">ITPrism</a>';

    /**
     * Minimum required version of Prism library.
     *
     * @var string
     */
    public $requiredPrismVersion = '1.2';

    /**
     *  Build long format of the version text.
     *
     * @return string Long format version.
     */
    public function getLongVersion()
    {
        return
            $this->product . ' ' . $this->release . '.' . $this->devLevel . ' ' .
            $this->devStatus . ' ' . $this->releaseDate;
    }

    /**
     *  Build long medium of the version text.
     *
     * @return string Medium format version
     */
    public function getMediumVersion()
    {
        return
            $this->release . '.' . $this->devLevel . ' ' .
            $this->releaseType . ' ( ' . $this->devStatus . ' )';
    }

    /**
     *  Build short format of the version text.
     *
     * @return string Short version format
     */
    public function getShortVersion()
    {
        return $this->release . '.' . $this->devLevel;
    }
}
