<?php

// Creating the widget 
class strawberry_cta_widget extends WP_Widget {

    function __construct() {
        parent::__construct(
                'strawberry_cta_widget', __('Call to action', 'strawberry'), array(
            'description' => __('Add an Call to Action', 'strawberry'),
            'classname' => 'cta',
                )
        );

        add_action('admin_enqueue_scripts', array($this, 'upload_scripts'));
    }

    public function widget($args, $instance) {
        $title = apply_filters('widget_title', $instance['title']);

        echo $args['before_widget'];
        if (!empty($title))
            echo $args['before_title'] . $title . $args['after_title'];
        ?>

        <?php if (!empty($instance['cta_image'])): ?>
            <div class="cta_image"><img src="<?php echo $instance['cta_image']; ?>" alt="<?php echo $title; ?>" class="img-responsive" /></div>
        <?php endif; ?>

        <div class="cta_excerpt"><?php echo $instance['cta_text']; ?></div>
        <?php if (!empty($instance['cta_button_text'])): ?>
            <div class="cta_action"><a href="<?php echo $instance['cta_button_link']; ?>"><?php echo $instance['cta_button_text']; ?></a></div>
        <?php endif; ?>
        <?php
        echo $args['after_widget'];
    }

    public function form($instance) {
        if (isset($instance['title'])) {
            $title = $instance['title'];
        } else {
            $title = __('New title', 'strawberry');
        }
        ?>
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title'); ?></label> 
            <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" />
        </p>

        <p>
            <label for="<?php echo $this->get_field_id('cta_text'); ?>"><?php _e('Text:'); ?></label> 
            <textarea class="widefat" id="<?php echo $this->get_field_id('cta_text'); ?>" name="<?php echo $this->get_field_name('cta_text'); ?>" cols="10" rows="10"><?php echo esc_attr($instance['cta_text']); ?></textarea>
        </p>

        <p>
            <label for="<?php echo $this->get_field_id('cta_button_text'); ?>"><?php _e('Button Text'); ?></label> 
            <input class="widefat" id="<?php echo $this->get_field_id('cta_button_text'); ?>" name="<?php echo $this->get_field_name('cta_button_text'); ?>" type="text" value="<?php echo esc_attr($instance['cta_button_text']); ?>" />
        </p>

        <p>
            <label for="<?php echo $this->get_field_id('cta_button_link'); ?>"><?php _e('Button Link'); ?></label> 
            <input class="widefat" id="<?php echo $this->get_field_id('cta_button_link'); ?>" name="<?php echo $this->get_field_name('cta_button_link'); ?>" type="text" value="<?php echo esc_attr($instance['cta_button_link']); ?>" />
        </p>

        <p>
            <label for="<?php echo $this->get_field_name( 'cta_image' ); ?>"><?php _e( 'Image:' ); ?></label>
            <input name="<?php echo $this->get_field_name( 'cta_image' ); ?>" id="<?php echo $this->get_field_id( 'cta_image' ); ?>" class="widefat" type="text" size="36"  value="<?php echo esc_attr($instance['cta_image']); ?>" />
            <input class="upload_image_button" type="button" value="Upload Image" />
        </p>
        <?php
    }

    public function upload_scripts() {
        wp_enqueue_script('media-upload');
        wp_enqueue_script('thickbox');

        wp_enqueue_script('upload_media_widget', get_template_directory_uri() . '/libs/strawberry/cta-widget/' . 'cta.js', array('jquery'));
        wp_enqueue_style('thickbox');
    }

}

function wpb_load_widget() {
    register_widget('strawberry_cta_widget');
}

add_action('widgets_init', 'wpb_load_widget');
?>