<?php

defined( 'ABSPATH' ) || exit;

function gma_archive_question_filter_layout() {
    global $gma_general_settings;
    if ( isset( $gma_general_settings[ 'show-archive-filter' ] ) && $gma_general_settings[ 'show-archive-filter' ] ) {
	    gma_load_template( 'archive', 'question-filter' );
    }
}
add_action( 'gma_before_questions_archive', 'gma_archive_question_filter_layout', 12 );

function gma_search_form() {
    global $gma_general_settings;
    $show_searchbox = isset( $gma_general_settings[ 'show-archive-search' ] ) && $gma_general_settings[ 'show-archive-search' ];
	?>
    <div class="row">
        <div class="<?php if ( gma_current_user_can( 'post_question' )) echo 'col-sm-8 col-md-7';else echo 'col-12'; ?>">
            <?php if ( $show_searchbox ) { ?>
                <form id="gma-search" class="gma-search mb-1 mb-md-2">
                    <input data-nonce="<?php echo wp_create_nonce( '_gma_filter_nonce' ) ?>" class="form-control rounded-0" type="text" placeholder="<?php _e( 'Search', 'give-me-answer-lite' ); ?>" name="qs" value="<?php echo isset( $_GET['qs'] ) ? esc_html( $_GET['qs'] ) : '' ?>">
                </form>
            <?php } ?>
        </div>
	    <?php if ( gma_current_user_can( 'post_question' )) : ?>
            <div class="col-sm-4 col-md-5 text-right">
                <div class="gma-ask-question">
                    <a class="btn btn-outline-primary btn-responsive-block mb-1 mb-md-0 btn-sm px-2 py-2 font-weight-bold gma-ask-question" href="<?php echo gma_get_ask_link(); ?>">
					    <?php _e( 'Ask Question', 'give-me-answer-lite' ); ?>
                    </a>
                </div>
            </div>
	    <?php endif; ?>
    </div>
	<?php
}
add_action( 'gma_before_questions_archive', 'gma_search_form', 11 );

function gma_class_for_question_details_container(){
	$class = array();
	$class[] = 'question-details';
	$class = apply_filters( 'gma-class-questions-details-container', $class );
	echo implode( ' ', $class );
}

add_action( 'gma_after_answers_list', 'gma_answer_paginate_link' );
function gma_answer_paginate_link() {
	global $wp_query;
	$question_url = get_permalink();
	$page = isset( $_GET['ans-page'] ) ? intval( $_GET['ans-page'] ) : 1;

	$args = array(
		'base' => add_query_arg( 'ans-page', '%#%', $question_url ),
		'format' => '',
		'current' => $page,
		'total' => $wp_query->gma_answers->max_num_pages,
        'prev_text' => __('prev', 'give-me-answer-lite'),
        'next_text' => __('next', 'give-me-answer-lite'),
	);

	$paginate = paginate_links( $args );
	$paginate = str_replace( 'page-number', 'gma-page-number', $paginate );
	$paginate = str_replace( 'current', 'gma-current', $paginate );
	$paginate = str_replace( 'next', 'Next', $paginate );
	$paginate = str_replace( 'prev', 'Prev', $paginate );
	$paginate = str_replace( 'dots', 'gma-dots bg-white text-secondary border', $paginate );

	if ( $wp_query->gma_answers->max_num_pages > 1 ) {
		echo '<div class="gma-pagination">';
		echo $paginate;
		echo '</div>';
	}
}

