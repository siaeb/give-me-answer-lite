<?php

defined( 'ABSPATH' ) || exit;

/**
 * Class GMA_Ajax
 */
class GMA_Ajax {
	public function __construct() {

        //
        add_action( 'wp_ajax_gma-action-delete-comment', [$this, 'delete_comment_without_ajax'] );

		// Ajax flag answer spam
		add_action( 'wp_ajax_gma-action-flag-answer', array( $this, 'flag_answer' ) );

		//Question
		add_action( 'wp_ajax_gma-update-question-status', array( $this, 'update_status' ) );

		// Ajax search and suggest question
		add_action( 'wp_ajax_gma-auto-suggest-search-result', array( $this, 'auto_suggest_for_seach' ) );
		add_action( 'wp_ajax_nopriv_gma-auto-suggest-search-result', array( $this, 'auto_suggest_for_seach' ) );

		add_action( 'wp_ajax_gma-voting-best-answer', [$this, 'vote_best_answer'] );
		add_action( 'wp_ajax_gma-action-vote', [$this, 'action_vote'] );
		add_action( 'wp_ajax_nopriv_gma-action-vote', [$this, 'action_vote'] );
		add_action( 'wp_ajax_gma-ajax-delete-answer', array( $this, 'delete_answer' ));
		add_action( 'wp_ajax_gma-ajax-delete-question', array( $this, 'delete_question' ) );
		add_action( 'wp_ajax_gma-ajax-delete-comment', array( $this, 'delete_comment' ) );
		add_action( 'wp_ajax_gma-ajax-add-comment', [$this,'add_comment'] ) ;
		add_action( 'wp_ajax_nopriv_gma-ajax-add-comment', [$this,'add_comment'] ) ;
		add_action( 'wp_ajax_gma-ajax-new-answer', [$this,'new_answer'] ) ;
		add_action( 'wp_ajax_nopriv_gma-ajax-new-answer', [$this,'new_answer'] ) ;

		// Follow question
		add_action( 'wp_ajax_gma-follow-question', array( $this, 'follow_question' ) );

		// Image upload
		add_action( 'wp_ajax_gma-upload-image', [$this, 'upload_image'] );
		add_action( 'wp_ajax_nopriv_gma-upload-image', [$this, 'upload_image'] );

		// Profile
		add_action( 'wp_ajax_gma-save-profile', [$this, 'save_profile'] );

		// Settings
		add_action( 'wp_ajax_gma-save-voting-settings', [$this, 'save_voting_settings'] );
		add_action( 'wp_ajax_gma-save-admin-mobiles', [$this, 'save_admin_mobiles'] );
		add_action( 'wp_ajax_gma-save-sms-gateway-settings', [$this, 'save_sms_gateway_settings'] );
		add_action( 'wp_ajax_gma-save-notification-settings', [$this, 'save_notification_settings'] );
		add_action( 'wp_ajax_gma-save-avatar-settings', [$this, 'save_avatar_settings'] );
		add_action( 'wp_ajax_gma-save-pages-settings', [$this, 'save_pages_settings'] );
		add_action( 'wp_ajax_gma-save-settings', [$this, 'save_settings'] );

		// Comment
		add_action( 'wp_ajax_gma-get-comment-form', [$this, 'get_comment_form'] );
		add_action( 'wp_ajax_gma-edit-comment', [$this, 'edit_comment'] );

		// Ajax Pagination
        add_action( 'wp_ajax_gma-questions-list', [$this, 'ajax_pagination'] );
        add_action( 'wp_ajax_nopriv_gma-questions-list', [$this, 'ajax_pagination'] );

		// Comment vote
        add_action( 'wp_ajax_gma-vote-comment', [$this, 'vote_comment'] );
        add_action( 'wp_ajax_nopriv_gma-vote-comment', [$this, 'vote_comment'] );

		add_action( 'wp_ajax_gma-get-hidden-comments', [$this, 'get_hidden_comments'] );
		add_action( 'wp_ajax_nopriv_gma-get-hidden-comments', [$this, 'get_hidden_comments'] );

		add_action( 'wp_ajax_gma-get-socials', [$this, 'get_social_urls'] );
		add_action( 'wp_ajax_nopriv_gma-get-socials', [$this, 'get_social_urls'] );

		add_action( 'wp_ajax_gma-publish-question', [$this, 'publish_question'] );

		add_action( 'wp_ajax_gma-upload-profile-picture', [$this, 'upload_profile_picture'] );

		// autocomplete username
        add_action('wp_ajax_nopriv_gma-autocomplete-username', [$this, 'autocomplete_username']);
        add_action('wp_ajax_gma-autocomplete-username', [$this, 'autocomplete_username']);


        // Get user summary
        add_action('wp_ajax_nopriv_gma-get-user-summary', [$this, 'get_user_summary']);
        add_action('wp_ajax_gma-get-user-summary', [$this, 'get_user_summary']);
	}

	function get_user_summary() {
	    if (!isset($_GET['_wpnonce']) || !wp_verify_nonce($_GET['_wpnonce'], '_gma_user_summary_nonce')) {
	        wp_die( __('Are you cheating huh ?!', 'give-me-answer-lite') );
        }
	    if (!isset($_GET['userid']) || ! is_numeric($_GET['userid'])) {
	        wp_die(__('User ID is missing.', 'give-me-answer-lite'));
        }
	    $user = get_user_by('id', absint($_GET['userid']));
	    if ($user) {
            $image  = gma_get_user_image($_GET['userid']);
            $user   = new GMA_User($_GET['userid']);
            ob_start();
            include GMA_DIR . '/templates/user-summary.php';
            $result = ob_get_clean();
            wp_send_json_success($result);
        }
	    wp_send_json_error(__('User not found.', 'give-me-answer-lite'));
    }

	function autocomplete_username() {
	    global $wpdb;

	    $search_string = esc_attr(trim($_GET['name']));
        $search_string = esc_sql( trim( $_GET['name'] ) );

        $query = "
            SELECT u.ID `id`, u.user_login, um1.meta_value, um2.meta_value FROM `{$wpdb->users}` u
            INNER JOIN {$wpdb->usermeta} um1 ON um1.user_id = u.ID and um1.meta_key = 'first_name'
            INNER JOIN {$wpdb->usermeta} um2 ON um2.user_id = u.ID and um2.meta_key = 'last_name'
            WHERE u.user_login LIKE '%{$search_string}%' OR um1.meta_value LIKE '%{$search_string}%' OR um2.meta_value LIKE '%{$search_string}%'
        ";
        $users_found = $wpdb->get_results($query);
        $users_found = array_map(function ($user) {
            return [
                'id'        => $user->id,
                'name'      => $user->user_login,
                'displayname' => gma_user_displayname($user->id),
                'avatar'    => gma_get_user_image($user->id),
                'url'       => gma_get_user_questions_url($user->id),
            ];
            }, $users_found);
        wp_send_json($users_found);
    }

