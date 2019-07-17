<?php

defined( 'ABSPATH' ) || exit;


/** 
 * This file was used to include all functions which i can't classify, just use those for support my work
 */

/** 
 * Array
 */
function gma_array_insert( &$array, $element, $position = null ) {
	if ( is_array( $element ) ) {
		$part = $element;
	} else {
		$part = array( $position => $element );
	}

	$len = count( $array );

	$firsthalf = array_slice( $array, 0, $len / 2 );
	$secondhalf = array_slice( $array, $len / 2 );

	$array = array_merge( $firsthalf, $part, $secondhalf );
	return $array;
}

if ( ! function_exists( 'dw_strip_email_to_display' ) ) { 
	/**
	 * Strip email for display in front end
	 * @param  string  $text name
	 * @param  boolean $echo Display or just return
	 * @return string        New text that was stripped
	 */
	function dw_strip_email_to_display( $text, $echo = false ) {
		preg_match( '/( [^\@]* )\@( .* )/i', $text, $matches );
		if ( ! empty( $matches ) ) {
			$text = $matches[1] . '@...';
		}
		if ( $echo ) {
			echo $text;
		}
		return $text;
	}
}  

// CAPTCHA
function gma_valid_captcha( $type ) {
	global $gma_general_settings;

	if ( 'question' == $type && ! gma_is_captcha_enable_in_submit_question() ) {
		return true;
	}

	if ( 'single-question' == $type && ! gma_is_captcha_enable_in_single_question() ) {
		return true;
	}
	
	return apply_filters( 'gma_valid_captcha', false );
}

add_filter( 'gma_valid_captcha', 'gma_recaptcha_check' );
function gma_recaptcha_check( $res ) {
	global $gma_general_settings;
	$type_selected = isset( $gma_general_settings['captcha-type'] ) ? $gma_general_settings['captcha-type'] : 'default';

	$is_old_version = $type_selected == 'google-recaptcha' ? true : false;
	if ( $type_selected == 'default' || $is_old_version ) {
		$number_1 = isset( $_POST['gma-captcha-number-1'] ) ? intval( $_POST['gma-captcha-number-1'] ) : 0;
		$number_2 = isset( $_POST['gma-captcha-number-2'] ) ? intval( $_POST['gma-captcha-number-2'] ) : 0;
		$result = isset( $_POST['gma-captcha-result'] ) ? intval( $_POST['gma-captcha-result'] ) : 0;

		if ( ( $number_1 + $number_2 ) === $result ) {
			return true;
		}

		return false;
	}

	return $res;
}


function gma_json_error_with_captcha($err_msg) {
    global $gma_general_settings;
    wp_send_json_error( [
        'error'     => $err_msg,
        'captcha'   => [
            'status'   => [
               'single-question' => $gma_general_settings['captcha-in-single-question'] ? true : false,
            ],
            'type'     => $gma_general_settings[ 'captcha-type' ],
            'number_1' => mt_rand( 0,20 ),
            'number_2' => mt_rand( 0,20 ),
        ],
    ] );
}

/**
* Get tags list of question
*
* @param int $quetion id of question
* @param bool $echo
* @return string
* @since 1.4.0
*/
function gma_get_tag_list( $question = false, $echo = false ) {
	if ( !$question ) {
		$question = get_the_ID();
	}

	$terms = wp_get_post_terms( $question, 'gma-question_tag' );
	$lists = array();
	if ( $terms ) {
		foreach( $terms as $term ) {
			$lists[] = $term->name;
		}
	}

	if ( empty( $lists ) ) {
		$lists = '';
	} else {
		$lists = implode( ',', $lists );
	}

	if ( $echo ) {
		echo $lists;
	}

	return $lists;
}


function gma_is_front_page() {
	global $gma_general_settings;

	if ( ! $gma_general_settings ) {
		$gma_general_settings = get_option( 'gma_options' );
	}

	if ( !isset( $gma_general_settings['pages']['archive-question'] ) ) {
		return false;
	}

	$page_on_front = get_option( 'page_on_front' );

	if ( (int) $page_on_front === (int) $gma_general_settings['pages']['archive-question'] ) {
		return true;
	}

	return false;
}

function gma_has_question( $args = array() ) {
	global $wp_query;

	return $wp_query->gma_questions->have_posts();
}

function gma_the_question() {
	global $wp_query;

	$wp_query->gma_questions->set( 'orderby', 'modified' );
	return $wp_query->gma_questions->the_post();
}

function gma_has_question_stickies() {
	global $wp_query;

	return isset( $wp_query->gma_question_stickies ) ? $wp_query->gma_question_stickies->have_posts() : false;
}

