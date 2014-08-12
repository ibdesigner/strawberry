<?php
if (!class_exists('Strawberry')) {
    exit('Strawberry class not included');
}

class Strawberry_posts_widget extends WP_Widget {

    private $template_dir;

    public function __construct() {

        $this->template_dir = dirname(__FILE__) . "/templates/";

        parent::__construct(
                'strawberry_posts_wigdet', // Base ID
                __('Taxonomy Posts Widget', 'text_domain'), // Name
                array('description' => __('Listing posts from selected Taxonomies', 'text_domain'),) // Args
        );
    }

    public function widget($args, $instance) {
        $title = apply_filters('widget_title', $instance['title']);

        $valid_sort_orders = array('date', 'title', 'comment_count', 'rand');
        if (in_array($instance['sort_by'], $valid_sort_orders)) {
            $sort_by = $instance['sort_by'];
            $sort_order = (bool) $instance['asc_sort_order'] ? 'ASC' : 'DESC';
        } else {
            $sort_by = 'date';
            $sort_order = 'DESC';
        }

        if (!isset($instance['template']) || $instance['template'] == "") {
            $instance['template'] = 'default';
        }

        $query_args['posts_per_page'] = $instance["num"];
        if (isset($instance["cat"]) && $instance["cat"] > 0) {
            $query_args['cat'] = $instance["cat"];
        }

        if (isset($instance["excerpt_length"]) && $instance["excerpt_length"] > 0) {
            $query_args['excerpt_length'] = $instance["excerpt_length"];
        }

        $query_args['order'] = $sort_order;
        $query_args['order_by'] = $sort_by;
        $query_args['post_type'] = $instance["post_type"];

        $posts = Strawberry::cache(1)->posts($query_args);
        echo "<pre>";
        print_r($instance);
        echo "</pre>";
        $params = array(
            'posts' => $posts,
            'instance' => $instance
        );

        echo $args['before_widget'];
        if (!empty($title)) {
            echo $args['before_title'] . $title . $args['after_title'];
        }

        echo $this->fetch_template($instance['template'], $params);

        echo $args['after_widget'];
    }

