<?php
if (!class_exists('Strawberry')) {
    exit('Strawberry class not included');
}

if (!class_exists('StrawberryCache')) {
    exit('StrawberryCache class not included');
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

        $widget_key = "widget-" . $this->id;

        if (!isset($instance['cache_time']) || $instance['cache_time'] == "") {
            $instance['cache_time'] = 60;
        }

        $title = apply_filters('widget_title', $instance['title']);


        if (!isset($instance['template']) || $instance['template'] == "") {
            $instance['template'] = 'default';
        }

        $query_args['post__in'] = $instance['articles'];
        $query_args['post_type'] = 'any';
        $query_args['orderby'] = 'post__in';

        $posts = Strawberry::cache(2)->posts($query_args);

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
            <label for="search_posts_input">Search Posts</label>
            <input type="text" id="search_posts_input" name="post_var"/>
            <span class=" search_posts button button-primary right">Search</span>
        <div class="search-posts-list" data-widget-number="<?php echo $this->number ?>" data-widget-id="<?php echo $this->id_base ?>"></div>
        </p>
        <p>
        <ul class="search-posts-list-selected">
            <?php
            global $post;
            $myarray = query_posts(array('post_type' => 'any', 'post__in' => $manual_post_ids, 'showposts' => -1, 'orderby' => 'post__in'));

            if (have_posts()) {
                while (have_posts()) {
                    the_post();
                    ?>
                    <li id="post_<?php echo $post->ID; ?>">
                        <span class="widget-post-title"><?php the_title(); ?></span>
                        <input type="hidden" name="<?php echo $this->get_field_name('articles'); ?>[]" value="<?php echo $post->ID; ?>" />
                        <span class="delete-post button button-default right">delete</span>
                    </li>
                    <?php
                }
            }
            wp_reset_postdata();
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

function load_scripts() {
    // load our jquery file that sends the $.post request

    wp_enqueue_style('blank_plugin_template', get_template_directory_uri() . '/libs/strawberry/posts-widget/resources/style.css');
    wp_enqueue_script('ajax-test', get_template_directory_uri() . '/libs/strawberry/posts-widget/resources/ajax.js', array('jquery'), 'v1.0');

    // make the ajaxurl var available to the above script
    wp_localize_script('ajax-test', 'the_ajax_script', array('ajaxurl' => admin_url('admin-ajax.php')));
}

add_action('wp_print_scripts', 'load_scripts');

add_action('wp_ajax_test_response', 'ajax_process_request');

function ajax_process_request() {
    // first check if data is being sent and that it is the data we want
    global $post;


    if (isset($_POST['post_var'])) {
        $args = array(
            's' => $_POST['post_var'],
            'posts_per_page' => 5,
        );
        $the_query = new WP_Query($args);
        if ($the_query->have_posts()) {
            while ($the_query->have_posts()) {
                $the_query->the_post();
                $search_query[] = array(
                    'post_id' => $post->ID,
                    'title' => get_the_title()
                );
            }
        } else {
            $search_query = 'no post has found';
        }
        /* Restore original Post Data */
        wp_reset_postdata();

        echo json_encode($search_query);
        die();
    }
}
?>