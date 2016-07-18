<?php
/**
 * @package      Magicgallery
 * @subpackage   Component
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2016 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;?>
<?php foreach ( $this->items as $item ) {
    $images = JArrayHelper::getValue($this->images, $item->id);
    $defaultImage = array();

    if (!empty($images)) {
        $defaultImage = JArrayHelper::getValue($images, 'default');
        unset($images['default']);
    }
    ?>
    <div class="row">
        <?php if ($defaultImage) { ?>
            <div class="col-md-3" style="width: <?php echo $this->params->get('thumb_width', 200); ?>; height: <?php echo $this->params->get('thumb_height', 200); ?>;">
                <?php if ($this->params->get('image_linkable')) { ?>
                <a href="<?php echo $this->mediaUrl . "/". $defaultImage["image"]; ?>" <?php echo $this->openLink; ?> data-lightbox-gallery="com-list-nivo-gallery<?php echo $item->id;?>" class="<?php echo $this->modalClass;?>">
                    <?php } ?>
                    <img
                        width="<?php echo $this->params->get('thumb_width', 200); ?>"
                        height="<?php echo $this->params->get('thumb_height', 200); ?>"
                        src="<?php echo $this->mediaUrl . '/'. $defaultImage['thumbnail']; ?>"
                        alt="<?php echo $this->escape($item->title); ?>"
                        title="<?php echo $this->escape($item->title); ?>"
                        class="thumbnail"
                        />
                    <?php if ($this->params->get('image_linkable')) { ?>
                </a>
            <?php } ?>

                <?php if ($this->params->get('display_additional_images', 0) and !empty($images)) {?>
                    <div class="mg-additional-images">
                        <?php
                        $i = 0;
                        foreach ($images as $image) {
                            ?>
                            <a href="<?php echo $this->mediaUrl . '/'. $image['image'];?>" <?php echo $this->openLink;?> data-lightbox-gallery="com-list-nivo-gallery<?php echo $item->id;?>" class="<?php echo $this->modalClass;?>">
                                <img
                                    width="<?php echo $this->params->get('additional_images_thumb_width', 50); ?>"
                                    height="<?php echo $this->params->get('additional_images_thumb_height', 50); ?>"
                                    src="<?php echo $this->mediaUrl . '/'. $image['thumbnail'];?>"
                                    alt=""
                                    title=""
                                    />
                            </a>

                            <?php
                            $i++;
                            if ($i == $this->params->get('additional_images_number', 3)) {
                                break;
                            }
                        }
                        ?>
                    </div>
                <?php } ?>
            </div>
        <?php } ?>
        <div class="col-md-9">
            <?php
            if ($this->params->get('display_title')) { ?>
                <h3>
                <?php if ($this->params->get('title_linkable') and !empty($item->url)) { ?>
                    <a href="<?php echo $item->url; ?>" <?php echo $this->openLink; ?>><?php echo $this->escape($item->title); ?></a>
                <?php } else { ?>
                    <?php echo $this->escape($item->title); ?>
                <?php } ?>
                <?php echo (!empty($this->event->onContentAfterTitle) ) ? $this->event->onContentAfterTitle : ''; ?>
                </h3>
            <?php } ?>

            <?php echo $item->description; ?>
        </div>
    </div>
<?php }?>