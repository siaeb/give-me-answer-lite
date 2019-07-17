<?php

defined( 'ABSPATH' ) || exit;


/**
 * Control all status 
 */
function gma_question_print_status( $question_id = false, $echo = true ) {
	if ( !gma_is_enable_status() ) {
		return;
	}

	if ( !$question_id ) {
		$question_id = get_the_ID();
	}

	$status = get_post_meta( $question_id, '_gma_status', true );

	if ( 'close' == $status ) {
		$status = 'closed';
	}

	if ( 're-open' == $status ) {
		$status = 'open';
	}

	if ( $status ) {
		$return = '<span title="'.__( ucwords( $status ), 'give-me-answer-lite' ).'" class="gma-status gma-status-'.strtolower( $status ).'">'.__( ucwords( $status ), 'give-me-answer-lite' ).'</span>';
		if ( $echo ) {
			echo $return;
			return;
		}
		return $return;
	}
}

// Detect resolved question
function gma_is_resolved( $question_id = false, $status = false ) {
	if ( ! $question_id ) {
	   $question_id = get_the_ID();
	}
	if ( ! $status ) {
		$status = get_post_meta( $question_id, '_gma_status', true );
	}
	if ( $status == 'resolved' ) {
		return true;
	}
	return false;
}

// Detect closed question
function gma_is_closed( $question_id = false ) {
	if ( ! $question_id ) {
	   $question_id = get_the_ID();
	}

	if ( 'gma-answer' == get_post_type( $question_id ) ) {
		$question_id = gma_get_question_from_answer_id( $question_id );
	}

	$status = get_post_meta( $question_id, '_gma_status', true );
	if ( $status == 'close' ) {
		return true;
	}
	return false;
}

// Detect open question
function gma_is_open( $question_id = false, $status = false ) {
	if ( ! $question_id ) {
	   $question_id = get_the_ID();
	}
	if ( ! $status ) {
		$status = get_post_meta( $question_id, '_gma_status', true );
	}
	if ( $status == 'open' || $status == 're-open' ) {
		return true;
	}
	return false;
}

// Detect open pending
function gma_is_pending( $question_id = false ) {
	if ( ! $question_id ) {
	   $question_id = get_the_ID();
	}
	$status = get_post_field( 'post_status', $question_id );
	if ( $status == 'pending' ) {
		return true;
	}
	return false;
}

// Detect answered question ( have an answer that was posted by supporter and still open )
function gma_is_answered( $question_id, $status = false ) {
	if ( ! $question_id ) {
		$question_id = get_the_ID();
	}
	if ( gma_is_resolved( $question_id, $status ) ) {
		return true;
	}
	$latest_answer = gma_get_latest_answer( $question_id );

	if ( $latest_answer && gma_is_staff_answer( $latest_answer ) ) {
		return true;
	}
	return false;
}



// Detect new question
function gma_is_new( $question_id = null, $status = false ) {
	global  $gma_general_settings;
	$hours = isset( $gma_general_settings['question-new-time-frame'] ) ? (int) $gma_general_settings['question-new-time-frame'] : 4;
	$created_date = get_post_time( 'U', false, $question_id );
	$hours = $hours * 60 * 60 * 1000;

	if ( $created_date + $hours > current_time('U') && gma_is_open( $question_id, $status ) ) {
		return true;
	}
	return false;
}


/**
 * The status that admin can see it
 */
// detect overdue question
function gma_is_overdue( $question_id ) {
	global  $gma_general_settings;
	$created_date = get_post_time( 'U', false, $question_id );

	$days = isset( $gma_general_settings['question-overdue-time-frame'] ) ? (int) $gma_general_settings['question-overdue-time-frame'] : 2;
	$days = $days * 24 * 60 * 60 * 1000;
	if ( $created_date + $days > current_time('U') ) {
		return false;
	}
	return true;
}

// Detect new answer from an other user
function gma_have_new_reply( $question_id = false ) {
	//if latest answer is not administrator 
	if ( ! $question_id ) {
		$question_id = get_the_ID();
	}

	$latest_answer = gma_get_latest_answer( $question_id );
	if ( $latest_answer ) {
		if ( gma_is_staff_answer( $latest_answer ) ) {
			//answered
			return 'staff-answered';
		} else {
			//Is open
			return strtotime( $latest_answer->post_date );
		}
	}
	return false;
}

// Detect new comment
function gma_have_new_comment( $question_id = false ) {
	//if latest answer is not administrator 
	if ( ! $question_id ) {
		global $post;
		$question_id = $post->ID;
	}

	$lastest_comment = false;

	$comments = get_comments( array(
		'status'    => 'approve',
		'post_id'   => $question_id,
	) );   

	if ( ! empty( $comments ) ) {
		$lastest_comment = $comments[0];
	}

	$answers = wp_cache_get( 'gma-answers-for-'.$question_id, 'gma' );
	if ( false == $answers ) {
		$args = array(
			'post_type' => 'gma-answer',
			'post_parent' => $question_id,
			'post_per_page' => '-1',
			'post_status' => array('publish')
		);

		$answers = get_posts($args);

		wp_cache_set( 'gma-answers-for'.$question_id, $answers, 'gma', 21600 );
	}

	if ( ! empty( $answers ) ) {
		//Comment of answers
		foreach ( $answers as $answer ) {
			$comments = get_comments( array( 
				'post_id'   => $answer->ID,
				'status'    => 'approve',
				'number'    => 1,
			) );
			if ( empty( $comments ) ) {
				continue;
			}
			if ( $lastest_comment ) {
				if ( strtotime( $comments[0]->comment_date_gmt ) > strtotime( $lastest_comment->comment_date_gmt )  ) {
					$lastest_comment = $comments[0];
				}
			} else {
				$lastest_comment = $comments[0];
			}
		}
	}

	if ( $lastest_comment ) {
		if ( ! $lastest_comment->user_id ) {
			return strtotime( $lastest_comment->comment_date_gmt );
		} else {
			if ( user_can( $lastest_comment->user_id, 'edit_posts' ) ) {
				return false;
			} else {
				return strtotime( $lastest_comment->comment_date_gmt );
			}
		}
	} else {
		return false;
	}
}
// End statuses of admin

