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
 * @var stdClass $resource
 * @var Magicgallery\Gallery\Gallery $gallery
 * @var array $galleries
 */
?>
<?php
$classes = array('pull-center');
if ($this->params->get('display_tip', 0)) {
    $classes[] = 'hasTooltip';
}

echo JHtml::_('Prism.ui.bootstrap3StartTabSet', 'js-mg-com-tabbed', array('active' => $this->activeTab));
$i = 1;
foreach ($this->items as $gallery) {
        echo JHtml::_('Prism.ui.bootstrap3AddTab', 'js-mg-com-tabbed', $gallery->getAlias(), $gallery->getTitle());
        ?>
        <div class="row">
            <?php
            $resources = $gallery->getEntities();
            foreach ($resources as $resource) {
                $projectDescriptionClean = trim(strip_tags($resource->description));

                if (!empty($projectDescriptionClean) and $this->params->get('description_max_charts', 0)) {
                    $projectDescriptionClean = JHtmlString::truncate($projectDescriptionClean, $this->params->get('description_max_charts'));
                }

                $titleClean = $titleCleanTruncated = $this->escape($resource->title);
                if ($this->params->get('title_max_charts', 0)) {
                    $titleClean = JHtmlString::truncate($titleClean, $this->params->get('title_max_charts'));
                }
                ?>

                <?php if ($resource !== null and ($resource->image and $resource->thumbnail)) { ?>
                    <div class="col-xs-6 col-md-4">
                        <a href="<?php echo $gallery->getMediaUri() . '/' . $resource->image; ?>" <?php echo $this->openLink; ?> class="thumbnail mt-10 <?php echo $this->modalClass; ?>" data-lightbox-gallery="js-com-tabbed-nivo<?php echo $resource->gallery_id;?>" title="<?php echo $titleClean; ?>">
                            <img src="<?php echo $gallery->getMediaUri() . '/' . $resource->thumbnail; ?>" alt="<?php echo $titleClean; ?>" class="<?php echo implode(' ', $classes); ?>"
                                <?php if ($this->params->get('display_tip', 0)) { ?>
                                    title="<?php echo JHtml::tooltipText($titleClean . '::' . $this->escape($projectDescriptionClean)); ?>"
                                <?php } ?>
                                />
                        </a>

                        <?php if ($this->params->get('display_title', 0)) { ?>
                            <h3>
                                <?php if ($this->params->get('title_linkable') and $gallery->getUrl()) { ?>
                                    <a href="<?php echo $gallery->getUrl(); ?>" <?php echo $this->openLink; ?>><?php echo $titleCleanTruncated; ?></a>
                                <?php } else { ?>
                                    <?php echo $titleCleanTruncated; ?>
                                <?php } ?>
                            </h3>
                        <?php } ?>

                        <?php if ($this->params->get('display_description', 0) and !empty($projectDescriptionClean)) { ?>
                            <p><?php echo $this->escape($projectDescriptionClean); ?></p>
                        <?php } ?>

                        <?php if ($this->params->get('display_url', 0) and $gallery->getUrl()) { ?>
                            <a href="<?php echo $gallery->getUrl(); ?>" <?php echo $this->openLink; ?>><?php echo $gallery->getUrl(); ?></a>
                        <?php } ?>
                    </div>
                <?php }  // if($resource !== null... { ?>
            <?php } // foreach ($resources as $resource) { ?>
        </div>
        <?php echo JHtml::_('Prism.ui.bootstrap3EndTab'); ?>
    <?php } // foreach ($this->items as $gallery) { ?>
<?php echo JHtml::_('Prism.ui.bootstrap3EndTabSet'); ?>