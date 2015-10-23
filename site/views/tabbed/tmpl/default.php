<?php
/**
 * @package      MagicGallery
 * @subpackage   Component
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;
?>

<?php if ($this->params->get('show_page_heading', 1)) { ?>
<h1><?php echo $this->escape($this->params->get('page_heading')); ?></h1>
<?php } ?>

<?php echo (!empty($this->event->onContentBeforeDisplay) ) ? $this->event->onContentBeforeDisplay : '';?>

<?php
switch($this->modal) {
    case 'fancybox':
        echo $this->loadTemplate('fancybox');
        break;

    case 'nivo': // Nivo modal
        echo $this->loadTemplate('nivo');
        break;

    default:
        echo $this->loadTemplate('nomodal');
        break;
}

echo (!empty($this->event->onContentAfterDisplay) ) ? $this->event->onContentAfterDisplay : '';
