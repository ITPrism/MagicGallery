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
<form enctype="multipart/form-data" action="<?php echo JRoute::_('index.php?option=com_magicgallery'); ?>" method="post" name="adminForm" id="entity-form" class="form-validate" >
    <div class="form-horizontal">
        <div class="row-fluid form-horizontal-desktop">
            <div class="span9" >
                <?php echo $this->form->getControlGroup('title'); ?>
                <?php echo $this->form->getControlGroup('alias'); ?>
                <?php echo $this->form->getControlGroup('catid'); ?>
                <?php echo $this->form->getControlGroup('url'); ?>
                <?php echo $this->form->getControlGroup('published'); ?>

                <div class="control-group">
                    <div class="control-label"><?php echo $this->form->getLabel('image'); ?></div>
                    <div class="controls">

                        <div class="fileupload fileupload-new" data-provides="fileupload">
                            <span class="btn btn-file">
                                <span class="fileupload-new"><i class="icon-folder-open"></i> <?php echo JText::_('COM_MAGICGALLERY_SELECT_IMAGE'); ?></span>
                                <span class="fileupload-exists"><i class="icon-edit"></i> <?php echo JText::_('COM_MAGICGALLERY_CHANGE'); ?></span>
                                <?php echo $this->form->getInput('image'); ?>
                            </span>
                            <span class="fileupload-preview"></span>
                            <a href="#" class="close fileupload-exists" data-dismiss="fileupload" style="float: none">×</a>
                        </div>
                    </div>
                </div>

                <div class="control-group">
                    <div class="control-label"><?php echo $this->form->getLabel('thumbnail'); ?></div>
                    <div class="controls">
                        <div class="fileupload fileupload-new" data-provides="fileupload">
                            <span class="btn btn-file">
                                <span class="fileupload-new"><i class="icon-folder-open"></i> <?php echo JText::_('COM_MAGICGALLERY_SELECT_THUMBNAIL'); ?></span>
                                <span class="fileupload-exists"><i class="icon-edit"></i> <?php echo JText::_('COM_MAGICGALLERY_CHANGE'); ?></span>
                                <?php echo $this->form->getInput('thumbnail'); ?>
                            </span>
                            <span class="fileupload-preview"></span>
                            <a href="#" class="close fileupload-exists" data-dismiss="fileupload" style="float: none">×</a>
                        </div>
                    </div>
                </div>

                <?php echo $this->form->getControlGroup('id'); ?>
                <?php echo $this->form->getControlGroup('description'); ?>

            <?php if (!empty($this->item->thumbnail)) {?>
                <h4><?php echo JText::_('COM_MAGICGALLERY_THUMBNAIL');?></h4>
                <img src="<?php echo $this->mediaUri .'/'. $this->item->thumbnail; ?>"  />
                <br />

                <a href="<?php echo JRoute::_('index.php?option=com_magicgallery&task=entity.removeImage&type=thumbnail&id=' . $this->item->id); ?>" class="btn btn-danger mtb-20" >
                    <i class="icon-trash"></i>
                    <?php echo JText::_('COM_MAGICGALLERY_DELETE_THUMBNAIL'); ?>
                </a>
            <?php }?>


            <?php if (!empty($this->item->image)) {?>
                <h4><?php echo JText::_('COM_MAGICGALLERY_LARGE_IMAGE');?></h4>
                <img src="<?php echo $this->mediaUri .'/'. $this->item->image; ?>" />
                <br />

                <a href="<?php echo JRoute::_('index.php?option=com_magicgallery&task=entity.removeImage&type=image&id=' . $this->item->id); ?>" class="btn btn-danger mtb-20" >
                    <i class="icon-trash"></i>
                    <?php echo JText::_('COM_MAGICGALLERY_DELETE_IMAGE'); ?>
                </a>
            <?php }?>
            </div>

            <div class="span3">
                <?php echo $this->loadTemplate('resize');?>
            </div>
        </div>

    </div>

    <?php echo $this->form->getInput('gallery_id'); ?>
    <input type="hidden" name="task" value="" />
    <input type="hidden" name="gid" value="<?php echo $this->galleryId; ?>" />
    <?php echo JHtml::_('form.token'); ?>
</form>