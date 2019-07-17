<?php

defined( 'ABSPATH' ) || exit;


/**
 * Return number of answer for a question
 * @param  int $question_id Question ID ( if null get ID of current post )
 * @return int      Number of answer
 */
function gma_question_answers_count( $question_id = null ) {

	if ( ! $question_id ) {
		global $post;
		$question_id = $post->ID;
	}

	$answer_count = wp_cache_get( 'gma_answer_count_for_' . $question_id );

	if ( !$answer_count ) {

		$args = array(
			'post_type'     => 'gma-answer',
			'post_parent'   => $question_id,
			'post_per_page' => '-1',
			'post_status'   => array('publish')
		);

		if ( gma_current_user_can( 'edit_question', $question_id ) || gma_current_user_can( 'manage_question' ) ) {
			$args['post_status'][] = 'private';
		}

		$answer = new WP_Query($args);
		$answer_count = $answer->found_posts;

		wp_cache_set( 'gma_answer_count_for_' . $question_id, $answer_count, '', 15*60 );
	}

	return $answer_count;
}

function gma_is_answer_flag( $post_id ) {
	if ( gma_is_user_flag( $post_id ) ) {
		return true;
	} else {
		$flag = get_post_meta( $post_id, '_flag', true );
		if ( empty( $flag ) || ! is_array( $flag ) ) {
			return false;
		}
		$flag = unserialize( $flag );
		$flag_point = array_sum( $flag );
		if ( $flag_point > 5 ) {
			return true;
		}
	}
	return false; //showing
}

function gma_is_the_best_answer( $answer_id = false ) {
	if ( ! $answer_id ) {
		$answer_id = get_the_ID();
	}
	$question_id = gma_get_question_from_answer_id( $answer_id );
	$best_answer = gma_get_the_best_answer( $question_id );
	if ( $best_answer && $best_answer == $answer_id ) {
		return true;
	}
	return false;
}

function gma_get_best_answer_args( $answer_id = false ) {
	if ( ! $answer_id ) {
		$answer_id = get_the_ID();
	}
	$question_id = gma_get_question_from_answer_id( $answer_id );
	$best_answer = gma_get_the_best_answer( $question_id );
	if ( $best_answer && $best_answer == $answer_id ) {
		$accept_by   = get_post_meta( $question_id, '_gma_best_answer_by', true );
		$accept_date = get_post_meta( $question_id, '_gma_best_answer_date', true );
        $accept_by   = get_user_by('id', $accept_by );
		$result =  [
		   'accept_by'         => $accept_by,
           'accept_date'       => $accept_date,
           'accept_date_human' => human_time_diff( strtotime( $accept_date ) ),
        ];
		$result[ 'title' ] = __('Accepted by ', 'give-me-answer-lite') . $accept_by->user_login . __(' in ', 'give-me-answer-lite') . $result['accept_date_human'];
		$result[ 'title' ] .= __(' ago', 'give-me-answer-lite');

		return $result;
	}
	return false;
}

function gma_get_the_best_answer( $question_id = false ) {
	if ( ! $question_id ) {
		$question_id = get_the_ID();
	}
	if ( 'gma-question' != get_post_type( $question_id ) ) {
		return false;
	}

	$user_vote = get_post_meta( $question_id, '_gma_best_answer', true );

	if ( $user_vote && get_post( $user_vote ) ) {
		return $user_vote;
	}

	$answer_id = get_transient( 'gma-best-answer-for-' . $question_id );
	if ( ! $answer_id ) {
		$answers = get_posts( array(
			'post_type' => gma_lite()->answer->get_slug(),
			'posts_per_page' => 1,
			'meta_key' => '_gma_votes',
			'post_parent' => $question_id,
			'fields' => 'ids',
			'orderby' => 'meta_value_num',
			'order' => 'DESC'
		) );
		$answer_id = ! empty( $answers ) ? $answers[0] : false;
		set_transient( 'gma-best-answer-for-'.$question_id, $answer_id, 21600 );
	}

	if ( $answer_id && ( int ) gma_vote_count( $answer_id ) > 2 ) {
		return $answer_id;
	}
	return false;
}

/**
 * Draft answer
 */

function gma_user_get_draft( $question_id = false ) {
	if ( ! $question_id ) {
		$question_id = get_the_ID();
	}

	if ( ! $question_id || 'gma-question' != get_post_type( $question_id ) ) {
		return false;
	}

	if ( ! is_user_logged_in() ) {
		return false;
	}
	global $current_user;
	$args = array(
   		'post_type' => 'gma-answer',
   		'post_parent' => $question_id,
		'post_status' => 'draft',
	);

	if ( ! current_user_can( 'edit_posts' ) ) {
		$args['author'] = $current_user->ID;
	}

	$answers = get_posts( $args );

	if ( ! empty( $answers ) ) {
		return $answers;
	}
	return false;
}


