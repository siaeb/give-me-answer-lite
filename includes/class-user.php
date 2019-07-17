<?php
defined( 'ABSPATH' ) || exit;

function gma_get_following_user( $question_id = false ) {
	if ( ! $question_id ) {
		$question_id = get_the_ID();
	}
	$followers = get_post_meta( $question_id, '_gma_followers' );
	
	if ( empty( $followers ) ) {
		return false;
	}
	
	return $followers;
}
/** 
 * Did user flag this post ?
 */
function gma_is_user_flag( $post_id, $user_id = null ) {
	if ( ! $user_id ) {
		global $current_user;
		if ( $current_user->ID > 0 ) {
			$user_id = $current_user->ID;
		} else {
			return false;
		}
	}
	$flag = get_post_meta( $post_id, '_flag', true );
	if ( ! $flag ) {
		return false;
	}
	$flag = unserialize( $flag );
	if ( ! is_array( $flag ) ) {
		return false;
	}
	if ( ! array_key_exists( $user_id, $flag ) ) {
		return false;
	}
	if ( $flag[$user_id] == 1 ) {
		return true;
	}
	return false;
}


function gma_user_post_count( $user_id, $post_type = 'post' ) {
	$posts = new WP_Query( array(
		'author' => $user_id,
		'post_status'		=> array( 'publish', 'private' ),
		'post_type'			=> $post_type,
		'fields' => 'ids',
	) );
	return $posts->found_posts;
}

function gma_user_question_count( $user_id ) {
	return gma_user_post_count( $user_id, 'gma-question' );
}

function gma_user_answer_count( $user_id ) {
	return gma_user_post_count( $user_id, 'gma-answer' );
}


function gma_user_most_answer( $number = 10, $from = false, $to = false ) {
	global $wpdb;
	
	$query = "SELECT post_author, count( * ) as `answer_count` 
				FROM `{$wpdb->prefix}posts` 
				WHERE post_type = 'gma-answer' 
					AND post_status = 'publish'
					AND post_author <> 0";
	if ( $from ) {
		$from = date( 'Y-m-d h:i:s', $from );
		$query .= " AND `{$wpdb->prefix}posts`.post_date > '{$from}'";
	}
	if ( $to ) {
		$to = date( 'Y-m-d h:i:s', $to );
		$query .= " AND `{$wpdb->prefix}posts`.post_date < '{$to}'";
	}

	$prefix = '-all';
	if ( $from && $to ) {
		$prefix = '-' . ( $from - $to );
	}

	$query .= " GROUP BY post_author 
				ORDER BY `answer_count` DESC LIMIT 0,{$number}";
	$users = wp_cache_get( 'gma-most-answered' . $prefix );
	if ( false == $users ) {
		$users = $wpdb->get_results( $query, ARRAY_A  );
		wp_cache_set( 'gma-most-answered', $users );
	}
	return $users;            
}

function gma_user_most_answer_this_month( $number = 10 ) {
	$from = strtotime( 'first day of this month' );
	$to = strtotime( 'last day of this month' );
	return gma_user_most_answer( $number, $from, $to );
}

function gma_user_most_answer_last_month( $number = 10 ) {
	$from = strtotime( 'first day of last month' );
	$to = strtotime( 'last day of last month' );
	return gma_user_most_answer( $number, $from, $to );
}

function gma_is_followed( $post_id = false, $user_id = false ) {
	if ( ! $post_id ) {
		$post_id = get_the_ID();
	}

	if ( ! $user_id ) {
		$user = wp_get_current_user();
		$user_id = $user->ID;
	}

	if ( in_array( $user_id, get_post_meta( $post_id, '_gma_followers', false ) ) ) {
		return true;
	}
	return false;
}

/**
* Get username
*
* @param string $display_name
* @return string
* @since 1.0
*/
function gma_the_author( $display_name ) {
	global $post;

	if ( 'gma-answer' == $post->post_type || 'gma-question' == $post->post_type) {
		if ( gma_is_anonymous( $post->ID ) ) {
			$anonymous_name = get_post_meta( $post->ID, '_gma_anonymous_name', true );
			$display_name = $anonymous_name ? $anonymous_name : __( 'Anonymous', 'give-me-answer-lite' );
		}
	}

	return $display_name;
}
add_filter( 'the_author', 'gma_the_author' );

/**
* Get user's profile link
*
* @param int $user_id
* @return string
* @since 1.0
*/
function gma_get_author_link( $user_id = false ) {
	if ( ! $user_id ) {
		return false;
	}

	$user = get_user_by( 'id', $user_id );
	if(! $user){
		return false;
	}

	$url = gma_get_user_questions_url( $user_id );

	return apply_filters( 'gma_get_author_link', $url, $user_id );
}


