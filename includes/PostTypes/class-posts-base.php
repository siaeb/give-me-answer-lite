<?php

defined( 'ABSPATH' ) || exit;

add_action( 'init', 'gma_anonymous_create_session' );
function gma_anonymous_create_session() {
	if ( !gma_get_current_user_session() ) {
		$expire = time() + 10*YEAR_IN_SECONDS;

		$secure = is_ssl();

		$secure_in_cookie = $secure && 'https' === parse_url( get_option( 'home' ), PHP_URL_SCHEME );

		if ( $secure ) {
			$auth_cookie_secure = SECURE_AUTH_COOKIE;
		} else {
			$auth_cookie_secure = AUTH_COOKIE;
		}

		$token = wp_generate_password( 43, false, false );

		setcookie('gma_anonymous', $token, $expire, COOKIEPATH, COOKIE_DOMAIN, $secure_in_cookie, true );
		if ( COOKIEPATH != SITECOOKIEPATH ) {
			setcookie('gma_anonymous', $token, $expire, SITECOOKIEPATH, COOKIE_DOMAIN, $secure_in_cookie, true );
		}
	}
}

function gma_get_current_user_session() {
	return isset( $_COOKIE['gma_anonymous'] ) && !empty( $_COOKIE['gma_anonymous'] ) ? $_COOKIE['gma_anonymous'] : false;
}

/**
 * Check for current user can vote for the question
 * @param  int  $post_id ID of object ( question /answer ) post
 * @param  int  $point      Point of vote
 * @param  boolean $user    Current user id
 * @return boolean          Voted or not
 */
function gma_is_user_voted( $post_id, $point, $user = false ) {
	if ( ! $user ) {
		if ( is_user_logged_in() ) {
			$user = get_current_user_id();
		} else {
			$user = gma_get_current_user_session();
		}
	}

	$votes = get_post_meta( $post_id, '_gma_votes_log', true );

	if ( empty( $votes ) ) {
		return false;
	}

	if ( array_key_exists( $user, $votes ) ) {
		if ( $votes[ $user ] == $point ) {
			return $votes[$user];
		}
	}

	return false;
}


function gma_get_user_vote( $post_id, $user = false ) {
	if ( ! $user ) {
		global $current_user;
		$user = $current_user->ID;
	}
	if ( gma_is_user_voted( $post_id, 1, $user ) ) {
		return 'up';
	} else if ( gma_is_user_voted( $post_id, -1, $user ) ) {
		return 'down';
	}
	return false;
}

/**
 * Calculate number of votes for specify post
 * @param  int $post_id ID of post
 * @return int
 */
function gma_update_vote_count( $post_id ) {
	if ( ! $post_id ) {
		global $post;
		$post_id = $post->ID;
	}
	$votes = get_post_meta( $post_id, '_gma_votes_log', true );

	if ( empty( $votes ) ) {
		update_post_meta( $post_id, '_gma_votes', 0 );
		return 0;
	}

	$total = array_sum( $votes );
	update_post_meta( $post_id, '_gma_votes', $total );
	return $total;
}

/**
 * Return vote point of post
 * @param  int $post_id ID of post
 * @param  boolean $echo        Print or not
 * @return int  Vote point
 */
function gma_vote_count( $post_id = false, $echo = false ) {
	if ( ! $post_id ) {
		global $post;
		$post_id = $post->ID;
		if( isset( $post->vote_count ) ) {
			return $post->vote_count;
		}
	}
	$votes = get_post_meta( $post_id, '_gma_votes', true );
	if ( empty( $votes ) ) {
		return 0;
	}
	if ( $echo ) {
		echo $votes;
	}
	return ( int ) $votes;
}

/**
 * Check for current user can vote for the comment
 * @param  int  $post_id ID of comment
 * @param  int  $point       Point of vote
 * @param  boolean $user     Current user id
 * @return boolean           Voted or not
 */
function gma_is_user_voted_comment( $comment_id, $user = false ) {
	if ( ! $user ) {
		if ( is_user_logged_in() ) {
			$user = get_current_user_id();
		} else {
			$user = gma_get_current_user_session();
		}
	}

	$votes = get_comment_meta( $comment_id, '_gma_votes_log', true );

	if ( empty( $votes ) ) {
		return false;
	}

	if ( array_key_exists( $user, $votes ) ) {
		if ( ( int ) $votes[$user] == 1 ) {
			return $votes[$user];
		}
	}

	return false;
}

/**
 * Calculate number of votes for specify comment
 * @param  int $post_id ID of comment
 * @return integer
 */
