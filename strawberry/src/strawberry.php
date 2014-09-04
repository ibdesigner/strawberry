<?php

class Strawberry {

    /**
     * Default custom excerpt length
     * @var INT
     */
    private static $excerpt_length = 200;

    /**
     * Default thumb size
     * @var STRING
     */
    private static $thumb_size = 'medium';

    /**
     *
     * @var INT - default cache time in seconds can be overwritten by cache function 
     */
    private static $cache_time = 30;

    /**
     * TODO: Add WP_CACHE into the ecuation. Cache data only if IS DEFINED and set to TRUE
     * @param array $args - wordpress wp_query params
     * @return array
     */
    public static function posts($args) {

        $cache_key = md5(serialize($args));

        $strawberry_query = StrawberryCache::get($cache_key);

        if (false === $strawberry_query) {

            $posts_q = new WP_Query($args);

            if (!isset($args['thumb_size'])) {
                $args['thumb_size'] = self::$thumb_size;
            }

            if (!isset($args['excerpt_length'])) {
                $args['excerpt_length'] = self::$excerpt_length;
            }

            $x = 0;
            $arr = "";

            if (defined('WP_DEBUG') && WP_DEBUG === true) {
                echo '<div class="alert alert-info">' . $posts_q->request . '</div>';
            }

            while ($posts_q->have_posts()) : $posts_q->the_post();
                $pid = get_the_ID();
                $content = wpautop(get_the_content($pid));

                $arr[$x]['ID'] = $pid;
                $arr[$x]['title'] = get_the_title($pid);
                $arr[$x]['content'] = $content;
                $arr[$x]['excerpt'] = get_the_excerpt();
                $arr[$x]['content_excerpt'] = self::crop_text($args['excerpt_length'], $content);
                //$arr[$x]['images']      = self::images($pid);
                $arr[$x]['thumb'] = self::feature_image($pid, false);
                $arr[$x]['permalink'] = get_permalink($pid);
                $arr[$x]['meta'] = self::metas($pid);
                $arr[$x]['author'] = array('name' => get_the_author(), 'permalink' => get_the_author_link());
                $arr[$x]['date'] = strtotime(get_the_date('Y-m-d H:i:s'));

                if (isset($args['taxonomy']) && $args['taxonomy'] === true) {
                    $arr[$x]['terms'] = self::terms($pid);
                }

                $x++;
            endwhile;

            wp_reset_postdata();

            StrawberryCache::time(self::$cache_time)->set($cache_key, $arr);
            return $arr;
        } else {
            return $strawberry_query;
        }
    }

    public static function cache($seconds) {
        self::$cache_time = $seconds;
        return new self();
    }

    /**
     * @param: array - wordpress wp_query params
     * @return: single/first post from database
     */
    public static function single($args) {
        $posts = self::posts($args);
        if (count($posts) >= 1) {
            return $posts[0];
        }
    }

    /**
     * Extracts a portion of text
     * @param: $length of text, text
     * @return: cropped text from begining
     */
    public static function crop_text($length, $excerpt) {
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
    public static function images($pid) {
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
                    $thumb_data = self::get_image_data($photo->ID, $size);
                    $results[$x]['thumbnails'][$size] = $thumb_data;

                    $image_content = get_post($photo->ID);
                    $results[$x]['src'] = $image_content->guid;
                    $results[$x]['caption'] = $image_content->post_excerpt;
                    $results[$x]['description'] = $image_content->post_content;

                    $image_meta = self::metas($photo->ID);
                    $results[$x]['alt'] = isset($image_meta['_wp_attachment_image_alt']) ? $image_meta['_wp_attachment_image_alt'] : "";

                    $results[$x]['permalink'] = get_permalink($photo->ID);
                    $results[$x]['ID'] = $photo->ID;
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
    private static function get_image_data($image_id, $size) {
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
    public static function feature_image($pid) {
        $image_sizes = get_intermediate_image_sizes();
        foreach ($image_sizes as $size) {
            $thumb[$size] = self::get_image_data(get_post_thumbnail_id($pid), $size, false, '');
        }

        return $thumb;
    }

    /**
     * 
     * @param type $pid
     * @return type
     */
    public static function metas($pid) {
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
        if (isset($m)) {
            return $m;
        }
    }

    /**
     * 
     * @param INT $pid
     * @return ARRAY
     */
    public static function terms($pid) {
        $taxonomies = self::public_taxonomies();


        $post_terms = wp_get_post_terms($pid, $taxonomies);
        $x = 0;
        $post_terms_array = array();

        foreach ($post_terms as $post_term) {
            $post_terms_array[$x][$post_term->taxonomy]['term_id'] = $post_term->term_id;
            $post_terms_array[$x][$post_term->taxonomy]['name'] = $post_term->name;
            $post_terms_array[$x][$post_term->taxonomy]['url'] = get_term_link($post_term);
            $x++;
        }

        if (!empty($post_terms_array)) {
            foreach ($post_terms_array as $terms_array) {
                foreach ($terms_array as $key => $value) {
                    $terms[$key][] = $value;
                }
            }
            return $terms;
        }
    }

    /**
     * 
     * @return ARRAY
     */
    private static function public_taxonomies() {
        $args = array(
            'public' => true,
        );
        $taxonomies = get_taxonomies($args, 'names', 'and');
        if ($taxonomies) {
            foreach ($taxonomies as $taxonomy) {
                $tax_list[] = $taxonomy;
            }
        }

        return $tax_list;
    }

}

?>