function gma_the_sticky() {
	global $wp_query;

	return $wp_query->gma_question_stickies->the_post();
}

function gma_has_answers() {
	global $wp_query;

	return isset( $wp_query->gma_answers ) ? $wp_query->gma_answers->have_posts() : false;
}

function gma_the_answers() {
	global $wp_query;

	return $wp_query->gma_answers->the_post();
}

/**
 * @param bool $question_id
 * @return int
 */
function gma_get_answer_count($question_id = false ) {

	if ( ! $question_id ) {
		$question_id = get_the_ID();
	}

	$answer_count = get_post_meta( $question_id, '_gma_answers_count', true );

	if ( current_user_can( 'edit_posts' ) ) {
		return $answer_count;
	} else {
		$answer_private = get_post_meta( $question_id, 'gma_answers_private_count', true );

		if ( empty( $answer_private ) ) {
			global $wp_query;
			$args = array(
				'post_type' => 'gma-answer',
				'post_status' => 'private',
				'post_parent' => $question_id,
				'no_found_rows' => true,
				'update_post_meta_cache' => false,
				'update_post_term_cache' => false,
				'fields' => 'ids'
			);

			$private_answer = new WP_Query( $args );

			update_post_meta( $question_id, 'gma_answers_private_count', count( $private_answer ) );
			$answer_private = count( $private_answer );
		}

		return (int) $answer_count - (int) $answer_private;
	}
}


function gma_get_answers_count( $question_id, $author ) {
	global $wpdb;
	$query = "SELECT COUNT(*) FROM {$wpdb->posts} WHERE (post_author = %d AND post_parent = %d AND post_type = 'gma-answer' AND post_status = 'publish')";
	return $wpdb->get_var($wpdb->prepare( $query, $author, $question_id ) );
}

if ( ! function_exists( 'gma_get_answers' ) ) {
	function gma_get_answers( $question_id, $exclude = [] ) {
		if ( ! $question_id ) return [];
		$args = array(
			'post_type'              => 'gma-answer',
			'post_status'            => 'publish',
			'post_parent'            => $question_id,
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'fields'                 => '*'
		);

		if ( ! is_array( $exclude ) ) $exclude = [ $exclude ];

		if ( is_array( $exclude ) && count( $exclude ) )  {
			$args[ 'post__not_in' ] = $exclude;
		}

		return get_posts( $args );
	}
}

function gma_is_ask_form() {
	global $gma_general_settings;
	if ( !isset( $gma_general_settings['pages']['submit-question'] ) ) {
		return false;
	}

	return is_page( $gma_general_settings['pages']['submit-question'] );
}

function gma_is_archive_question() {
	global $gma_general_settings;
	if ( !isset( $gma_general_settings['pages']['archive-question'] ) ) {
		return false;
	}
	
	return is_page( $gma_general_settings['pages']['archive-question'] );
}

function gma_question_status( $question = false ) {
	if ( !$question ) {
		$question = get_the_ID();
	}

	return get_post_meta( $question, '_gma_status', true );
}

function gma_current_filter() {
	return isset( $_GET['filter'] ) && !empty( $_GET['filter'] ) ? sanitize_text_field( $_GET['filter'] ) : 'all';
}

function gma_get_ask_link() {
	global $gma_general_settings;

	return get_permalink( $gma_general_settings['pages']['submit-question'] );
}

function gma_get_question_link( $post_id ) {
	if ( 'gma-answer' == get_post_type( $post_id ) ) {
		$post_id = gma_get_question_from_answer_id( $post_id );
	}

	return get_permalink( $post_id );
}

function gma_get_post_parent_id( $post_id = false ){
	if(!$post_id){
		return false;
	}

	$parent_id = wp_cache_get( 'gma_'. $post_id .'_parent_id', 'gma' );
	if( $parent_id ){
		return $parent_id;
	}

	$parent_id = wp_get_post_parent_id( $post_id );
	//cache
	if($parent_id){
		wp_cache_set( 'gma_'. $post_id .'_parent_id', $parent_id, 'gma', 15*60 );
	}
	
	return $parent_id;
}

if ( ! function_exists( 'gma_has_shortcode' ) ) {
	function gma_has_shortcode( $post_id, $shortcode_name ) {
		global $wpdb;

		if ( ! $post_id )        return false;
		if ( ! $shortcode_name ) return false;

		$post = get_post( $post_id );

		if ( ! $post ) return false;

		// determine whether this page contains "my_shortcode" shortcode
		$shortcode_found = false;
		if ( has_shortcode($post->post_content, $shortcode_name) ) {
			$shortcode_found = true;
		} else if ( isset($post->ID) ) {
			$result = $wpdb->get_var( $wpdb->prepare(
				"SELECT count(*) FROM $wpdb->postmeta " .
				"WHERE post_id = %d and meta_value LIKE '%%" . $shortcode_name ."%%'", $post->ID ) );
			$shortcode_found = ! empty( $result );
		}

		return $shortcode_found;
	}
}

