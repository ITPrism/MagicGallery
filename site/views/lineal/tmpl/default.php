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

<?php if ($this->params->get('display_category_description', 0) and !empty($this->category)) {
    echo $this->category->getDescription();
} ?>

<?php echo (!empty($this->event->onContentBeforeDisplay)) ? $this->event->onContentBeforeDisplay : ''; ?>

<?php if (isset($this->item)) {
    switch ($this->modal) {
        case 'fancybox':
            echo $this->loadTemplate('fancybox');
            break;
        case 'nivo':
            echo $this->loadTemplate('nivo');
            break;
        case 'magnific':
            echo $this->loadTemplate('magnific');
            break;
        case 'swipebox':
            echo $this->loadTemplate('swipebox');
            break;
        default:
            echo $this->loadTemplate('nomodal');
            break;
    }
    echo (!empty($this->event->onContentAfterDisplay)) ? $this->event->onContentAfterDisplay : '';
    ?>

    <?php if (($this->params->def('show_pagination', 1) == 1 || ($this->params->get('show_pagination') == 2)) && ($this->pagination->get('pages.total') > 1)) { ?>
        <div class="pagination">
            <?php if ($this->params->def('show_pagination_results', 1)) { ?>
                <p class="counter pull-right"> <?php echo $this->pagination->getPagesCounter(); ?> </p>
            <?php } ?>
            <?php echo $this->pagination->getPagesLinks(); ?> </div>
    <?php } ?>

<?php } // if ( isset($this->item) ) {?>