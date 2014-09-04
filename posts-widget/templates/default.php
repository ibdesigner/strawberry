<?php global $exclude_ids; ?>
<?php foreach($posts as $post): ?>
<div class="row article">
    <div class="col-md-12">
        <a href="<?php echo $post['permalink'] ?>"><img src="<?php echo $post['thumb'][$instance['thumbnail']]['src']  ?>" class="img-responsive" alt=""/></a>
    </div>
    <div class="col-md-12">
        <div class="title"> <a href="<?php echo $post['permalink'] ?>"><?php echo $post['title'] ?></a></div>
        
        <?php if(isset($instance['excerpt']) && $instance['excerpt'] == 'on') : ?>
        <div class="excerpt"><?php echo $post['content_excerpt'] ?></div>
        <?php endif;?>
        
        <?php if(isset($instance['date']) && $instance['date'] == 'on') : ?>
        <div class="date"><?php echo date("d/m/Y", $post['date']) ?></div>
        <?php endif;?>
    </div>
</div>
<?php $exclude_ids[] = $post['ID'];  endforeach; ?>