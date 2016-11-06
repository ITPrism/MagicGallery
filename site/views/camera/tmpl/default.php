<?php
/**
 * @package      Magicgallery
 * @subpackage   Component
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2016 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;
?>
<?php if ($this->params->get('show_page_heading', 1)) { ?>
    <h1><?php echo $this->escape($this->params->get('page_heading')); ?></h1>
<?php } ?>

<?php
if ($this->params->get('display_category_description', 0) and !empty($this->category)) {
    echo $this->category->getDescription();
}

echo (!empty($this->event->onContentBeforeDisplay)) ? $this->event->onContentBeforeDisplay : '';

if ($this->gallery !== null) {
    echo $this->gallery->render();
}

echo (!empty($this->event->onContentAfterDisplay)) ? $this->event->onContentAfterDisplay : '';