function gma_question_paginate_link() {
	global $wp_query, $gma_general_settings, $gma_atts;

	$archive_question_url = get_permalink( $gma_general_settings['pages']['archive-question'] );
	$page_text = gma_is_front_page() ? 'page' : 'paged';
	$page = get_query_var( $page_text ) ? get_query_var( $page_text ) : 1;

	$tag = get_query_var( 'gma-question_tag' ) ? get_query_var( 'gma-question_tag' ) : false;
	$cat = get_query_var( 'gma-question_category' ) ? get_query_var( 'gma-question_category' ) : false;

	$url = $cat 
			? get_term_link( $cat, get_query_var( 'taxonomy' ) ) 
			: ( $tag ? get_term_link( $tag, get_query_var( 'taxonomy' ) ) : $archive_question_url );

	if(isset($gma_atts['category']) && isset($gma_atts['page_id']) && $gma_atts['page_id']){
		$url = get_permalink($gma_atts['page_id']);
	}

	$args = array(
		'base'      => add_query_arg( $page_text, '%#%', $url ),
		'format'    => '',
		'current'   => $page,
		'total'     => $wp_query->gma_questions->max_num_pages,
        'prev_text' => __('Prev' , 'give-me-answer-lite'),
        'next_text' => __('Next' , 'give-me-answer-lite'),
	);


	$paginate   = paginate_links( $args );
	$paginate   = str_replace( 'page-number', 'gma-page-number', $paginate );
	$paginate   = str_replace( 'current', 'gma-current', $paginate );
//	foreach ( $paginate as &$item ) {
//

//		if ( strpos( $item, 'dots' )  ) continue;
//
//	    $a        = new SimpleXMLElement( $item );
//		$originUrl = parse_url( $a[ 'href' ] );
//		parse_str($originUrl['query'], $queryString);
//		$new_url = $originUrl['scheme'].'://'.$originUrl['host'].$originUrl['path'].'?paged='.$queryString['paged'];
//		$item = preg_replace('/"[a-zA-Z0-9.\/\-\?\&]*"/', $new_url, $item);
//	}

	if ( $wp_query->gma_questions->max_num_pages > 1 ) {
		echo '<div class="gma-pagination">';
		    echo $paginate;
		echo '</div>';
	}
}

function gma_question_button_action() {
	$html = '';
	if ( is_user_logged_in() ) {
		$followed = gma_is_followed() ? 'followed' : 'follow';
		$text = __( 'Subscribe', 'give-me-answer-lite' );
		$html .= '<label for="gma-favorites">';
		$html .= '<input type="checkbox" id="gma-favorites" data-post="'. get_the_ID() .'" data-nonce="'. wp_create_nonce( '_gma_follow_question' ) .'" value="'. $followed .'" '. checked( $followed, 'followed', false ) .'/>';
		$html .= '<span>' . $text . '</span>';
		$html .= '</label>';
		if ( gma_current_user_can( 'edit_question' ) ) {
			$html .= '<a class="gma_edit_question" href="'. add_query_arg( array( 'edit' => get_the_ID() ), get_permalink() ) .'">' . __( 'Edit', 'give-me-answer-lite' ) . '</a> ';
		}

		if ( gma_current_user_can( 'delete_question' ) ) {
			$action_url = add_query_arg( array( 'action' => 'gma_delete_question', 'question_id' => get_the_ID() ), admin_url( 'admin-ajax.php' ) );
			$html .= '<a class="gma_delete_question" href="'. wp_nonce_url( $action_url, '_gma_action_remove_question_nonce' ) .'">' . __( 'Delete', 'give-me-answer-lite' ) . '</a> ';
		}
	}

	echo apply_filters( 'gma_question_button_action', $html );
}

function gma_answer_button_action() {
	$html = '';
	if ( is_user_logged_in() ) {
		if ( gma_current_user_can( 'edit_answer' ) ) {
			$parent_id = gma_get_question_from_answer_id();
			$html .= '<a class="gma_edit_question" href="'. add_query_arg( array( 'edit' => get_the_ID() ), get_permalink( $parent_id ) ) .'">' . __( 'Edit', 'give-me-answer-lite' ) . '</a> ';
		}

		if ( gma_current_user_can( 'delete_answer' ) ) {
			$action_url = add_query_arg( array( 'action' => 'gma_delete_answer', 'answer_id' => get_the_ID() ), admin_url( 'admin-ajax.php' ) );
			$html .= '<a class="gma_delete_answer" href="'. wp_nonce_url( $action_url, '_gma_action_remove_answer_nonce' ) .'">' . __( 'Delete', 'give-me-answer-lite' ) . '</a> ';
		}
	}

	echo apply_filters( 'gma_answer_button_action', $html );
}


function gma_question_add_class( $classes, $class, $post_id ){
	if ( get_post_type( $post_id ) == 'gma-question' ) {

		$have_new_reply = gma_have_new_reply();
		if ( $have_new_reply == 'staff-answered' ) {
			$classes[] = 'staff-answered';
		}
	}
	return $classes;
}
add_action( 'post_class', 'gma_question_add_class', 10, 3 );

/**
 * callback for comment of question
 */
function gma_answer_comment_callback( $comment, $args, $depth ) {
	$GLOBALS['comment'] = $comment;
	global $post;

	if ( get_user_by( 'id', $comment->user_id ) ) {
		gma_load_template( 'content', 'comment' );
	}
}

function gma_question_comment_callback( $comment, $args, $depth ) {
	$GLOBALS['comment'] = $comment;
	global $post;
	gma_load_template( 'content', 'comment' );
}

