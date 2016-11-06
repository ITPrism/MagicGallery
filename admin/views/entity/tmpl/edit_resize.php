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
/**
 * @var array $fields
 */
?>
<div class="panel panel-default">
    <div class="panel-heading"><?php echo JText::_('COM_MAGICGALLERY_RESIZE_OPTIONS'); ?></div>
    <div class="panel-body">
        <?php
        $fields = $this->form->getGroup('resize');
        foreach ($fields as $field) { ?>
            <div class="control-group">
                <?php if (!$field->hidden) : ?>
                <div class="control-label">
                    <?php echo $field->label; ?>
                </div>
                <?php endif; ?>
                <div class="controls">
                    <?php echo $field->input; ?>
                </div>
            </div>
        <?php } ?>
    </div>
</div>