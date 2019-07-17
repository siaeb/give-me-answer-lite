<?php
defined( 'ABSPATH' ) || exit;

class GMA_Popular_Question extends WP_Widget {

	/**
	 * Constructor
	 *
	 * @return void
	 **/
	function __construct() {
		$widget_ops = array( 
			'classname' => 'gma-widget gma-popular-question',
			'description' => __( 'Show a list of popular questions.', 'give-me-answer-lite' )
		);
		parent::__construct( 'gma-popular-question', __( 'Give Me Answer : Popular Questions', 'give-me-answer-lite' ), $widget_ops );
	}

	function widget( $args, $instance ) {
		extract( $args, EXTR_SKIP );
		$instance = wp_parse_args( $instance, array( 
			'title' => __( 'Popular Questions', 'give-me-answer-lite' ),
			'number' => 5,
		) );
		
		echo $before_widget;
		echo $before_title;
		echo $instance['title'];
		echo $after_title;
		
		$args = array(
			'posts_per_page'       => $instance['number'],
			'order'             => 'DESC',
			'orderby'           => 'meta_value_num',
			'meta_key'           => '_gma_views',
			'post_type'         => 'gma-question',
			'suppress_filters'  => false,
		);
		$questions = new WP_Query( $args );
		if ( $questions->have_posts() ) {
			echo '<div class="gma-widget gma-popular-questions gma">';
			echo '<ul>';
			while ( $questions->have_posts() ) {
			    $questions->the_post();
                $best_answer = gma_get_the_best_answer( get_the_ID() );
                $views_count = gma_question_views_count( get_the_ID() );
			    ?>
                <li class="py-1">
                    <a class="d-flex align-items-center gma-question-hyperlink" href="<?php echo get_permalink(); ?>">
                        <div class="question-views mr-1 <?php if ( $best_answer ) echo 'gma-stack-badge-success text-white';else echo 'gma-stack-badge-light'; ?> px-2 py-1 rounded-0"><?php echo $views_count; ?></div>
                        <div class="text-truncate"><?php echo get_the_title() ?></div>
                    </a>
                </li>
                <?php
			}   
			echo '</ul>';
			echo '</div>';
		}
		wp_reset_query();
		wp_reset_postdata();
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