<?php
/**
 * @package      MagicGallery
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2014 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;
?>

<div class="row-fluid">
    <div class="span12">
        <form enctype="multipart/form-data" action="<?php echo JRoute::_('index.php?option=com_magicgallery'); ?>" method="post" name="adminForm" id="project-form" class="form-validate form-horizontal" >

            <?php echo $this->form->getControlGroup('title'); ?>
            <?php echo $this->form->getControlGroup('alias'); ?>
            <?php echo $this->form->getControlGroup('catid'); ?>
            <?php echo $this->form->getControlGroup('url'); ?>
            <?php echo $this->form->getControlGroup('published'); ?>
            <?php echo $this->form->getControlGroup('user_id'); ?>
            <?php echo $this->form->getControlGroup('id'); ?>
            <?php echo $this->form->getControlGroup('description'); ?>

            <input type="hidden" name="task" value="" />
            <?php echo JHtml::_('form.token'); ?>

        </form>
    </div>
</div>