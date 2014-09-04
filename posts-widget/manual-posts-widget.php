<?php
if (!class_exists('Strawberry')) {
    exit('Strawberry class not included');
}


class Strawberry_manual_posts_widget extends WP_Widget {

    private $template_dir;

    public function __construct() {

        $this->template_dir = dirname(__FILE__) . "/templates/";

        parent::__construct(
                'strawberry_manual_posts_widget', __('Manual Posts Widget', 'strawberry'), array('description' => __('Listing manual selected posts', 'text_domain'),)
        );
    }

    public function widget($args, $instance) {
        
        if(defined('WP_DEBUG') && WP_DEBUG === true){
            $time = explode(' ', microtime());
            $start = $time[1] + $time[0];
        }   
        
        $widget_key = "manual-posts-widget-" . $this->id;

        if (!isset($instance['cache_time']) || $instance['cache_time'] == "") {
            $instance['cache_time'] = 60;
        }

        $title = apply_filters('widget_title', $instance['title']);


        if (!isset($instance['template']) || $instance['template'] == "") {
            $instance['template'] = 'default';
        }

        if (!isset($instance['cache_time']) || $instance['cache_time'] == "") {
            $instance['cache_time'] = 60;
        }

        $query_args['post__in'] = $instance['articles'];
        $query_args['post_type'] = 'post'; 
        $query_args['orderby'] = 'post__in';

        $posts = Strawberry::cache($instance['cache_time'])->posts($query_args);

        $params = array(
            'posts' => $posts,
            'instance' => $instance
        );

        $output = $args['before_widget'];

        if (!empty($title)) {
            $output .= $args['before_title'] . $title . $args['after_title'];
        }

        $output .= $this->fetch_template($instance['template'], $params);

        echo $output .= $args['after_widget'];
        
        if(defined('WP_DEBUG') && WP_DEBUG === true){
            $time = explode(' ', microtime());
            $finish  = $time[1] + $time[0];
            $total_time = round(($finish - $start), 4);
            echo '<div class="alert alert-info">Widget generated in '.$total_time.' seconds.</div>';
        }
    }

