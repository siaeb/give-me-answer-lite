<?php
defined( 'ABSPATH' ) || exit;

class GMA_Latest_Question extends WP_Widget {
	/**
	 * Constructor
	 *
	 * @return void
	 **/
	function __construct() {
		$widget_ops = array(
            'classname'     => 'gma-widget gma-latest-questions',
            'description'   => __( 'Show a list of latest questions.', 'give-me-answer-lite' )
        );
		parent::__construct( 'gma-latest-question', __( 'Give Me Answer : Latest Questions', 'give-me-answer-lite' ), $widget_ops );
	}

	function widget( $args, $instance ) {
		extract( $args, EXTR_SKIP );
		$instance = wp_parse_args( $instance, array( 
			'title' => __( 'Latest Questions' , 'give-me-answer-lite' ),
			'number' => 5,
		) );
		
		echo $before_widget;
		echo $before_title;
		echo $instance['title'];
		echo $after_title;
		
		$args = array(
			'posts_per_page'    => $instance['number'],
			'order'             => 'DESC',
			'orderby'           => 'post_date',
			'post_type'         => 'gma-question',
			'suppress_filters'  => false,
		);
		$questions = new WP_Query( $args );
		if ( $questions->have_posts() ) {
			echo '<div class="gma-widget gma-popular-questions">';
			echo '<ul>';
			while ( $questions->have_posts() ) { $questions->the_post( );
				echo '<li>';
				echo '<a href="'. get_permalink() .'" class="question-title">';
				the_title();
				echo '</a>';
				echo ' '.__( 'asked by', 'give-me-answer-lite' ) . ' ' . get_the_author_link();
				if ( isset( $instance['question_date'] ) && $instance['question_date'] ) {
					echo ', ' . sprintf( esc_html__( '%s ago', 'give-me-answer-lite' ), human_time_diff( get_post_time('U', true, get_the_ID() ) ) ) . '.';
				}
				echo '</li>';
			}   
			echo '</ul>';
			echo '</div>';
		}
		wp_reset_query( );
		wp_reset_postdata( );
		echo $after_widget;
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
			'question_date' => false
		) );

		?>
		<p><label for="<?php echo $this->get_field_id( 'title' ) ?>"><?php _e( 'Widget title', 'give-me-answer-lite' ) ?></label>
		<input type="text" name="<?php echo $this->get_field_name( 'title' ) ?>" id="<?php echo $this->get_field_id( 'title' ) ?>" value="<?php echo $instance['title'] ?>" class="widefat">
		</p>
		<p><label for="<?php echo $this->get_field_id( 'number' ) ?>"><?php _e( 'Number of posts', 'give-me-answer-lite' ) ?></label>
		<input type="text" name="<?php echo $this->get_field_name( 'number' ) ?>" id="<?php echo $this->get_field_id( 'number' ) ?>" value="<?php echo $instance['number'] ?>" class="widefat">
		</p>
		<p>
			<input type="checkbox" name="<?php echo $this->get_field_name( 'question_date' ) ?>" id="<?php echo $this->get_field_id( 'question_date' ) ?>" <?php checked( 'on', $instance['question_date'] ) ?> class="widefat">
			<label for="<?php echo $this->get_field_id( 'question_date' ) ?>"><?php _e( 'Show question date', 'give-me-answer-lite' ) ?></label>
		</p>
		<?php
	}
}

?>