function gma_body_class( $classes ) {
	global $post, $gma_options;
	if ( ( $gma_options['pages']['archive-question'] && is_page( $gma_options['pages']['archive-question'] )  )
		|| ( is_archive() &&  ( 'gma-question' == get_post_type()
				|| 'gma-question' == get_query_var( 'post_type' )
				|| 'gma-question_category' == get_query_var( 'taxonomy' )
				|| 'gma-question_tag' == get_query_var( 'taxonomy' ) ) )
	){
		$classes[] = 'list-gma-question';
	}

	if ( $gma_options['pages']['submit-question'] && is_page( $gma_options['pages']['submit-question'] ) ){
		$classes[] = 'submit-gma-question';
	}
	return $classes;
}
add_filter( 'body_class', 'gma_body_class' );


/**
 * Add Icon for Give Me Answer Menu In Dashboard
 */
function gma_add_guide_menu_icons_styles(){
	echo sprintf( '<style type="text/css">#adminmenu .menu-icon-gma-question div.wp-menu-image:before {content: "%s";}</style>', '\f223' ) ;
}
add_action( 'admin_head', 'gma_add_guide_menu_icons_styles' );

function gma_load_template( $name, $extend = false, $include = true ){
	global $gma;
	gma_lite()->template->load_template( $name, $extend, $include );
}

function gma_post_class( $post_id = false ) {
	$classes = array();

	if ( !$post_id ) {
		$post_id = get_the_ID();
	}

	if ( 'gma-question' == get_post_type( $post_id ) ) {
		$classes[] = 'gma-question-item';

		if ( !is_singular( 'gma-question' ) && gma_is_sticky( $post_id ) ) {
			$classes[] = 'gma-sticky';
		}
	}

	if ( 'gma-answer' == get_post_type( $post_id ) ) {
		$classes[] = 'gma-answer-item row mx-0';

		if ( gma_is_the_best_answer( $post_id ) ) {
			$classes[] = 'gma-best-answer';
		}

		if ( 'private' == get_post_status( $post_id ) ) {
			$classes[] = 'gma-status-private';
		}
	}

	return implode( ' ', apply_filters( 'gma_post_class', $classes ) );
}