function gma_get_drafts( $question_id = false ) {
	if ( ! $question_id ) {
		$question_id = get_the_ID();
	}

	if ( ! $question_id || 'gma-question' != get_post_type( $question_id ) ) {
		return false;
	}

	if ( ! is_user_logged_in() ) {
		return false;
	}
	global $current_user;

	$answers = get_posts(  array(
		'post_type' => 'gma-answer',
		'posts_per_page' => 40,
		'post_parent' => $question_id,
		'post_status' => 'draft',
	) );

	if ( ! empty( $answers ) ) {
		return $answers;
	}
	return false;
}

/**
 * Update answers count for question when new answer was added
 * @param  int $answer_id   new answer id
 * @param  int $question_id question id
 */
function gma_question_answer_count( $question_id ) {
	return gma_question_answer_count_by_status( $question_id, array( 'publish', 'private') );
}

function gma_question_answer_count_by_status( $question_id, $status = 'publish' ) {
	$query = new WP_Query( array(
		'post_type' => 'gma-answer',
		'post_status' => $status,
		'post_parent' => $question_id,
		'fields' => 'ids'
	) );
	return $query->found_posts;
}

/**
* Get question id from answer id
*
* @param int $answer_id
* @return int
* @since 1.4.0
*/
function gma_get_question_from_answer_id( $answer_id = false ) {
	if ( !$answer_id ) {
		$answer_id = get_the_ID();
	}

	return gma_get_post_parent_id( $answer_id );
}

class GMA_Posts_Answer extends GMA_Posts_Base {

	public function __construct() {

	    $args = array(
            'plural'        => __( 'Answers', 'give-me-answer-lite' ),
            'singular'      => __( 'Answer', 'give-me-answer-lite' ),
            'menu'          => __( 'Answers', 'give-me-answer-lite' ),
            'show_in_menu'  => 'give-me-answer-lite',
        );

		parent::__construct( 'gma-answer', $args );

		add_action( 'manage_' . $this->get_slug() . '_posts_custom_column', array( $this, 'columns_content' ), 10, 2 );
		add_action( 'post_row_actions', array( $this, 'unset_old_actions' ) );
		add_action( 'add_meta_boxes', array( $this, 'question_metabox' ) );
		add_filter( 'wp_insert_post_data', array( $this, 'save_metabox_post_data' ), 10, 2 );
		
		//Cache
		add_action( 'gma_add_answer', array( $this, 'update_transient_when_add_answer' ), 10, 2 );
		add_action( 'gma_delete_answer', array( $this, 'update_transient_when_remove_answer' ), 10, 2 );
		add_action( 'gma_delete_answer', array( $this, 'update_question_status' ), 10, 3 );

		// Prepare answers content
		add_filter( 'gma_prepare_answer_content', array( $this, 'pre_content_kses' ), 10 );
		add_filter( 'gma_prepare_answer_content', array( $this, 'pre_content_filter' ), 20 );

		// prepare edit content
		add_filter( 'gma_prepare_edit_answer_content', array( $this, 'pre_content_kses' ), 10 );
		add_filter( 'gma_prepare_edit_answer_content', array( $this, 'pre_content_filter' ), 20 );

		// Publish answer
		add_action( 'admin_init', [$this, 'publish_answer'] );
    }


	public function publish_answer() {
        if ( isset( $_GET[ 'status' ], $_GET[ 'post' ] ) && 'publish' == $_GET[ 'status' ] && current_user_can( 'publish_posts' ) ) {
            if ( $post_id = absint( $_GET[ 'post' ] ) ) {
                $current_post = get_post( $post_id, 'ARRAY_A' );
                if ( $current_post['post_status'] != 'publish' ) {
                    $current_post['post_status'] = 'publish';
                    wp_update_post($current_post);

                    // Update question answers count
                    gma_lite()->utility->update_question_answers_count( $current_post[ 'post_parent' ] );
                    // Update question date
                    gma_lite()->utility->update_question_date( $current_post[ 'post_parent' ] );
                }
            }
        }
    }

	public function set_supports() {
		return array(
			'title',
            'editor',
            'comments',
			'custom-fields',
            'author',
            'page-attributes',
		);
	}

	public function set_has_archive() {
		return false;
	}

	public function columns_head( $defaults ) {
		if ( isset( $_GET['post_type'] ) && sanitize_text_field( $_GET['post_type'] ) == $this->get_slug() ) {
			$defaults = array(
				'cb'            => '<input type="checkbox">',
				'info'          => __( 'Answer', 'give-me-answer-lite' ),
				'author'        => __( 'Author', 'give-me-answer-lite' ),
				'comment'       => '<span><span class="vers"><div title="Comments" class="comment-grey-bubble"></div></span></span>',
				'gma-question' => __( 'In Response To', 'give-me-answer-lite' ),
			);
		}
		return $defaults;
	}

	public function unset_old_actions( $actions ) {
		global $post;

		if ( $post->post_type == 'gma-answer' ) {
			$actions = array();
		}

		return $actions;
	}