function gma_update_comment_vote_count( $comment_id ) {

	$votes = get_comment_meta( $comment_id, '_gma_votes_log', true );

	if ( empty( $votes ) ) {
		update_comment_meta( $comment_id, '_gma_votes', 0 );
		return 0;
	}

	$total = array_sum( $votes );
	update_comment_meta( $comment_id, '_gma_votes', $total );

	return $total;
}

/**
 * Return vote point of post
 * @param  int $post_id ID of post
 * @param  boolean $echo        Print or not
 * @return int  Vote point
 */
function gma_comment_vote_count( $comment_id = false, $echo = false ) {
	$votes = get_comment_meta( $comment_id, '_gma_votes', true );
	if ( empty( $votes ) ) {
		return 0;
	}
	if ( $echo ) {
		echo $votes;
	}
	return ( int ) $votes;
}

function gma_decode_pre_entities( $matches ) {
	$content = $matches[0];
	$content = str_replace( $matches[1], '', $content );
	$content = str_replace( $matches[5], '', $content );
	$content = str_replace( $matches[2], '', $content );
	//$content = str_replace( $matches[3], html_entity_decode( $matches[3] ), $content );
	return $content;
}

function gma_content_html_decode( $content ) {
	return preg_replace_callback( '/( <pre> )<code( [^>]* )>( .* )<\/( code )[^>]*>( <\/pre[^>]*> )/isU' , 'gma_decode_pre_entities',  $content );
}
/**
 * Is post was submitted by a guest
 * @param  int $post_id question/answer id
 * @return boolean
 */
function gma_is_anonymous( $post_id = 0 ) {
	if ( empty( $post_id ) ) $post_id = get_the_ID();
	$anonymous = get_post_meta( $post_id, '_gma_is_anonymous', true );
	if ( $anonymous ) {
		return true;
	}
	return false;
}

function gma_answer_get_edit_content( $post_id = false ) {
	if ( ! $post_id ) {
		$post_id = get_the_ID();
	}

	$content = get_post_field( 'post_content', $post_id );

	return apply_filters( 'gma_answer_get_edit_content', $content, $post_id );
}

function gma_question_get_edit_content( $post_id = false ) {
	if ( ! $post_id ) {
		$post_id = get_the_ID();
	}

	$content = get_post_field( 'post_content', $post_id );

	return apply_filters( 'gma_question_get_edit_content', $content, $post_id );
}

function gma_question_get_edit_title( $post_id = false ) {
	if ( $post_id ) {
		$post_id = get_the_ID();
	}

	$title = get_the_title( $post_id );

	return apply_filters( 'gma_question_get_edit_title', $title, $post_id );
}

function gma_comment_get_edit_content( $comment_id ) {
	$comment_content = get_comment_text( $comment_id );

	return apply_filters( 'gma_comment_get_edit_content', $comment_content, $comment_id );
}

function gma_get_latest_action_date( $question = false, $before = '<span>', $after = '</span>' ){
	if ( ! $question ) {
		$question = get_the_ID();
	}
	global $post, $gma_general_settings;

	$question_list_link = isset( $gma_general_settings['pages']['archive-question'] ) ? get_permalink( $gma_general_settings['pages']['archive-question'] ) : false;
	$latest_answer = gma_get_latest_answer( $question );
	$last_activity_date = $latest_answer ? $latest_answer->post_date_gmt : get_post_field( 'post_date_gmt', $question );
	$post_id = $latest_answer ? $latest_answer->ID : $question;
	$author_id = $post->post_author;
	$author_url = '';
	if ( $author_id == 0 || gma_is_anonymous( $post_id ) ) {
		$anonymous_name = get_post_meta( $post_id, '_gma_anonymous_name', true );
		$author_email = get_post_meta( $post_id, '_gma_anonymous_email', true );
		if ( $anonymous_name ) {
			$display_name = $anonymous_name . ' ';
		} else {
			$display_name = __( 'Anonymous', 'give-me-answer-lite' )  . ' ';
		}
	} else {
		$display_name = get_the_author_meta( 'display_name', $author_id );
		$author_url = $question_list_link
                      ? add_query_arg( array( 'user-question' => get_the_author_meta( 'user_login', $author_id ) ), $question_list_link )
                      : get_author_posts_url( $author_id );
		$author_email = get_the_author_meta( 'user_email', $author_id );
	}
	$author_avatar = wp_cache_get( 'avatar_of_' . $author_id, 'gma' );
	if ( false === $author_avatar ) {
		$author_avatar = get_avatar( $author_email, 48 );
		wp_cache_set( 'avatar_of_'. $author_email, $author_avatar, 'gma', 60*60*24*7 );
	}
	$author_display = gma_is_anonymous() ? $display_name : sprintf( '<a href="%1$s" title="%2$s" rel="author">%3$s</a>', $author_url, esc_attr( sprintf( __( 'Posts by %s' ), $display_name ) ), $display_name );
	$author_link = sprintf(
		'<span class="gma-author"><span class="gma-user-avatar">%2$s</span>%1$s</span>',
		$author_display,
		$author_avatar
	);
	
	if ( $last_activity_date && $post->last_activity_type == 'answer' ) {
		$date = human_time_diff( strtotime( $last_activity_date ), current_time( 'timestamp' ) );
		return sprintf( __( '%s answered <span class="gma-date">%s</span> ago', 'give-me-answer-lite' ), $author_link, $date );
	}

	if ( 'gma-answer' == get_post_type( $question ) ) {
		return sprintf( __( '%s answered <span class="gma-date">%s</span> ago', 'give-me-answer-lite' ), $author_link, human_time_diff( get_post_time( 'U', true, $question ) ) );
	}
	return sprintf( __( '%s asked <span class="gma-date">%s</span> ago', 'give-me-answer-lite' ), $author_link, human_time_diff( get_post_time( 'U', true, $question ) ) );
}

