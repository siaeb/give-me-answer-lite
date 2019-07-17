<?php
defined( 'ABSPATH' ) || exit;

class GMA_Handle {
	public function __construct() {
		// question
		add_action( 'wp_loaded', array( $this, 'submit_question' ), 11 );
		add_action( 'wp_loaded', array( $this, 'update_question' ) );

		// answer
		add_action( 'wp_loaded', array( $this, 'insert_answer') );
		add_action( 'wp_loaded', array( $this, 'update_answer' ) );

		// comment
		add_action( 'wp_loaded', array( $this, 'insert_comment' ) );
		add_action( 'wp_loaded', array( $this, 'update_comment' ) );

		// Open / Close question
        add_action( 'wp_loaded', array( $this, 'close_question' ) );
        add_action( 'wp_loaded', array( $this, 'open_question' ) );

	}


	public function insert_answer() {
		global $gma_options;
		if ( ! isset( $_POST['gma-action'] ) || ! isset( $_POST['submit-answer'] ) ) {
			return false;
		}
		// do_action( 'gma_add_answer', $answer_id, $question_id );
		// die();
		if ( 'add-answer' !== sanitize_text_field( $_POST['gma-action'] ) ) {
			return false;
		}

		if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( esc_html(  $_POST['_wpnonce'] ), '_gma_add_new_answer' ) ) {
			gma_add_notice( __( 'Are you cheating huh ?!', 'give-me-answer-lite' ), 'error' );
		}

		if ( sanitize_text_field( $_POST['submit-answer'] ) == __( 'Delete draft', 'give-me-answer-lite' ) ) {
			$draft = isset( $_POST['answer-id'] ) ? intval( $_POST['answer-id'] ) : 0;
			if ( $draft )
				wp_delete_post( $draft );
		}

		if ( empty( $_POST['answer-content'] ) ) {
			gma_add_notice( __( 'Answer content is empty', 'give-me-answer-lite' ), 'error' );
		}

		if ( empty( $_POST['question_id'] ) ) {
			gma_add_notice( __( 'Question ID is required.', 'give-me-answer-lite' ), 'error' );
		}

		if ( !gma_current_user_can( 'post_answer' ) ) {
			gma_add_notice( __( 'You do not have permission to submit question.', 'give-me-answer-lite' ), 'error' );
		}

		if ( !is_user_logged_in() && ( empty( $_POST['user-email'] ) || !is_email( sanitize_email( $_POST['user-email'] ) ) ) ) {
			gma_add_notice( __( 'Missing email information', 'give-me-answer-lite' ), 'error' );
		}

		if ( !is_user_logged_in() && ( empty( $_POST['user-name'] ) ) ) {
			gma_add_notice( __( 'Missing name information', 'give-me-answer-lite' ), 'error' );
		}

		if ( ! gma_valid_captcha( 'single-question' ) ) {
			gma_add_notice( __( 'Captcha is not correct', 'give-me-answer-lite' ), 'error' );
		}

		$user_id = 0;
		$is_anonymous = false;
		if ( is_user_logged_in() ) {
			$user_id = get_current_user_id();
		} else {
			$is_anonymous = true;
			if ( isset( $_POST['user-email'] ) && is_email( $_POST['user-email'] ) ) {
				$post_author_email = sanitize_email( $_POST['user-email'] );
			}
			if ( isset( $_POST['user-name'] ) && !empty( $_POST['user-name'] ) ) {
				$post_author_name = sanitize_text_field( $_POST['user-name'] );
			}
		}

		$question_id = intval( $_POST['question_id'] );

		$answer_title = __( 'Answer for ', 'give-me-answer-lite' ) . get_post_field( 'post_title', $question_id );
		$answ_content = apply_filters( 'gma_prepare_answer_content', $_POST['answer-content'] );

		$answers = array(
			'comment_status' => 'open',
			'post_author'    => $user_id,
			'post_content'   => $answ_content,
			'post_title'     => $answer_title,
			'post_type'      => 'gma-answer',
			'post_parent'	 => $question_id,
		);

		$answers['post_status'] = isset( $_POST['save-draft'] )
									? 'draft'
										: ( isset( $_POST['gma-status'] ) && $_POST['gma-status'] ? sanitize_text_field( $_POST['gma-status'] ) : 'publish' );

