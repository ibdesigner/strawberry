<?php
if (!class_exists('Strawberry')) {
    exit('Strawberry class not included');
}

class Strawberry_posts_widget extends WP_Widget {

    private $template_dir;

    public function __construct() {

        $this->template_dir = dirname(__FILE__) . "/templates/";

        parent::__construct(
                'strawberry_posts_wigdet', __('Taxonomy Posts Widget', 'strawberry'), array('description' => __('Listing posts from selected Taxonomies', 'text_domain'),)
        );
    }

    public function widget($args, $instance) {
        if (defined('WP_DEBUG') && WP_DEBUG === true) {
            $time = explode(' ', microtime());
            $start = $time[1] + $time[0];
        }

        $widget_key = "taxonomy-widget-" . $this->id;

        if (!isset($instance['cache_time']) || $instance['cache_time'] == "") {
            $instance['cache_time'] = 60;
        }

        $title = apply_filters('widget_title', $instance['title']);

        $valid_sort_orders = array('date', 'title', 'comment_count', 'rand');
        if (in_array($instance['sort_by'], $valid_sort_orders)) {
            $sort_by = $instance['sort_by'];
            $sort_order = (bool) isset($instance['asc_sort_order']) ? 'ASC' : 'DESC';
        } else {
            $sort_by = 'date';
            $sort_order = 'DESC';
        }

        if (!isset($instance['template']) || $instance['template'] == "") {
            $instance['template'] = 'default';
        }

        $query_args['posts_per_page'] = (int) $instance["num"];
        if (isset($instance["cat"]) && $instance["cat"] > 0) {
            $query_args['cat'] = (int) $instance["cat"];
        }

        if (isset($instance["offset"]) && $instance["offset"] > 0) {
            $query_args['offset'] = (int) $instance["offset"];
        }

        if (isset($instance["excerpt_length"]) && $instance["excerpt_length"] > 0) {
            $query_args['excerpt_length'] = (int) $instance["excerpt_length"];
        }

        $query_args['order'] = $sort_order;
        $query_args['order_by'] = $sort_by;
        $query_args['post_type'] = $instance["post_type"];
        $query_args['taxonomy'] = true;

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

        $output .= $args['after_widget'];

        echo $output;



        if (defined('WP_DEBUG') && WP_DEBUG === true) {
            $time = explode(' ', microtime());
            $finish = $time[1] + $time[0];
            $total_time = round(($finish - $start), 4);
            echo '<div class="alert alert-info">Widget generated in ' . $total_time . ' seconds.</div>';
        }
    }

    function form($instance) {

        if ($this->updated === true) {
            delete_transient("widget-" . $this->id);
        }
        ?>
        <p>
            <label for="<?php echo $this->get_field_id("title"); ?>">
                <?php _e('Title', 'strawberry'); ?>:
                <input class="widefat" id="<?php echo $this->get_field_id("title"); ?>" name="<?php echo $this->get_field_name("title"); ?>" type="text" value="<?php echo esc_attr(isset($instance['title']) ? $instance['title'] : ""); ?>" />
            </label>
        </p>
        <p>
            <label>
                <?php _e('Category', 'strawberry'); ?>:
                <?php wp_dropdown_categories(array('show_option_all' => 'Toate categoriile', 'class' => "widefat", 'name' => $this->get_field_name("cat"), 'selected' => isset($instance['cat']) ? $instance['cat'] : "")); ?>
            </label>
        </p>
        <p>
            <label>
                <?php _e('Post type', 'strawberry'); ?>:
                <?php $post_types = get_post_types(array('public' => true), 'names'); ?> 
                <select class="widefat" id="<?php echo $this->get_field_id("post_type"); ?>"  name='<?php echo $this->get_field_name("post_type"); ?>'>
                    <?php foreach ($post_types as $post_type) { ?>
                        <option <?php selected(isset($instance['post_type']) ? $instance['post_type'] : "", $post_type); ?> value="<?php echo $post_type; ?>"><?php echo $post_type; ?></option>
                    <?php } ?>
                </select>
            </label>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id("num"); ?>">
                <?php _e('Number of posts to show', 'strawberry'); ?>:
                <input  class="widefat" id="<?php echo $this->get_field_id("num"); ?>" name="<?php echo $this->get_field_name("num"); ?>" type="text" value="<?php echo absint(isset($instance['num']) ? $instance['num'] : ""); ?>" size='3' />
            </label>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id("offset"); ?>">
                <?php _e('Step over x posts', 'strawberry'); ?>:
                <input  class="widefat" id="<?php echo $this->get_field_id("offset"); ?>" name="<?php echo $this->get_field_name("offset"); ?>" type="text" value="<?php echo absint(isset($instance['offset']) ? $instance['offset'] : ""); ?>" size='3' />
            </label>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id("sort_by"); ?>">
                <?php _e('Sort by', 'strawberry'); ?>:
                <?php $sort_by = isset($instance['sort_by']) ? $instance['sort_by'] : "" ?>
                <select class="widefat" id="<?php echo $this->get_field_id("sort_by"); ?>" name="<?php echo $this->get_field_name("sort_by"); ?>">
                    <option value="date"<?php selected($sort_by, "date"); ?>>Date</option>
                    <option value="title"<?php selected($sort_by, "title"); ?>>Title</option>
                    <option value="comment_count"<?php selected($sort_by, "comment_count"); ?>>Number of comments</option>
                    <option value="rand"<?php selected($sort_by, "rand"); ?>>Random</option>
                </select>
            </label>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id("asc_sort_order"); ?>">
                <input type="checkbox" class="checkbox" 
                       id="<?php echo $this->get_field_id("asc_sort_order"); ?>" 
                       name="<?php echo $this->get_field_name("asc_sort_order"); ?>"
                       <?php checked(isset($instance['asc_sort_order']) ? (bool) $instance['asc_sort_order'] : false, true); ?> />
                       <?php _e('Reverse sort order (ascending)', 'strawberry'); ?>
            </label>
        </p>

        <p>
            <label for="<?php echo $this->get_field_id("excerpt"); ?>">
                <input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id("excerpt"); ?>" name="<?php echo $this->get_field_name("excerpt"); ?>"<?php checked(isset($instance['excerpt']) ? (bool) $instance['excerpt'] : false, true); ?> />
                <?php _e('Show post excerpt', 'strawberry'); ?>
            </label>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id("excerpt_length"); ?>">
                <?php _e('Excerpt length (in letters):', 'strawberry'); ?>
            </label>
            <input class="widefat" type="text" id="<?php echo $this->get_field_id("excerpt_length"); ?>" name="<?php echo $this->get_field_name("excerpt_length"); ?>" value="<?php echo isset($instance['excerpt_length']) ? $instance['excerpt_length'] : ""; ?>" size="3" />
        </p>        

        <p>
            <label for="<?php echo $this->get_field_id("date"); ?>">
                <input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id("date"); ?>" name="<?php echo $this->get_field_name("date"); ?>"<?php checked(isset($instance['date']) ? (bool) $instance['date'] : false, true); ?> />
                <?php _e('Show post date', 'strawberry'); ?>
            </label>
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
    register_widget('Strawberry_posts_widget');
});
?>