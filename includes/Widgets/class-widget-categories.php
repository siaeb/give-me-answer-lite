<?php


class GMA_Categories extends \WP_Widget {

	public function __construct() {
		$widget_options = [
			'classname' => __('GMA_Widget_Category', 'give-me-answer-lite'),
			'description' => __('Question categories', 'give-me-answer-lite'),
		];
		parent::__construct( 'GMA_Widget_Category', __('Give Me Answer : Categories', 'give-me-answer-lite'), $widget_options, [] );
	}


	public function widget( $args, $instance ) {
		$categories = get_terms( 'gma-question_category', ['hide_empty' => false, 'orderby' => 'count', 'order' => 'DESC'] );
		extract( $args, EXTR_SKIP );
		$instance = wp_parse_args( $instance, [
			'title'  => __('Category', 'give-me-answer-lite'),
		] );

		echo $before_widget;
		echo $before_title;
        echo $instance['title'];
		echo $after_title;
		?>
        <div class="gma">
            <?php foreach ( $categories as $item ) {  ?>
                <a href="<?php echo get_term_link( $item ); ?>" class="d-flex align-items-center mb-2 justify-content-between">
                    <div><?php echo $item->name; ?></div>
                    <div><?php echo number_format( $item->count); ?></div>
                </a>
            <?php } ?>
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
            'title'	    => __('Categories', 'give-me-answer-lite'),
        ) );
        ?>
        <p><label for="<?php echo $this->get_field_id( 'title' ) ?>"><?php _e( 'Widget title', 'give-me-answer-lite' ) ?></label>
            <input type="text" name="<?php echo $this->get_field_name( 'title' ) ?>" id="<?php echo $this->get_field_id( 'title' ) ?>" value="<?php echo sanitize_text_field( $instance['title'] ); ?>" class="widefat">
        </p>
        <?php
    }

}