	public function row_actions( $actions, $always_visible = false ) {
		$action_count = count( $actions );
		$i = 0;

		if ( ! $action_count )
			return '';

		$out = '<div class="' . ( $always_visible ? 'row-actions visible' : 'row-actions' ) . '">';
		foreach ( $actions as $action => $link ) {
			++$i;
			( $i == $action_count ) ? $sep = '' : $sep = ' | ';
			$out .= "<span class='$action'>$link$sep</span>";
		}
		$out .= '</div>';

		return $out;
	}

	public function columns_content( $column_name, $post_ID ) {
		$answer = get_post( $post_ID );
		switch ( $column_name ) {
			case 'comment' :
				$comment_count = get_comment_count( $post_ID );
				echo '<a href="'.admin_url( 'edit-comments.php?p='.$post_ID ).'"  class="post-com-count"><span class="comment-count">'.$comment_count['approved'].'</span></a>';
				break;
			case 'info':
				//Build row actions
				$actions = array(
					'edit'      => sprintf( '<a href="%s">%s</a>', get_edit_post_link( $post_ID ), __( 'Edit', 'give-me-answer-lite' ) ),
					'delete'    => sprintf( '<a href="%s">%s</a>', get_delete_post_link( $post_ID ), __( 'Delete', 'give-me-answer-lite' ) ),
					'view'      => sprintf( '<a href="%s">%s</a>', get_permalink( $post_ID ), __( 'View', 'give-me-answer-lite' ) ),
				);

				if ( 'pending' == get_post_status( $post_ID ) ) {
				    $actions['publish'] = sprintf( '<a href="%s">%s</a>',
                      add_query_arg(
                       [
				         'post'     => $answer->ID,
                         'status'   => 'publish',
                       ],admin_url( 'edit.php?post_type=gma-answer' ) ), __( 'Publish', 'give-me-answer-lite' ) );
                }

				printf(
					'%s %s <a href="%s">%s %s</a> <br /> %s %s',
					__( 'Submitted', 'give-me-answer-lite' ),
					__( 'on', 'give-me-answer-lite' ),
					get_permalink(),
					date( 'M d Y', get_post_time( 'U', true, $answer ) ),
					( time() - get_post_time( 'U', true, $answer ) ) > 60 * 60 * 24 * 2 ? '' : ' at ' . human_time_diff( get_post_time( 'U', true, $answer ) ) . ' ago',
					substr( get_the_content(), 0 , 140 ) . ' ...',
					$this->row_actions( $actions )
				);
				break;
			case 'gma-question':
				$question_id = gma_get_post_parent_id( $post_ID );
				if ( $question_id ) {
					$question = get_post( $question_id );
					echo '<a href="' . get_permalink( $question_id ) . '" >' . $question->post_title . '</a><br>';
				}
				break;
		}
	}

	//Cache
	public function update_transient_when_add_answer( $answer_id, $question_id ) {
		// Update cache for latest answer of this question
		$answer = get_post( $answer_id );
		if ( ! in_array( $answer->post_status, [ 'pending', 'draft' ] ) ) {
            set_transient( 'gma_latest_answer_for_' . $question_id, $answer, 15*60 );
            delete_transient( 'gma_answer_count_for_' . $question_id );
        }
	}

	public function update_transient_when_remove_answer( $answer_id, $question_id ) {
		// Remove Cached Latest Answer
		delete_transient( 'gma_latest_answer_for_' . $question_id );
		delete_transient( 'gma_answer_count_for_' . $question_id );
	}

	public function update_question_status( $answer_id, $question_id ) {
	    $total_remaining_answers = gma_get_answer_count( $question_id );
	    // Check if no answer exist
	    if ( false == $total_remaining_answers ) {
	        update_post_meta( $question_id, '_gma_status', 'open' );
        }
    }

	public function question_metabox() {
		add_meta_box(
			'gma-answer-question-metabox',
			__( 'Question ID', 'give-me-answer-lite' ),
			array( $this, 'question_metabox_output' ),
			'gma-answer',
			'side'
		);
	}

	public function question_metabox_output( $post ) {
		$question = $post->post_parent ? $post->post_parent : 0;
		?>
		<p>
			<strong><?php _e( 'ID', 'give-me-answer-lite' ) ?></strong>
		</p>
		<p>
			<label for="_question" class="screen-reader-text"><?php _e( 'ID', 'give-me-answer-lite' ) ?></label>
			<input name="_question" type="text" size="4" id="_question" value="<?php echo (int) $question ?>">
		</p>
		<?php
	}

	public function save_metabox_post_data( $data, $postarr ) {
		// only for admin
		if(!is_admin() || !current_user_can( 'edit_posts' )){
			return $data;
		}

		if ( 'gma-answer' !== $data['post_type'] ) {
			return $data;
		}

		if ( !isset( $_POST['_question'] ) || empty( $_POST['_question'] ) ) {
			return $data;
		}

		$data['post_parent'] = intval($_POST['_question']);
		
		return $data;
	}
}

?>