    function form($instance) {
        // Check values
        if ($instance) {
            $manual_post_ids = $instance['articles']; // Added
        } else {
            $manual_post_ids = array(); // Added
        }
        ?>
        <p>
            <label for="<?php echo $this->get_field_id("title"); ?>">
                <?php _e('Title', 'strawberry'); ?>:
                <input class="widefat" id="<?php echo $this->get_field_id("title"); ?>" name="<?php echo $this->get_field_name("title"); ?>" type="text" value="<?php echo esc_attr(isset($instance['title']) ? $instance['title'] : ""); ?>" />
            </label>
        </p>
        <p>
            <label for="search_posts_input">Search Posts</label>
            <input type="text" id="search_posts_input" name="post_var"/>
            <span class=" search_posts button button-primary right">Search</span>
        <div class="search-posts-list" data-widget-number="<?php echo $this->number ?>" data-widget-id="<?php echo $this->id_base ?>"></div>
        </p>
        <p>
        <ul class="search-posts-list-selected">
            <?php
            if (!empty($manual_post_ids)) {
                $query_args = array('post_type' => 'any', 'post__in' => $manual_post_ids, 'showposts' => -1, 'orderby' => 'post__in');

                $articles = Strawberry::cache(1)->posts($query_args);
                if (count($articles) > 0) {
                    foreach ($articles as $article) {
                        ?>
                        <li id="post_<?php echo $article['ID']; ?>">
                            <span class="widget-post-title"><?php echo $article['title']; ?></span>
                            <input type="hidden" name="<?php echo $this->get_field_name('articles'); ?>[]" value="<?php echo $article['ID']; ?>" />
                            <span class="delete-post button button-default right">delete</span>
                        </li>
                        <?php
                    }
                }
            }
            ?>
        </ul>			
        </p>

        <!-- TEMPLATE -->
        <p>
            <label>
                <?php _e('Select a template', 'strawberry'); ?>:<br />
                <?php
                $files = array_diff(scandir($this->template_dir), array('..', '.'));
                ?> 
                <select class="widefat" id="<?php echo $this->get_field_id("template"); ?>"  name='<?php echo $this->get_field_name("template"); ?>'>
                    <?php
                    foreach ($files as $file) {
                        $simple_file = basename($file, '.php');
                        $file_name = ucwords(str_replace("_", " ", $simple_file));
                        ?>
                        <option <?php selected(isset($instance['template']) ? $instance['template'] : "", $simple_file); ?> value="<?php echo $simple_file; ?>"><?php echo $file_name; ?></option>
                    <?php } ?>
                </select>
            </label>
        </p>

        <?php if (function_exists('the_post_thumbnail') && current_theme_supports("post-thumbnails")) : ?>
            <p>
                <label for="<?php echo $this->get_field_id("thumb"); ?>">
                    <input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id("thumb"); ?>" name="<?php echo $this->get_field_name("thumb"); ?>"<?php checked(isset($instance['thumb']) ? (bool) $instance['thumb'] : false, true); ?> />
                    <?php _e('Show post thumbnail', 'strawberry'); ?>
                </label>
            </p>
            <p>
                <label>
                    <?php _e('Thumbnail', 'strawberry'); ?>:<br />
                    <?php $image_sizes = get_intermediate_image_sizes(); ?>
                    <select class="widefat" id='<?php echo $this->get_field_id("thumbnail"); ?>' name="<?php echo $this->get_field_name("thumbnail"); ?>">
                        <?php foreach ($image_sizes as $size_name): ?>
                            <option <?php selected(isset($instance['thumbnail']) ? $instance['thumbnail'] : "", $size_name); ?> value="<?php echo $size_name ?>"><?php echo $size_name ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
            </p>

            <p>
                <label for="<?php echo $this->get_field_id("cache_time"); ?>">
                    <?php _e('Cache time (seconds)', 'strawberry'); ?>:<br />
                    <input class="widefat" type="text" id="<?php echo $this->get_field_id("cache_time"); ?>" name="<?php echo $this->get_field_name("cache_time"); ?>" value="<?php echo isset($instance['cache_time']) ? $instance['cache_time'] : ""; ?>" />
                </label>
            </p>
        <?php endif; ?>

        <?php
    }

    private function fetch_template($template_name, $params = null) {

        if (!empty($params) && is_array($params)) {
            extract($params);
        }

        $template = $this->template_dir . $template_name . ".php";

        if (file_exists($template)) {
            ob_start();
            include($template);
            $contents = ob_get_contents();
            ob_end_clean();
        } else {
            $contents = 'template not found';
        }

        return $contents;
    }

}

add_action('widgets_init', function() {
    register_widget('Strawberry_manual_posts_widget');
});

if (is_admin()) {

    function load_scripts() {
        wp_enqueue_style('blank_plugin_template', get_template_directory_uri() . '/libs/strawberry/posts-widget/resources/style.css');
        wp_enqueue_script('ajax-test', get_template_directory_uri() . '/libs/strawberry/posts-widget/resources/ajax.js', array('jquery'), 'v1.0');
        wp_localize_script('ajax-test', 'the_ajax_script', array('ajaxurl' => admin_url('admin-ajax.php')));
    }

    add_action('wp_print_scripts', 'load_scripts');

    add_action('wp_ajax_test_response', 'ajax_process_request');

    function ajax_process_request() {

        if (isset($_POST['post_var'])) {
            $args = array(
                's' => $_POST['post_var'],
                'posts_per_page' => 5,
            );
            $articles = Strawberry::cache(1)->posts($args);
            if (count($articles) > 0) {
                foreach ($articles as $article) {
                    $search_query[] = array(
                        'post_id' => $article['ID'],
                        'title' => $article['title']
                    );
                }
            } else {
                $search_query = 'no post has found';
            }

            echo json_encode($search_query);
            die();
        }
    }

}
?>