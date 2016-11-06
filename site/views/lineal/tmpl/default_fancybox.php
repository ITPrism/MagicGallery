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
/**
 * @var Magicgallery\Entity\Entities $resources
 * @var stdClass $resource
 */
?>
<div class="row">
    <?php if (!empty($this->defaultResource)) { ?>
        <div class="col-md-4 col-sm-12 col-xs-12">
            <?php if ($this->params->get('image_linkable')) {?>
            <a href="<?php echo $this->item->media_uri .'/'. $this->defaultResource->getImage();?>" <?php echo $this->openLink;?> class="<?php echo $this->modalClass;?>" rel="js-com-group<?php echo $this->item->id;?>" title="<?php echo $this->escape($this->defaultResource->getTitle()); ?>">
                <?php }?>
                <img
                    width="<?php echo $this->params->get('thumb_width', 300); ?>"
                    height="<?php echo $this->params->get('thumb_height', 300); ?>"
                    src="<?php echo $this->item->media_uri .'/'. $this->defaultResource->getThumbnail();?>"
                    alt="<?php echo $this->escape($this->defaultResource->getTitle()); ?>"
                    title="<?php echo $this->escape($this->defaultResource->getTitle()); ?>"
                    class="thumbnail"
                    />
            <?php if ($this->params->get('image_linkable')) {?></a><?php } ?>

            <?php if ($this->params->get('display_additional_images', 0) and count($this->item->entities) > 0) {?>
                <div class="mg-additional-images">
                    <?php
                    $i = 0;
                    $resources = $this->item->entities;
                    foreach ($resources as $resource) { ?>
                        <a href="<?php echo $this->item->media_uri .'/'. $resource->image;?>" <?php echo $this->openLink;?> class="<?php echo $this->modalClass;?>" rel="js-com-group<?php echo $this->item->id;?>" title="<?php echo $this->escape($resource->title); ?>">
                            <img
                                width="<?php echo $this->params->get('additional_images_thumb_width', 50); ?>"
                                height="<?php echo $this->params->get('additional_images_thumb_height', 50); ?>"
                                src="<?php echo $this->item->media_uri .'/'. $resource->thumbnail;?>"
                                alt="<?php echo $this->escape($resource->title); ?>"
                                title="<?php echo $this->escape($resource->title); ?>"
                                />
                        </a>
                        <?php
                        $i++;
                        if ($i === (int)$this->params->get('additional_images_number', 3)){ break; }
                    }
                    ?>
                </div>
            <?php } ?>
        </div>
    <?php } ?>
    <div class="col-md-8 col-sm-12 col-xs-12">
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