function gma_comment_form( $args = array(), $post_id = null ) {
    global $gma_general_settings;
	if ( null === $post_id )
		$post_id = get_the_ID();
	else
		$post_id = $post_id;
	$commenter = wp_get_current_commenter();
	$user = wp_get_current_user();
	$user_identity = $user->exists() ? $user->display_name : '';
	$args = wp_parse_args( $args );
	if ( ! isset( $args['format'] ) ) {
        $args['format'] = current_theme_supports( 'html5', 'comment-form' ) ? 'html5' : 'xhtml';
    }

	$html5    = 'html5' === $args['format'];
	$fields   = array(
		'author'  => '<div class="input-group my-2"><span class="input-group-append" id="basic-addon2"><span class="input-group-text">'.__('Name', 'give-me-answer-lite').'</span></span><input type="text" class="form-control" name="name" value="" placeholder="' . __('Please enter your firstname', 'give-me-answer-lite') .'"></div>',
		'email'  => '<div class="input-group my-2"><span class="input-group-append" id="basic-addon2"><span class="input-group-text">'.__('Email','give-me-answer-lite').'</span></span><input type="text" class="form-control" name="email" value="" placeholder="' . __('Please enter your email', 'give-me-answer-lite') . '"></div>',
	);

	/**
	 * Filter the default comment form fields.
	 *
	 * @since 3.0.0
	 *
	 * @param array $fields The default comment fields.
	 */
	$fields = apply_filters( 'comment_form_default_fields', $fields );
	$defaults = array(
		'fields'               => $fields,
		'comment_field'        => '',
		'must_log_in'          => '<p class="must-log-in">' . sprintf( __( 'You must be <a href="%s">logged in</a> to post a comment.','give-me-answer-lite' ), wp_login_url( apply_filters( 'the_permalink', get_permalink( $post_id ) ) ) ) . '</p>',
		'logged_in_as'         => '<p class="comment-form-comment"><textarea id="comment" name="comment" placeholder="Comment" rows="4" aria-required="true"></textarea></p>',
		'comment_notes_before' => '<p class="comment-form-comment"><textarea id="comment" name="comment" placeholder="Comment" rows="4" aria-required="true"></textarea></p>',
		'comment_notes_after'  => '<p class="form-allowed-tags">' . sprintf( __( 'You may use these <abbr title="HyperText Markup Language">HTML</abbr> tags and attributes: %s','give-me-answer-lite' ), ' <code>' . allowed_tags() . '</code>' ) . '</p>',
		'id_form'              => 'commentform',
		'id_submit'            => 'submit',
		'title_reply'          => __( 'Leave a Reply','give-me-answer-lite' ),
		'title_reply_to'       => __( 'Leave a Reply to %s','give-me-answer-lite' ),
		'cancel_reply_link'    => __( 'Cancel reply', 'give-me-answer-lite' ),
		'label_submit'         => __( 'Post Comment', 'give-me-answer-lite' ),
		'format'               => 'xhtml',
	);
	/**
	 * Filter the comment form default arguments.
	 *
	 * Use 'comment_form_default_fields' to filter the comment fields.
	 *
	 * @since 3.0.0
	 *
	 * @param array $defaults The default comment form arguments.
	 */
	$args = wp_parse_args( $args, apply_filters( 'comment_form_defaults', $defaults ) );

	// is user blocked ?!


	// is comment open ?!
	if ( comments_open( $post_id )) :
		/**
		 * Fires before the comment form.
		 *
		 * @since 3.0.0
		 */
		do_action( 'comment_form_before' );
		?>

        <div id="gma-respond" class="gma-comment-form">
			<?php if ( ! gma_current_user_can( 'post_comment' ) ) : ?>
				<?php

                echo $args['must_log_in'];

				/**
				 * Fires after the HTML-formatted 'must log in after' message in the comment form.
				 *
				 * @since 3.0.0
				 */
				do_action( 'comment_form_must_log_in_after' );
				?>
			<?php else : ?>
                <form method="post" id="<?php echo esc_attr( $args['id_form'] ); ?>" class="comment-form"<?php echo $html5 ? ' novalidate' : ''; ?>>

					<?php
					/**
					 * Fires before the comment fields in the comment form.
					 *
					 * @since 3.0.0
					 */
					do_action( 'comment_form_before_fields' );

					if ( ! is_user_logged_in() ) {
						echo '<div class="gma-anonymous-fields">';
						foreach ( (array ) $args['fields'] as $name => $field ) {
							/**
							 * Filter a comment form field for display.
							 *
							 * The dynamic portion of the filter hook, $name, refers to the name
							 * of the comment form field. Such as 'author', 'email', or 'url'.
							 *
							 * @since 3.0.0
							 *
							 * @param string $field The HTML-formatted output of the comment form field.
							 */
							echo apply_filters( "comment_form_field_{$name}", $field ) . "\n";
						}
						echo '</div>';
					}

					/**
					 * Fires after the comment fields in the comment form.
					 *
					 * @since 3.0.0
					 */
					do_action( 'comment_form_after_fields' );

					/**
					 * Fires at the top of the comment form, inside the <form> tag.
					 *
					 * @since 3.0.0
					 */
					do_action( 'comment_form_top' );
					?>

                    <?php
                        /**
                         * Filter the 'logged in' message for the comment form for display.
                         *
                         * @since 3.0.0
                         *
                         * @param string $args['logged_in_as'] The logged-in-as HTML-formatted message.
                         * @param array  $commenter            An array containing the comment author's username, email, and URL.
                         * @param string $user_identity        If the commenter is a registered user, the display name, blank otherwise.
                         */
                        echo apply_filters( 'comment_form_logged_in', $args['logged_in_as'], $commenter, $user_identity );
                        ?>
                        <?php
                        /**
                         * Fires after the is_user_logged_in() check in the comment form.
                         *
                         * @since 3.0.0
                         *
                         * @param array  $commenter     An array containing the comment author's username, email, and URL.
                         * @param string $user_identity If the commenter is a registered user, the display name, blank otherwise.
                         */
                        do_action( 'comment_form_logged_in_after', $commenter, $user_identity );
                    ?>

					<?php
					/**
					 * Fires before comment submit button
                     *
                     * @since 1.0
                     *
					 */
                    do_action( 'gma_before_comment_submit_button' );

					/**
					 * Filter the content of the comment textarea field for display.
					 *
					 * @since 3.0.0
					 *
					 * @param string $args['comment_field'] The content of the comment textarea field.
					 */
					echo apply_filters( 'comment_form_field_comment', $args['comment_field'] );
					?>
                    <input name="comment-submit" type="button" id="<?php echo esc_attr( $args['id_submit'] ); ?>" value="<?php echo esc_attr( $args['label_submit'] ); ?>" class="btn btn-primary" />
					<?php comment_id_fields( $post_id ); ?>
					<?php
					/**
					 * Fires at the bottom of the comment form, inside the closing </form> tag.
					 *
					 * @since 1.5.0
					 *
					 * @param int $post_id The post ID.
					 */
					do_action( 'comment_form', $post_id );
					?>
                </form>
			<?php endif; ?>
        </div><!-- #respond -->
		<?php
		/**
		 * Fires after the comment form.
		 *
		 * @since 3.0.0
		 */
		do_action( 'comment_form_after' );
	else :
		/**
		 * Fires after the comment form if comments are closed.
		 *
		 * @since 3.0.0
		 */
		do_action( 'comment_form_comments_closed' );
	endif;
}

