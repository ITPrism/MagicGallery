<?php
/**
 * @package      MagicGallery
 * @subpackage   Component
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;
?>
<?php
$classes = array("pull-center");
if ($this->params->get("display_tip", 0)) {
    $classes[] = "hasTooltip";
}

echo JHtml::_('Prism.ui.bootstrap3StartTabSet', 'js-mg-com-tabbed', array('active' => $this->activeTab));
$i = 1;
foreach ($this->items as $item) {

    if (isset($this->galleries[$item->id])) {
        echo JHtml::_('Prism.ui.bootstrap3AddTab', "js-mg-com-tabbed", $item->alias, $item->title);
        ?>
        <div class="row">
            <?php
            /** @var Magicgallery\Gallery\Gallery $gallery */
            foreach ($this->galleries[$item->id] as $gallery) {
                $projectDescriptionClean = Joomla\String\String::trim(strip_tags($gallery->getDescription()));

                if (!empty($projectDescriptionClean) and $this->params->get("description_max_charts", 0)) {
                    $projectDescriptionClean = JHtmlString::truncate($projectDescriptionClean, $this->params->get("description_max_charts"));
                }

                $titleClean = $this->escape($gallery->getTitle());
                if ($this->params->get("title_max_charts", 0)) {
                    $titleClean = JHtmlString::truncate($titleClean, $this->params->get("title_max_charts"));
                }

                $defaultResource = $gallery->getDefaultItem();
                ?>

                <?php if ($defaultResource and $defaultResource->getThumbnail()) { ?>
                <div class="col-xs-6 col-md-4">
                    <a href="<?php echo $this->mediaUrl . "/" . $defaultResource->getImage(); ?>" <?php echo $this->openLink; ?> class="thumbnail mt-10">
                        <img src="<?php echo $this->mediaUrl . "/" . $defaultResource->getThumbnail(); ?>"
                             alt="<?php echo $titleClean; ?>"
                             class="<?php echo implode(" ", $classes); ?>"
                            <?php if ($this->params->get("display_tip", 0)) { ?>
                            title="<?php echo JHtml::tooltipText($titleClean . "::" . $this->escape($projectDescriptionClean)); ?>"
                            <?php } ?>
                            />
                    </a>

                    <?php if ($this->params->get("display_title", 0)) { ?>
                        <h3>
                            <?php if ($this->params->get("title_linkable") and $gallery->getUrl()) { ?>
                                <a href="<?php echo $gallery->getUrl(); ?>" <?php echo $this->openLink; ?>><?php echo $titleClean; ?></a>
                            <?php } else { ?>
                                <?php echo $titleClean; ?>
                            <?php } ?>
                        </h3>
                    <?php } ?>

                    <?php if ($this->params->get("display_description", 0) AND !empty($projectDescriptionClean)) { ?>
                        <p><?php echo $this->escape($projectDescriptionClean); ?></p>
                    <?php } ?>

                    <?php if ($this->params->get("display_url", 0) and $gallery->getUrl()) { ?>
                        <a href="<?php echo $gallery->getUrl(); ?>" <?php echo $this->openLink; ?>><?php echo $gallery->getUrl(); ?></a>
                    <?php } ?>
                </div>
                <?php }  // if($project['thumb']) { ?>

            <?php } // foreach ($this->projects[$item->id] ... ?>

        </div>
        <?php echo JHtml::_('Prism.ui.bootstrap3EndTab'); ?>

    <?php } // if (isset($this->projects[$item->id])) { ?>

<?php } ?>
<?php echo JHtml::_('Prism.ui.bootstrap3EndTabSet'); ?>
