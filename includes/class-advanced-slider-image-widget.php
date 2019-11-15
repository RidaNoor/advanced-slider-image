<?php

/**
 * Class AdvancedSlider_Image_Widget
 */
class AdvancedSlider_Image_Widget extends WP_Widget
{
    public function __construct() {
        parent::__construct(
            'AdvancedSlider_Image_widget',
            'advanced-IT Slider',
            array( 'description' => __( 'advanced-IT Slider', 'advanced-slider-image' ), )
        );
    }

    public function widget( $args, $instance ) {

        extract($args);

        if (isset($instance['slider_id'])) {
            $slider_id = $instance['slider_id'];

            $title = apply_filters( 'widget_title', $instance['title'] );
            /**
             * @var $before_widget
             * @var $after_title
             * @var $before_title
             * @var $after_widget
             */
            echo $before_widget;
            if ( ! empty( $title ) )
                echo $before_title . $title . $after_title;

            echo do_shortcode("[advanced_it_slider id={$slider_id}]");
            echo $after_widget;
        }
    }

    public function update( $new_instance, $old_instance ) {
        $instance = array();
        $instance['slider_id'] = strip_tags( $new_instance['slider_id'] );
        $instance['title'] = strip_tags( $new_instance['title'] );

        return $instance;
    }

    public function form( $instance ) {
        $title = "";

        if (isset($instance['title'])) {
            $title = $instance['title'];
        }
        ?>
        <p>
        <p>
            <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
        </p>
        <label for="<?php echo $this->get_field_id('slider_id'); ?>"><?php _e('Select Slider:', 'advanced_it_slider'); ?></label>
        <select id="<?php echo $this->get_field_id('slider_id'); ?>" name="<?php echo $this->get_field_name('slider_id'); ?>">
            <option value="">&#8211;<?php _e('Select','advanced-slider-image'); ?>&#8211;</option>
            <?php
            global $wpdb;
            $query = "SELECT id FROM " . AdvancedSlider_Image()->get_slider_table_name();
            $sliders = $wpdb->get_results( $query );
            foreach($sliders as $slider_example) :
                $slider = new AdvancedSlider_Image_Slider($slider_example->id);
                ?>
                <option <?php if($slider->get_id() == $instance['slider_id']){ echo 'selected'; } ?> value="<?php echo $slider->get_id(); ?>"><?php echo $slider->get_name(); ?></option>
            <?php endforeach; ?>
        </select>
        </p>

        <?php
    }
}