if ( ! function_exists( 'gma_display_date' ) ) {
	function gma_display_date( $olddate, $with_time = false ) {
		$ts     = strtotime( $olddate );
		$diff   = time() - $ts;
		$num_days = floor( $diff / 86400 );
		if ( $num_days >= 2 ) {
			$format = get_option( 'date_format' );
			if ( $with_time ) $format .= get_option( 'time_format' );
			return date_i18n( $format, $ts, true );
		}
		return sprintf( _x( '%s ago', '%s = human-readable time difference', 'give-me-answer-lite' ), human_time_diff( $ts ) );
	}
}

if ( ! function_exists( 'gma_get_total_comments' ) ) {
	function gma_get_total_comments( $post_type ){
		global $wpdb;
		$cc = $wpdb->get_var("SELECT COUNT(comment_ID)
        FROM $wpdb->comments
        WHERE comment_post_ID in (
          SELECT ID 
          FROM $wpdb->posts 
          WHERE post_type = '$post_type' AND post_status = 'publish') AND comment_approved = '1'
      ");
		return $cc;
	}
}

if ( ! function_exists( 'gma_get_users_page_id' ) ) {
	function gma_get_users_page_id() {
		global $wpdb;
		if ( false === get_transient( 'gma_users_pageid' ) ) {
			$pageid =  $wpdb->get_var('SELECT ID FROM '.$wpdb->prefix.'posts WHERE post_content LIKE "%[gma-users]%"');
			set_transient( 'gma_users_pageid', $pageid, 24 * 60 * 60 );
			return $pageid;
        }
        return get_transient( 'gma_users_pageid' );
	}
}

if ( ! function_exists( 'gma_update_modified_date' ) ) {
	function gma_update_modified_date( $question_id, $modified_date, $modified_date_gmt ) {
		$data = array(
			'ID'                => $question_id,
			'post_modified'     => $modified_date,
			'post_modified_gmt' => $modified_date_gmt,
		);
		wp_update_post( $data );
	}
}


if ( ! function_exists( 'gma_timeformat_convert' ) ) {
	function gma_timeformat_convert( $timestamp ) {
		return date("Y-m-d H:i:s", $timestamp );
	}
}


if ( ! function_exists( 'gma_total_answers_count' ) ) {
	function gma_total_answers_count() {
		$result = wp_count_posts( 'gma-answer' );
		return $result->publish;
	}
}

if ( ! function_exists( 'gma_total_comments_count' ) ) {
	function gma_total_comments_count() {
		$qcomments_count = gma_get_total_comments( 'gma-question' );
		$acomments_count = gma_get_total_comments( 'gma-answer' );
		return $qcomments_count + $acomments_count;
	}
}


if ( ! function_exists( 'gma_update_user_meta' ) ) {
	function gma_update_user_meta($user_id, $meta_key, $meta_value) {
		global $wpdb;
		return $wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->usermeta} SET `meta_value` = %s WHERE user_id = %d AND meta_key = %s", $meta_value, $user_id, $meta_key ) );
	}
}

if ( ! function_exists( 'gma_get_user_questions_url' ) ) {
	function gma_get_user_questions_url( $user_id ) {
	    global $gma_general_settings;
	    return add_query_arg( ['user' => $user_id, 'section' => 'questions'], get_permalink( $gma_general_settings[ 'pages' ][ 'user-profile' ] ) );
	}
}

if ( ! function_exists( 'gma_get_user_answers_url' ) ) {
	function gma_get_user_answers_url( $user_id ) {
		global $gma_general_settings;
		return add_query_arg( ['user' => $user_id, 'section' => 'answers'], get_permalink( $gma_general_settings[ 'pages' ][ 'user-profile' ] ) );
	}
}

if ( ! function_exists( 'gma_get_userwall_url' ) ) {
	function gma_get_userwall_url( $user_id ) {
		global $gma_general_settings;
		return add_query_arg( ['user' => $user_id, 'section' => 'wall'], get_permalink( $gma_general_settings[ 'pages' ][ 'user-profile' ] ) );
	}
}

