<?php
defined( 'ABSPATH' ) || exit;

class GMA_Related_Questions extends WP_Widget {

    public function __construct() {
		$widget_options = [
			'classname' => 'GMA_Widget_RelatedQuestions',
			'description' => __('Related questions', 'give-me-answer-lite'),
		];
		parent::__construct( 'GMA_Widget_RelatedQuestions', __('Give Me Answer : Related Questions', 'give-me-answer-lite'), $widget_options, [] );
	}

	public function widget( $args, $instance ) {
		global $post;
		extract( $args, EXTR_SKIP );
		$instance          = wp_parse_args( $instance, [
		        'title'  => '',
                'number' => 5,
        ] );
		$related_questions = gma_related_question( $post->ID, $instance[ 'number' ], false );
		?>

        <?php
            echo $before_widget;
            echo $before_title;
        ?>
        <?php
            echo esc_html( $instance[ 'title' ] );
            echo $after_title;
        ?>

        <div class="gma">
		<ul class="list-unstyled related-questions">
            <?php foreach ( $related_questions as $related ) { ?>
                <?php
                $best_answer = gma_get_the_best_answer( $related->ID );
                $views_count = gma_question_views_count( $related->ID );
                ?>
                <li class="py-1">
                    <a class="d-flex align-items-center gma-question-hyperlink" href="<?php echo get_permalink( $related->ID ); ?>">
                        <div class="question-views mr-1 <?php if ( $best_answer ) echo 'gma-stack-badge-success text-white';else echo 'gma-stack-badge-light'; ?> px-2 py-1 rounded-0"><?php echo $views_count; ?></div>
                        <div class="text-truncate"><?php echo $related->post_title; ?></div>
                    </a>
                </li>
            <?php } ?>
        </ul>
        </div>

        <?php
            echo $after_widget;
        ?>

		<?php
	}

	function update( $new_instance, $old_instance ) {

		// update logic goes here
		$updated_instance = $new_instance;
		return $updated_instance;
	}

	function form( $instance ) {
		$instance = wp_parse_args( $instance, array(
			'title'	    => '',
			'number'    => 5,
		) );
		?>
        <p><label for="<?php echo $this->get_field_id( 'title' ) ?>"><?php _e( 'Widget title', 'give-me-answer-lite' ) ?></label>
            <input type="text" name="<?php echo $this->get_field_name( 'title' ) ?>" id="<?php echo $this->get_field_id( 'title' ) ?>" value="<?php echo sanitize_text_field( $instance['title'] ); ?>" class="widefat">
        </p>
        <p><label for="<?php echo $this->get_field_id( 'number' ) ?>"><?php _e( 'Number of posts', 'give-me-answer-lite' ) ?></label>
            <input type="text" name="<?php echo $this->get_field_name( 'number' ) ?>" id="<?php echo $this->get_field_id( 'number' ) ?>" value="<?php echo intval( $instance['number'] ); ?>" class="widefat">
        </p>
		<?php
	}

}