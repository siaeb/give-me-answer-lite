<?php
defined( 'ABSPATH' ) || exit;

class GMA_Closed_Question extends WP_Widget {

	/**
	 * Constructor
	 *
	 * @return void
	 **/
	function __construct() {
		$widget_ops = array( 'classname' => 'gma-widget gma-closed-questions', 'description' => __( 'Show a list of questions marked as closed.', 'give-me-answer-lite' ) );
		parent::__construct( 'gma-closed-question', __( 'Give Me Answer : Closed Questions', 'give-me-answer-lite' ), $widget_ops );
	}

	function widget( $args, $instance ) {
		extract( $args, EXTR_SKIP );
		$instance = wp_parse_args( $instance, array( 
			'title' => __( 'Closed Questions', 'give-me-answer-lite' ),
			'number' => 5,
		) );
		
		echo $before_widget;
		echo $before_title;
		echo $instance['title'];
		echo $after_title;
		$args = array(
			'post_type' => 'gma-question',
			'posts_per_page' => $instance['number'],
			'meta_query' => array(
				'relation' => 'OR',
				array(
					'key' => '_gma_status',
					'compare' => '=',
					'value' => 'resolved',
				),
				array(
					'key' => '_gma_status',
					'compare' => '=',
					'value' => 'closed',
				),
			),
		);
		$questions = new WP_Query( $args );
		if ( $questions->have_posts() ) {
			echo '<div class="gma-widget gma-popular-questions">';
			echo '<ul>';
			while ( $questions->have_posts() ) { $questions->the_post( );
				echo '<li><a href="'.get_permalink( ).'" class="question-title">'.get_the_title( ).'</a> '.__( 'asked by' , 'give-me-answer-lite' ).' '. get_the_author_link( ) . '</li>';
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