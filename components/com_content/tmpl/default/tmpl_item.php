
<?php if ($title_quote) { ?>
<div class="item-title-quote"><?php echo $title_quote ?></div>
<?php } ?>

<h2 class="item-title"><a href="<?php echo $element_link ?>"><?php echo $title ?> <?php echo $new ?>
<?php if ($title_alias) { ?>
<br /><span><?php echo $title_alias ?></span>
<?php } ?>
</a></h2>

<?php if ($image_thumb) { ?>
<a href="<?php echo $element_link ?>"><?php echo $image_thumb ?></a>
<?php } ?>

<?php echo $text_intro ?>

<?php #echo $medias ?>

<?php #echo $read_more ?>
<?php #echo $item_infos ?>

<div class="content-break"></div>
<hr />