function gma_display_sticky_questions(){
	$sticky_questions = get_option( 'gma_sticky_questions', array() );
	if ( ! empty( $sticky_questions ) ) {
		$query = array(
			'post_type' => 'gma-question',
			'post__in' => $sticky_questions,
			'posts_per_page' => 40,
		);
		$sticky_questions = new WP_Query( $query );
		?>
		<div class="sticky-questions">
			<?php while ( $sticky_questions->have_posts() ) : $sticky_questions->the_post(); ?>
				<?php gma_load_template( 'content', 'question' ); ?>
			<?php endwhile; ?>
		</div>
		<?php
		wp_reset_postdata();
	}
}
add_action( 'gma-before-question-list', 'gma_display_sticky_questions' );

function gma_is_sticky( $question_id = false ) {
	if ( ! $question_id ) {
		$question_id = get_the_ID();
	}
	$sticky_questions = get_option( 'gma_sticky_questions', array() );
	if ( in_array( $question_id, $sticky_questions ) ) {
		return true;
	}
	return false;
}


function gma_question_states( $states, $post ){
	if ( gma_is_sticky( $post->ID ) && 'gma-question' == get_post_type( $post->ID ) ) {
		$states[] = __( 'Sticky Question','give-me-answer-lite' );
	}
	return $states;
}
add_filter( 'display_post_states', 'gma_question_states', 10, 2 );


function gma_get_ask_question_link( $echo = true, $label = false, $class = false ){
	global $gma_options;
	$submit_question_link = get_permalink( $gma_options['pages']['submit-question'] );
	if ( $gma_options['pages']['submit-question'] && $submit_question_link ) {


		if ( gma_current_user_can( 'post_question' ) ) {
			$label = $label ? $label : __( 'Ask a question', 'give-me-answer-lite' );
		} elseif ( ! is_user_logged_in() ) {
			$label = $label ? $label : __( 'Login to ask a question', 'give-me-answer-lite' );
			$submit_question_link = wp_login_url( $submit_question_link );
		} else {
			return false;
		}
		//Add filter to change ask question link text
		$label = apply_filters( 'gma_ask_question_link_label', $label );

		$class = $class ? $class  : 'gma-btn-success';
		$button = '<a href="'.$submit_question_link.'" class="gma-btn '.$class.'">'.$label.'</a>';
		$button = apply_filters( 'gma_ask_question_link', $button, $submit_question_link );
		if ( ! $echo ) {
			return $button;
		}
		echo $button;
	}
}

function gma_get_template( $template = false ) {
	$templates = apply_filters( 'gma_get_template', array(
		'single-gma-question.php',
		'page.php',
		'single.php',
		'index.php',
	) );

	$temp_dir = array(
		1 => trailingslashit( get_stylesheet_directory() ),
		10 => trailingslashit( get_template_directory() )
	);

	if ( isset( $template ) ) {
		foreach( $temp_dir as $link ) {
			if ( file_exists( $link . $template ) ) {
				return $link . $template;
			}
		}
	}

	$old_template = $template;
	foreach ( $templates as $template ) {
		if ( $template == $old_template ) {
			continue;
		}
		foreach( $temp_dir as $link ) {
			if ( file_exists( $link . $template ) ) {
				return $link . $template;
			}
		}
	}
	return false;
}

function gma_has_sidebar_template() {
	global $gma_template;
	$template = get_stylesheet_directory() . '/gma-templates/';
	if ( is_single() && file_exists( $template . '/sidebar-single.php' ) ) {
		include $template . '/sidebar-single.php';
		return;
	} elseif ( is_single() ) {
		if ( file_exists( GMA_DIR . 'includes/templates/'.$gma_template.'/sidebar-single.php' ) ) {
			include GMA_DIR . 'includes/templates/'.$gma_template.'/sidebar-single.php';
		} else {
			get_sidebar();
		}
		return;
	}

	return;
}