if ( ! function_exists( 'gma_get_user_favorites_url' ) ) {
	function gma_get_user_favorites_url( $user_id ) {
		global $gma_general_settings;
		return add_query_arg( ['user' => $user_id, 'section' => 'favorites'], get_permalink( $gma_general_settings[ 'pages' ][ 'user-profile' ] ) );
	}
}

if ( ! function_exists( 'gma_get_edit_profile_url' ) ) {
	function gma_get_edit_profile_url( $user_id ) {
		global $gma_general_settings;
		return add_query_arg(
		        [
		            'user' => $user_id,
                    'section' => 'editprofile'
                ],
                get_permalink( $gma_general_settings[ 'pages' ][ 'user-profile' ] )
        );
	}
}


if ( ! function_exists( 'gma_get_user_id' ) ) {
    function gma_get_user_id() {
        return isset( $_GET[ 'user' ] ) ? absint( $_GET['user'] ) : '';
    }
}


if ( ! function_exists( 'gma_get_user_image' ) ) {
	function gma_get_user_image( $user_id ) {
		$profile = get_user_meta( $user_id, 'gma_picture_url', true );
		$profile = apply_filters('gma_user_image_url', $profile, $user_id);
		if ( $profile ) {
            return $profile;
        }
		return GMA_URI . 'assets-admin/img/pplaceholder2.jpg';
	}
}

if ( ! function_exists( 'gma_get_image_or_placeholder' ) ) {
	function gma_get_image_or_placeholder( $image_url ) {
		if ( $image_url ) return $image_url;
		return GMA_URI . 'assets-admin/img/pplaceholder2.jpg';
	}
}

if ( ! function_exists( 'gma_user_displayname' ) ) {
	function gma_user_displayname( $user_id ) {
		if ( ! $user_id ) return false;
		$user = get_user_by( 'id', $user_id );
		if ( $user->first_name && $user->last_name ) {
			return $user->first_name . ' ' . $user->last_name;
		} else if ( ! empty( $user->first_name ) ) {
			return $user->first_name;
		} else if ( ! empty( $user->last_name ) ) {
			return $user->last_name;
		}
		return $user->user_login;
	}
}

if ( ! function_exists( 'gma_count_users' ) ) {
	function gma_count_users() {
		global $wpdb;
		return $wpdb->get_var( "SELECT COUNT(ID) FROM $wpdb->users" );
	}
}

if ( ! function_exists( 'gma_get_template_page_url' ) ) {
	function gma_get_template_page_url($TEMPLATE_NAME){
		$url = null;
		$pages = get_pages(array(
			'meta_key' => '_wp_page_template',
			'meta_value' => $TEMPLATE_NAME
		));
		if(isset($pages[0])) {
			$url = get_page_link($pages[0]->ID);
		}
		return $url;
	}
}

if ( ! function_exists( 'gma_get_tags_page_url' ) ) {
	function gma_get_tags_page_url() {

	}
}

if ( ! function_exists( 'gma_update_post_modified' ) ) {
	function gma_update_post_modified($post_id, $post_modified, $post_modified_gmt) {
		global $wpdb;
		if ( ! $post_id ) return false;
		$query = "UPDATE {$wpdb->posts} SET post_modified = %s,post_modified_gmt = %s WHERE ID = %d";
		return $wpdb->query( $wpdb->prepare( $query, $post_modified, $post_modified_gmt, $post_id ) );
	}
}

if ( ! function_exists( 'gma_get_anonymous_user' ) ) {
	function gma_get_anonymous_user($post_id) {
		$name  = get_post_meta( $post_id, '_gma_anonymous_name', true );
		$email = get_post_meta( $post_id, '_gma_anonymous_email', true );
		return [
			'name'  => $name ? $name : __('Anonymous' , 'give-me-answer-lite') ,
			'email' => $email,
			'url'   => '',
		];
	}
}

if ( ! function_exists( 'gma_get_author_info' ) ) {
	function gma_get_author_info($post_id) {

		$post = get_post( $post_id );
		if ( ! $post ) return false;

		// Check if post is anonymous
		if ( gma_is_anonymous( $post_id ) ) {
			$name = get_post_meta( $post_id, '_gma_anonymous_name', true );
			$email = get_post_meta( $post_id, '_gma_anonymous_email', true );
			return [
				'anonymous'    => true,
				'display_name' => $name ? $name : __('Anonymous' , 'give-me-answer-lite') ,
				'email'        => $email,
			];
		}

		$author = get_user_by( 'id', $post->post_author );
		$result =  [
			'anonymous' => false,
			'id'        => $author->ID,
			'email'     => $author->user_email,
		];
		if ( $author->first_name && $author->last_name ) {
			$result[ 'display_name' ] =  $author->first_name . ' ' . $author->last_name;
		} else if ( ! empty( $author->first_name ) ) {
			$result[ 'display_name' ] =  $author->first_name;
		} else if ( ! empty( $author->last_name ) ) {
			$result[ 'display_name' ] = $author->last_name;
		} else {
			$result[ 'display_name' ] = $author->user_login;
		}

		return $result;
	}
}


