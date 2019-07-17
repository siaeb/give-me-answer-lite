<?php

defined( 'ABSPATH' ) || exit;


/**
 * Generate html for metabox of question status meta data
 * @param  object $post Post Object
 * @return void       
 */
function gma_question_status_box_html( $post ){
		$meta = get_post_meta( $post->ID, '_gma_status', true );
		$meta = $meta ? $meta : 'open';
	?>
	<p>
		<label for="gma-question-status">
			<?php _e( 'Status','give-me-answer-lite' ) ?><br>&nbsp;
			<select name="gma-question-status" id="gma-question-status" class="widefat">
				<option <?php selected( $meta, 'open' ); ?> value="open"><?php _e( 'Open','give-me-answer-lite' ) ?></option>
				<option <?php selected( $meta, 'pending' ); ?> value="pending"><?php _e( 'Pending','give-me-answer-lite' ) ?></option>
				<option <?php selected( $meta, 'resolved' ); ?> value="resolved"><?php _e( 'Resolved','give-me-answer-lite' ) ?></option>
				<option <?php selected( $meta, 're-open' ); ?> value="re-open"><?php _e( 'Re-Open','give-me-answer-lite' ) ?></option>
				<option <?php selected( $meta, 'closed' ); ?> value="closed"><?php _e( 'Closed','give-me-answer-lite' ) ?></option>
			</select>
		</label>
	</p>    
	<p>
		<label for="gma-question-sticky">
			<?php _e( 'Sticky','give-me-answer-lite' ); ?><br><br>&nbsp;
			<?php
				$sticky_questions = get_option( 'gma_sticky_questions', array() );
			?>
			<input <?php checked( true, in_array( $post->ID, $sticky_questions ), true ); ?> type="checkbox" name="gma-question-sticky" id="gma-question-sticky" value="1" ><span class="description"><?php _e( 'Pin question to top of archive page.','give-me-answer-lite' ); ?></span>
		</label>
	</p>
	<?php
}

class GMA_Metaboxes {
	public function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'answers_metabox' ) );
		add_filter( 'postbox_classes_gma-question_gma-answers', array( $this, 'add_css_class_metabox' ) );
		add_action( 'admin_init', array( $this, 'add_status_metabox' ) );
		add_action( 'save_post', array( $this, 'question_status_save' ) );
	}

	//Add a metabox that was used for display list of answers of a questions
	public function answers_metabox(){
		add_meta_box( 'gma-answers', __( 'Answers','give-me-answer-lite' ), array( $this, 'metabox_answers_list' ), 'gma-question' );
	}

	/**
	 * generate html for metabox that was used for display list of answers of a questions
	 */
	public function metabox_answers_list(){
		$answer_list_table = new GMA_Answer_List_Table();
		$answer_list_table->display();
	}

	public function add_css_class_metabox( $classes ){
		$classes[] = 'gma-answer-list';
		return $classes;
	}
	/**
	 * Add metabox for question status meta data
	 * @return void
	 */
	public function add_status_metabox(){
		add_meta_box( 'gma-post-status', __( 'Question Meta Data','give-me-answer-lite' ), 'gma_question_status_box_html', 'gma-question', 'side', 'high' );
	}

	public function question_status_save( $post_id ){
		if ( ! wp_is_post_revision( $post_id ) ) {
			if ( isset( $_POST['gma-question-status'] ) ) {
				update_post_meta( $post_id, '_gma_status', esc_html( $_POST['gma-question-status'] ) );
			}
			if ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) {

				$sticky_questions = get_option( 'gma_sticky_questions', array() );
				if ( isset( $_POST['gma-question-sticky'] ) && sanitize_text_field( $_POST['gma-question-sticky'] ) ) {
					if ( ! in_array( $post_id, $sticky_questions ) ) {
						$sticky_questions[] = $post_id;
						update_option( 'gma_sticky_questions', $sticky_questions );
					}
				} else {
					if ( in_array( $post_id, $sticky_questions ) ) {
						if ( ($key = array_search( $post_id, $sticky_questions ) ) !== false ) {
							unset( $sticky_questions[$key] );
						}
						update_option( 'gma_sticky_questions', $sticky_questions );
					}
				}
			}
		}
	}
}

?>