    function form($instance) {
        ?>
        <p>
            <label for="<?php echo $this->get_field_id("title"); ?>">
        <?php _e('Title'); ?>:
                <input class="widefat" id="<?php echo $this->get_field_id("title"); ?>" name="<?php echo $this->get_field_name("title"); ?>" type="text" value="<?php echo esc_attr($instance["title"]); ?>" />
            </label>
        </p>
        <p>
            <label>
        <?php _e('Category'); ?>:
                <?php wp_dropdown_categories(array('show_option_all' => 'Toate categoriile', 'name' => $this->get_field_name("cat"), 'selected' => $instance["cat"])); ?>
            </label>
        </p>
        <p>
            <label>
        <?php _e('Post type'); ?>:
                <?php $post_types = get_post_types(array('public' => true), 'names'); ?> 
                <select id="<?php echo $this->get_field_id("post_type"); ?>"  name='<?php echo $this->get_field_name("post_type"); ?>'>
                <?php foreach ($post_types as $post_type) { ?>
                        <option <?php selected($instance["post_type"], $post_type); ?> value="<?php echo $post_type; ?>"><?php echo $post_type; ?></option>
                    <?php } ?>
                </select>
            </label>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id("num"); ?>">
        <?php _e('Number of posts to show'); ?>:
                <input style="text-align: center;" id="<?php echo $this->get_field_id("num"); ?>" name="<?php echo $this->get_field_name("num"); ?>" type="text" value="<?php echo absint($instance["num"]); ?>" size='3' />
            </label>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id("sort_by"); ?>">
        <?php _e('Sort by'); ?>:
                <select id="<?php echo $this->get_field_id("sort_by"); ?>" name="<?php echo $this->get_field_name("sort_by"); ?>">
                    <option value="date"<?php selected($instance["sort_by"], "date"); ?>>Date</option>
                    <option value="title"<?php selected($instance["sort_by"], "title"); ?>>Title</option>
                    <option value="comment_count"<?php selected($instance["sort_by"], "comment_count"); ?>>Number of comments</option>
                    <option value="rand"<?php selected($instance["sort_by"], "rand"); ?>>Random</option>
                </select>
            </label>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id("asc_sort_order"); ?>">
                <input type="checkbox" class="checkbox" 
                       id="<?php echo $this->get_field_id("asc_sort_order"); ?>" 
                       name="<?php echo $this->get_field_name("asc_sort_order"); ?>"
        <?php checked((bool) $instance["asc_sort_order"], true); ?> />
                       <?php _e('Reverse sort order (ascending)'); ?>
            </label>
        </p>

        <p>
            <label for="<?php echo $this->get_field_id("excerpt"); ?>">
                <input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id("excerpt"); ?>" name="<?php echo $this->get_field_name("excerpt"); ?>"<?php checked((bool) $instance["excerpt"], true); ?> />
        <?php _e('Show post excerpt'); ?>
            </label>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id("excerpt_length"); ?>">
        <?php _e('Excerpt length (in letters):'); ?>
            </label>
            <input style="text-align: center;" type="text" id="<?php echo $this->get_field_id("excerpt_length"); ?>" name="<?php echo $this->get_field_name("excerpt_length"); ?>" value="<?php echo $instance["excerpt_length"]; ?>" size="3" />
        </p>        

        <p>
            <label for="<?php echo $this->get_field_id("date"); ?>">
                <input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id("date"); ?>" name="<?php echo $this->get_field_name("date"); ?>"<?php checked((bool) $instance["date"], true); ?> />
        <?php _e('Show post date'); ?>
            </label>
        </p>

        <!-- TEMPLATE -->
        <p>
            <label>
        <?php _e('Select a template'); ?>:<br />
                <?php
                $files = array_diff(scandir($this->template_dir), array('..', '.'));
                ?> 
                <select id="<?php echo $this->get_field_id("template"); ?>"  name='<?php echo $this->get_field_name("template"); ?>'>
                <?php
                foreach ($files as $file) {
                    $simple_file = basename($file, '.php');
                    $file_name = ucwords(str_replace("_", " ", $simple_file));
                    ?>
                        <option <?php selected($instance["template"], $simple_file); ?> value="<?php echo $simple_file; ?>"><?php echo $file_name; ?></option>
                    <?php } ?>
                </select>
            </label>
        </p>

        <?php if (function_exists('the_post_thumbnail') && current_theme_supports("post-thumbnails")) : ?>
            <p>
                <label for="<?php echo $this->get_field_id("thumb"); ?>">
                    <input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id("thumb"); ?>" name="<?php echo $this->get_field_name("thumb"); ?>"<?php checked((bool) $instance["thumb"], true); ?> />
            <?php _e('Show post thumbnail'); ?>
                </label>
            </p>
            <p>
                <label>
            <?php _e('Thumbnail'); ?>:<br />
                    <?php $image_sizes = get_intermediate_image_sizes(); ?>
                    <select id='<?php echo $this->get_field_id("thumbnail"); ?>' name="<?php echo $this->get_field_name("thumbnail"); ?>">
                    <?php foreach ($image_sizes as $size_name): ?>
                            <option <?php selected($instance["thumbnail"], $size_name); ?> value="<?php echo $size_name ?>"><?php echo $size_name ?></option>
                        <?php endforeach; ?>
                    </select>"
                </label>
            </p>
            <p>
                <label>
            <?php _e('Container ID'); ?>:<br />
                    <label for="<?php echo $this->get_field_id("container_id"); ?>">
                        ID: <input class="widefat" style="width:40%;" type="text" id="<?php echo $this->get_field_id("container_id"); ?>" name="<?php echo $this->get_field_name("container_id"); ?>" value="<?php echo $instance["container_id"]; ?>" />
                    </label>
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