if ( ! function_exists( 'gma_followers_count' ) ) {
	function gma_followers_count( $post_id ) {
		if ( ! $post_id ) return false;
		$followers = get_post_meta( $post_id, '_gma_followers', false );
		return count( $followers );
	}
}






if ( ! function_exists( 'gma_delete_answer' ) ) {
	function gma_delete_answer( $answer_id ) {

		if ( ! $answer_id ) return false;

		$answer     = get_post( $answer_id );
		$question   = get_post( $answer->post_parent );

		do_action( 'gma_prepare_delete_answer', $answer_id);

		wp_delete_post( $answer_id, true );

		$answer_count = get_post_meta( $question->ID, '_gma_answers_count', true );
		$new_answer_count = (int) $answer_count - 1;
		if ( (int) $new_answer_count < 0 ) {
			$new_answer_count = intval( 0 );
		}
		update_post_meta( $question->ID, '_gma_answers_count', $new_answer_count );
		do_action( 'gma_delete_answer', $answer->ID, $question->ID );

		if ( $new_answer_count ) {
			$latest_answer = gma_get_latest_answer( $question->ID );
			gma_update_post_modified( $question->ID, $latest_answer->post_modified, $latest_answer->post_modified_gmt );
		} else {
			$created_date = get_post_meta( $question->ID, '_gma_created_date' );
			gma_update_post_modified( $question->ID, $created_date, $created_date );
		}

		return true;
	}
}

if ( ! function_exists( 'gma_get_avatar_classes' ) ) {
	function gma_get_avatar_classes() {
		global $gma_avatar;
		$result = isset( $gma_avatar[ 'type' ] ) && $gma_avatar[ 'type' ] == 'circle' ? 'rounded-circle' : 'rounded-0';
		return apply_filters( 'gma_avatar_classes', $result );
	}
}

if ( ! function_exists( 'gma_user_avatar' ) ) {
	function gma_user_avatar( $args, $return = false ) {
		$user_image_url = gma_get_user_image( $args[ 'user_id' ] );
		$html            = '<span class="gravatar-wrapper-32 mr-1">';
		$html           .= '<span class="avatar avatar-sm mt-0 ' . gma_get_avatar_classes() . '" ';
		$html           .= 'style="background-image:url(' . $user_image_url . ');';

		if ( isset( $args[ 'size' ] ) ) {
			$html       .= ' width:' . $args[ 'size' ] . 'px;height: ' . $args[ 'size' ] . 'px';
		}

		$html           .= '"></span>';
		$html           .= '</span>';
		$html            =  apply_filters( 'gma_user_avatar_html', $html, $args );
		if ( $return ) {
			return $html;
		}
		echo $html;
	}
}

if ( ! function_exists( 'gma_block_user' ) ) {
	function gma_block_user( $username_or_id, $reason = '', $date = '' ) {
		if ( ! $username_or_id ) return false;
		if ( ! $date ) $date = date( 'Y-m-d H:i:s' );
		if ( is_numeric( $username_or_id ) ) {
			$user = get_user_by( 'id', $username_or_id );
		} else {
			$user = get_user_by( 'login', $username_or_id );
		}
		if ( ! $user ) return false;

		do_action( 'gma_before_block_user', $user->ID, $reason, $date );
		update_user_meta( $user->ID, 'gma_is_blocked', 1 );
		update_user_meta( $user->ID, 'gma_block_reason', $reason );
		update_user_meta( $user->ID, 'gma_block_date', $date );
		do_action( 'gma_after_block_user', $user->ID, $reason, $date );

		return true;
	}
}

if ( ! function_exists( 'gma_unblock_user' ) ) {
	function gma_unblock_user( $username_or_id ) {
		if ( is_numeric( $username_or_id ) ) {
			$user = get_user_by( 'id', $username_or_id );
		} else {
			$user = get_user_by( 'login', $username_or_id );
		}
		if ( ! $user ) return false;

		do_action( 'gma_before_unblock_user', $user->ID);
		delete_user_meta( $user->ID, 'gma_is_blocked');
		delete_user_meta( $user->ID, 'gma_block_reason');
		delete_user_meta( $user->ID, 'gma_block_date');
		do_action( 'gma_after_unblock_user', $user->ID);

		return true;
	}
}

