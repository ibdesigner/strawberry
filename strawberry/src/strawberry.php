<?php

class Strawberry {
    
    var $excerpt_length = 200;
    var $thumb_size = 'medium';
    
    public function posts($args){
        $posts_q = new WP_Query($args);

        if (!isset($args['thumb_size'])) {
            $args['thumb_size'] = $this->thumb_size;
        }

        if (!isset($args['excerpt_length'])) {
            $args['excerpt_length'] = $this->excerpt_length;
        }

        $x = 0;
        $arr = "";
        while ($posts_q->have_posts()) : $posts_q->the_post();
            $post_id = get_the_ID();
            $arr[$x]['title'] = get_the_title($post_id);
            $arr[$x]['content'] = wpautop(get_the_content($post_id));
            $arr[$x]['excerpt'] = get_the_excerpt($post_id);
            $arr[$x]['content_excerpt'] = $this->strawberry_crop_text($args['excerpt_length'], $arr[$x]['content']);
            $arr[$x]['images'] = $this->strawberry_images($post_id);
            $arr[$x]['thumb'] = $this->strawberry_thumb_src($post_id, false);
            $arr[$x]['permalink'] = get_permalink($post_id);
            $arr[$x]['meta'] = $this->strawberry_metas($post_id);
            $x++;
        endwhile;
    
        return $arr;
    }
    
    public function single($args){
        $posts = $this->posts($args);
        return $posts[0];
    }
    
    private function strawberry_crop_text($length, $excerpt) {
        $excerpt = preg_replace(" (\[.*?\])", '', $excerpt);
        $excerpt = strip_shortcodes($excerpt);
        $excerpt = strip_tags($excerpt);
        $excerpt = substr($excerpt, 0, $length);
        $excerpt = substr($excerpt, 0, strripos($excerpt, " "));
        $excerpt = trim(preg_replace('/\s+/', ' ', $excerpt));

        return $excerpt;
    }
    
    private function strawberry_images( $post_id ) {
        $photos = get_children(
                array(
                    'post_parent' => $post_id,
                    'post_status' => 'inherit',
                    'post_type' => 'attachment',
                    'post_mime_type' => 'image',
                    'order' => 'ASC',
                    'orderby' => 'menu_order ID'
                )
        );
        $results = array();
        $image_sizes = get_intermediate_image_sizes();

        if ($photos) {
            $x = 0;
            foreach ( $photos as $photo ) {
            
                foreach( $image_sizes as $size ) {
                    $thumb_data = $this->get_image_data($photo->ID, $size);
                    $results[$x][$size] = $thumb_data;
                }
                $x++;
                
            }
        }
        return $results;
    }
    
    private function get_image_data($image_id, $size){
        $image_data = wp_get_attachment_image_src($image_id, $size);
        return array(
                        'src' => $image_data[0],
                        'width' => $image_data[1],
                        'height' => $image_data[2]
                    );
    }
    
    public function strawberry_thumb_src ( $post_id ) {
        $image_sizes = get_intermediate_image_sizes();
        foreach($image_sizes as $size){
            $thumb[$size] = $this->get_image_data(get_post_thumbnail_id($post_id), $size, false, '');       
        }
        
        return $thumb;
    }
    
    
    public function strawberry_metas($post_id) {
        $metas = get_post_meta($post_id);
        $x=0;
        
        foreach($metas as $key => $meta){
            if( count($meta) == 1 ){
                $m[$key] = $meta[0];
            } else {
                $m[$key] = $meta;
            }
            $x++;
        }
        
        return $m;
    }
    
}

?>