<?php

defined( 'ABSPATH' ) || exit;


/**
 *  Give Me Answer Shortcode
 */
class GMA_Shortcode {

	public function __construct() {
		add_shortcode( 'gma-list-questions', array( $this, 'archive_question') );
		add_shortcode( 'gma-submit-question-form', array( $this, 'submit_question_form_shortcode') );
		add_shortcode(  'gma-user-profile', [$this, 'user_profile_display'] );
		add_filter( 'the_content', array( $this, 'post_content_remove_shortcodes' ), 0 );
	}

	public function sanitize_output( $buffer ) {
		$search = array(
			'/\>[^\S ]+/s',  // strip whitespaces after tags, except space
			'/[^\S ]+\</s',  // strip whitespaces before tags, except space
			'/(\s)+/s',       // shorten multiple whitespace sequences
			"/\r/",
			"/\n/",
			"/\t/",
			'/<!--[^>]*>/s',
		);

		$replace = array(
			'>',
			'<',
			'\\1',
			'',
			'',
			'',
			'',
		);

		$buffer = preg_replace( $search, $replace, $buffer );
		return $buffer;
	}


	public function user_profile_display() {
	    include_once GMA_DIR . 'templates/profile/profile.php';
    }

	public function archive_question( $atts = array() ) {
		global $wp_query, $gma, $script_version, $gma_sript_vars, $gma_atts;
		$gma_atts = (array)$atts;
		$gma_atts['page_id'] = isset($wp_query->post) && isset($wp_query->post->ID) && $wp_query->post->ID ? $wp_query->post->ID : 0;
		ob_start();

		if ( isset( $atts['category'] ) ) {
			$atts['tax_query'][] = array(
				'taxonomy'  => 'gma-question_category',
				'terms'     => esc_html( $atts['category'] ),
				'field'     => 'slug'
			);
			unset( $atts['category'] );
		}

		if ( isset( $atts['tag'] ) ) {
			$atts['tax_query'][] = array(
				'taxonomy' => 'gma-question_tag',
				'terms' => esc_html( $atts['tag'] ),
				'field' => 'slug'
			);
			unset( $atts['tag'] );
		}

		gma_lite()->template->remove_all_filters( 'the_content' );
		gma_lite()->filter->prepare_archive_posts( $atts );
		echo '<div class="gma-container" >';
		gma_load_template( 'archive', 'question' );
		echo '</div>';
		$html = ob_get_contents();

		gma_lite()->template->restore_all_filters( 'the_content' );

		ob_end_clean();
		wp_enqueue_script( 'jquery-ui-autocomplete' );
		wp_enqueue_script( 'gma-questions-list', GMA_URI . 'assets-public/js/gma-questions-list.js', array( 'jquery', 'jquery-ui-autocomplete' ), $script_version, true );
		$gma_sript_vars[ 'ajaxurl' ]    = admin_url( 'admin-ajax.php' );
		$gma_sript_vars[ 'query_vars' ] = $wp_query->gma_questions->query_vars;
		$gma_sript_vars[ 'baseurl' ]    = get_permalink( $gma_atts[ 'page_id' ] );
		if ( false === gma_stop_generating_nonce() ) {
			$gma_sript_vars['_wpnonce'] = wp_create_nonce( '_gma_questions_list' );
        }
		wp_localize_script( 'gma-questions-list', 'gma', $gma_sript_vars );
		return apply_filters( 'gma-shortcode-question-list-content', $this->sanitize_output( $html ) );
	}

	public function submit_question_form_shortcode() {
		global $gma, $gma_sript_vars, $script_version, $gma_general_settings;
		ob_start();

		gma_lite()->template->remove_all_filters( 'the_content' );

		echo '<div class="gma-container" >';
		gma_load_template( 'question', 'submit-form' );
		echo '</div>';
		$html = ob_get_contents();

		gma_lite()->template->restore_all_filters( 'the_content' );

		ob_end_clean();
		wp_enqueue_script( 'jquery-ui-autocomplete' );
		wp_enqueue_script( 'gma-submit-question', GMA_URI . 'assets-public/js/gma-submit-question.js', array( 'jquery', 'jquery-ui-autocomplete' ), $script_version, true );
		$gma_sript_vars['qs_min_length']   = $gma_general_settings[ 'question' ][ 'min-length' ];
		$gma_sript_vars['qs_max_length']   = $gma_general_settings[ 'question' ][ 'max-length' ];
		$gma_sript_vars['qs_min_tags']     = $gma_general_settings[ 'min-tags-per-question' ];
		$gma_sript_vars['qs_max_tags']     = $gma_general_settings[ 'tags-per-question' ];
		$gma_sript_vars['predefined_tags'] = $gma_general_settings[ 'use-predefined-tags-for-question' ];
		$gma_sript_vars['display_tags']    = $gma_general_settings[ 'submit-question-display-tags' ] ? 'on' : 'off';
		$gma_sript_vars['l10n']            = [
		    'title-required'   => __('Required', 'give-me-answer-lite'),
		    'title-min-length' => __('The minimum length of the question title should be %s characters', 'give-me-answer-lite'),
		    'min-tags'         => __('Select at least %s tag(s)', 'give-me-answer-lite'),
        ];
		wp_localize_script( 'gma-submit-question', 'gma', $gma_sript_vars );
		return $this->sanitize_output( $html );
	}


	function post_content_remove_shortcodes( $content ) {
		$shortcodes = array(
			'gma-list-questions',
			'gma-submit-question-form',
		);
		if ( is_singular( 'gma-question' ) || is_singular( 'gma-answer' ) ) {
			foreach ( $shortcodes as $shortcode_tag ) 
				remove_shortcode( $shortcode_tag );
		}
		/* Return the post content. */
		return $content;
	}

	function question_list( $atts ) {
		extract( shortcode_atts( array(
			'categories' 	=> '',
			'number' 		=> '',
			'title' 		=> __( 'Question List', 'give-me-answer-lite' ),
			'orderby' 		=> 'modified',
			'order' 		=> 'DESC'
		), $atts ) );

		$args = array(
			'post_type' 		=> 'gma-question',
			'posts_per_page' 	=> $number,
			'orderby' 			=> $orderby,
			'order' 			=> $order,
		);

		if ( $term ) {
			$args['tax_query'][] = array(
				'taxonomy' 	=> 'gma-question_category',
				'terms' 	=> explode( ',', $categories ),
				'field' 	=> 'slug'
			);
		}

		if ( $title ) {
			echo '<h3>';
			echo $title;
			echo '</h3>';
		}

	}
}

?>