if ( ! function_exists( 'gma_convert_to_kilo' ) ) {
	function gma_convert_to_kilo( $value ) {
		if ($value > 999 && $value <= 999999) {
			$result = floor($value / 1000) . 'K';
		} elseif ($value > 999999) {
			$result = floor($value / 1000000) . 'M';
		} else {
			$result = $value;
		}
		return $result;
	}
}


if ( ! function_exists( 'gma_visit_profile' ) ) {
	function gma_visit_profile( $visitor_id, $visited_id, $created = '' ) {
		if ( ! $created ) $created = date( 'Y-m-d H:i:s' );

		do_action( 'gma_before_user_visit_profile', $visitor_id, $visited_id );


		// Visit count
		$curvisit = get_user_meta($visited_id, 'gma_profile_visit', true);
		$curvisit = $curvisit ? $curvisit : 0;
		update_user_meta( $visited_id, 'gma_profile_visit', ++$curvisit );



		if ( $visitor_id && ( $visitor_id != $visited_id ) ) {

			if ( $row_id = gma_lite()->profile_visit->is_visit( $visitor_id, $visited_id ) ) {
				gma_lite()->profile_visit->delete_all_visit( $visitor_id, $visited_id );
			}


			gma_lite()->profile_visit->insert( [
				'visitor_id' => $visitor_id,
				'visited_id' => $visited_id,
				'created'    => $created,
			] );
		}

		do_action( 'gma_after_user_visit_profile', $visitor_id, $visited_id );
	}
}

if ( ! function_exists( 'gma_the_vote_count' ) ) {
	function gma_the_vote_count( $echo = true, $format = false ) {
		$vote_count = gma_vote_count();
		if ( $vote_count > 0 ) {
		    if ( $format ) {
                $vote_count = '+' . $vote_count;
            }
		}
		if ( $echo ) {
			echo $vote_count;
		}
		return $vote_count;
	}
}

if ( ! function_exists( 'gma_get_ip_address' ) ) {
	function gma_get_ip_address() {
		$ipaddress = '';
		if (isset($_SERVER['HTTP_CLIENT_IP']))
			$ipaddress = $_SERVER['HTTP_CLIENT_IP'];
		else if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
			$ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
		else if(isset($_SERVER['HTTP_X_FORWARDED']))
			$ipaddress = $_SERVER['HTTP_X_FORWARDED'];
		else if(isset($_SERVER['HTTP_X_CLUSTER_CLIENT_IP']))
			$ipaddress = $_SERVER['HTTP_X_CLUSTER_CLIENT_IP'];
		else if(isset($_SERVER['HTTP_FORWARDED_FOR']))
			$ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
		else if(isset($_SERVER['HTTP_FORWARDED']))
			$ipaddress = $_SERVER['HTTP_FORWARDED'];
		else if(isset($_SERVER['REMOTE_ADDR']))
			$ipaddress = $_SERVER['REMOTE_ADDR'];
		else
			$ipaddress = 'UNKNOWN';
		return $ipaddress;

	}
}

function gma_get_administrator_emails(){
	$result = [];
	$blogusers = get_users('role=Administrator');
	foreach ($blogusers as $user) {
		$result[] = $user->user_email;
	}
	return $result;
}

if ( ! function_exists( 'gma_in_maintenance_mode' ) ) {
	function gma_in_maintenance_mode() {
		global $gma_general_settings;
		return $gma_general_settings[ 'general' ][ 'maintenance' ];
	}
}

if ( ! function_exists( 'gma_die_if_maintenance_mode' ) ) {
	function gma_die_if_maitenance_mode() {
		global $gma_general_settings;
		if ( $gma_general_settings[ 'general' ][ 'maintenance' ] && ! current_user_can( 'manage_options' ) ) {
			die();
		}
	}
}

if ( ! function_exists('gma_turn_url_into_hyperlink') ) {
	function gma_turn_url_into_hyperlink($string){
		//The Regular Expression filter
		$reg_exUrl = "/(?i)\b((?:https?:\/\/|www\d{0,3}[.]|[a-z0-9.\-]+[.][a-z]{2,4}\/)(?:[^\s()<>]+|\(([^\s()<>]+|(\([^\s()<>]+\)))*\))+(?:\(([^\s()<>]+|(\([^\s()<>]+\)))*\)|[^\s`!()\[\]{};:'\".,<>?«»“”‘’]))/";

		// Check if there is a url in the text
		if(preg_match_all($reg_exUrl, $string, $url)) {

			// Loop through all matches
			foreach($url[0] as $newLinks){
				if(strstr( $newLinks, ":" ) === false){
					$link = 'http://'.$newLinks;
				}else{
					$link = $newLinks;
				}

				// Create Search and Replace strings
				$search  = $newLinks;
				$replace = '<a href="'.$link.'" title="'.$newLinks.'" target="_blank">'.$link.'</a>';
				$string = str_replace($search, $replace, $string);
			}
		}

		//Return result
		return $string;
	}
}


