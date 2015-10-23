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
<?php foreach ($this->items as $i => $item) {
	    $ordering  = ($this->listOrder == 'a.ordering');
	    
        $disableClassName = '';
		$disabledLabel	  = '';
		if (!$this->saveOrder) {
			$disabledLabel    = JText::_('JORDERINGDISABLED');
			$disableClassName = 'inactive tip-top';
		}
		
	?>
	<tr class="row<?php echo $i % 2; ?>" sortable-group-id="<?php echo $item->catid?>">
		<td class="order nowrap center hidden-phone">
    		<span class="sortable-handler hasTooltip <?php echo $disableClassName?>" title="<?php echo $disabledLabel?>">
    			<i class="icon-menu"></i>
    		</span>
    		<input type="text" style="display:none" name="order[]" size="5" value="<?php echo $item->ordering;?>" class="width-20 text-area-order " />
    	</td>
		<td class="center">
            <?php echo JHtml::_('grid.id', $i, $item->id); ?>
        </td>
        <td class="center">
            <?php echo JHtml::_('jgrid.published', $item->published, $i, "galleries."); ?>
        </td>
		<td class="title">
			<a href="<?php echo JRoute::_("index.php?option=com_magicgallery&view=gallery&layout=edit&id=".(int)$item->id); ?>" >
                <?php echo $item->title; ?>
            </a>
            <div class="small">
                <?php echo JText::sprintf("COM_MAGICGALLERY_ALIAS_S", $this->escape($item->alias)); ?>
            </div>
            <div class="small">
                <a href="<?php echo JRoute::_("index.php?option=com_magicgallery&view=entities&gid=".(int)$item->id); ?>">
                <?php echo JText::sprintf("COM_MAGICGALLERY_ENTITIES_S", (isset($this->numberOfResources[$item->id])) ? (int)$this->numberOfResources[$item->id]['number'] : 0); ?>
                </a>
            </div>
        </td>
        <td class="nowrap hidden-phone">
            <?php echo (!empty($item->category)) ? $this->escape($item->category) : JText::_("COM_MAGICGALLERY_UNCATEGORISED"); ?>
        </td>
        <td class="nowrap hidden-phone">
            <?php echo (!empty($item->extension)) ? $this->escape($item->extension) : "--"; ?>
        </td>
        <td class="hidden-phone"><?php echo JHtmlString::truncate($item->url, 64); ?></td>
        <td class="center hidden-phone"><?php echo (int)$item->id;?></td>
	</tr>
<?php }?>
	  