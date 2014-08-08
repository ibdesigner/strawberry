<?php

class Strawberry {

    var $excerpt_length = 200;
    var $thumb_size = 'medium';

    /**
     * 
     * @param array $args - wordpress wp_query params
     * @return array
     */
    public function posts($args) {
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
            $pid = get_the_ID();
            $arr[$x]['title'] = get_the_title($pid);
            $arr[$x]['content'] = wpautop(get_the_content($pid));
            $arr[$x]['excerpt'] = get_the_excerpt($pid);
            $arr[$x]['content_excerpt'] = $this->strawberry_crop_text($args['excerpt_length'], $arr[$x]['content']);
            $arr[$x]['images'] = $this->strawberry_images($pid);
            $arr[$x]['thumb'] = $this->strawberry_thumb_src($pid, false);
            $arr[$x]['permalink'] = get_permalink($pid);
            $arr[$x]['meta'] = $this->strawberry_metas($pid);
            $x++;
        endwhile;

        return $arr;
    }

    /**
     * @param: array - wordpress wp_query params
     * @return: single/first post from database
     */
    public function single($args) {
        $posts = $this->posts($args);
        return $posts[0];
    }

    /**
     * Extracts a portion of text
     * @param: $length of text, text
     * @return: cropped text from begining
     */
    private function strawberry_crop_text($length, $excerpt) {
        $excerpt = preg_replace(" (\[.*?\])", '', $excerpt);
        $excerpt = strip_shortcodes($excerpt);
        $excerpt = strip_tags($excerpt);
        $excerpt = substr($excerpt, 0, $length);
        $excerpt = substr($excerpt, 0, strripos($excerpt, " "));
        $excerpt = trim(preg_replace('/\s+/', ' ', $excerpt));

        return $excerpt;
    }

    /**
     * Extracts children images (post_type = attachment) for selected post
     * @param: $pid (int)
     * @retun: array|false Returns all images as array of arrays with thumb names as keys in second array
     */
    private function strawberry_images($pid) {
        $photos = get_children(
                array(
                    'post_parent' => $pid,
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
            foreach ($photos as $photo) {

                foreach ($image_sizes as $size) {
                    $thumb_data = $this->get_image_data($photo->ID, $size);
                    $results[$x][$size] = $thumb_data;
                }
                $x++;
            }
            return $results;
        } else {
            return false;
        }
    }

    /**
     * 
     * @param type $image_id
     * @param type $size
     * @return type
     */
    private function get_image_data($image_id, $size) {
        $image_data = wp_get_attachment_image_src($image_id, $size);
        return array(
            'src' => $image_data[0],
            'width' => $image_data[1],
            'height' => $image_data[2]
        );
    }

    /**
     * 
     * @param type $pid
     * @return type
     */
    public function strawberry_thumb_src($pid) {
        $image_sizes = get_intermediate_image_sizes();
        foreach ($image_sizes as $size) {
            $thumb[$size] = $this->get_image_data(get_post_thumbnail_id($pid), $size, false, '');
        }

        return $thumb;
    }

    /**
     * 
     * @param type $pid
     * @return type
     */
    public function strawberry_metas($pid) {
        $metas = get_post_meta($pid);
        $x = 0;

        foreach ($metas as $key => $meta) {
            if (count($meta) == 1) {
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