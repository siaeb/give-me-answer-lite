<?php


class GMA_Top_Questioners extends WP_Widget {
    /**
     * Constructor
     *
     * @return void
     **/
    function __construct() {
        $widget_ops = array( 'classname' => 'gma-widget gma-top-questioners', 'description' => __( 'Show the list of top questioners.', 'give-me-answer-lite' ) );
        parent::__construct( 'gma-top-questioners', __( 'Give Me Answer : Top Questioners', 'give-me-answer-lite' ), $widget_ops );
    }

    function widget( $args, $instance ) {
        extract( $args, EXTR_SKIP );
        $instance = wp_parse_args( $instance, array(
            'title'  => __( 'Top Questioners', 'give-me-answer-lite' ),
            'number' => 6,
        ) );

        echo $before_widget;
        echo $before_title;
        echo $instance['title'];
        echo $after_title;

        $data = $this->get_data_from_database($instance['number']);
        if ( $data ) {
            echo '<div class="gma-widget gma-top-questioners gma">';
            echo '<ul class="list-unstyled">';
            foreach ($data as $item) {
                echo sprintf('<li><a href="%s" class="d-flex justify-content-between align-items-center"><div><img class="mr-1" src="%s" width="16px" height="16px"><span>%s</span></div><span>%s</span></a></li>', gma_get_user_questions_url($item->user_id), gma_get_user_image($item->user_id), $item->user_login, number_format($item->qcount));
            }
            echo '</ul>';
            echo '</div>';
        }
        echo $after_widget;
    }

    function update( $new_instance, $old_instance ) {
        // update logic goes here
        $updated_instance = $new_instance;
        return $updated_instance;
    }

    function form( $instance ) {
        $instance = wp_parse_args( $instance, array(
            'title'     => __('Top Questioners', 'give-me-answer-lite'),
            'number'    => 6,
        ) );
        ?>
        <p><label for="<?php echo $this->get_field_id( 'title' ) ?>"><?php _e( 'Widget title', 'give-me-answer-lite' ) ?></label>
            <input type="text" name="<?php echo $this->get_field_name( 'title' ) ?>" id="<?php echo $this->get_field_id( 'title' ) ?>" value="<?php echo $instance['title'] ?>" class="widefat">
        </p>
        <p><label for="<?php echo $this->get_field_id( 'number' ) ?>"><?php _e( 'Number of users', 'give-me-answer-lite' ) ?></label>
            <input type="text" name="<?php echo $this->get_field_name( 'number' ) ?>" id="<?php echo $this->get_field_id( 'number' ) ?>" value="<?php echo intval( $instance['number'] ); ?>" class="widefat">
        </p>
        <?php
    }


    private function get_data_from_database($limit = 10) {
        global $wpdb;
        $query = "
            SELECT u.ID `user_id`, u.user_login, COUNT(p.ID) `qcount` FROM {$wpdb->users} u
                INNER JOIN {$wpdb->posts} p ON p.post_author = u.ID AND p.post_type = 'gma-question' AND p.post_status = 'publish'
                GROUP BY p.post_author
                ORDER BY qcount DESC                        
                LIMIT %d
        ";
        return $wpdb->get_results($wpdb->prepare($query, $limit));
    }
}