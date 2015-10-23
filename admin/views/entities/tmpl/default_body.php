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
	    $ordering  = ($this->listOrder === 'a.ordering');
	    
        $disableClassName = '';
		$disabledLabel	  = '';
		if (!$this->saveOrder) {
			$disabledLabel    = JText::_('JORDERINGDISABLED');
			$disableClassName = 'inactive tip-top';
		}

    $itemParams = (!empty($item->params)) ? json_decode($item->params, true) : array();
    $previewImage = (!empty($item->thumbnail)) ? $item->thumbnail : $item->image;
	?>
	<tr class="row<?php echo $i % 2; ?>" sortable-group-id="<?php echo $item->id?>">
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
            <?php echo JHtml::_('jgrid.published', $item->published, $i, 'entities.'); ?>
        </td>
		<td class="title">
			<a href="<?php echo JRoute::_('index.php?option=com_magicgallery&view=entity&layout=edit&id='.(int)$item->id); ?>" >
                <?php echo $item->title; ?>
            </a>
        </td>
        <td class="nowrap hidden-phone">
            <?php if(!empty($previewImage)) { ?>
            <a href="<?php echo $this->mediaUri . $item->image; ?>" title="<?php echo $this->escape($item->title); ?>" class="lightbox" data-lightbox-gallery="gallery<?php echo (int)$item->gallery_id; ?>">
                <img src="<?php echo $this->mediaUri . $previewImage; ?>" width="50" height="50"/>
            </a>
            <?php } ?>
        </td>
        <td class="nowrap hidden-phone">
            <?php
            echo $this->escape($item->image);
            $imageParams = (!empty($itemParams['image'])) ? $itemParams['image'] : array();
            echo JHtml::_('Magicgallery.fileInfo', $imageParams);
            ?>
        </td>
        <td class="nowrap hidden-phone">
            <?php
            echo $this->escape($item->thumbnail);
            $imageParams = (!empty($itemParams['thumbnail'])) ? $itemParams['thumbnail'] : array();
            echo JHtml::_('Magicgallery.fileInfo', $imageParams);
            ?>
        </td>
        <td class="nowrap center hidden-phone">
            <?php echo JHtml::_('jgrid.isdefault', $item->home, $i, 'entities.');?>
        </td>
        <td class="center hidden-phone"><?php echo (int)$item->id;?></td>
	</tr>
<?php }?>
	  