// Get new reply
function gma_get_latest_answer( $question_id = false ) {
	if ( !$question_id ) {
		$question_id = get_the_ID();
	}

	// When we get latest answer by normal query it take a long time to query into database so i will try to setup transien here to improve it. Of course we will use another cache plugin for QA site in additional
	$latest = get_transient( 'gma_latest_answer_for_' . $question_id );
	if ( $latest ) {
		$args = array(
			'post_type' 		=> 'gma-answer',
			'post_parent' 		=> $question_id,
			'post_status'    	=> array('public', 'publish', 'private'),
	    	'numberposts' 		=> 1,
		);
		$recent_answers = wp_get_recent_posts( $args, OBJECT );
		if ($recent_answers && count( $recent_answers ) > 0 ) {
			$latest = $recent_answers[0];
			// This cache need to be update when new answer is added
			set_transient( 'gma_latest_answer_for_' . $question_id, $latest, 450 );
		}
	}

	return $latest;
}

/**
 * All status of the Answer
 */

// Detect staff-answer
function gma_is_staff_answer( $answer ) {
	if ( ! $answer ) {
		$answer = get_post( get_the_ID() );
		if ( 'gma-answer' != $answer->post_status ) {
			return false;
		}
	}
	if ( gma_current_user_can( 'edit_question') ) {
		return true;
	}
	return false;
}

/**
 * Return a message in context for question status code
 * @param  string $status status code
 * @return string         Status message
 */ 
function gma_question_get_status_name( $status ) {
	$status = strtolower( $status );  
	switch ( $status ) {
		case 'resolved':
			$message = __( 'Resolved', 'give-me-answer-lite' );
			break;
		case 'pending':
			$message = __( 'Pending', 'give-me-answer-lite' );
			break;
		case 're-open':
			$message = __( 'Re-Open', 'give-me-answer-lite' );
			break;
		case 'closed':
			$message = __( 'Closed', 'give-me-answer-lite' );
			break;
		case 'new':
			$message = __( 'New', 'give-me-answer-lite' );
			break;

		case 'answered':
			$message = __( 'Answered', 'give-me-answer-lite' );
			break;
		
		default:
			$message = __( 'Open', 'give-me-answer-lite' );
			break;
	}
	return $message;
}   


class GMA_Status {
	public function __construct() {
		add_action( 'wp_ajax_gma-update-privacy', array( $this, 'update_privacy' ) );
		add_action( 'gma_add_answer', array( $this, 'gma_auto_change_question_status' ) );
	}

	public function update_privacy() {
		if ( ! isset( $_POST['nonce'] ) ) {
			wp_send_json_error( array( 'message' => __( 'Are you cheating huh ?!', 'give-me-answer-lite' ) ) );
		}
		check_ajax_referer( '_gma_update_privacy_nonce', 'nonce' );

		if ( ! isset( $_POST['post'] ) ) {
			wp_send_json_error( array( 'message' => __( 'Missing post ID', 'give-me-answer-lite' ) ) );
		}

		global $current_user;
		$post_author = get_post_field( 'post_author', esc_html( $_POST['post'] ) );
		if ( gma_current_user_can( 'edit_question' ) || $current_user->ID == $post_author ) {
			$status = 'publish';
			if ( isset( $_POST['status'] ) && in_array( $_POST['status'], array( 'close', 'open', 'resolved' ) ) ) {
				$update = update_post_meta( intval( $_POST['post'] ), '_gma_status', esc_html( $_POST['status'] ) );
				if ( $update ) {
					wp_send_json_success( array( 'ID' => $update ) );
				} else {
					wp_send_json_error(  array(
						'message'   => __( 'Post does not exist','give-me-answer-lite' )
					) );
				}
			} else {
				wp_send_json_error( array(
					'message'   => __( 'Invalid post status','give-me-answer-lite' )
				) );
			}
		} else {
			wp_send_json_error( array(
				'message'   => __( 'You do not have permission to edit question', 'give-me-answer-lite' )
			) );
		}	
	}
	/**
	 * Update question status when have new answer
	 */
	public function gma_auto_change_question_status( $answer_id ){
		if ( ! is_wp_error( $answer_id ) ) {
			$question_id = gma_get_post_parent_id( $answer_id );
			$answer = get_post( $answer_id );
			if ( $question_id && $answer->post_author ) {
				$question_status = get_post_meta( $question_id, '_gma_status', true );
				if ( gma_current_user_can( 'edit_question' ) ) {
					update_post_meta( $question_id, '_gma_status', 'answered' );
				} else {
					if ( $question_status == 'resolved' || $question_status == 'answered' ) {
						update_post_meta( $question_id, '_gma_status', 'open' );
					}
				}
			}
		}
	}
}



?>
