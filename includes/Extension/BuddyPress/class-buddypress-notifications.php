<?php


class GMA_BP_Notifications {

    public function __construct() {
        add_action( 'init', [$this,'mark_notifications'], 10 );
        add_filter( 'bp_notifications_get_registered_components', [$this, 'get_registered_components'] );
        add_filter( 'bp_notifications_get_notifications_for_user', [$this, 'format_notifications'], 11, 7 );
        add_action( 'gma_add_answer', [$this, 'add_answer_notification'], 99, 2 );
        add_action( 'gma_add_comment', [$this, 'add_comment_notification'], 99, 2 );
        add_action( 'gma_vote_best_answer', [$this, 'add_best_answer_notification'] );
        add_action( 'gma_unvote_best_answer', [$this, 'remove_best_answer_notification'] );
        add_action( 'gma_vote', [$this, 'vote_notification'], 11, 3 );
    }

    function mark_notifications() {

        if (!isset($_GET['action'])) return;

        switch (strtolower($_GET['action'])) {
            case 'bp_gma_mark_new_answer_read':
                $this->mark_new_answer_read();
                break;
            case 'bp_gma_mark_answer_new_comment_read':
                $this->mark_new_answer_comment_read();
                break;
            case 'bp_gma_mark_question_new_comment_read':
                $this->mark_question_new_comment_read();
                break;
            case 'bp_gma_mark_best_answer_read':
                $this->mark_selected_best_answer_as_read();
                break;
        }

    }

    function get_registered_components( $component_names = array() ) {
        // Force $component_names to be an array
        if ( ! is_array( $component_names ) ) {
            $component_names = array();
        }

        array_push( $component_names, 'give-me-answer-lite' );

        return $component_names;
    }

