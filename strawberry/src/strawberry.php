<?php

class Strawberry {
    
    
    public function posts($args){
        $posts_q = new WP_Query($args);

        if (!isset($args['thumb_size'])) {
            $args['thumb_size'] = 'medium';
        }

        if (!isset($args['excerpt_length'])) {
            $args['excerpt_length'] = 100;
        }

        $x = 0;
        $arr = "";
        while ($posts_q->have_posts()) : $posts_q->the_post();
            $post_id = get_the_ID();
            $arr[$x]['title'] = get_the_title($post_id);
            $arr[$x]['content'] = wpautop(get_the_content($post_id));
            $arr[$x]['excerpt'] = get_the_excerpt($post_id);
            $arr[$x]['content_excerpt'] = strawberry_crop_text($args['excerpt_length'], $arr[$x]['content']);
            $arr[$x]['images'] = strawberry_images($post_id, $args['thumb_size']);
            $arr[$x]['thumb'] = strawberry_thumb_src($post_id, $args['thumb_size'], false);
            $arr[$x]['full'] = strawberry_thumb_src($post_id, 'full', false); 
            $arr[$x]['permalink'] = get_permalink($post_id);
            $arr[$x]['meta'] = strawberry_metas($post_id);
            $x++;
        endwhile;

        return $arr;
    }
    
    public function single($args){
        $posts = $this->posts($args);
        return $posts[0];
    }
    
}

?>