if ( ! function_exists( 'gma_is_admin' ) ) {
	function gma_is_admin( $user_id = '' ) {
		if ( ! $user_id ) $user_id = get_current_user_id();
		return user_can($user_id, 'manage_options');
	}
}

if ( ! function_exists( 'gma_get_user_wall_status' ) ) {
	function gma_get_user_wall_status( $user_id = '' ) {
		if ( ! $user_id ) $user_id = get_current_user_id();
		$result = get_user_meta( $user_id, 'gma-user-wall-status', true );
		if ( $result === '0' ) return false;
		return true;
	}
}

if ( ! function_exists( 'gma_error_if_in_maintenance' ) ) {
	function gma_error_if_in_maintenance() {
		if ( gma_in_maintenance_mode() && ! gma_is_admin() ) {
			?>
        <p class="font-weight-bold text-danger text-center gma-maintenance-text">
            <?php _e('Question & Answer is temporarily disabled', 'give-me-answer-lite'); ?>
        </p>
	<?php
	     }
	}
}

if ( ! function_exists( 'gma_is_maintenance_for_user' ) ) {
    function gma_stop_generating_nonce() {
        if ( gma_is_admin() ) return false;
        if ( gma_in_maintenance_mode() ) return true;
        return false;
    }
}


if ( ! function_exists( 'gma_shortcode_postid' ) ) {
    function gma_shortcode_postid( $shortcode_name ) {
	    global $wpdb;
	    if ( false == get_transient( 'gma_' . $shortcode_name . '_postid' ) ) {
		    $query = "SELECT ID FROM {$wpdb->posts} WHERE `post_content` LIKE '%s' AND `post_status` = 'publish' LIMIT 1";
		    $result =  $wpdb->get_var( $wpdb->prepare( $query, '%[' . $wpdb->esc_like( $shortcode_name ) . ']%' ) );
		    set_transient( 'gma_' . $shortcode_name . '_postid', $result, 60*60*12 );
		    return $result;
        }
	    return get_transient( 'gma_' . $shortcode_name . '_postid' );
    }
}


if ( ! function_exists( 'gma_anonymous_vote_status' ) ) {
    function gma_anonymous_vote_status() {
        global $gma_general_settings;
	    return isset( $gma_general_settings[ 'allow-anonymous-vote' ] ) && $gma_general_settings[ 'allow-anonymous-vote' ];
    }
}

if ( ! function_exists( 'gma_has_more_comments' ) ) {
    function gma_get_hidden_comments($post_id = '') {
        global $gma_general_settings;
        if ( ! $post_id ) $post_id = get_the_ID();
        $comments_count = wp_count_comments( $post_id );
        if ( ! $gma_general_settings['comment']['per-page'] ) return 0;
        if ( false == $comments_count->approved ) return 0;
        if ( $comments_count->approved < $gma_general_settings['comment']['per-page'] ) return 0;
        return $comments_count->approved - $gma_general_settings['comment']['per-page'];
    }
}

if ( ! function_exists( 'gma_get_current_user_id' ) ) {
    function gma_get_current_user_id() {
	    global $gma_general_settings,$current_user ;
	    $gma_user_id = '';
	    if ( is_user_logged_in() ) {
		    $gma_user_id = $current_user->ID;
	    } else {
		    $gma_user_id = gma_get_current_user_session();
	    }
	    return $gma_user_id;
    }
}

function gma_social_urls($url_encoded, $title = '', $summary = '') {
	$result =  [
		'telegram' => 'https://t.me/share/url?url={url}&text={title}',
		'facebook' => 'https://www.facebook.com/sharer.php?u={url}',
		'twitter'  => 'https://twitter.com/intent/tweet?url={url}&text={title}',
		'linkedin' => 'https://www.linkedin.com/shareArticle?mini=true&url={url}&title={title}&summary={text}',
		'whatsapp' => 'https://wa.me/?text={url}',
	];

	$result[ 'telegram' ] = str_replace([ '{url}', '{title}' ], [ $url_encoded, $title ], $result[ 'telegram' ] );
	$result[ 'facebook' ] = str_replace([ '{url}' ],[ $url_encoded ], $result[ 'facebook' ] );
	$result[ 'twitter' ]  = str_replace([ '{url}', '{title}' ],[ $url_encoded, $title ], $result[ 'twitter' ] );
	$result[ 'linkedin' ] = str_replace([ '{url}', '{title}', '{text}' ],[ $url_encoded, $title, $summary ], $result[ 'linkedin' ] );
	$result[ 'whatsapp' ] = str_replace([ '{url}' ],[ $url_encoded ], $result[ 'whatsapp' ] );
	return $result;
}