		do_action( 'gma_prepare_add_answer' );

		if ( gma_count_notices( 'error' ) > 0 ) {
			return false;
		}

		$answers = apply_filters( 'gma_insert_answer_args', $answers );
		
		$answer_id = wp_insert_post( $answers );

		if ( !is_wp_error( $answer_id ) ) {
			if ( $answers['post_status'] != 'draft' ) {
				update_post_meta( $question_id, '_gma_status', 'answered' );
				update_post_meta( $question_id, '_gma_answered_time', time() );
				update_post_meta( $answer_id, '_gma_votes', 0 );
				$answer_count = get_post_meta( $question_id, '_gma_answers_count', true );
				update_post_meta( $question_id, '_gma_answers_count', (int) $answer_count + 1 );
			}

			if ( $is_anonymous ) {
				update_post_meta( $answer_id, '_gma_is_anonymous', true );

				if ( isset( $post_author_email ) && is_email( $post_author_email ) ) {
					update_post_meta( $answer_id, '_gma_anonymous_email', $post_author_email );
				}

				if ( isset( $post_author_name ) && !empty( $post_author_name ) ) {
					update_post_meta( $answer_id, '_gma_anonymous_name', $post_author_name );
				}
			} else {
				if ( !gma_is_followed( $question_id, get_current_user_id() ) ) {
					add_post_meta( $question_id, '_gma_followers', get_current_user_id() );
				}
			}

			do_action( 'gma_add_answer', $answer_id, $question_id );
			$this->update_modified_date( $question_id , current_time( 'timestamp', 0 ), current_time( 'timestamp', 1 ) );

			exit( wp_redirect( get_permalink( $question_id ) ) );
		} else {
			gma_add_wp_error_message( $answer_id );
		}
	}

	public function update_answer() {
		if ( isset( $_POST['gma-edit-answer-submit'] ) ) {
			if ( !gma_current_user_can( 'edit_answer', $_POST[ 'answer_id' ] ) ) {
				gma_add_notice( __( "You do not have permission to edit answer.", 'give-me-answer-lite' ), 'error' );
			}

			if ( !isset( $_POST['_wpnonce'] ) && !wp_verify_nonce( esc_html( $_POST['_wpnonce'] ), '_gma_edit_answer' ) ) {
				gma_add_notice( __( 'Are you cheating huh ?!', 'give-me-answer-lite' ), 'error' );
			}

			$answer_content = apply_filters( 'gma_prepare_edit_answer_content', $_POST['answer_content'] );
			if ( empty( $answer_content ) ) {
				gma_add_notice( __( 'You must enter a valid answer content.', 'give-me-answer-lite' ), 'error' );
			}

			$answer_id = isset( $_POST['answer_id'] ) ? intval( $_POST['answer_id'] ) : false;

			if ( !$answer_id ) {
				gma_add_notice( __( 'Answer is missing.', 'give-me-answer-lite' ), 'error' );
			}

			if ( 'gma-answer' !== get_post_type( $answer_id ) ) {
				gma_add_notice( __( 'This post is not answer.', 'give-me-answer-lite' ), 'error' );
			}

			do_action( 'gma_prepare_insert_question', $answer_id );

			if ( gma_count_notices( 'error' ) > 0 ) {
				return false;
			}

			$answer       = get_post( $answer_id );
			$question_id  = gma_get_question_from_answer_id( $answer_id );


			// convert answer to comment
			if ( isset( $_POST[ 'commenton' ] ) && $_POST[ 'commenton' ] == 'on' ) {
				if ( isset( $_POST[ 'commenton-post' ] ) && absint($_POST[ 'commenton-post' ]) ) {

					$args = array(
						'comment_post_ID'   => intval( $_POST['commenton-post'] ),
						'comment_content'   => $answer_content,
						'comment_parent'    => '',
						'comment_type'		=> 'gma-comment',
						'comment_date'      => $answer->post_date,
					);

					if ( $answer->post_author ) {
						$args['user_id']        = $answer->post_author;
						$args['comment_author'] = gma_user_displayname( $answer->post_author );
					} else {
						$anonymous               = gma_get_anonymous_user( $answer->ID );
						$args['comment_author']  = isset( $anonymous['name'] ) ? sanitize_text_field( $anonymous['name'] ) : 'Anonymous';
						$args['comment_author_email'] = sanitize_email(  $anonymous['email'] );
						$args['comment_author_url'] = isset( $anonymous['url'] ) ? esc_url( $anonymous['url'] ) : '';
						$args['user_id']    = -1;
					}

					$comment_id = wp_insert_comment( $args );
					if ( $comment_id ) {

						// Move answer comments to new post
						gma_move_comments( $answer->ID, intval( $_POST[ 'commenton-post' ] ) );

						gma_delete_answer( $answer_id );

						wp_safe_redirect( get_permalink( $question_id ) );
						die();
					}

					gma_add_notice( __( 'Error, Please try again', 'give-me-answer-lite' ), 'error' );
					return false;

				}
			}

			$args = array(
				'ID' => $answer_id,
				'post_content' => $answer_content
			);

			$new_answer_id = wp_update_post( $args );

			if ( !is_wp_error( $new_answer_id ) ) {
				$old_post = get_post( $answer_id  );
				$new_post = get_post( $new_answer_id );
				do_action( 'gma_update_answer', $new_answer_id, $old_post, $new_post );
				$question_id = gma_get_post_parent_id( $new_answer_id );

				wp_safe_redirect( get_permalink( $question_id ) . '#answer-' . $new_answer_id );
			} else {
				gma_add_wp_error_message( $new_answer_id );
				return false;
			}
			exit();
		}
	}

	public function insert_comment() {
		global $current_user;
		if ( isset( $_POST['comment-submit'] ) ) {

			if ( ! gma_current_user_can( 'post_comment' ) ) {
				gma_add_notice( __( 'You can\'t post comment', 'give-me-answer-lite' ), 'error', true );
			}

			if ( ! isset( $_POST['comment_post_ID'] ) ) {
				gma_add_notice( __( 'Missing post id.', 'give-me-answer-lite' ), 'error', true );
			}

			$comment_content = isset( $_POST['comment'] ) ? $_POST['comment'] : '';
			$comment_content = apply_filters( 'gma_pre_comment_content', $comment_content );

			if ( empty( trim($comment_content) ) ) {
				gma_add_notice( __( 'Missing comment content', 'give-me-answer-lite' ), 'error', true );
			}


			$args = array(
				'comment_post_ID'   => intval( $_POST['comment_post_ID'] ),
				'comment_content'   => $comment_content,
				'comment_parent'    => isset( $_POST['comment_parent']) ? intval( $_POST['comment_parent'] ) : 0,
				'comment_type'		=> 'gma-comment'
			);

			if ( is_user_logged_in() ) {
				$args['user_id']        = $current_user->ID;
				$args['comment_author'] = $current_user->display_name;
			} else {
				if ( ! isset( $_POST['email'] ) || ! sanitize_email( $_POST['email'] ) ) {
					gma_add_notice( __( 'Missing email information', 'give-me-answer-lite' ), 'error', true );
				}

				if ( ! isset( $_POST['name'] ) || empty( $_POST['name'] ) ) {
					gma_add_notice( __( 'Missing name information', 'give-me-answer-lite' ), 'error', true );
				}

				$args['comment_author'] = isset( $_POST['name'] ) ? sanitize_text_field( $_POST['name'] ) : 'Anonymous';
				$args['comment_author_email'] = sanitize_email(  $_POST['email'] );
				$args['comment_author_url'] = isset( $_POST['url'] ) ? esc_url( $_POST['url'] ) : '';
				$args['user_id']    = -1;
			}
			
			$question_id = absint( $_POST['comment_post_ID'] );
			if ( 'gma-answer' == get_post_type( $question_id ) ) {
				$question_id = gma_get_question_from_answer_id( $question_id );
			}

			$redirect_to = get_permalink( $_POST['comment_post_ID'] );

			if ( gma_count_notices( 'error', true ) > 0 ) {
			    return false;
			}

			$args = apply_filters( 'gma_insert_comment_args', $args );

			$comment_id = wp_insert_comment( $args );

			global $comment;
			$comment = get_comment( $comment_id );
			$client_id = isset( $_POST['clientId'] ) ? sanitize_text_field( $_POST['clientId'] ) : false;
			do_action( 'gma_add_comment', $comment_id, $client_id );
			
			$redirect_to = apply_filters( 'gma_submit_comment_success_redirect', $redirect_to, $question_id);
			exit(wp_safe_redirect( $redirect_to ));
		}
	}

	public function update_comment() {
		global $post_submit_filter;
		if ( isset( $_POST['gma-edit-comment-submit'] ) ) {
			if ( ! isset( $_POST['comment_id']) ) {
				gma_add_notice( __( 'Comment is missing', 'give-me-answer-lite' ), 'error' );
			}
			$comment_id = intval( $_POST['comment_id'] );
			$comment_content = isset( $_POST['comment_content'] ) ? $_POST['comment_content'] : '';
			$comment_content = apply_filters( 'gma_pre_update_comment_content', $comment_content );

			if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( $_POST['_wpnonce'] ), '_gma_edit_comment' ) ) {
				gma_add_notice( __( 'Are you cheating huh ?!', 'give-me-answer-lite' ), 'error' );
			}

			if ( !gma_current_user_can( 'edit_comment', $comment_id ) ) {
				gma_add_notice( __( 'You do not have permission to edit comment.', 'give-me-answer-lite' ), 'error' );
			}

			if ( strlen( $comment_content ) <= 0 || ! isset( $comment_id ) || ( int )$comment_id <= 0 ) {
				gma_add_notice( __( 'Comment content must not be empty.', 'give-me-answer-lite' ), 'error' );
			} else {
				$commentarr = array(
					'comment_ID'        => $comment_id,
					'comment_content'   => $comment_content
				);

				$intval = wp_update_comment( $commentarr );
				if ( !is_wp_error( $intval ) ) {
					$comment = get_comment( $comment_id );
					exit( wp_safe_redirect( gma_get_question_link( $comment->comment_post_ID ) ) );
				}else {
					gma_add_wp_error_message( $intval );
				}
			}
		}
	}

	public function submit_question() {
		global $gma_options, $gma_general_settings;

		if ( isset( $_POST['gma-question-submit'] ) ) {
			global $gma_current_error;
			$valid_captcha = gma_valid_captcha( 'question' );

			$gma_submit_question_errors = new WP_Error();

			if ( isset( $_POST['_wpnonce'] ) && wp_verify_nonce( esc_html( $_POST['_wpnonce'] ), '_gma_submit_question' ) ) {
				if ( $valid_captcha ) {
					if ( empty( $_POST['question-title'] ) ) {
						gma_add_notice( __( 'You must enter a valid question title.', 'give-me-answer-lite' ), 'error' );
						return false;
					}


					if ( !is_user_logged_in() ) {
						if ( empty( $_POST['_gma_anonymous_email'] ) || !is_email( sanitize_email( $_POST['_gma_anonymous_email'] ) ) ) {
							gma_add_notice( __( 'Missing email information', 'give-me-answer-lite' ), 'error' );
							return false;
						}

						if ( empty( $_POST['_gma_anonymous_name'] ) ) {
							gma_add_notice( __( 'Missing name information', 'give-me-answer-lite' ), 'error' );
							return false;
						}
					}

					$title        = esc_html( $_POST['question-title'] );
					$title_length = strlen( $title );

					// Check length of title
					$err_message = sprintf( __('The title of the question must be between %s and %s characters', 'give-me-answer-lite'),
											$gma_general_settings['question']['min-length'] ,
											$gma_general_settings['question']['max-length'] );
					if ( $title_length < $gma_general_settings[ 'question' ][ 'min-length' ] || $title_length >  $gma_general_settings[ 'question' ][ 'max-length' ]) {
						gma_add_notice( $err_message, 'error' );
						return false;
					}

					$category = isset( $_POST['question-category'] ) ? intval( $_POST['question-category'] ) : 0;
					if ( ! term_exists( $category, 'gma-question_category' ) ) {
						$category = 0;
					}
					
					$tags        = isset( $_POST['question-tag'] ) ? $_POST['question-tag']: '';
					$final_tags  = [];
					if ( $tags ) {
						$tags = stripslashes( $tags );
						$tags = json_decode( $tags );
						if ( ! is_array( $tags ) ) $tags = array( $tags );
						foreach ( $tags as $tag ) {
							$final_tags[] = $tag->value;
						}
						$final_tags = implode(',', $final_tags);
					} else {
						$final_tags = '';
					}

					$content = isset( $_POST['question-content'] ) ?  $_POST['question-content']  : '';
					$content = apply_filters( 'gma_prepare_question_content', $content );

					$user_id = 0;
					$is_anonymous = false;
					if ( is_user_logged_in() ) {
						$user_id = get_current_user_id();
					} else {
						//$post_author_email = $_POST['user-email'];
						if ( isset( $_POST['login-type'] ) && sanitize_text_field( $_POST['login-type'] ) == 'sign-in' ) {
							$user = wp_signon( array(
								'user_login'    => isset( $_POST['user-name'] ) ? esc_html( $_POST['user-name'] ) : '',
								'user_password' => isset( $_POST['user-password'] ) ? esc_html( $_POST['user-password'] ) : '',
							), false );

							if ( ! is_wp_error( $user ) ) {
								global $current_user;
								$current_user = $user;
								get_currentuserinfo();
								$user_id = $user->data->ID;
							} else {
								$gma_current_error = $user;
								return false;
							}
						} elseif ( isset( $_POST['login-type'] ) && sanitize_text_field( $_POST['login-type'] ) == 'sign-up' ) {
							//Create new user
							$users_can_register = get_option( 'users_can_register' );
							if ( isset( $_POST['user-email'] ) && isset( $_POST['user-name-signup'] )
									&& $users_can_register && ! email_exists( $_POST['user-email'] )
										&& ! username_exists( $_POST['user-name-signup'] ) ) {

								if ( isset( $_POST['password-signup'] ) ) {
									$password = esc_html( $_POST['password-signup'] );
								} else {
									$password = wp_generate_password( 12, false );
								}

								$user_id = wp_create_user(
									esc_html( $_POST['user-name-signup'] ),
									$password,
									sanitize_email( $_POST['user-email'] )
								);
								if ( is_wp_error( $user_id ) ) {
									$gma_current_error = $user_id;
									return false;
								}
								wp_new_user_notification( $user_id, $password );
								$user = wp_signon( array(
									'user_login'    => esc_html( $_POST['user-name-signup'] ),
									'user_password' => $password,
								), false );
								if ( ! is_wp_error( $user ) ) {
									global $current_user;
									$current_user = $user;
									get_currentuserinfo();
									$user_id = $user->data->ID;
								} else {
									$gma_current_error = $user;
									return false;
								}
							} else {
								$message = '';
								if ( ! $users_can_register ) {
									$message .= __( 'User Registration was disabled.','give-me-answer-lite' ).'<br>';
								}
								if ( isset( $_POST['user-name'] ) && email_exists( sanitize_email( $_POST['user-email'] ) ) ) {
									$message .= __( 'This email is already registered, please choose another one.','give-me-answer-lite' ).'<br>';
								}
								if ( isset( $_POST['user-name'] ) && username_exists( esc_html( $_POST['user-name'] ) ) ) {
									$message .= __( 'This username is already registered. Please use another one.','give-me-answer-lite' ).'<br>';
								}
								// $gma_current_error = new WP_Error( 'submit_question', $message );
								gma_add_notice( $message, 'error' );
								return false;
							}
						} else {
							$is_anonymous = true;
							$question_author_email = isset( $_POST['_gma_anonymous_email'] ) && is_email( $_POST['_gma_anonymous_email'] ) ? sanitize_email( $_POST['_gma_anonymous_email'] ) : false;
							$question_author_name = isset( $_POST['_gma_anonymous_name'] ) && !empty( $_POST['_gma_anonymous_name'] ) ? sanitize_text_field( $_POST['_gma_anonymous_name'] ) : false;
							$user_id = 0;
						}
					}

					$post_status = ( isset( $_POST['question-status'] ) && esc_html( $_POST['question-status'] ) ) ? $_POST['question-status'] : 'publish';

					//Enable review mode
					global $gma_general_settings;
					if ( isset( $gma_general_settings['enable-review-question'] )
						&& $gma_general_settings['enable-review-question']
						&& $post_status != 'private' && ! current_user_can( 'manage_options' ) ) {
						 $post_status = 'pending';
					}

					$postarr = array(
						'comment_status' => 'open',
						'post_date'      => date( 'Y-m-d H:i:s' ),
						'post_author'    => $user_id,
						'post_content'   => wp_kses_post( $content ),
						'post_status'    => $post_status,
						'post_title'     => $title,
						'post_type'      => 'gma-question',
						'tax_input'      => array(
							'gma-question_category'    => array( $category ),
							'gma-question_tag'         => explode( ',', $final_tags ),
						)
					);

					if ( apply_filters( 'gma-current-user-can-add-question', gma_current_user_can( 'post_question' ), $postarr ) ) {
						$new_question = $this->insert_question( $postarr );
						do_action('gma_after_insert_question',$new_question);
					} else {
						//$gma_submit_question_errors->add( 'submit_question',  __( 'You do not have permission to submit question.', 'give-me-answer-lite' ) );
						gma_add_notice( __( 'You do not have permission to submit question.', 'give-me-answer-lite' ), 'error' );
						$new_question = $gma_submit_question_errors;
					}

					if ( gma_count_notices( 'error' ) == 0 ) {

						if ( $is_anonymous ) {
							update_post_meta( $new_question, '_gma_anonymous_email', $question_author_email );
							update_post_meta( $new_question, '_gma_anonymous_name', $question_author_name );
							update_post_meta( $new_question, '_gma_is_anonymous', true );
						}

						if ( isset( $gma_options['enable-review-question'] ) && $gma_options['enable-review-question'] && !current_user_can( 'manage_options' ) && $post_status != 'private' ) {
							gma_add_notice( __( 'Your question is waiting moderator.', 'give-me-answer-lite' ), 'warning' );
						} else {
							exit( wp_safe_redirect( get_permalink( $new_question ) ) );
						}
					}
				} else {
					gma_add_notice( __( 'Captcha is not correct', 'give-me-answer-lite' ), 'error' );
				}
			} else {
				gma_add_notice( __( 'Are you cheating huh ?!', 'give-me-answer-lite' ), 'error' );
			}
		}
	}

	public function update_question() {
		if ( isset( $_POST['gma-edit-question-submit'] ) ) {
			if ( isset( $_POST['_wpnonce'] ) && wp_verify_nonce( esc_html( $_POST['_wpnonce'] ), '_gma_edit_question' ) ) {

				if ( !gma_current_user_can( 'edit_question', $_POST[ 'question_id' ] ) ) {
					gma_add_notice( __( "You do not have permission to edit question", 'give-me-answer-lite' ), 'error' );
				}

				$question_title = apply_filters( 'gma_prepare_edit_question_title', sanitize_text_field( $_POST['question_title'] ) );
				if ( empty( $question_title ) ) {
					gma_add_notice( __( 'You must enter a valid question title.', 'give-me-answer-lite' ), 'error' );
				}

				$question_id = isset( $_POST['question_id'] ) ? sanitize_text_field( $_POST['question_id'] ) : false;
				$question    = get_post( $question_id );

				if ( !$question_id ) {
					gma_add_notice( __( 'Question ID is required.', 'give-me-answer-lite' ), 'error' );
				}

				if ( 'gma-question' !== get_post_type( $question_id ) ) {
					gma_add_notice( __( 'This post is not question.', 'give-me-answer-lite' ), 'error' );
				}


				$question_content = apply_filters( 'gma_prepare_edit_question_content', $_POST['question_content'] );

				$tags = isset( $_POST['question-tag'] ) ? $_POST['question-tag']: '';
				if ( $tags ) {
					$tags = stripslashes( $tags );
					$tags = json_decode( $tags );
					$tmp = array_map( function( $tag ) {
						return $tag->value;
					}, $tags );
					$tags = implode( ',', $tmp );
				}


				$category = isset( $_POST['question-category'] ) ? intval( $_POST['question-category'] ) : 0;
				if ( ! term_exists( $category, 'gma-question_category' ) ) {
					$category = 0;
				}

				do_action( 'gma_prepare_update_question', $question_id );

				if ( gma_count_notices( 'error' ) > 0 ) {
					return false;
				}

				$args = array(
					'ID' => $question_id,
					'post_content' => $question_content,
					'post_title' => $question_title,
					'tax_input' => array(
						'gma-question_category' => array( $category ),
						'gma-question_tag'		=> explode( ',', $tags )
					),
				);

				$new_question_id = wp_update_post( $args );

				if ( !is_wp_error( $new_question_id ) ) {

					// Edit post tags and categories
					if ( isset( $args['tax_input'] ) ) {
						foreach ( $args['tax_input'] as $taxonomy => $tags ) {
							wp_set_post_terms( $new_question_id, $tags, $taxonomy );
						}
					}	

					$new_post = get_post( $new_question_id );
					do_action( 'gma_update_question', $new_question_id, $question, $new_post );
					wp_safe_redirect( get_permalink( $new_question_id ) );
				} else {
					gma_add_wp_error_message( $new_question_id );
					return false;
				}
			} else {
				gma_add_notice( __( 'Are you cheating huh ?!', 'give-me-answer-lite' ), 'error' );
				return false;
			}
			exit(0);
		}
	}

	public function insert_question( $args ) {
		if ( is_user_logged_in() ) {
			$user_id = get_current_user_id();
		} elseif ( gma_current_user_can( 'post_question' ) ) {
			$user_id = 0;
		} else {
			return false;
		}

		$args = wp_parse_args( $args, array(
			'comment_status' => 'open',
			'post_author'    => $user_id,
			'post_content'   => '',
			'post_status'    => 'pending',
			'post_title'     => '',
			'post_type'      => 'gma-question',
		) );
		
		$args = apply_filters( 'gma_insert_question_args', $args );

		$new_question = wp_insert_post( $args, true );

		if ( ! is_wp_error( $new_question ) ) {

			if ( isset( $args['tax_input'] ) ) {
				foreach ( $args['tax_input'] as $taxonomy => $tags ) {
					wp_set_post_terms( $new_question, $tags, $taxonomy );
				}
			}
			update_post_meta( $new_question, '_gma_status', 'open' );
			update_post_meta( $new_question, '_gma_views', 0 );
			update_post_meta( $new_question, '_gma_votes', 0 );
			update_post_meta( $new_question, '_gma_answers_count', 0 );
			update_post_meta( $new_question, '_gma_created_date', date( 'Y-m-d H:i:s' ) );


			do_action( 'gma_add_question', $new_question, $user_id );
		}
		return $new_question;
	}

    /**
     * Close Question
     *
     * @since 1.0
     * @return void
     */
	public function close_question() {
	    global $gma_general_settings;
        if (isset( $_POST['action'] ) && 'close-question' == strtolower($_POST['action'])) {
            $question_id = isset($_POST['question-id']) && absint($_POST['question-id']) ? absint($_POST['question-id']) : false;
            if ( ! $question_id ) wp_die();
            $reason   = isset($_POST['close-reason']) ? sanitize_text_field($_POST['close-reason']) : '';
            gma_lite()->utility->close_question($question_id, $reason);

            // If possible redirect to questions archive
            if (isset($gma_general_settings['pages']['archive-question'])) {
                wp_safe_redirect(get_permalink($gma_general_settings['pages']['archive-question']));
                exit(0);
            }

            // redirect to question page
            wp_safe_redirect(get_permalink($question_id));

            exit(0);
        }
    }

    /**
     * Open question
     *
     * @since 1.0
     * @return void
     */
    public function open_question() {
        global $gma_general_settings;
        if (isset($_POST['action']) && 'openquestion' == strtolower($_POST['action'])) {
            // Check nonce
            if (! isset($_POST['_wpnonce']) || ! wp_verify_nonce($_POST['_wpnonce'], '_gma_open_question')) {
                wp_die( __('Are you cheating huh ?!', 'give-me-answer-lite') );
            }
            $question_id = isset($_POST['questionid']) && is_numeric($_POST['questionid']) ? absint($_POST['questionid']) : false;
            if ( ! $question_id ) return;
            gma_lite()->utility->open_question($question_id);

            // Redirect to archive questions
            if (isset($gma_general_settings['pages']['archive-question'])) {
                wp_safe_redirect(get_permalink($gma_general_settings['pages']['archive-question']));
                exit(0);
            }

            wp_safe_redirect(get_permalink($question_id));
            exit(0);
        }
    }

	function update_modified_date( $question_id, $modified_date, $modified_date_gmt ) {
		$data = array(
			'ID' => $question_id,
			'post_modified' => $this->timeformat_convert( $modified_date ),
			'post_modified_gmt' => $this->timeformat_convert( $modified_date_gmt ),
		);
		wp_update_post( $data );
	}

	function timeformat_convert( $timestamp ) {
		return date("Y-m-d H:i:s", $timestamp );
	}
}