function gma_is_edit() {
	$gets = array( 'edit', 'comment_edit' );

	foreach( $gets as $get ) {
		if ( isset( $_GET[ $get ] ) && is_numeric( $_GET[ $get ] ) ) {
			return true;
		}
	}

	return false;
}

/**
 * Check if we are sharing a post or not
 *
 * @since 1.0
 * @return bool
 */
function gma_is_share() {
    return isset( $_GET['share'] ) && absint($_GET['share']);
}

/**
 * Check if in close mode
 *
 * @since 1.0
 * @return bool
 */
function gma_is_close() {
    if (isset($_GET['action']) && strtolower($_GET['action']) == 'close') {
        return isset($_GET['queid']) && absint($_GET['queid']);
    }
    return false;
}


/**
 * Check if in add comment mode
 *
 * @since 1.0
 *
 * @return bool
 */
function gma_is_add_comment() {
    if (isset($_GET['action']) && 'addcomment' == strtolower($_GET['action'])) {
        return isset($_GET['quoraid']) && absint($_GET['quoraid']) && gma_current_user_can('post_comment');
    }
    return false;
}

class GMA_Posts_Base {

	private $slug;
	private $labels;


	public function __construct( $slug, $labels ) {
		$this->slug = $slug;
		$this->labels = is_array( $labels ) ? $labels : array();

		// add posttype
		add_action( 'init', array( $this, 'register' ) );

		// Do any init by it self
		add_action( 'init', array( $this, 'init' ) );

		// Custom Admin List Table
		add_filter( 'manage_posts_columns', array( $this, 'columns_head' ) );

		// Auto convert url in the conthen to clickable and no-follow
		add_filter( 'the_content', array( $this, 'auto_convert_urls' ) );

		add_filter( 'wp_insert_post_data', array( $this, 'hook_on_update_anonymous_post' ), 10, 2 );
	}

	// Abstract, do all init actions for itself
	public function init(){}

	public function columns_head( $default ){
	    return $default;
	}

	public function get_slug() {
		return $this->slug;
	}

	public function get_name_labels() {
		return wp_parse_args( $this->labels, array(
			'plural' => __( 'GMA Posts', 'give-me-answer-lite' ),
			'singular' => __( 'GMA Post', 'give-me-answer-lite' ),
			'rewrite' => true,
		) );
	}

	public function set_labels() {
		$names = $this->get_name_labels();

		return $labels = array(
			'name'                => $names['plural'],
			'singular_name'       => $names['singular'],
			'add_new'             => __( 'Add New', 'give-me-answer-lite' ) . ' ' . $names['singular'],
			'add_new_item'        => __( 'Add New', 'give-me-answer-lite' ) . ' ' . $names['singular'],
			'edit_item'           => __( 'Edit', 'give-me-answer-lite' ) . ' ' . $names['singular'],
			'new_item'            => __( 'New', 'give-me-answer-lite' ) . ' ' . $names['singular'],
			'view_item'           => __( 'View', 'give-me-answer-lite' ) . ' ' . $names['singular'],
			'search_items'        => __( 'Search ', 'give-me-answer-lite' ) . $names['plural'],
			'not_found'           => $names['plural'] . ' ' . __( 'not found', 'give-me-answer-lite' ),
			'not_found_in_trash'  => $names['plural'] . ' ' . __( 'not found in Trash', 'give-me-answer-lite' ),
			'parent_item_colon'   => __( 'Parent:', 'give-me-answer-lite' ) . ' ' . $names['singular'],
			'menu_name'           => isset( $names['menu'] ) ? $names['menu'] : $names['plural'],
		);
	}

