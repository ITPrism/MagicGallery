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
<tr>
    <th width="1%" class="nowrap center hidden-phone">
        <?php echo JHtml::_('searchtools.sort', '', 'a.ordering', $this->listDirn, $this->listOrder, null, 'asc', 'JGRID_HEADING_ORDERING', 'icon-menu-2'); ?>
	</th>
    <th width="1%" class="center">
        <?php echo JHtml::_('grid.checkall'); ?>
	</th>
	<th width="1%" style="min-width:55px" class="nowrap center">
		<?php echo JHtml::_('searchtools.sort', 'JSTATUS', 'a.published', $this->listDirn, $this->listOrder); ?>
	</th>
	<th>
	     <?php echo JHtml::_('searchtools.sort',  'JGLOBAL_TITLE', 'a.title', $this->listDirn, $this->listOrder); ?>
	</th>
    <th width="10%" class="nowrap hidden-phone">
        <?php echo JHtml::_('searchtools.sort',  'COM_MAGICGALLERY_CATEGORY', 'b.title', $this->listDirn, $this->listOrder); ?>
    </th>
    <th width="10%" class="nowrap hidden-phone">
        <?php echo JHtml::_('searchtools.sort',  'COM_MAGICGALLERY_EXTENSION', 'a.extension', $this->listDirn, $this->listOrder); ?>
    </th>
    <th width="20%" class="nowrap hidden-phone">
		<?php echo JText::_('COM_MAGICGALLERY_URL'); ?>
	</th>
    <th width="3%" class="nowrap center hidden-phone">
        <?php echo JHtml::_('searchtools.sort',  'JGRID_HEADING_ID', 'a.id', $this->listDirn, $this->listOrder); ?>
    </th>
</tr>