add_action( 'gma_after_single_question_content', 'gma_load_answers' );
function gma_load_answers() {
	gma_lite()->template->load_template( 'answers' );
}

class GMA_Template {
	private $active = 'default';
	private $page_template = 'page.php';
	public $filters;

	public function __construct() {
		$this->filters = new stdClass();
		add_filter( 'template_include', array( $this, 'question_content' ) );
		//add_filter( 'term_link', array( $this, 'force_term_link_to_setting_page' ), 10, 3 );
		add_filter( 'comments_open', array( $this, 'close_default_comment' ), 10, 2 );

		//Template Include Hook
		add_filter( 'single_template', array( $this, 'redirect_answer_to_question' ), 20 );
		add_filter( 'comments_template', array( $this, 'generate_template_for_comment_form' ), 20 );

		//Wrapper
		add_action( 'gma_before_page', array( $this, 'start_wrapper_content' ) );
		add_action( 'gma_after_page', array( $this, 'end_wrapper_content' ) );

		add_filter( 'option_thread_comments', array( $this, 'disable_thread_comment' ) );

		add_action( 'wp_head', [$this, 'load_custom_css'], 999 );
	}

	function load_custom_css() {
		global $gma_general_settings;
		if ( $gma_general_settings[ 'customize' ]['css']['print-css'] ) {
            ?>
                    <!-- Custom CSS -->
                    <style>
                        <?php echo $gma_general_settings[ 'customize' ]['css']['content']; ?>
                    </style>
            <?php
		}
	}

	public function start_wrapper_content() {
		$this->load_template( 'content', 'start-wrapper' );
		echo '<div class="gma-container" >';
	}

	public function end_wrapper_content() {
		echo '</div>';
		$this->load_template( 'content', 'end-wrapper' );
		wp_reset_query();
	}


	public function redirect_answer_to_question( $template ) {
		global $post, $gma_options;
		if ( is_singular( 'gma-answer' ) ) {
			$question_id = gma_get_post_parent_id( $post->ID );
			if ( $question_id ) {
				wp_safe_redirect( get_permalink( $question_id ) );
				exit( 0 );
			}
		}
		return $template;
	}

	public function generate_template_for_comment_form( $comment_template ) {
		if (  is_single() && ('gma-question' == get_post_type() || 'gma-answer' == get_post_type() ) ) {
			return $this->load_template( 'comments', false, false );
		}
		return $comment_template;
	}

	public function page_template_body_class( $classes ) {
		$classes[] = 'page-template';

		$template_slug  = $this->page_template;
		$template_parts = explode( '/', $template_slug );

		foreach ( $template_parts as $part ) {
			$classes[] = 'page-template-' . sanitize_html_class( str_replace( array( '.', '/' ), '-', basename( $part, '.php' ) ) );
			$classes[] = sanitize_html_class( str_replace( array( '.', '/' ), '-', basename( $part, '.php' ) ) );
		}
		$classes[] = 'page-template-' . sanitize_html_class( str_replace( '.', '-', $template_slug ) );

		return $classes;
	}

	public function question_content( $template ) {
		global $wp_query;
		$gma_options = get_option( 'gma_options' );
		$template_folder = trailingslashit( get_template_directory() );
		if ( isset( $gma_options['pages']['archive-question'] ) ) {
			$page_template = get_post_meta( $gma_options['pages']['archive-question'], '_wp_page_template', true );
		}

		$page_template = isset( $page_template ) && !empty( $page_template ) ? $page_template : 'page.php';
		$this->page_template = $page_template;

		if ( is_singular( 'gma-question' ) ) {
			ob_start();

			remove_filter( 'comments_open', array( $this, 'close_default_comment' ) );

			echo '<div class="gma-container" >';
			$this->load_template( 'single', 'question' );
			echo '</div>';

			$content = ob_get_contents();

			add_filter( 'comments_open', array( $this, 'close_default_comment' ), 10, 2 );

			ob_end_clean();

			// Reset post
			global $post, $current_user;

			$this->reset_content( array(
				'ID'             => $post->ID,
				'post_title'     => $post->post_title,
				'post_author'    => 0,
				'post_date'      => $post->post_date,
				'post_content'   => $content,
				'post_type'      => 'gma-question',
				'post_status'    => $post->post_status,
				'is_single'      => true,
			) );

			$single_template = isset( $gma_options['single-template'] ) ? $gma_options['single-template'] : false;

			$this->remove_all_filters( 'the_content' );
			add_filter( 'body_class', array( $this, 'page_template_body_class' ) );
			return gma_get_template( $page_template );
		}
		if ( is_tax( 'gma-question_category' ) || is_tax( 'gma-question_tag' ) || is_post_type_archive( 'gma-question' ) || is_post_type_archive( 'gma-answer' ) || isset( $wp_query->query_vars['gma-question_tag'] ) || isset( $wp_query->query_vars['gma-question_category'] ) ) {

			$post_id = isset( $gma_options['pages']['archive-question'] ) ? $gma_options['pages']['archive-question'] : 0;
			if ( $post_id ) {
				$page = get_post( $post_id );
				if ( is_tax( 'gma-question_category' ) || is_tax( 'gma-question_tag' ) ) {
					$page->is_tax = true;
				}
				$this->reset_content( $page );
				add_filter( 'body_class', array( $this, 'page_template_body_class' ) );
				return gma_get_template( $page_template );
			}
		}

		if ( is_page( $gma_options['pages']['archive-question'] ) ) {
			$wp_query->is_archive = true;
		}

		return $template;
	}

