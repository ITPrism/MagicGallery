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
 * @var Magicgallery\Entity\Entity $defaultResource
 * @var Magicgallery\Entity\Entities $resources
 * @var Magicgallery\Gallery\Gallery $gallery
 * @var stdClass $resource
 */
foreach ($this->items as $gallery) {
    $mediaUrl        = $gallery->getMediaUri();
    $resources       = $gallery->getEntities();
    $defaultResource = null;

    if (count($resources) > 0) {
        $defaultResource = $resources->getDefaultEntity();
        $defaultWidth  = $this->params->get('thumb_width', 200);
        $defaultHeight = $this->params->get('thumb_height', 200);
    }
    ?>
    <div class="row mb-15">
        <?php if ($defaultResource) { ?>
        <div class="col-md-4 col-sm-12 col-xs-12" style="width: <?php echo $this->params->get('thumb_width', 200); ?>; height: <?php echo $this->params->get('thumb_height', 200); ?>;">
            <?php if ($this->params->get('image_linkable')) { ?>
            <a href="<?php echo $mediaUrl . '/'. $defaultResource->getImage(); ?>" <?php echo $this->openLink; ?> data-lightbox-gallery="com-list-nivo-gallery<?php echo $defaultResource->getGalleryId();?>" class="<?php echo $this->modalClass;?>">
            <?php } ?>
                <img width="<?php echo $defaultWidth; ?>" height="<?php echo $defaultHeight; ?>" src="<?php echo $mediaUrl . '/'. $defaultResource->getThumbnail(); ?>" alt="<?php echo $this->escape($defaultResource->getTitle()); ?>" title="<?php echo $this->escape($defaultResource->getTitle()); ?>" class="thumbnail mb-5"/>
            <?php if ($this->params->get('image_linkable')) { ?>
            </a>
            <?php } ?>

            <?php if ($this->params->get('display_additional_images', 0) and count($resources) > 0) { ?>
            <div class="mg-additional-images">
            <?php
            $i = 0;
            foreach ($resources as $resource) {
                if ((int)$resource->home !== 1) {
                    $resourceWidth  = $this->params->get('additional_images_thumb_width', 50);
                    $resourceHeight = $this->params->get('additional_images_thumb_height', 50);
                ?>
                <a href="<?php echo $mediaUrl . '/' . $resource->image; ?>" <?php echo $this->openLink; ?> data-lightbox-gallery="com-list-nivo-gallery<?php echo $resource->gallery_id; ?>" class="<?php echo $this->modalClass; ?>" title="<?php echo $this->escape($resource->title); ?>">
                    <img width="<?php echo $resourceWidth; ?>" height="<?php echo $resourceHeight; ?>" src="<?php echo $mediaUrl . '/' . $resource->thumbnail; ?>"/>
                </a>
                <?php
                $i++;
                    if ($i === (int)$this->params->get('additional_images_number', 3)) {
                    break;
                    }
                }
            } ?>
            </div>
            <?php } ?>
        </div>
    <?php } ?>
    <div class="col-md-8 col-sm-12 col-xs-12">
        <?php
        if ($this->params->get('display_title')) { ?>
            <h3>
            <?php if ($this->params->get('title_linkable') and $gallery->getUrl()) { ?>
                <a href="<?php echo $gallery->getUrl(); ?>" <?php echo $this->openLink; ?>><?php echo $this->escape($gallery->getTitle()); ?></a>
            <?php } else { ?>
                <?php echo $this->escape($gallery->getTitle()); ?>
            <?php } ?>
            <?php echo (!empty($this->event->onContentAfterTitle) ) ? $this->event->onContentAfterTitle : ''; ?>
            </h3>
        <?php } ?>

        <?php echo $gallery->getDescription(); ?>
    </div>
    </div>
<?php }?>