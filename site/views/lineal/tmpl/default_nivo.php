<?php
/**
 * @package      MagicGallery
 * @subpackage   Component
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;?>
<div class="row">
    <?php if(!empty($this->defaultImage)) { ?>
        <div class="col-md-4">
            <?php if ($this->params->get('image_linkable')) {?>
            <a href="<?php echo $this->mediaUrl.$this->defaultImage->getImage();?>" <?php echo $this->openLink;?> data-lightbox-gallery="com-list-nivo-gallery<?php echo $this->item->id;?>" class="<?php echo $this->modalClass;?>">
                <?php }?>
                <img
                    width="<?php echo $this->params->get('lineal_thumb_width', 300); ?>"
                    height="<?php echo $this->params->get('lineal_thumb_height', 300); ?>"
                    src="<?php echo $this->mediaUrl.$this->defaultImage->getThumbnail();?>"
                    alt="<?php echo $this->escape($this->item->title);?>"
                    title="<?php echo $this->escape($this->item->title);?>"
                    class="thumbnail"
                    />
                <?php if ($this->params->get('image_linkable')) {?></a><?php } ?>

            <?php if ($this->params->get('display_additional_images', 0) and 0 < count($this->images)) {?>
                <div class="mg-additional-images">
                    <?php
                    $i = 0;
                    foreach($this->images as $eImage){?>
                        <a href="<?php echo $this->mediaUrl.$eImage->getImage();?>" <?php echo $this->openLink;?> data-lightbox-gallery="com-list-nivo-gallery<?php echo $this->item->id;?>" class="<?php echo $this->modalClass;?>">
                            <img
                                width="<?php echo $this->params->get('additional_images_thumb_width', 50); ?>"
                                height="<?php echo $this->params->get('additional_images_thumb_height', 50); ?>"
                                src="<?php echo $this->mediaUrl.$eImage->getThumbnail();?>"
                                alt=""
                                title=""
                                class="thumbnail"
                                />
                        </a>
                        <?php
                        $i++;
                        if($i == $this->params->get('additional_images_number', 3)){ break; }
                    }
                    ?>
                </div>
            <?php } ?>
        </div>
    <?php } ?>
    <div class="col-md-8">
        <?php if ($this->params->get('display_title')) {?>
            <h3>
                <?php if ($this->params->get('title_linkable') and !empty($this->item->url)) { ?>
                    <a href="<?php echo $this->item->url;?>" <?php echo $this->openLink;?>><?php echo $this->escape($this->item->title);?></a>
                <?php } else { ?>
                    <?php echo $this->escape($this->item->title);?>
                <?php }?>
                <?php echo (!empty($this->event->onContentAfterTitle) ) ? $this->event->onContentAfterTitle : ''; ?>
            </h3>
        <?php }?>

        <?php echo $this->item->description;?>

    </div>
</div>