if ( ! function_exists( 'gma_statistics' ) ) {
	/**
     * Get summary of question and answer statistics
     *
     * @since 1.0
     *
     *
	 * @return array
	 */
    function gma_statistics() {
	    $questions_count = wp_count_posts( 'gma-question' );
	    $answers_count   = wp_count_posts( 'gma-answer' );
	    $total_users     = gma_count_users();
	    $qcomments_count = gma_get_total_comments( 'gma-question' );
	    $acomments_count = gma_get_total_comments( 'gma-answer' );
	    $total_comments  = $qcomments_count + $acomments_count;

	    return [
	       'questions'  => $questions_count->publish,
           'answers'    => $answers_count->publish,
		   'comments'   => $total_comments,
           'users'      => $total_users,
        ];
    }
}


/**
 * Get question id based on answer or question id
 *
 * @since 1.0
 *
 * @param int|false $quora_id
 */
function gma_get_question_id( $quora_id ) {
    if ( false == $quora_id ) return false;
    $post = get_post( $quora_id );
    if ( $post ) {
        // Check if post is a question
        if ( $post->post_type == 'gma-question' ) {
	        return $post->ID;
        }
        // It is an answer
        return gma_get_question_from_answer_id( $post->ID );
    }
    return false;
}


/**
 * Move comments from one post to another
 *
 * @since 1.0
 *
 * @param integer $old_post_id
 * @param integer $new_post_id
 *
 * @return bool|int
 */
function gma_move_comments( $old_post_id, $new_post_id ) {
    global $wpdb;
    $query = "UPDATE `{$wpdb->comments}` SET comment_post_ID = %d WHERE comment_post_ID = %d";
    return $wpdb->query( $wpdb->prepare( $query, $new_post_id, $old_post_id ) );
}

/**
 * Format point
 *
 * @param integer $point
 *
 * @return int|string
 */
function gma_format_point( $point ) {
    if ( false == $point ) return 0;
    return $point;
}


/**
 * Get count of pending answers
 *
 *
 * @since 1.0
 * @version 1.0
 *
 * @return int
 */
function gma_count_pending_answers() {
    global $wpdb;
    return (int)$wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_status = %s AND post_type = 'gma-answer'", 'pending' ) );
}

/**
 * Get count of pending answers
 *
 *
 * @since 1.0
 * @version 1.0
 *
 * @return int
 */
function gma_count_pending_questions() {
    global $wpdb;
    return (int)$wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_status = %s AND post_type = 'gma-question'", 'pending' ) );
}

/**
 * Get count of pending answers
 *
 * @since 1.0
 *
 * @param string $user_id
 * @return int
 */
function gma_count_user_pending_answers( $question_id, $user_id = '' ) {
    global $wpdb;
    if ( ! $user_id ) $user_id = get_current_user_id();
    return (int)$wpdb->get_var( $wpdb->prepare( "SELECT count(*) FROM {$wpdb->posts} WHERE post_author = %d && post_type = %s AND post_parent = %d AND post_status = %s ", $user_id, 'gma-answer', $question_id, 'pending' ) );
}


/**
 * Get previous question
 *
 * @since 1.0
 * @param integer $question_id
 * @return array|bool
 */
function gma_get_previous_question($question_id) {
    global $wpdb;
    if (! $question_id) return false;
    return $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->posts} WHERE ID < %d and post_type = %s and post_status = %s order by ID DESC LIMIT 1", $question_id, 'gma-question', 'publish'));
}

/**
 * Get next question
 *
 * @since 1.0
 * @param integer $question_id
 * @return array|bool|object
 */
function get_get_next_question($question_id) {
    global $wpdb;
    if (! $question_id) return false;
    return $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->posts} WHERE ID > %d and post_type = %s and post_status = %s order by ID ASC LIMIT 1", $question_id, 'gma-question', 'publish'));
}

if ( ! function_exists('gma_can_user_change_profile_pic') ) {
    function gma_can_user_change_profile_pic($actual_user_id, $changer_id = '') {
        if ( ! $changer_id ) $changer_id = get_current_user_id();
        if ( user_can($changer_id, 'manage_options') ) return true;
        if ( $actual_user_id == $changer_id ) return true;
        return false;
    }
}

?>