	public function reset_content( $args ) {
		global $wp_query, $post;
		if ( isset( $wp_query->post ) ) {
			$dummy = wp_parse_args( $args, array(
				'ID'                    => $wp_query->post->ID,
				'post_status'           => $wp_query->post->post_status,
				'post_author'           => $wp_query->post->post_author,
				'post_parent'           => $wp_query->post->post_parent,
				'post_type'             => $wp_query->post->post_type,
				'post_date'             => $wp_query->post->post_date,
				'post_date_gmt'         => $wp_query->post->post_date_gmt,
				'post_modified'         => $wp_query->post->post_modified,
				'post_modified_gmt'     => $wp_query->post->post_modified_gmt,
				'post_content'          => $wp_query->post->post_content,
				'post_title'            => $wp_query->post->post_title,
				'post_excerpt'          => $wp_query->post->post_excerpt,
				'post_content_filtered' => $wp_query->post->post_content_filtered,
				'post_mime_type'        => $wp_query->post->post_mime_type,
				'post_password'         => $wp_query->post->post_password,
				'post_name'             => $wp_query->post->post_name,
				'guid'                  => $wp_query->post->guid,
				'menu_order'            => $wp_query->post->menu_order,
				'pinged'                => $wp_query->post->pinged,
				'to_ping'               => $wp_query->post->to_ping,
				'ping_status'           => $wp_query->post->ping_status,
				'comment_status'        => $wp_query->post->comment_status,
				'comment_count'         => $wp_query->post->comment_count,
				'filter'                => $wp_query->post->filter,

				'is_404'                => false,
				'is_page'               => false,
				'is_single'             => false,
				'is_archive'            => false,
				'is_tax'                => false,
				'current_comment'		=> 0,
			) );
		} else {
			$dummy = wp_parse_args( $args, array(
				'ID'                    => -1,
				'post_status'           => 'private',
				'post_author'           => 0,
				'post_parent'           => 0,
				'post_type'             => 'page',
				'post_date'             => 0,
				'post_date_gmt'         => 0,
				'post_modified'         => 0,
				'post_modified_gmt'     => 0,
				'post_content'          => '',
				'post_title'            => '',
				'post_excerpt'          => '',
				'post_content_filtered' => '',
				'post_mime_type'        => '',
				'post_password'         => '',
				'post_name'             => '',
				'guid'                  => '',
				'menu_order'            => 0,
				'pinged'                => '',
				'to_ping'               => '',
				'ping_status'           => '',
				'comment_status'        => 'closed',
				'comment_count'         => 0,
				'filter'                => 'raw',

				'is_404'                => false,
				'is_page'               => false,
				'is_single'             => false,
				'is_archive'            => false,
				'is_tax'                => false,
				'current_comment'		=> 0,
			) );
		}
		// Bail if dummy post is empty
		if ( empty( $dummy ) ) {
			return;
		}
		// Set the $post global
		$post = new WP_Post( (object ) $dummy );
		setup_postdata( $post );
		// Copy the new post global into the main $wp_query
		$wp_query->post       = $post;
		$wp_query->posts      = array( $post );

		// Prevent comments form from appearing
		$wp_query->post_count 		= 1;
		$wp_query->is_404     		= $dummy['is_404'];
		$wp_query->is_page    		= $dummy['is_page'];
		$wp_query->is_single  		= $dummy['is_single'];
		$wp_query->is_archive 		= $dummy['is_archive'];
		$wp_query->is_tax     		= $dummy['is_tax'];
		$wp_query->current_comment 	= $dummy['current_comment'];

	}

