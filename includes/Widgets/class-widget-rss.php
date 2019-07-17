<?php


class GMA_Widget_RSS extends WP_Widget {

    public function __construct() {
        $widget_options = [
            'classname' => __('GMA_Widget_RSS', 'give-me-answer-lite'),
            'description' => __('RSS Widget', 'give-me-answer-lite'),
        ];
        parent::__construct( 'GMA_Widget_RSS', __('Give Me Answer : RSS', 'give-me-answer-lite'), $widget_options, [] );
    }


    public function widget( $args, $instance ) {
        extract( $args, EXTR_SKIP );
        $instance = wp_parse_args( $instance, [
            'title'  => __('Recent questions and answers', 'give-me-answer-lite'),
        ] );
        echo $before_widget;
        ?>
        <div class="gma">
            <i class="fa fa-rss-square"></i>
            <a href="<?php echo get_feed_link('give-me-answer-lite'); ?>"><?php echo $instance['title']; ?></a>
        </div>

        <?php
        echo $after_widget;
    }

    function update( $new_instance, $old_instance ) {

        // update logic goes here
        $updated_instance = $new_instance;
        return $updated_instance;
    }

    function form( $instance ) {
        $instance = wp_parse_args( $instance, array(
            'title'	    => __('Recent questions and answers', 'give-me-answer-lite'),
        ) );
        ?>
        <p><label for="<?php echo $this->get_field_id( 'title' ) ?>"><?php _e( 'Widget title', 'give-me-answer-lite' ) ?></label>
            <input type="text" name="<?php echo $this->get_field_name( 'title' ) ?>" id="<?php echo $this->get_field_id( 'title' ) ?>" value="<?php echo sanitize_text_field( $instance['title'] ); ?>" class="widefat">
        </p>
        <?php
    }

}