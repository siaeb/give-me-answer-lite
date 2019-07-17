<?php


class GMA_Ask_Question extends WP_Widget {
    /**
     * Constructor
     *
     * @return void
     **/
    function __construct() {
        $widget_ops = array( 'classname' => 'gma-widget gma-widget-ask-question', 'description' => __( 'Display ask question button.', 'give-me-answer-lite' ) );
        parent::__construct( 'gma-widget-ask-question', _x( 'Give Me Answer : Ask Question', 'widget', 'give-me-answer-lite' ), $widget_ops );
    }

    function widget( $args, $instance ) {
        global $gma_general_settings;
        extract( $args, EXTR_SKIP );
        $instance = wp_parse_args( $instance, array(
            'title' => __( 'Ask Question', 'give-me-answer-lite' ),
        ) );

        echo '<div class="gma">';
            echo sprintf('<a href="%s" class="btn btn-outline-primary btn-block mb-3">%s</a>', get_permalink($gma_general_settings['pages']['submit-question']), $instance['title']);
        echo '</div>';
    }

    function update( $new_instance, $old_instance ) {
        // update logic goes here
        $updated_instance = $new_instance;
        return $updated_instance;
    }

    function form( $instance ) {
        $instance = wp_parse_args( $instance, array(
            'title' => '',
            'number' => 5,
        ) );
        ?>
        <p><label for="<?php echo $this->get_field_id( 'title' ) ?>"><?php _e( 'Button Title', 'give-me-answer-lite' ) ?></label>
            <input type="text" name="<?php echo $this->get_field_name( 'title' ) ?>" id="<?php echo $this->get_field_id( 'title' ) ?>" value="<?php echo $instance['title'] ?>" class="widefat">
        </p>
        <?php
    }
}