    function format_notifications( $action, $item_id, $secondary_item_id, $total_items, $format = 'string',$action_name, $component_name ) {

        $allowed_actions = [
            'gma_new_answer_reply',
            'gma_new_answer_comment',
            'gma_question_new_comment',
            'gma_vote_up',
            'gma_vote_down',
            'gma_best_answer'
        ];

        if (false == in_array($action_name, $allowed_actions)) {
            return $action;
        }

        // New answer notifications
        if ( 'gma_new_answer_reply' === $action_name ) {
            $answer = get_post( $item_id );
            if( !$answer ){
                return __('Post not found!', 'give-me-answer-lite');
            }

            $gma_notif_title = get_the_title( $answer->post_parent );
            $gma_notif_link = wp_nonce_url( add_query_arg( array( 'action' => 'bp_gma_mark_new_answer_read', 'question_id' => $answer->post_parent, 'answer_id' => $answer->ID ), get_permalink( $answer->post_parent ) ), 'bp_gma_mark_new_answer_' . $answer->ID );
            $gma_notif_title_attr  = __( 'Question Replies', 'give-me-answer-lite' );
            $gma_notif_label = apply_filters('bp_gma_label_notification', __('GMA: ','give-me-answer-lite'));

            if ( (int) $total_items > 1 ) {
                $text   = sprintf( $gma_notif_label .__( 'You have %d new replies', 'give-me-answer-lite' ), (int) $total_items );
                $filter = 'bp_gma_multiple_new_subscription_notification';
            } else {
                if ( !empty( $secondary_item_id ) ) {
                    $text = sprintf( $gma_notif_label .__( 'You have %d new reply to %2$s from %3$s', 'give-me-answer-lite' ), (int) $total_items, $gma_notif_title, bp_core_get_user_displayname( $secondary_item_id ) );

                } else {
                    $text = sprintf( $gma_notif_label .__( 'You have %d new reply to %s', 'give-me-answer-lite' ), (int) $total_items, $gma_notif_title );

                }
                $filter = 'bp_gma_single_new_subscription_notification';
            }

            // WordPress Toolbar
            if ( 'string' === $format ) {
                $return = apply_filters( $filter, '<a href="' . esc_url( $gma_notif_link ) . '" title="' . esc_attr( $gma_notif_title_attr ) . '">' . esc_html( $text ) . '</a>', (int) $total_items, $text, $gma_notif_link );

                // Deprecated BuddyBar
            } else {
                $return = apply_filters( $filter, array(
                    'text' => $text,
                    'link' => $gma_notif_link
                ), $gma_notif_link, (int) $total_items, $text, $gma_notif_title );
            }

            do_action( 'bp_gma_format_buddypress_notifications', $action, $item_id, $secondary_item_id, $total_items );
            return $return;
        }

        if ( 'gma_new_answer_comment' === $action_name ) {
            $comment = get_comment( $item_id );
            $answer  = get_post($comment->comment_post_ID);
            if ( ! $comment ) {
                return __('Comment not found !', 'give-me-answer-lite');
            }
            $answer                 = get_post( $comment->comment_post_ID );
            $gma_notif_title        = __('your answer', 'give-me-answer-lite');
            $gma_notif_link         = wp_nonce_url( add_query_arg( array( 'action' => 'bp_gma_mark_answer_new_comment_read', 'question_id' => $answer->post_parent, 'answer_id' => $answer->ID, 'comment_id' => $comment->comment_ID ), get_permalink( $answer->post_parent ) ), 'bp_gma_mark_answer_new_comment_' . $comment->comment_ID);
            $gma_notif_title_attr   = __( 'Answer Comments', 'give-me-answer-lite' );
            $gma_notif_label        = apply_filters('bp_gma_label_notification', __('GMA: ','give-me-answer-lite'));

            if ( (int) $total_items > 1 ) {
                $text   = sprintf( $gma_notif_label .__( 'You have %d new comments', 'give-me-answer-lite' ), (int) $total_items );
                $filter = 'bp_gma_multiple_new_subscription_notification';
            } else {
                if ( !empty( $secondary_item_id ) ) {
                    $text = sprintf( $gma_notif_label .__( 'You have %d new comment to %2$s from %3$s', 'give-me-answer-lite' ), (int) $total_items, $gma_notif_title, bp_core_get_user_displayname( $secondary_item_id ) );

                } else {
                    $text = sprintf( $gma_notif_label .__( 'You have %d new comment to %s', 'give-me-answer-lite' ), (int) $total_items, $gma_notif_title );

                }
                $filter = 'bp_gma_single_new_subscription_notification';
            }

            // WordPress Toolbar
            if ( 'string' === $format ) {
                $return = apply_filters( $filter, '<a href="' . esc_url( $gma_notif_link ) . '" title="' . esc_attr( $gma_notif_title_attr ) . '">' . esc_html( $text ) . '</a>', (int) $total_items, $text, $gma_notif_link );
            } else {
                $return = apply_filters( $filter, array('text' => $text, 'link' => $gma_notif_link), $gma_notif_link, (int) $total_items, $text, $gma_notif_title );
            }

            do_action( 'bp_gma_format_buddypress_notifications', $action, $item_id, $secondary_item_id, $total_items );
            return $return;
        }

        if ( 'gma_question_new_comment' === $action_name ) {
            $comment = get_comment( $item_id );
            if ( ! $comment ) {
                return __('Comment not found !', 'give-me-answer-lite');
            }
            $question               = get_post( $comment->comment_post_ID );
            $gma_notif_title        = __('your question', 'give-me-answer-lite');
            $gma_notif_link         = wp_nonce_url( add_query_arg( array( 'action' => 'bp_gma_mark_question_new_comment_read', 'question_id' => $question->ID, 'comment_id' => $comment->comment_ID ), get_permalink( $comment->comment_post_ID ) ), 'bp_gma_mark_question_new_comment_' . $comment->comment_ID);
            $gma_notif_title_attr   = __( 'Question Comments', 'give-me-answer-lite' );
            $gma_notif_label        = apply_filters('bp_gma_label_notification', __('GMA: ','give-me-answer-lite'));

            if ( (int) $total_items > 1 ) {
                $text   = sprintf( $gma_notif_label .__( 'You have %d new comments', 'give-me-answer-lite' ), (int) $total_items );
                $filter = 'bp_gma_multiple_new_subscription_notification';
            } else {
                if ( !empty( $secondary_item_id ) ) {
                    $text = sprintf( $gma_notif_label .__( 'You have %d new comment to %2$s from %3$s', 'give-me-answer-lite' ), (int) $total_items, $gma_notif_title, bp_core_get_user_displayname( $secondary_item_id ) );

                } else {
                    $text = sprintf( $gma_notif_label .__( 'You have %d new comment to %s', 'give-me-answer-lite' ), (int) $total_items, $gma_notif_title );

                }
                $filter = 'bp_gma_single_new_subscription_notification';
            }

            // WordPress Toolbar
            if ( 'string' === $format ) {
                $return = apply_filters( $filter, '<a href="' . esc_url( $gma_notif_link ) . '" title="' . esc_attr( $gma_notif_title_attr ) . '">' . esc_html( $text ) . '</a>', (int) $total_items, $text, $gma_notif_link );
            } else {
                $return = apply_filters( $filter, array('text' => $text, 'link' => $gma_notif_link), $gma_notif_link, (int) $total_items, $text, $gma_notif_title );
            }

            do_action( 'bp_gma_format_buddypress_notifications', $action, $item_id, $secondary_item_id, $total_items );
            return $return;
        }

        if ( 'gma_best_answer' === $action_name ) {
            $answer = get_post( $item_id );
            if ( ! $answer ) {
                return __('Answer not found !', 'give-me-answer-lite');
            }
            $gma_notif_link         = wp_nonce_url( add_query_arg( array( 'action' => 'bp_gma_mark_best_answer_read', 'answer_id' => $answer->ID), get_permalink( $answer->post_parent ) ), 'bp_gma_mark_best_answer_' . $answer->ID);
            $gma_notif_title_attr   = __( 'Best Answers', 'give-me-answer-lite' );
            $gma_notif_label        = apply_filters('bp_gma_label_notification', __('GMA: ','give-me-answer-lite'));

            if ( (int) $total_items > 1 ) {
                $text   = sprintf( $gma_notif_label .__( '%d of your answers selected as best.', 'give-me-answer-lite' ), (int) $total_items );
                $filter = 'bp_gma_multiple_new_subscription_notification';
            } else {
                $text = sprintf( $gma_notif_label .__( 'Your answer selected as best.', 'give-me-answer-lite' ), (int) $total_items);
                $filter = 'bp_gma_single_new_subscription_notification';
            }

            // WordPress Toolbar
            if ( 'string' === $format ) {
                $return = apply_filters( $filter, '<a href="' . esc_url( $gma_notif_link ) . '" title="' . esc_attr( $gma_notif_title_attr ) . '">' . esc_html( $text ) . '</a>', (int) $total_items, $text, $gma_notif_link );
            } else {
                $return = apply_filters( $filter, array('text' => $text, 'link' => $gma_notif_link), $gma_notif_link, (int) $total_items, $text);
            }

            do_action( 'bp_gma_format_buddypress_notifications', $action, $item_id, $secondary_item_id, $total_items );
            return $return;
        }
    }