	public function register() {
		$names = $this->get_name_labels();
		
		$this->register_taxonomy();

		$args = array(
			'labels'              => array(),
			'hierarchical'        => false,
			'description'         => 'description',
			'taxonomies'          => array(),
			'public'              => true,
			'show_ui'             => true,
			'show_in_menu'        => false,
			'show_in_admin_bar'   => true,
            'show_in_nav_menus'   => true,
            'menu_icon'           => null,
            'menu_position'       => null,
			'publicly_queryable'  => true,
			'exclude_from_search' => false,
			'has_archive'         => true,
			'query_var'           => true,
			'can_export'          => true,
			'rewrite'             => $names['rewrite'],
			'capability_type'     => 'post',
			'supports'            => array(
				'title',
                'editor',
                'author',
                'thumbnail',
				'excerpt',
                'custom-fields',
                'trackbacks',
                'comments',
				'revisions',
                'page-attributes',
                'post-formats'
			)
		);

		foreach ( $args as $key => $value ) {
			$method = 'set_' . $key;
			if ( method_exists( $this, $method ) ) {
				$args[$key] = call_user_func( array( $this, $method ) );
			}
		}

		register_post_type( $this->get_slug(), $args );
	}

	public function register_taxonomy() {}

	public function pre_content_filter( $content ) {
		return preg_replace_callback( '/<( code )( [^>]* )>( .* )<\/( code )[^>]*>/isU' , array( $this, 'convert_pre_entities' ),  $content );
	}

	public function pre_content_kses( $content ) {
		// tags allowed for post content
		$filter = apply_filters( 'gma_filter_kses', array(
			'a'             => array(
				'href'  => array(),
				'title' => array()
			),
			'br'            => array(),
			'em'            => array(),
			'strong'        => array(),
			'code'          => array(
				'class' => array()
			),
			'blockquote'    => array(),
			'quote'         => array(),
			'span'          => array(
				'style' 	=> array()
			),
			'img'           => array(
				'src'    	=> array(),
				'alt'    	=> array(),
				'width'  	=> array(),
				'height' 	=> array(),
				'style'  	=> array()
			),
			'ul'            => array(),
			'li'            => array(),
			'ol'            => array(),
			'pre'           => array()
		));

		return wp_kses( $content, $filter );
	}

	public function convert_pre_entities( $matches ) {
		$string = $matches[0];
		preg_match( '/class=\\\"( [^\\\"]* )\\\"/', $matches[2], $sub_match );
		if ( empty( $sub_match ) ) {
			$string = str_replace( $matches[1], $matches[1] . ' class="prettyprint"', $string );
		} else {
			if ( strpos( $sub_match[1], 'prettyprint' ) === false ) {
				$new_class = str_replace( $sub_match[1], $sub_match[1] . ' prettyprint', $sub_match[0] );
				$string = str_replace( $matches[2], str_replace( $sub_match[0], $new_class, $matches[2] ), $string );
			}
		}

		$string = str_replace( $matches[3],  htmlentities( $matches[3], ENT_COMPAT , 'UTF-8', false  ), $string );
		
		return '<pre>' . $string . '</pre>';
	}

	public function auto_convert_urls( $content ) {
		global $post;
		if ( is_single() && ( 'gma-question' == $post->post_type || 'gma-answer' == $post->post_type ) ) {
			$content = make_clickable( $content );
			$content = preg_replace_callback( '|<a (.+?)>|i', array( $this, 'auto_nofollow_callback' ), $content );
		}
		return $content;
	}

	public function auto_nofollow_callback( $matches ) {
		$text = $matches[1];
		$text = str_replace( array( ' rel="nofollow"', " rel='nofollow'" ), '', $text );
		return "<a $text rel=\"nofollow\">";
	}

	public function hook_on_update_anonymous_post( $data, $postarr ) {
		if ( isset( $postarr['ID'] ) && get_post_meta( $postarr['ID'], '_gma_is_anonymous', true ) ) {
			$data['post_author'] = 0;
		} 
		return $data;
	}

	public function rewrite() {
		return true;
	}
}

?>