    public function delete_comment_without_ajax() {
        if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( $_GET['_wpnonce'] ), '_gma_delete_comment' ) ) {
            wp_die( __( 'Are you cheating huh?', 'give-me-answer-lite' ) );
        }

        $comment_id = absint( $_GET['comment_id'] );

        if ( ! isset( $comment_id ) ) {
            wp_die( __( 'Comment ID must be showed.', 'give-me-answer-lite' ) );
        }


        if ( !gma_current_user_can( 'delete_comment', $comment_id ) && !gma_current_user_can( 'manage_comment' ) ) {
            wp_die( __( 'You do not have permission to edit comment.', 'give-me-answer-lite' ) );
        }

        $comment = get_comment( $comment_id );
        wp_trash_comment( intval( $comment_id ) );
        exit( wp_safe_redirect( get_permalink( $comment->comment_post_ID ) ) );
    }

	function upload_profile_picture() {
        global $gma_avatar;

		if ( ! isset( $_POST[ '_wpnonce' ] ) || !wp_verify_nonce( $_POST[ '_wpnonce' ] ,'_gma-edit-profile' ) ) {
			wp_die( __('Are you cheating huh ?!', 'give-me-answer-lite') );
		}

		$user_id   = isset( $_POST[ 'user_id' ] ) ? sanitize_text_field( $_POST[ 'user_id' ] ) : '';

		// If current user is not an admin
		if ( ! gma_is_admin( get_current_user_id() ) ) {
            if ( get_current_user_id() != $user_id ) {
                wp_die( __('Are you cheating huh ?!', 'give-me-answer-lite') );
            }
        }

		$picture_url = '';
		if ( $_FILES['picture'] && ! empty( $_FILES[ 'picture' ] )  ) {
			$upload = new GMA_Upload($_FILES[ 'picture' ], 'fa_IR');
			$upload->mime_check = true;
			$upload->allowed = array( 'image/jpg', 'image/jpeg', 'image/png', 'image/bmp', 'image/pjpeg' );
			// maximum length bytes
			$upload->file_max_size = $gma_avatar['max-size-kb'] * 1024;
			$upload->file_auto_rename = true;
			$upload->dir_auto_chmod = true;
			$wp_upload_dir = wp_upload_dir();
			$upload->process( $wp_upload_dir[ 'path' ] );
			if ( $upload->processed ) {
				$picture_url  = $wp_upload_dir[ 'url' ] . '/' . $upload->file_dst_name;
				$picture_path = $wp_upload_dir[ 'path' ] . '/' . $upload->file_dst_name;
			} else {
				wp_send_json_error( $upload->error );
			}
		} else {
			$picture_url  = '';
			$picture_path = '';
		}

		if ( ! empty( $picture_url ) ) {
			// Remove previous picture
			$old_picture_path = get_user_meta( $user_id, 'gma_picture_path', true );
			unlink( $old_picture_path );
		}

		if ( $picture_url ) {
			update_user_meta( $user_id, 'gma_picture_url', $picture_url );
			gma_update_user_meta( $user_id, 'gma_picture_path', $picture_path );
		}

		wp_send_json_success();
    }

	function publish_question() {
	    if ( ! isset( $_POST[ 'questionID' ] ) || ! absint( $_POST[ 'questionID' ] ) ) {
	        wp_die( __('Question ID is missing.', 'give-me-answer-lite') );
        }

	    if ( false == gma_is_admin() ) {
	        wp_die( __('Permission error.', 'give-me-answer-lite') );
        }

	    wp_update_post( [
	        'ID' => $_POST[ 'questionID' ],
            'post_status' => 'publish',
        ] );

	    wp_send_json_success();
    }

	function get_social_urls() {
	    if ( ! isset( $_GET[ 'post' ] ) || ! absint( $_GET['post'] ) ) {
	        wp_die( __('Post ID is missing.', 'give-me-answer-lite') );
        }

	    $quora = get_post( $_GET[ 'post' ] );
	    if ( $quora ) {
	        $question = $quora->post_parent ? get_post( $quora->post_parent ) : $quora;
	        $qurl     = get_permalink( $question->ID );
	        $qtitle   = $question->post_title;

	        // If post is an answer
	        if ( $quora->post_parent ) {
	            $qurl   .= '#answer-' . $quora->ID;
	            $qtitle .= __('Answer to ', 'give-me-answer-lite') . $qtitle;
            }
	        $socials = gma_social_urls( urlencode( $qurl ), $qtitle );
	        $result = [
		        'url'   => $qurl,
		        'urls'  => $socials,
            ];
	        wp_send_json_success( $result );
        }
    }

	function get_hidden_comments() {
	    global $gma_general_settings;

	    if ( ! isset( $_GET['postid'] ) || ! absint( $_GET['postid'] ) ) {
	        wp_die( __('Post ID is missing.', 'give-me-answer-lite') );
        }

	    $post = get_post( $_GET['postid'] );
	    if ( $post ) {
            $comments = get_approved_comments($_GET['postid']);
            $comments = array_slice($comments, $gma_general_settings['comment']['per-page'] );
            $result   = [];
            foreach ( $comments as $comment ) {
	            $result['comments'][] = [
                    'id'         => $comment->comment_ID,
                    'text'       => nl2br( $comment->comment_content ),
                    'date'       => gma_display_date( $comment->comment_date ),
                    'is_owner'   => $post->post_author == $comment->user_id,
                    'is_yours'   => get_current_user_id() == $comment->user_id,
                    'author'         => [
	                    'name'       => gma_user_displayname($comment->user_id),
	                    'url'        => gma_get_author_link( $comment->user_id ),
                        'avatar'     => gma_get_user_image( $comment->user_id ),
                    ],
                    'anonymous'     => $comment->user_id == -1 ? true : false,
                    'voting'        => [
                        'count'     => gma_comment_vote_count( $comment->comment_ID ),
                        'voted'     => gma_is_user_voted_comment($comment->comment_ID, gma_get_current_user_id()) ? true : false,
                    ],
                    'can_delete'    => gma_current_user_can( 'delete_comment', $comment->comment_ID ) || ($comment->user_id == get_current_user_id()),
                    'can_edit'      => gma_current_user_can( 'edit_comment', $comment->comment_ID ) || ($comment->user_id == get_current_user_id()),
                ];
	            $result[ 'logged_in' ]      = is_user_logged_in();
	            $result[ 'anonymous_vote' ] = $gma_general_settings['allow-anonymous-vote'] ? true : false;
	            $result[ 'vote_nonce' ]     = wp_create_nonce( '_gma_cmvote' );
	            $result['nonce']            = wp_create_nonce( '_gma_comment_nonce' );
            }

            wp_send_json_success( $result );
        }
    }


	function ajax_pagination() {
        if ( ! isset( $_GET[ '_wpnonce' ] ) || ! wp_verify_nonce( $_GET['_wpnonce'], '_gma_questions_list' ) ) {
            wp_die(  __('Are you cheating huh ?!', 'give-me-answer-lite') );
        }

	    unset( $_GET[ 'action' ] );

	    $atts            = json_decode(str_replace( '\\', '',  $_GET[ 'vars' ]), true);
        $atts['paged']   = absint( $_GET[ 'paged' ] );

		gma_lite()->template->remove_all_filters( 'the_content' );
		gma_lite()->filter->prepare_archive_posts( $atts );

		global $wp_query;
		$wp_query->query_vars[ 'page' ]          = absint($_GET['paged']);
		$wp_query->query_vars[ 'paged' ]         = absint($_GET['paged']);
		$wp_query->query_vars[ 'max_num_pages' ] = $wp_query->gma_questions->max_num_pages;

		ob_start();

		echo '<div>';
        echo '<div class="gma-questions-list">';
			do_action( 'gma_before_questions_list' );
			if ( gma_has_question() ) :
				while ( gma_has_question() ) : gma_the_question();
					if ( get_post_status() == 'publish' || ( get_post_status() == 'private' && gma_current_user_can( 'edit_question', get_the_ID() ) ) ) :
						gma_load_template( 'content', 'question' );
					endif;
				endwhile;
			else :
				gma_load_template( 'content', 'none' );
			endif;
			do_action( 'gma_after_questions_list' );
        echo '</div>';
        echo '<div class="gma-questions-footer d-flex">';
			gma_question_paginate_link();
        echo '</div>';

		echo '</div>';

		$html = ob_get_clean();

		wp_send_json_success( $html  );
    }


	function edit_comment() {
		if ( ! isset( $_POST[ '_wpnonce' ] ) || ! wp_verify_nonce( $_POST[ '_wpnonce' ], '_gma_ajax_edit_comment' ) ) {
			wp_die( __('Are you cheating huh ?!', 'give-me-answer-lite') );
		}
		$comment_id     = isset( $_POST[ 'commentID' ] )   ? sanitize_text_field( $_POST[ 'commentID' ] ) : '';
		$comment_text   = isset( $_POST[ 'commentText' ] ) ? stripslashes( $_POST[ 'commentText' ] ) : '';
		$comment    = get_comment( $comment_id );
		if ( $comment ) {
			wp_update_comment( [
				'comment_ID' => $comment_id,
				'comment_content' => $comment_text,
			] );
			wp_send_json_success( nl2br( $comment_text ) );
		}
	}

	function get_comment_form() {
		if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( $_GET['_wpnonce'], '_gma_comment_nonce' ) ) {
			wp_die( __('Are you cheating huh ?!', 'give-me-answer-lite') );
		}

		$comment_id = isset( $_GET[ 'commentID' ] ) ? sanitize_text_field( $_GET['commentID'] ) : '';
		$comment    = get_comment( $comment_id );
		if ( $comment ) {
			ob_start();
			?>
				<input type="hidden" class="nonce" value="<?php echo wp_create_nonce( '_gma_ajax_edit_comment' ); ?>">
				<textarea class="form-control" name="comment-text" rows="3" style="width: 100%"><?php echo $comment->comment_content ?></textarea>
				<button class="btn btn-primary save-changes mt-1" data-commentid="<?php echo $comment->comment_ID; ?>"><?php _e('Save changes', 'give-me-answer-lite'); ?></button>
				<button class="btn btn-outline-secondary border-0 cancel-cm-edit mt-1"><?php _e('cancel', 'give-me-answer-lite'); ?></button>
				<script type="text/javascript">
					jQuery( '.save-changes' ).click( function() {
					    var $           = jQuery;
					    var self        = $( this ), selfText = self.html();
					    var commentForm = $( this ).closest( '.gma-comment-form' );
					    var commentMain = $( this ).closest( '.gma-comment' ).find( '.gma-comment-main' );
					    var commentID   = $( this ).data( 'commentid' );
					    var commentText = commentForm.find( '[name=comment-text]' ).val();
					    var nonce       = commentForm.find( '.nonce' ).val();
					    $.ajax( {
						    url: gma.ajax_url,
						    type: 'POST',
						    data: {
						        action: 'gma-edit-comment',
							    commentID: commentID,
							    commentText: commentText,
							    _wpnonce   : nonce,
						    },
						    beforeSend: function() {
							    self.addClass( 'disabled-content' );
							    window.question_answer.showPleaseWait();
						    },
						    success: function( response ) {
								if ( response.success === true ) {
									commentMain.find( 'p' ).html( response.data );
									commentForm.hide();
									commentMain.show();
                                    commentMain.parent().css( { backgroundColor: 'rgb(254, 194, 105)' } );
                                    setTimeout( function() {
                                        commentMain.parent().animate({ backgroundColor: '#fff' }, 1150);
                                    }, 500 );
								} else {
								    window.question_answer.showErrorMessage('', response.data);
								}
						    },
						    complete: function() {
                                self.removeClass( 'disabled-content' );
                                window.question_answer.hidePleaseWait();
						    },
					    } );
					} );
					jQuery( '.cancel-cm-edit' ).click( function() {
                        var $           = jQuery;
					    $( this ).closest( '.gma-comment' ).find( '.gma-comment-form' ).hide();
					    $( this ).closest( '.gma-comment' ).find( '.gma-comment-main' ).show();
					} );
				</script>
			<?php
			wp_send_json_success( ob_get_clean() );
		}
		wp_die(__('Comment not found!', 'give-me-answer-lite'));
	}


	private function parse_settings_data( $data_str, $nonce_action_name, $nonce_field_name = '_wpnonce' ) {
		if ( empty( $_POST[ 'data' ] ) ) {
			wp_die( __('Settings data is required.', 'give-me-answer-lite') );
		}

		$data = [];
		parse_str( $_POST[ 'data' ], $data );

		if ( ! isset( $data[ $nonce_field_name ] ) || !wp_verify_nonce( $data[ $nonce_field_name ], $nonce_action_name ) ) {
			wp_die( __('Are you cheating huh ?!', 'give-me-answer-lite') );
		}

		return $data;
	}

	private function check_nonce( $data, $nonce_action, $nonce_field = '_wpnonce'  ) {
		if ( ! isset( $data[ $nonce_field ] ) || !wp_verify_nonce( $data[ $nonce_field ], $nonce_action ) ) {
			wp_die( __('Are you cheating huh ?!', 'give-me-answer-lite') );
		}
	}

	function save_settings() {
		global $gma_options;
		$data = [];
		parse_str( $_POST[ 'data' ], $data );

		$this->check_nonce( $data, $data[ 'option_page' ] . '-options' );

		// Validate inputs
		if ( isset( $data['gma_options']['tags-per-question'], $data['gma_options']['min-tags-per-question'] )  ) {
		    $mintags = $data['gma_options']['min-tags-per-question'];
		    $maxtags = $data['gma_options']['tags-per-question'];
		    if ( ! gma_lite()->utility->is_digit( $mintags )  ) wp_send_json_error(__('Please enter digit value for minimum tags', 'give-me-answer-lite'));
		    if ( ! gma_lite()->utility->is_digit( $maxtags )  ) wp_send_json_error(__('Please enter digit value for maximum tags', 'give-me-answer-lite'));
		    if ( $mintags > $maxtags ) wp_send_json_error(__('Min tags value must be lower than max tags', 'give-me-answer-lite'));
        }

		update_option( 'gma_options', array_merge( $gma_options, $data[ 'gma_options' ] ) );

		wp_send_json_success();
	}

	function save_pages_settings() {
		global $gma_general_settings;
		$data = $this->parse_settings_data( $_POST[ 'data' ], 'gma-general-settings-options' );

		update_option( 'gma_options', array_merge( $gma_general_settings, $data[ 'gma_options' ] ) );

		wp_send_json_success();
	}

	function save_avatar_settings() {
		$data = $this->parse_settings_data( $_POST[ 'data' ],  'gma-settings-avatar-options' );
		update_option( 'gma_avatar', $data['gma_avatar'] );
		wp_send_json_success($data);
	}

	function save_notification_settings() {
		$data = $this->parse_settings_data( $_POST[ 'data' ],  'gma-sms-settings-notification-options' );
		update_option( 'gma-smsnoti', $data[ 'gma-smsnoti' ] );
		wp_send_json_success( $data );
	}

	function save_sms_gateway_settings() {
		$data = $this->parse_settings_data( $_POST[ 'data' ], 'gma-sms-settings-gateway-options' );
		update_option( 'gma-smsgateway', $data[ 'gma-smsgateway' ] );
		wp_send_json_success( $data );
	}

	function save_admin_mobiles() {
		$data = $this->parse_settings_data( $_POST[ 'data' ], 'gma-sms-settings-options' );
		update_option( 'gma-admin-mobiles', $data[ 'gma-admin-mobiles' ] );
		wp_send_json_success();
	}

	function save_voting_settings() {
		if ( ! isset( $_POST[ 'data' ] ) ) {
			wp_die( __('Settings data is required.', 'give-me-answer-lite') );
		}
		$data = [];
        parse_str( $_POST[ 'data' ], $data );

		update_option( 'gma_options', $data[ 'gma_options' ] );
		wp_send_json_success();
	}

	function follow_question() {
		check_ajax_referer( '_gma_follow_question', 'nonce' );
		if ( ! isset( $_POST['post'] ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid Post', 'give-me-answer-lite' ) ) );
		}
		$question = get_post( intval( $_POST['post'] ) );
		if ( is_user_logged_in() && $question ) {
			global $current_user;
			if ( ! gma_is_followed( $question->ID )  ) {
				do_action( 'gma_follow_question', $question->ID, $current_user->ID );
				add_post_meta( $question->ID, '_gma_followers', $current_user->ID );
				wp_send_json_success( array( 'code' => 'followed', 'text' => 'Unsubscribe', 'followers' => gma_followers_count( $question->ID ) ) );
			} else {
				do_action( 'gma_unfollow_question', $question->ID, $current_user->ID );
				delete_post_meta( $question->ID, '_gma_followers', $current_user->ID );
				wp_send_json_success( array( 'code' => 'unfollowed', 'text' => 'Subscribe', 'followers' => gma_followers_count( $question->ID ) ) );
			}
		}

		wp_send_json_error( array( 'code' => 'not-logged-in' ) );

	}


	function save_profile() {
		if ( ! isset( $_POST[ '_wpnonce' ] ) || !wp_verify_nonce( $_POST[ '_wpnonce' ] ,'_gma-edit-profile' ) ) {
			wp_die( __('Are you cheating huh ?!', 'give-me-answer-lite') );
		}

		$is_user_admin  = current_user_can( 'manage_options' );
		$user_id        = isset( $_POST[ 'user_id' ] ) ? sanitize_text_field( $_POST[ 'user_id' ] ) : '';
		$first_name     = isset( $_POST[ 'firstname' ] ) ? sanitize_text_field( $_POST[ 'firstname' ] ) : '';
		$last_name      = isset( $_POST[ 'lastname' ] ) ? sanitize_text_field( $_POST[ 'lastname' ] ) : '';
		$mobile         = isset( $_POST[ 'mobile' ] ) ? sanitize_text_field( $_POST[ 'mobile' ] ) : '';
		$about          = isset( $_POST[ 'about' ] ) ? sanitize_text_field( $_POST[ 'about' ] ) : '';
		$university     = isset( $_POST[ 'university' ] ) ? sanitize_text_field( $_POST[ 'university' ] ) : '';


		if ( $is_user_admin ) {
			if ( ! $user_id ) $user_id = get_current_user_id();
		} else {
			$user_id = get_current_user_id();
		}

		do_action( 'gma_before_profile_save' );

		update_user_meta( $user_id, 'first_name', $first_name );
		update_user_meta( $user_id, 'last_name', $last_name );
		update_user_meta( $user_id, 'gma_mobile', $mobile );
		update_user_meta( $user_id, 'gma_about', $about );
		update_user_meta( $user_id, 'gma_university', $university );

		// Change password
		$curuser = get_user_by( 'id', get_current_user_id() );
		if ( $curuser && ! empty( $_POST[ 'oldpass' ] )&& ! empty( $_POST[ 'newpass' ] )&& ! empty( $_POST[ 'newpassagain' ] ) ) {

			if ( ! wp_check_password( $_POST[ 'oldpass' ], $curuser->user_pass, $curuser->ID ) ) {
				wp_send_json_error( __('Current password is wrong!', 'give-me-answer-lite') );
			}

			if ( isset( $_POST[ 'newpass' ], $_POST[ 'newpassagain' ] ) && ($_POST[ 'newpass' ] != $_POST[ 'newpassagain' ]) ) {
				wp_send_json_error( __('Password and pasword again is not equal.', 'give-me-answer-lite') );
			}       
			
			wp_set_password( $_POST[ 'newpass' ], $curuser->ID );

		}

		do_action( 'gma_after_profile_save' );

		wp_send_json_success();
	}


    /**
     * @return void
     */
	function new_answer() {
		global $gma_general_settings;

		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( esc_html(  $_POST['nonce'] ), '_gma_add_new_answer' ) ) {
			gma_add_notice( __( 'Are you cheating huh ?!', 'give-me-answer-lite' ), 'error' );
		}

		if ( ! gma_current_user_can( 'post_answer' ) && ! is_user_logged_in() ) {
			wp_die( __('Permission error.', 'give-me-answer-lite') );
		}

		if ( empty( $_POST['answer'] ) ) {
		    gma_json_error_with_captcha( __( 'Answer content is empty', 'give-me-answer-lite' ) );
		}

		if ( empty( $_POST['question'] ) ) {
			wp_die( __( 'Question ID is required.', 'give-me-answer-lite' ) );
		}

		if ( !gma_current_user_can( 'post_answer' ) ) {
			wp_die( __( 'You do not have permission to submit question.', 'give-me-answer-lite' ) );
		}

		if ( ! gma_valid_captcha( 'single-question' ) ) {
			gma_json_error_with_captcha( __( 'Captcha is not correct', 'give-me-answer-lite' ) );
		}


		$question_id  = intval( $_POST['question'] );
		$question     = get_post( $question_id );
		$best_answer  = gma_get_the_best_answer( $question_id );
		$current_user = get_user_by( 'id', get_current_user_id() );
		$answer_title = __( 'Answer for ', 'give-me-answer-lite' ) . get_post_field( 'post_title', $question_id );
		$answ_content = $_POST[ 'answer' ];

		if ( $best_answer && $gma_general_settings[ 'close-has-best-answer-question' ] ) {
			wp_send_json_error( __('You do not have permission to post new answer', 'give-me-answer-lite') );
		}

		$answers = array(
			'comment_status' => 'open',
			'post_date'      => date( 'Y-m-d H:i:s' ),
			'post_author'    => get_current_user_id(),
			'post_content'   => $answ_content,
			'post_title'     => $answer_title,
			'post_type'      => 'gma-answer',
			'post_parent'	 => $question_id,
		);

		if ( ! gma_is_admin() ) {
            $answers['post_status'] = $gma_general_settings['answer']['moderation']  ? 'pending' : 'publish';
        } else {
		    $answers[ 'post_status' ] = 'publish';
        }

		do_action( 'gma_prepare_add_answer' );

		$answers    = apply_filters( 'gma_insert_answer_args', $answers );

        remove_filter('content_save_pre', 'wp_filter_post_kses');
        remove_filter('content_filtered_save_pre', 'wp_filter_post_kses');
		$answer_id  = wp_insert_post( $answers );
        add_filter('content_save_pre', 'wp_filter_post_kses');
        add_filter('content_filtered_save_pre', 'wp_filter_post_kses');
		$new_answer = get_post( $answer_id );


		$is_anonymous     = false;
		$post_author_name = '';
		if ( ! is_user_logged_in() ) {
			$is_anonymous = true;
			if ( isset( $_POST['userEmail'] ) && is_email( $_POST['userEmail'] ) ) {
				$post_author_email = sanitize_email( $_POST['userEmail'] );
			}
			if ( isset( $_POST['userName'] ) && !empty( $_POST['userName'] ) ) {
				$post_author_name = sanitize_text_field( $_POST['userName'] );
			}
		}

		if ( !is_wp_error( $answer_id ) ) {
			if ( ! in_array( strtolower( $answers[ 'post_status' ] ), [ 'draft', 'pending' ] ) ) {
				update_post_meta( $question_id, '_gma_status', 'answered' );
				update_post_meta( $question_id, '_gma_answered_time', time() );
				update_post_meta( $answer_id, '_gma_votes', 0 );
                gma_lite()->utility->update_question_answers_count( $question_id );
                gma_lite()->utility->update_question_date( $question_id );
			}

			if ( $is_anonymous ) {
				update_post_meta( $answer_id, '_gma_is_anonymous', true );

				if ( isset( $post_author_email ) && is_email( $post_author_email ) ) {
					update_post_meta( $answer_id, '_gma_anonymous_email', $post_author_email );
				}

				if ( isset( $post_author_name ) && !empty( $post_author_name ) ) {
					update_post_meta( $answer_id, '_gma_anonymous_name', $post_author_name );
				}
			}

			do_action( 'gma_add_answer', $answer_id, $question_id );

			// Total answers count
			$answers_count   = wp_count_posts( 'gma-answer' );
            $qs_answers_count = gma_question_answers_count( sanitize_text_field( $_POST[ 'question' ] ) );
			$response = [
				'answer' => [
					'id'         => $answer_id,
					'content'    => stripslashes( $answ_content ),
					'author'     => [
					    'id'     => gma_get_current_user_id(),
						'url'    => gma_get_author_link( get_current_user_id() ),
						'name'   => $is_anonymous ? $post_author_name : gma_user_displayname($current_user->ID),
						'image'  => gma_get_user_image( get_current_user_id() ),
					],
					'date'       => gma_display_date( date( 'Y-m-d H:i:s' ) ),
					'delete'     => [
						'cap'    => $is_anonymous ? false : gma_current_user_can( 'delete_question', $answer_id ),
						'url'    => add_query_arg( array( 'action' => 'gma_delete_answer', 'answer_id' => $new_answer->ID, '_wpnonce' => wp_create_nonce('_gma_action_remove_answer_nonce') ), admin_url( 'admin-ajax.php' ) ),
					],
					'edit'       => [
						'cap'    => $is_anonymous ? false : gma_current_user_can( 'edit_answer', $answer_id ),
						'url'    => add_query_arg( array( 'edit' => $new_answer->ID ), get_permalink( gma_get_post_parent_id( $new_answer->ID ) ) ),
					],
					'nonce'      => [
						'vote'          => wp_create_nonce( '_gma_answer_vote_nonce' ),
						'best_answer'   => wp_create_nonce('_gma_vote_best_answer'),
						'delete'        => wp_create_nonce('_gma_action_remove_answer_nonce'),
                        'user_summary'  => wp_create_nonce('_gma_user_summary_nonce'),
					],
                    'best_answer' => [
                        'can_select' => gma_is_admin() || $question->post_author == get_current_user_id(),
                    ],
				],
				'question' => [
					'id'   => $_POST[ 'question' ],
				],
				'total_question_answers' => $qs_answers_count,
				'total_answers'          => $answers_count->publish,
                'answers_title'          => $qs_answers_count . ' ' . _n('Answer', 'Answers', $qs_answers_count, 'give-me-answer-lite'),
                'can_submit_comment'     => $gma_general_settings[ 'comment' ][ 'comment-on-as' ] == 1 ? true : false,
                'captcha'                => [
                    'number1' => mt_rand( 0, 20 ),
                    'number2' => mt_rand( 0, 20 ),
                ],
			];

			wp_send_json_success( $response );
		}

		gma_add_wp_error_message( $answer_id );

	}

	function upload_image() {
		global $gma_general_settings;
		if ( $_FILES['upload'] && ! empty( $_FILES[ 'upload' ] )  ) {

			$max_image_size_kb = absint( $gma_general_settings[ 'max-image-size-kb' ] );


			$upload = new GMA_Upload($_FILES[ 'upload' ], 'fa_IR');
			$upload->mime_check = true;
			$upload->allowed = array( 'image/jpg', 'image/jpeg', 'image/png', 'image/bmp', 'image/pjpeg' );
			// maximum size in bytes
			$upload->file_max_size    = $max_image_size_kb * 1024;
			$upload->file_auto_rename = true;
			$upload->dir_auto_chmod = true;
			$wp_upload_dir = wp_upload_dir();
			$upload->process($wp_upload_dir[ 'path' ]);
			if ( $upload->processed ) {
				$response = [
					'uploaded' => 1,
					'fileName' => $upload->file_dst_name,
					'url'      => trailingslashit( $wp_upload_dir[ 'url' ] ) . $upload->file_dst_name,
				];
				wp_send_json( $response );
			} else {
				$response = [
					'uploaded' => 0,
					'error'    => [
						'message' => $upload->error,
					],
				];
				wp_send_json( $response );
			}
		}
		wp_send_json_error( __('There is no photo to upload' ,'give-me-answer-lite') );
	}

	function vote_best_answer() {
		global $current_user;

		if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], '_gma_vote_best_answer' ) ) {
			wp_die( __( 'Are you cheating huh ?!', 'give-me-answer-lite' ) );
		}

		if ( ! isset( $_POST['answer'] ) || ! isset( $_POST[ 'type' ] ) ) {
			exit( 0 );
		}

		if ( ! in_array( $_POST[ 'type' ], ['check', 'uncheck'] ) ) {
			exit( 0 );
		}

		$answer_id = intval( $_POST['answer'] );
		$question_id = gma_get_post_parent_id( $answer_id );
		$question = get_post( $question_id );
		if ( $current_user->ID == $question->post_author || gma_is_admin() ) {
			if ( $_POST[ 'type' ] == 'check' ) {
				do_action( 'gma_vote_best_answer', $answer_id );
				update_post_meta( $question_id, '_gma_best_answer', $answer_id );
				update_post_meta( $question_id, '_gma_best_answer_by', get_current_user_id() );
				update_post_meta( $question_id, '_gma_best_answer_date', date( 'Y-m-d H:i:s' ) );
			} else {
				do_action( 'gma_unvote_best_answer', $answer_id );
				delete_post_meta( $question_id, '_gma_best_answer' );
				delete_post_meta( $question_id, '_gma_best_answer_by' );
				delete_post_meta( $question_id, '_gma_best_answer_date' );
			}
			wp_send_json_success();
		}
		wp_die('error');
	}

	function vote_comment() {
		global $gma_general_settings;

	    if ( ! isset( $_POST[ '_wpnonce' ] ) || ! wp_verify_nonce( $_POST['_wpnonce'],'_gma_cmvote' ) ) {
	        wp_die( __('Are you cheating, huh?', 'give-me-answer-lite') );
        }

	    if ( ! isset( $_POST[ 'commentID' ] ) || ! absint( $_POST[ 'commentID' ] ) ) {
	        wp_die( __('Comment ID is missing', 'give-me-answer-lite') );
        }

	    $comment = get_comment( absint( $_POST[ 'commentID' ] ) );

	    if ( $comment ) {
		    //vote
		    $gma_user_vote_id = '';
		    if ( is_user_logged_in( ) ) {
			    $gma_user_vote_id = get_current_user_id();
		    } else {
			    if(isset($gma_general_settings['allow-anonymous-vote']) && $gma_general_settings['allow-anonymous-vote']){
				    $gma_user_vote_id = gma_get_current_user_session();
			    }
		    }

		    if ( $comment->user_id == $gma_user_vote_id ) {
		        wp_send_json_error( __('You can\'t vote for your comment', 'give-me-answer-lite') );
            }

		    if ( $gma_user_vote_id == '' ) {
		        wp_die( __('You aren\'t allowed voted for this comment', 'give-me-answer-lite') );
            }

		    $votes = get_comment_meta( $comment->comment_ID, '_gma_votes_log', true );

		    //remove vote serialize
		    $data_votes = @unserialize($votes);
		    if ( $data_votes !== false ) {
			    $votes = $data_votes;
		    }

		    if( !$votes || !is_array($votes) ){
			    $votes = array();
		    }

		    if ( isset( $votes[ $gma_user_vote_id ] ) ) {
			    unset( $votes[ $gma_user_vote_id ] );
		    } else {
			    $votes[ $gma_user_vote_id ] = 1;
		    }

		    do_action( 'gma_before_vote_comment', $comment->comment_ID, $gma_user_vote_id );

		    update_comment_meta( $comment->comment_ID, '_gma_votes_log', $votes);

		    // Update vote point
		    gma_update_comment_vote_count( $comment->comment_ID );

		    do_action( 'gma_after_vote_comment', $comment->comment_ID, $gma_user_vote_id, gma_is_user_voted_comment( $comment->comment_ID, $gma_user_vote_id ) );

		    $point = gma_comment_vote_count( $comment->comment_ID );

		    $result = [
		       'vote'  => $point,
		       'voted' => gma_is_user_voted_comment( $comment->comment_ID, $gma_user_vote_id ),
            ];

		    wp_send_json_success( $result );
        }
	    wp_die( 'Comment not found!' );
    }

	function action_vote() {
		$result = array(
			'error_code'    => 'authorization',
			'error_message' => __( 'Are you cheating, huh?', 'give-me-answer-lite' ),
		);

		$vote_for = isset( $_POST['vote_for'] ) && sanitize_text_field( $_POST['vote_for'] ) == 'question'
			? 'question' : 'answer';

		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( $_POST['nonce'] ), '_gma_'.$vote_for.'_vote_nonce' ) ) {
			wp_send_json_error( $result );
		}

		if ( ! isset( $_POST[ 'post' ] ) ) {
			$result['error_code']       = 'missing ' . $vote_for;
			$result['error_message']    = __( 'What '.$vote_for.' are you looking for?', 'give-me-answer-lite' );
			wp_send_json_error( $result );
		}

		$post_id = sanitize_text_field( $_POST[ 'post' ] );
		$point = isset( $_POST['type'] ) && sanitize_text_field( $_POST['type'] ) == 'up' ? 1 : -1;

		//vote
		$gma_user_vote_id = '';
		if ( is_user_logged_in( ) ) {
			global $current_user;
			$gma_user_vote_id = $current_user->ID;
		}else{
			global $gma_general_settings;
			if(isset($gma_general_settings['allow-anonymous-vote']) && $gma_general_settings['allow-anonymous-vote']){
				$gma_user_vote_id = gma_get_current_user_session();
			}
		}


		$post = get_post( $post_id );
		if ( $post->post_author == get_current_user_id() ) {
			wp_send_json_error([
			   'error_message' => __('You can not vote your content.', 'give-me-answer-lite'),
            ]);
		}

		if ($gma_user_vote_id != '' ){
			if ( ! gma_is_user_voted( $post_id, $point, $gma_user_vote_id ) ) {
				$votes = get_post_meta( $post_id, '_gma_votes_log', true );

				//remove vote serialize
				$data_votes = @unserialize($votes);
				if ($data_votes !== false) {
					$votes = $data_votes;
				}

				if(!$votes || !is_array($votes)){
					$votes = array();
				}

				if ( isset( $votes[ $gma_user_vote_id ] ) ) {

					if ( ( $votes[$gma_user_vote_id] == 1 && $point == -1 ) ) {
						unset( $votes[ $gma_user_vote_id ] );
					} else if ( ( $votes[$gma_user_vote_id] == -1 && $point == 1 ) ) {
						unset( $votes[ $gma_user_vote_id ] );
					}

				} else {
					$votes[ $gma_user_vote_id ] = $point;
				}


				//update
				do_action( 'gma_vote_'.$vote_for, $post_id, ( int ) $point );

				do_action( 'gma_vote', $post_id, $gma_user_vote_id, $point );

				update_post_meta( $post_id, '_gma_votes_log', $votes);

				// Update vote point
				gma_update_vote_count( $post_id );

				$total_points = gma_vote_count( $post_id );

				wp_send_json_success(
                    array(
                        'vote'    => gma_format_point( $total_points ),
                        'is_vote' => gma_is_user_voted( $post_id, $point, $gma_user_vote_id ),
                    )
                );
			} else {
				$result['error_code'] = 'voted';
				$result['error_message'] = __( 'You voted for this ' . $vote_for, 'give-me-answer-lite' );
				wp_send_json_error( $result );
			}
		}else{
			$result['error_code'] = 'anonymous';
			$result['error_message'] = __( 'You aren\'t allowed voted for this ' . $vote_for, 'give-me-answer-lite' );
			wp_send_json_error( $result );
		}
	}

	function delete_comment() {
		if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( $_POST['_wpnonce'] ), '_gma_comment_nonce' ) ) {
			wp_die( __( 'Are you cheating huh ?!', 'give-me-answer-lite' ) );
		}

		if ( ! isset( $_POST[ 'commentID' ] ) ) {
			wp_die( __('What are you looking for ?', 'give-me-answer-lite') );
		}


		$comment = get_comment( intval( $_POST[ 'commentID' ] ) );
		if ( ! $comment ) {
			wp_die( 'Comment not found' );
		}

		if ( ! gma_current_user_can( 'delete_comment' ) &&  $comment->user_id != get_current_user_id() ) {
			wp_die( __('Permission error', 'give-me-answer-lite') );
		}

		wp_delete_comment( intval( $_POST[ 'commentID' ] ) );

		wp_send_json_success( ['total_comments' => number_format( gma_total_comments_count() )] );
	}

	function delete_answer() {
	    global $gma_general_settings;

		if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( $_POST['_wpnonce'] ), '_gma_action_remove_answer_nonce' ) ) {
			wp_die( __( 'Are you cheating huh ?!', 'give-me-answer-lite' ) );
		}

		if ( ! isset( $_POST['answerID'] ) ) {
			wp_die( __( 'Answer ID must be showed.', 'give-me-answer-lite' ) );
		}

		$answer = get_post( $_POST[ 'answerID' ] );
		if ( $answer ) {
			gma_delete_answer( $_POST[ 'answerID' ] );


			// If request is not ajax
			if ( ! gma_lite()->utility->is_ajax_request() ) {
			    wp_redirect(get_permalink($answer->post_parent));
            }

			// For refresh widget
			$total_answers    = gma_total_answers_count();
            $total_qu_answers = gma_get_answer_count( $answer->post_parent );

            $need_refresh = false;
			if ( false == $gma_general_settings['show-all-answers-on-single-question-page'] &&
			     $total_qu_answers > $gma_general_settings['answer-per-page'] )
			{
			    $need_refresh = true;
            }


			$response = [
			    'need_refresh'         => $need_refresh,
				'answers_count'        => $total_qu_answers,
				'total_answers'        => $total_answers,
				'total_answers_format' => number_format( $total_answers ),
                'answers_title'        => $total_qu_answers . ' ' . _n('Answer', 'Answers', $total_qu_answers, 'give-me-answer-lite'),
			];

			wp_send_json_success( $response );
		}

		wp_die( __('Answer not found !', 'give-me-answer-lite') );
	}

	function delete_question() {
		global $gma_general_settings;
		if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( $_POST['_wpnonce'] ), '_gma_action_remove_question_nonce' ) ) {
			wp_die( __( 'Are you cheating huh ?!', 'give-me-answer-lite' ) );
		}

		if ( ! isset( $_POST['question_id'] ) ) {
			wp_die( __( 'Question ID is required.', 'give-me-answer-lite' ), 'error' );
		}

		if ( 'gma-question' !== get_post_type( intval( $_POST['question_id'] ) ) ) {
			wp_die( __( 'This post is not question.', 'give-me-answer-lite' ) );
		}

		if ( !gma_current_user_can( 'delete_answer', $_POST[ 'question_id' ] ) ) {
			wp_die( __( 'You do not have permission to delete this post.', 'give-me-answer-lite' ) );
		}


		$id = wp_delete_post( intval( $_POST['question_id'] ) );

		if ( is_wp_error( $id ) ) {
			wp_die( __('Error deleting question', 'give-me-answer-lite') );
		}

		do_action( 'gma_delete_question', intval( $_POST['question_id'] ) );

		$url = home_url();
		if ( isset( $gma_general_settings['pages']['archive-question'] ) ) {
			$url = get_permalink( $gma_general_settings['pages']['archive-question'] );
		}

		wp_send_json_success( [ 'list_questions' => $url ] );
	}

	function add_comment() {

		global $gma_general_settings;

		if ( ! isset( $_POST[ 'postID' ] ) ) {
			wp_die( __('Post ID is required.', 'give-me-answer-lite') );
		}

		if ( ! isset( $_POST[ 'comment' ] ) ) {
			wp_die( __('Comment text is empty', 'give-me-answer-lite') );
		}

		if ( ! gma_current_user_can( 'post_comment' ) ) {
			wp_die( __( 'You can\'t post comment', 'give-me-answer-lite'  ) );
		}

		// check minlength of comment
		if ( strlen( $_POST[ 'comment' ] ) < $gma_general_settings[ 'comment' ][ 'min-length' ] ) {
			$err_message = sprintf( __('Minimum comment length is %s character(s)', 'give-me-answer-lite'), $gma_general_settings[ 'comment' ][ 'min-length' ] );
			wp_send_json_error( $err_message );
		}

		$comment_content = isset( $_POST['comment'] ) ? $_POST['comment'] : '';
		$comment_content = apply_filters( 'gma_pre_comment_content', $comment_content );

		if ( empty( $comment_content ) ) {
			wp_die( __( 'Please enter your comment content', 'give-me-answer-lite' ) );
		}

		$current_user = get_user_by('id', get_current_user_id() );


		$args = array(
			'comment_post_ID'   => intval( $_POST['postID'] ),
			'comment_content'   => $comment_content,
			'comment_parent'    => 0,
			'comment_type'		=> 'gma-comment',
            'comment_date'      => date( 'Y-m-d H:i:s' ),
 		);

		if ( is_user_logged_in() ) {
			$args['user_id']        = $current_user->ID;
			$args['comment_author'] = $current_user->display_name;
		} else {

			if ( ! isset( $_POST['email'] ) || ! sanitize_email( $_POST['email'] ) ) {
				wp_die( __( 'Missing email information', 'give-me-answer-lite' ) );
			}

			if ( ! isset( $_POST['name'] ) || empty( $_POST['name'] ) ) {
				wp_die( __( 'Missing name information', 'give-me-answer-lite' ) );
			}

			$args['comment_author']       = isset( $_POST['name'] ) ? sanitize_text_field( $_POST['name'] ) : 'Anonymous';
			$args['comment_author_email'] = sanitize_email(  $_POST['email'] );
			$args['comment_author_url']   = isset( $_POST['url'] ) ? esc_url( $_POST['url'] ) : '';
			$args['user_id']              = -1;
		}

		$comment_id = wp_insert_comment( $args );
		$comment    = get_comment( $comment_id );
		$client_id  = isset( $_POST['clientId'] ) ? sanitize_text_field( $_POST['clientId'] ) : false;
		do_action( 'gma_add_comment', $comment_id, $client_id );

        $question_id = gma_get_question_from_answer_id( $_POST[ 'postID' ] );
        $question    = get_post( $question_id );

		$response = [
			'id'             => $comment_id,
			'text'           => nl2br( $comment->comment_content ),
			'date'           => str_replace(' ', 'T', $comment->comment_date),
			'date_fa'        => gma_display_date( $comment->comment_date ),
			'nonce'          => wp_create_nonce( '_gma_comment_nonce' ),
			'author'         => [
				'name'       => is_user_logged_in() ? gma_user_displayname($current_user->ID) : sanitize_text_field( $_POST[ 'name' ] ),
				'url'        => gma_get_author_link( $current_user->ID ),
                'avatar'     => gma_get_user_image( $args['user_id'] ),
			],
			'edit'           => add_query_arg( array( 'comment_edit' => $comment->comment_ID ), home_url() ),
			'delete'         => wp_nonce_url( add_query_arg( array( 'action' => 'gma-action-delete-comment', 'comment_id' => $comment->comment_ID ), admin_url( 'admin-ajax.php' ) ), '_gma_delete_comment' ),
			'is_anonymous'   => is_user_logged_in() ? 0 : 1,
			'total_comments' => number_format( gma_total_comments_count() ),
            'owner'          => $question->post_author == $comment->user_id,
		];


		wp_send_json_success( $response );
	}


	function gma_unvote_best_answer( $answer_id ) {
		$answer = get_post( $answer_id );
		gma_lite()->user_points->vote_best_select( get_current_user_id(), 'down' );
		gma_lite()->user_points->vote_best_selected( $answer->post_author, 'down' );
	}

	function gma_vote_best_answer( $answer_id ) {
		$answer = get_post( $answer_id );
		gma_lite()->user_points->vote_best_select( get_current_user_id(), 'up' );
		gma_lite()->user_points->vote_best_selected( $answer->post_author, 'up' );
	}

	public function flag_answer() {
		if ( ! isset( $_POST['wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( $_POST['wpnonce'] ), '_gma_action_flag_answer_nonce' ) ) {
			wp_send_json_error( array( 'message' => __( 'Are you cheating huh ?!', 'give-me-answer-lite' ) ) );
		}
		if ( ! isset( $_POST['answer_id'] ) ) {
			wp_send_json_error( array( 'message' => __( 'Missing id of answer', 'give-me-answer-lite' ) ) );
		}

		global $current_user;
		$answer_id = intval( $_POST['answer_id'] );
		$flag = get_post_meta( $answer_id, '_flag', true );
		if ( ! $flag ) {
			$flag = array();
		} else {
			$flag = unserialize( $flag );
		}
		// _flag[ user_id => flag_bool , ...]
		if ( gma_is_user_flag( $answer_id, $current_user->ID ) ) {
			//unflag
			$flag[$current_user->ID] = $flag_score = 0;
		} else {
			$flag[$current_user->ID] = $flag_score = 1;

		}
		$flag = serialize( $flag );
		update_post_meta( $answer_id, '_flag', $flag );
		wp_send_json_success( array( 'status' => $flag_score ) );
	}

	public function update_status() {

	    if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( $_POST['nonce'] ), '_gma_update_question_status_nonce' ) ) {
            wp_die( __('Are you cheating huh ?!', 'give-me-answer-lite') );
		}

		if ( ! isset( $_POST['question'] ) ) {
			wp_die( 0 );
		}

		if ( ! isset( $_POST['status'] ) || ! in_array( sanitize_text_field( $_POST['status'] ), array( 'open', 're-open', 'resolved', 'closed', 'pending' ) ) ) {
			wp_die( 0 );
		}

		global $current_user;
		$question_id = intval( $_POST['question'] );
		$question = get_post( $question_id );

		if ( gma_current_user_can( 'edit_question' ) || $current_user->ID == $question->post_author ) {
			$status = sanitize_text_field( $_POST['status'] );
			update_post_meta( $question_id, '_gma_status', $status );
			if ( $status == 'resolved' ) {
				update_post_meta( $question_id, '_gma_resolved_time', time() );
			}
		} else {
			wp_send_json_error( array(
				'message'   => __( 'You do not have permission to edit question status', 'give-me-answer-lite' )
			) );
		}
	}

	public function auto_suggest_for_seach(){
		if ( ! isset( $_POST['nonce'])  ) {
			wp_send_json_error( array( array( 
				'error' => 'sercurity',
				'message' => __( 'Are you cheating huh ?!', 'give-me-answer-lite' )
			) ) );
		}
		check_ajax_referer( '_gma_filter_nonce', 'nonce' );


		if ( ! isset( $_POST['title'] ) ) {
			wp_send_json_error( array( array( 
				'error' => 'empty title',
				'message' => __( 'Not Found!!!', 'give-me-answer-lite' ),
			) ) );
		}

		$status = 'publish';
		if ( is_user_logged_in() ) {
			$status = array( 'publish', 'private' );
		}

		$search = sanitize_text_field( $_POST['title'] );
		$args_query = array(
			'post_type'			=> 'gma-question',
			'posts_per_page'	=> 6,
			'post_status'		=> $status,
		);
		preg_match_all( '/#\S*\w/i', $search, $matches );
		if ( $matches && is_array( $matches ) && count( $matches ) > 0 && count( $matches[0] ) > 0 ) {
			$args_query['tax_query'][] = array(
				'taxonomy' => 'gma-question_tag',
				'field' => 'slug',
				'terms' => $matches[0],
				'operator'  => 'IN',
			);
			$search = preg_replace( '/#\S*\w/i', '', $search );
		}
		$args_query['s'] = $search;
		$args_query = apply_filters( 'gma_prepare_search_query_args', $args_query );
		$query = new WP_Query( $args_query );
		if ( ! $query->have_posts() ) {
			global $current_search;
			$current_search = $search;
			add_filter( 'posts_where' , array( $this, 'posts_where_suggest' ) );
			unset( $args_query['s'] );
			$query = new WP_Query( $args_query );
			remove_filter( 'posts_where' , array( $this, 'posts_where_suggest') );
		}
		$results = array();
		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();
				$results[] = array(
					'title' => get_post_field( 'post_title', get_the_ID() ),
					'url' => get_permalink( get_the_ID() )
				);
			}
			wp_reset_query();
			wp_send_json_success( $results );
		} else {
			wp_reset_query();
			wp_send_json_error( array( array( 'error' => 'not found', 'message' => __( 'Not Found!!!', 'give-me-answer-lite' ) ) ) );
		}
	}

	public function posts_where_suggest( $where ) {
		global $current_search;
		$first = true;
		$s = explode( ' ', $current_search );
		if ( count( $s ) > 0 ) {
			$where .= ' AND (';
			foreach ( $s as $w ) {
				if ( ! $first ) {
					$where .= ' OR ';
				}
				$where .= "post_title REGEXP '".preg_quote( $w )."'";
				$first = false;
			}
			$where .= ' ) ';
		}
		return $where;
	}
}