    function add_answer_notification( $answer_id, $question_id ) {
        $post = get_post( $question_id );
        $answer = get_post( $answer_id );

        if($answer->post_status=='publish' || $answer->post_status=='private'){
            $author_id = $post->post_author;
            bp_notifications_add_notification( array(
                'user_id'           => $author_id,
                'item_id'           => $answer_id,
                'component_name'    => 'give-me-answer-lite',
                'component_action'  => 'gma_new_answer_reply',
                'date_notified'     => bp_core_current_time(),
                'is_new'            => 1,
            ) );
        }
    }

    function add_comment_notification($comment_id, $client_id) {
        $comment = get_comment($comment_id);
        $post    = get_post($comment->comment_post_ID);

        // If post is an answer send notification to question owner
        if ('gma-answer' == $post->post_type) {
            $question = get_post($post->post_parent);

            // For question owner
            if ($question->post_author != get_current_user_id() && $question->post_author != $post->post_author) {
                bp_notifications_add_notification([
                    'user_id'           => $question->post_author,
                    'item_id'           => $comment_id,
                    'component_name'    => 'give-me-answer-lite',
                    'component_action'  => 'gma_question_new_comment',
                    'date_notified'     => bp_core_current_time(),
                    'is_new'            => 1
                ]);
            }

            // For answer owner
            bp_notifications_add_notification([
                'user_id'           => $post->post_author,
                'item_id'           => $comment_id,
                'component_name'    => 'give-me-answer-lite',
                'component_action'  => 'gma_new_answer_comment',
                'date_notified'     => bp_core_current_time(),
                'is_new'            => 1
            ]);
        }


        if ('gma-question' == $post->post_type) {
            if ($post->post_author != get_current_user_id()) {
                // Send notification for answer owner
                bp_notifications_add_notification([
                    'user_id'           => $post->post_author,
                    'item_id'           => $comment_id,
                    'component_name'    => 'give-me-answer-lite',
                    'component_action'  => 'gma_question_new_comment',
                    'date_notified'     => bp_core_current_time(),
                    'is_new'            => 1
                ]);
            }
        }



    }

    function add_best_answer_notification($answer_id) {
        $answer = get_post($answer_id);
        if ($answer->post_author != bp_loggedin_user_id()) {
            bp_notifications_add_notification([
                'user_id'           => $answer->post_author,
                'item_id'           => $answer->ID,
                'component_name'    => 'give-me-answer-lite',
                'component_action'  => 'gma_best_answer',
                'date_notified'     => bp_core_current_time(),
                'is_new'            => 1
            ]);
        }
    }

