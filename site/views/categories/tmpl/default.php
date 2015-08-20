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

<?php echo (!empty($this->event->onContentBeforeDisplay)) ? $this->event->onContentBeforeDisplay : ""; ?>

<div class="row">
<?php if (!empty($this->items)) { ?>

<?php foreach ($this->items as $item) { ?>
    <div class="col-xs-6 col-md-4">
        <a class="thumbnail" href="<?php echo JRoute::_(MagicGalleryHelperRoute::getCategoryViewRoute($this->projectsView, $item->slug)); ?>">
            <?php if (!empty($item->image)) { ?>
                <img src="<?php echo JUri::root() . $item->image; ?>" alt="<?php echo $this->escape($item->title); ?>"/>
            <?php } else { ?>
                <?php echo $item->title; ?>
            <?php } ?>
        </a>
    </div>
<?php } ?>

</div>
<?php echo (!empty($this->event->onContentAfterDisplay)) ? $this->event->onContentAfterDisplay : ""; ?>

<?php if (($this->params->def('show_pagination', 1) == 1 || ($this->params->get('show_pagination') == 2)) && ($this->pagination->get('pages.total') > 1)) { ?>
    <div class="pagination">
        <?php if ($this->params->def('show_pagination_results', 1)) { ?>
            <p class="counter pull-right"> <?php echo $this->pagination->getPagesCounter(); ?> </p>
        <?php } ?>
        <?php echo $this->pagination->getPagesLinks(); ?> </div>
<?php } ?>
<?php } ?>