/**
* Get question ids user is subscribing
*
* @param int $user_id
* @return array
* @since 1.0
*/
function gma_get_user_question_subscribes( $user_id = false, $posts_per_page = 5, $page = 1 ) {
	if ( !$user_id ) {
		return array();
	}

	$args = array(
		'post_type' 				=> 'gma-question',
		'posts_per_page'			=> $posts_per_page,
		'paged'						=> $page,
		'fields' 					=> 'ids',
		'update_post_term_cache' 	=> false,
		'update_post_meta_cache' 	=> false,
		'no_found_rows' 			=> true,
		'meta_query'				=> array(
			'key'					=> '_gma_followers',
			'value'					=> $user_id,
			'compare'				=> '='
		)
	);

	$question_id = wp_cache_get( '_gma_user_'. $user_id .'_question_subscribes' );

	if ( ! $question_id ) {
		$question_id = get_posts( $args );
		wp_cache_set( '_gma_user_'. $user_id .'_question_subscribes', $question_id, false, 450 );
	}

	return $question_id;
}

function gma_get_user_badge( $user_id = false ) {
	if ( !$user_id ) {
		return;
	}

	$badges = array();
	if ( user_can( $user_id, 'edit_posts' ) ) {
		$badges['staff'] = __( 'Staff', 'give-me-answer-lite' );
	}

	return apply_filters( 'gma_get_user_badge', $badges, $user_id );
}

function gma_print_user_badge( $user_id = false, $echo = false ) {
	if ( !$user_id ) {
		return;
	}

	$badges = gma_get_user_badge( $user_id );
	$result = '';
	if ( $badges && !empty( $badges ) ) {
		foreach( $badges as $k => $badge ) {
			$k = str_replace( ' ', '-', $k );
			$result .= '<span class="gma-label gma-'. strtolower( $k ) .'">'.$badge.'</span>';
		}
	}

	if ( $echo ) {
		echo $result;
	}

	return $result;
}


if ( ! function_exists( 'gma_get_user_info' ) ) {
	function gma_get_user_info( $user_id ) {
		global $wpdb;
		if ( ! $user_id ) return false;
		$query = "
			SELECT 
				u.*,
			    ( SELECT meta_value FROM `{$wpdb->usermeta}` WHERE user_id = u.ID and meta_key = 'first_name') `first_name`,
			    ( SELECT meta_value FROM `{$wpdb->usermeta}` WHERE user_id = u.ID and meta_key = 'last_name') `last_name`,
			    ( SELECT meta_value FROM `{$wpdb->usermeta}` WHERE user_id = u.ID and meta_key = 'gma_mobile') `mobile`,
			    ( SELECT meta_value FROM `{$wpdb->usermeta}` WHERE user_id = u.ID and meta_key = 'gma_university') `university`,
			    ( SELECT meta_value FROM `{$wpdb->usermeta}` WHERE user_id = u.ID and meta_key = 'gma_about') `about`,
			    ( SELECT meta_value FROM `{$wpdb->usermeta}` WHERE user_id = u.ID and meta_key = 'gma_picture_url') `picture`
			    FROM `{$wpdb->users}` u WHERE u.ID = %d
		";

		$result =  $wpdb->get_row( $wpdb->prepare( $query, $user_id ) );
		if ( $result ) {
			if ( $result->first_name && $result->last_name ) {
				$result->full_name = $result->first_name . ' ' . $result->last_name;
			} else {
				$result->full_name = $result->user_login;
			}

		}

		return $result;
	}
}


if ( ! function_exists( 'gma_get_profile_picture' ) ) {
	function gma_get_profile_picture( $user_id ) {
		if ( ! $user_id ) return false;
		$picture = get_user_meta( $user_id, 'gma_picture_url', true );
		if ( $picture ) return $picture;
		return GMA_URI . 'assets-admin/img/pplaceholder2.jpg';
	}
}

class GMA_User {

	public $ID;
	public $user_login;
	public $user_email;
	public $user_description;
	public $user_registered;
	public $user_firstname;
	public $user_lastname;
	public $display_name;
	public $mobile;
	public $university;
	public $about;
	public $roles;

	public function __construct( $user_id ) {
		$user                    = new WP_User( $user_id );
		$this->ID                = $user->ID;
		$this->user_login        = $user->user_login;
		$this->user_email        = $user->user_email;
		$this->user_description  = $user->user_description;
		$this->user_firstname    = $user->user_firstname;
		$this->user_lastname     = $user->user_lastname;
        $this->user_registered   = $user->user_registered;
        $this->roles             = $user->roles;

		if ( $user->first_name && $user->last_name ) {
			$this->display_name =  $user->first_name . ' ' . $user->last_name;
		} else if ( ! empty( $user->first_name ) ) {
			$this->display_name =  $user->first_name;
		} else if ( ! empty( $user->last_name ) ) {
			$this->display_name =  $user->last_name;
		} else {
			$this->display_name = $user->user_login;
		}

		$this->user_firstname   = apply_filters('gma_user_firstname', $this->user_firstname, $user_id);
		$this->user_lastname    = apply_filters('gma_user_lastname', $this->user_lastname, $user_id);
		$this->user_description = apply_filters('gma_user_description', $this->user_description, $user_id);
		$this->display_name     = apply_filters('gma_user_displayname', $this->display_name, $user_id);
		$this->mobile           = apply_filters('gma_user_mobile_number', get_user_meta( $user_id, 'gma_mobile', true ), $user_id);
		$this->university       = apply_filters('gma_user_university',get_user_meta( $user_id, 'gma_university', true ), $user_id);
		$this->about            = apply_filters('gma_user_about',get_user_meta( $user_id, 'gma_about', true ), $user_id);
	}


}
?>