	function disable_thread_comment( $value ) {
		if ( is_singular( 'gma-question' ) ) {
			return false;
		}

		return $value;
	}

	public function close_default_comment( $open, $post_id ) {
		global $gma_options;

		if ( get_post_type( $post_id ) == 'gma-question' || get_post_type( $post_id ) == 'gma-answer' || ( $gma_options['pages']['archive-question'] && $gma_options['pages']['archive-question'] == $post_id) || ( $gma_options['pages']['submit-question'] && $gma_options['pages']['submit-question'] == $post_id) ) {
			return false;
		}
		return $open;
	}

	public function remove_all_filters( $tag, $priority = false ) {
		global $wp_filter, $merged_filters;

		// Filters exist
		if ( isset( $wp_filter[$tag] ) ) {

			// Filters exist in this priority
			if ( ! empty( $priority ) && isset( $wp_filter[$tag][$priority] ) ) {

				// Store filters in a backup
				$this->filters->wp_filter[$tag][$priority] = $wp_filter[$tag][$priority];

				// Unset the filters
				unset( $wp_filter[$tag][$priority] );

				// Priority is empty
			} else {

				// Store filters in a backup
				$this->filters->wp_filter[$tag] = $wp_filter[$tag];

				// Unset the filters
				unset( $wp_filter[$tag] );
			}
		}

		// Check merged filters
		if ( isset( $merged_filters[$tag] ) ) {

			// Store filters in a backup
			$this->filters->merged_filters[$tag] = $merged_filters[$tag];

			// Unset the filters
			unset( $merged_filters[$tag] );
		}

		return true;
	}

	public function restore_all_filters( $tag, $priority = false ) {
		global $wp_filter, $merged_filters;

		// Filters exist
		if ( isset( $this->filters->wp_filter[$tag] ) ) {

			// Filters exist in this priority
			if ( ! empty( $priority ) && isset( $this->filters->wp_filter[$tag][$priority] ) ) {

				// Store filters in a backup
				$wp_filter[$tag][$priority] = $this->filters->wp_filter[$tag][$priority];

				// Unset the filters
				unset( $this->filters->wp_filter[$tag][$priority] );
				// Priority is empty
			} else {

				// Store filters in a backup
				$wp_filter[$tag] = $this->filters->wp_filter[$tag];

				// Unset the filters
				unset( $this->filters->wp_filter[$tag] );
			}
		}

		// Check merged filters
		if ( isset( $this->filters->merged_filters[$tag] ) ) {

			// Store filters in a backup
			$merged_filters[$tag] = $this->filters->merged_filters[$tag];

			// Unset the filters
			unset( $this->filters->merged_filters[$tag] );
		}

		return true;
	}

	public function get_template() {
		return $this->active;
	}

	public function get_template_dir() {
		return apply_filters( 'gma_get_template_dir', 'gma-templates/' );
	}

	public function load_template( $name, $extend = false, $include = true ) {
		if ( $extend ) {
			$name .= '-' . $extend;
		}

		$template = false;
		$template_dir = array(
			GMA_STYLESHEET_DIR . $this->get_template_dir(),
			GMA_TEMP_DIR . $this->get_template_dir(),
			GMA_DIR . 'templates/'
		);

		foreach( $template_dir as $temp_path ) {
			if ( file_exists( $temp_path . $name . '.php' ) ) {
				$template = $temp_path . $name . '.php';
				break;
			}
		}

		$template = apply_filters( 'gma-load-template', $template, $name );

		if ( !$template || !file_exists( $template ) ) {
			_doing_it_wrong( __FUNCTION__, sprintf( "<strong>%s</strong> does not exists in <code>%s</code>.", $name, $template ), '1.4.0' );
			return false;
		}

		if ( ! $include ) {
			return $template;
		}

		include $template;
	}
}

function gma_get_mail_template( $option, $name = '' ) {
	if ( ! $name ) {
		return '';
	}
	$template = get_option( $option );
	if ( $template ) {
		return $template;
	} else {
		if ( file_exists( GMA_DIR . 'templates/email/'.$name.'.html' ) ) {
			ob_start();
			load_template( GMA_DIR . 'templates/email/'.$name.'.html', false );
			$template = ob_get_contents();
			ob_end_clean();
			return $template;
		} else {
			return '';
		}
	}
}