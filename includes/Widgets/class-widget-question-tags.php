<?php

defined( 'ABSPATH' ) || exit;


	class GMA_Question_Tags extends WP_Widget {
		public function __construct() {
			$widget_options = [
				'classname'     => __('GMA_Widget_Category', 'give-me-answer-lite'),
				'description'   => __('Question tags', 'give-me-answer-lite'),
			];
			parent::__construct( 'GMA_Widget_Question_Tags', __('Give Me Answer : Question Tags', 'give-me-answer-lite'), $widget_options, [] );
		}

		public function widget( $args, $instance ) {
			extract( $args, EXTR_SKIP );
			$instance = wp_parse_args( $instance, array(
				'title' => __( 'Tags', 'give-me-answer-lite' ),
				'number' => 5,
			) );
			$tags = get_terms( 'gma-question_tag', [
                        'hide_empty' => true,
                        'orderby' => 'count',
                        'order' => 'DESC',
                        'number' => $instance['number']
                     ]
            );
			?>
            <div class="gma">

                <?php
                echo $before_widget;
                echo $before_title;
                echo $instance['title'];
                echo $after_title;
                ?>

                <?php foreach ( $tags as $item ) {  ?>
                <div class="my-1">
                    <a href="<?php echo get_term_link( $item ); ?>" class="mb-2">
                        <span class="gma-post-tag"><?php echo $item->name; ?></span>
                        <span class="item-multiplier  text-black-50">
                            <span class="item-multiplier-x">×</span>
                            &nbsp;
                            <span class="item-multiplier-count"><?php echo $item->count; ?></span>
                        </span>
                    </a>
                </div>
                <?php } ?>
            </div>
			<?php
		}

		function update( $new_instance, $old_instance ) {
			// update logic goes here
			$updated_instance = $new_instance;
			return $updated_instance;
		}

		function form( $instance ) {
			$instance = wp_parse_args( $instance, array(
				'title'  => __( 'Tags', 'give-me-answer-lite' ),
				'number' => 5,
			) );
			?>
            <p><label for="<?php echo $this->get_field_id( 'title' ) ?>"><?php _e( 'Widget title', 'give-me-answer-lite' ) ?></label>
                <input type="text" name="<?php echo $this->get_field_name( 'title' ) ?>" id="<?php echo $this->get_field_id( 'title' ) ?>" value="<?php echo $instance['title'] ?>" class="widefat">
            </p>
            <p><label for="<?php echo $this->get_field_id( 'number' ) ?>"><?php _e( 'Number of posts', 'give-me-answer-lite' ) ?></label>
                <input type="text" name="<?php echo $this->get_field_name( 'number' ) ?>" id="<?php echo $this->get_field_id( 'number' ) ?>" value="<?php echo $instance['number'] ?>" class="widefat">
            </p>
			<?php
		}

	}
?>