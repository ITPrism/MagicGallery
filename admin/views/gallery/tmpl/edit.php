<?php
/**
 * @package      Magicgallery
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2016 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;
?>
<form enctype="multipart/form-data" action="<?php echo JRoute::_('index.php?option=com_magicgallery'); ?>" method="post" name="adminForm" id="gallery-form" class="form-validate" >
    <div class="form-horizontal">
        <?php echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'details')); ?>

        <?php echo JHtml::_('bootstrap.addTab', 'myTab', 'details', JText::_('COM_MAGICGALLERY_CONTENT')); ?>
        <div class="row-fluid">
            <div class="span12">
            <?php echo $this->form->getControlGroup('title'); ?>
            <?php echo $this->form->getControlGroup('alias'); ?>
            <?php echo $this->form->getControlGroup('catid'); ?>
            <?php echo $this->form->getControlGroup('url'); ?>
            <?php echo $this->form->getControlGroup('published'); ?>
            <?php echo $this->form->getControlGroup('user_id'); ?>
            <?php echo $this->form->getControlGroup('id'); ?>
            <?php echo $this->form->getControlGroup('description'); ?>
            </div>
        </div>

        <?php echo JHtml::_('bootstrap.endTab'); ?>
        <?php echo JHtml::_('bootstrap.addTab', 'myTab', 'options', JText::_('COM_MAGICGALLERY_OPTIONS')); ?>
        <div class="row-fluid">
            <div class="span12">
            <?php echo $this->loadTemplate('options');?>
            </div>
        </div>
        <?php echo JHtml::_('bootstrap.endTab'); ?>

        <?php echo JHtml::_('bootstrap.addTab', 'myTab', 'publishing', JText::_('COM_MAGICGALLERY_PUBLISHING')); ?>
        <div class="row-fluid">
            <div class="span12">
            <?php echo $this->loadTemplate('publishing');?>
            </div>
        </div>
        <?php echo JHtml::_('bootstrap.endTab'); ?>

        <?php echo JHtml::_('bootstrap.endTabSet'); ?>

        <input type="hidden" name="task" value="" />
        <?php echo JHtml::_('form.token'); ?>
    </div>
</form>