    function remove_best_answer_notification($answer_id) {
        $answer = get_post($answer_id);
        bp_notifications_delete_notifications_by_item_id($answer->post_author, $answer->ID, 'give-me-answer-lite', 'gma_best_answer');
    }

    function vote_notification($post_id, $gma_user_vote_id, $point) {
        error_log('point : ' . $point);
        $post = get_post($post_id);
        if ( $post ) {

        }
    }

    /**
     * Mark new question answer as read
     *
     * @since 1.0
     * @return void
     */
    private function mark_new_answer_read() {

        if ( !isset( $_GET['answer_id'] ) || !is_numeric($_GET['answer_id']) ) {
            return;
        }

        // Get required data
        $user_id     = bp_loggedin_user_id();
        $answer_id   = intval( $_GET['answer_id'] );
        $question_id = intval( $_GET['question_id'] );

        // Check nonce
        $nonce = $_REQUEST['_wpnonce'];
        if ( ! wp_verify_nonce( $nonce, 'bp_gma_mark_new_answer_' . $answer_id ) ) {
            gma_add_notice( __( "Hello, Are you cheating huh?", 'give-me-answer-lite' ), 'error' );
        } elseif ( !current_user_can( 'edit_user', $user_id ) ) {
            gma_add_notice( __( "You do not have permission to mark notifications for that user.", 'give-me-answer-lite' ), 'error' );
        }

        if ( gma_count_notices( 'error' ) > 0 ) {
            return;
        }else{
            $success = bp_notifications_mark_notifications_by_item_id( $user_id, $answer_id, 'give-me-answer-lite', 'gma_new_answer_reply' );
        }

        if($success){
            wp_redirect(get_permalink($question_id));
            exit();
        }
    }

    /**
     * Mark new answer comment as read
     *
     * @since 1.0
     * @return void
     */
    private function mark_new_answer_comment_read() {
        if (!isset($_GET['question_id']) || !absint($_GET['question_id'])) return;
        if (!isset($_GET['answer_id']) || !absint($_GET['answer_id'])) return;
        if (!isset($_GET['comment_id']) || !absint($_GET['comment_id'])) return;
        $comment = get_comment($_GET['comment_id']);
        if (!isset($_GET['_wpnonce']) || !wp_verify_nonce($_REQUEST['_wpnonce'], 'bp_gma_mark_answer_new_comment_' . $comment->comment_ID)) return;

        bp_notifications_mark_notifications_by_item_id( bp_loggedin_user_id(), $_GET['comment_id'], 'give-me-answer-lite', 'gma_new_answer_comment' );
        wp_redirect(get_permalink($_GET['question_id']) . '#answer-' . $_GET['answer_id']);
        exit();
    }

    /**
     * Mark new question comment as read
     *
     * @since 1.0
     * @return void
     */
    private function mark_question_new_comment_read() {
        if (!isset($_GET['question_id']) || !absint($_GET['question_id'])) return;
        if (!isset($_GET['comment_id']) || !absint($_GET['comment_id'])) return;
        $comment = get_comment($_GET['comment_id']);
        if (!isset($_GET['_wpnonce']) || !wp_verify_nonce($_REQUEST['_wpnonce'], 'bp_gma_mark_question_new_comment_' . $comment->comment_ID)) return;
        bp_notifications_mark_notifications_by_item_id( bp_loggedin_user_id(), $_GET['comment_id'], 'give-me-answer-lite', 'gma_question_new_comment' );
        wp_redirect(get_permalink($_GET['question_id']));
        exit();
    }

    /**
     * Mark answer as best
     *
     * @since 1.0
     * @return void
     */
    private function mark_selected_best_answer_as_read() {
        if (!isset($_GET['answer_id']) || !absint($_GET['answer_id'])) return;
        $answer = get_post($_GET['answer_id']);
        if (!$answer) return;
        if (!isset($_GET['_wpnonce']) || !wp_verify_nonce($_REQUEST['_wpnonce'], 'bp_gma_mark_best_answer_' . $answer->ID)) return;
        bp_notifications_mark_notifications_by_item_id( bp_loggedin_user_id(), $answer->ID, 'give-me-answer-lite', 'gma_best_answer' );
        wp_redirect(get_permalink($answer->post_parent) . '/#answer-' . $answer->ID);
        exit();
    }
}

new GMA_BP_Notifications();