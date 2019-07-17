<?php

defined( 'ABSPATH' ) || exit;

class GMA_AssetsLoader {

	public function __construct() {
		add_action( 'admin_enqueue_scripts', [$this, 'load_backend_assets'] );
		add_action( 'wp_enqueue_scripts', [$this, 'load_frontend_assets' ]);
	}

	/**
	 * Load backend assets
	 *
	 * @since 1.0
	 *
	 * @return void
	 */
	function load_backend_assets() {
		$current_page   = isset( $_GET[ 'page' ] ) ? strtolower( $_GET[ 'page' ] ) : '';
		if ( $this->is_gma_backend_page() ) {

			wp_enqueue_style( 'gma-grid', GMA_URI . 'assets-public/css/gma.css' );

			// NProgress
			wp_enqueue_style( 'gma-nprogress', GMA_URI . 'assets-admin/css/nprogress.css' );
			wp_enqueue_script( 'gma-nprogress', GMA_URI . 'assets-admin/js/nprogress.js', [ 'jquery' ], '', true );

			// Chart JS
			wp_enqueue_style( 'gma-chartjs', GMA_URI . 'assets-admin/css/chart.min.css' );
			wp_enqueue_script( 'gma-chartjs', GMA_URI . 'assets-admin/js/chart.bundle.min.js', [ 'jquery' ], '', true );


			// SweetAlert
			wp_enqueue_script( 'gma-sweetalert2', GMA_URI . 'assets-admin/js/sweetalert2.all.min.js', [ 'jquery' ], '', true );

			// Chosen Select
			wp_enqueue_style( 'gma-chosen-select', GMA_URI . 'assets-admin/css/chosen.min.css' );
			wp_enqueue_script( 'gma-chosen-select', GMA_URI . 'assets-admin/js/chosen.min.js', [ 'jquery' ], '', true );

			wp_enqueue_style( 'gma-izitoast', GMA_URI . 'assets-admin/css/izitoast.min.css' );
			wp_enqueue_script( 'gma-izitoast', GMA_URI . 'assets-admin/js/izitoast.min.js', [ 'jquery' ], '', true );

			// Core
			wp_enqueue_script( 'gma-core', GMA_URI . 'assets-admin/js/gma-core.js', [ 'jquery' ], '', true );
			wp_localize_script( 'gma-core', 'gma_globals', $this->get_core_vars() );

			wp_enqueue_style( 'gma-fontawesome', GMA_URI . 'assets-admin/css/fontawesome.min.css' );


			// Settings
			wp_enqueue_script( 'gma-admin-settings-page', GMA_URI . 'assets-admin/js/gma-admin-settings-page.js', array( 'jquery' ), true, true );
			wp_localize_script( 'gma-admin-settings-page', 'gma_params', [
				'l10n' => [
					'saved'     => __('Saved !', 'give-me-answer-lite'),
					'sms-sent'  => __('SMS Sent !', 'give-me-answer-lite'),
				],
			] );

			// Admin style
			wp_enqueue_style( 'gma-admin-style', GMA_URI . 'assets-admin/css/gma-admin-style.css' );
			if ( is_rtl() ) {
				wp_enqueue_style( 'gma-admin-style-rtl', GMA_URI . 'assets-admin/css/gma-admin-style-rtl.css' );
			}

		}

		if ( $current_page == 'gma-blocked-users' ) {
			wp_enqueue_script( 'gma-add-block-user', GMA_URI . 'assets-admin/js/gma-add-block-user.js', [ 'jquery' ], '', true );
			wp_localize_script( 'gma-add-block-user', 'gma_params', [
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( '_gma_block_user' ),
				'l10n'    => [
					'blocked' => __('The user has been successfully blocked', 'give-me-answer-lite'),
					'remove'  => __('Remove user from blocked list?', 'give-me-answer-lite'),
				],
			] );
		}

		if ( $current_page == 'gma-user-walls' ) {
			wp_enqueue_script( 'gma-admin-user-walls', GMA_URI . 'assets-admin/js/gma-admin-userwall.js', [ 'jquery' ], '', true );
			wp_localize_script( 'gma-admin-user-walls', 'gma_params', [
				'ajaxurl'  => admin_url( 'admin-ajax.php' ),
				'_wpnonce' => wp_create_nonce( '_gma_admin_user_walls' ),
				'l10n'     => [
					'confirmDelete' => __('Want to delete message?', 'give-me-answer-lite'),
				],
			] );
		}

		if ( $current_page == 'give-me-answer-lite' ) {
			wp_enqueue_script( 'gma-dashboard', GMA_URI . 'assets-admin/js/gma-dashboard.js', [ 'jquery' ], '', true );
			wp_localize_script( 'gma-dashboard', 'gma_dashboard', [
				'questions' => [
					'total'          => gma_lite()->statistics->count_total_questions(),
					'answered'       => gma_lite()->statistics->count_answered_questions(),
					'unanswered'     => gma_lite()->statistics->count_unanswered_questions(),
					'statistics'     => gma_lite()->statistics->count_questions_by_month(),
				],
			] );
		}

		if ( $current_page == 'gma-comments' ) {
			wp_enqueue_script( 'admin-comments');
		}

	}

	/**
	 * Load frontend assets
	 *
	 * @since 1.0
	 *
	 * @return void
	 */
	function load_frontend_assets(){
		global $gma, $gma_options, $gma_avatar, $script_version, $gma_sript_vars, $gma_general_settings, $post, $wp_query;

		$terms = get_terms( ['taxonomy' => 'gma-question_tag'], ['hide_empty' => false] );
		$available_terms = [];
		foreach ( $terms as $item ) {
			$available_terms[] = $item->name;
		}
		$gma_sript_vars[ 'available_terms' ] = $available_terms;
		$gma_sript_vars[ 'tags_per_question' ] = isset( $gma_general_settings[ 'tags-per-question' ] ) ? $gma_general_settings[ 'tags-per-question' ] : 6;

		$question_category_rewrite = $gma_general_settings['question-category-rewrite'];
		$question_category_rewrite = $question_category_rewrite ? $question_category_rewrite : 'question-category';
		$question_tag_rewrite = $gma_general_settings['question-tag-rewrite'];
		$question_tag_rewrite = $question_tag_rewrite ? $question_tag_rewrite : 'question-tag';

		$assets_folder = GMA_URI . 'assets-public/';
		$script_version = gma_lite()->get_last_update();


		wp_enqueue_script( 'jquery' );

        wp_register_script(GMA_PREFIX . 'user-summary', GMA_URI . 'assets-public/js/gma-user-summary.js', ['jquery', GMA_PREFIX . 'tippy'], '1.0', true);
        wp_localize_script(GMA_PREFIX . 'user-summary', 'gma_user_summary_params',[
            'ajaxurl' => admin_url('admin-ajax.php'),
            'l10n'    => [
                'loading' => __('Loading...', 'give-me-answer-lite'),
            ],
        ]);

		if( is_singular( 'gma-question' ) ) {
			wp_enqueue_script( 'jquery-effects-core' );
			wp_enqueue_script( 'jquery-effects-highlight' );
		}

		//Register script and styles
		wp_register_style( GMA_PREFIX  . 'tagify', $assets_folder . 'css/tagify.min.css');
		wp_register_script( GMA_PREFIX . 'tagify', $assets_folder . 'js/tagify.min.js', array( 'jquery' ), $script_version, true );

		// Enqueue style
		wp_enqueue_style( 'gma-fontawesome', GMA_URI . 'assets-admin/css/fontawesome.min.css', array(), $script_version );
		if ( is_rtl() ) {
			wp_enqueue_style( 'gma-rtl', GMA_URI . 'assets-public/css/gma-rtl.css', array(), $script_version );
		} else {
			wp_enqueue_style( 'gma', GMA_URI . 'assets-public/css/gma.css', array(), $script_version );
		}
        wp_enqueue_script( 'gma-bootstrap', GMA_URI . 'assets-public/js/bootstrap.bundle.min.js', [], $script_version, true );
		wp_enqueue_style( 'gma-style', GMA_URI . 'assets-public/css/style.css', array(), $script_version );
		wp_enqueue_style( 'gma-nprogress', GMA_URI . 'assets-admin/css/nprogress.css' );
		wp_enqueue_script( 'gma-nprogress', GMA_URI . 'assets-admin/js/nprogress.js', [], $script_version, true );

		wp_enqueue_style( 'gma-izitoast', GMA_URI . 'assets-admin/css/izitoast.min.css' );
		wp_enqueue_script( 'gma-izitoast', GMA_URI . 'assets-admin/js/izitoast.min.js', [], $script_version, true );

		wp_enqueue_style( 'gma-izimodal', GMA_URI . 'assets-admin/css/izimodal.min.css' );
		wp_enqueue_script( 'gma-izimodal', GMA_URI . 'assets-admin/js/izimodal.min.js', [], $script_version, true );

		wp_enqueue_script( 'gma-sweetalert', GMA_URI . 'assets-admin/js/sweetalert2.all.min.js', [], $script_version, true );
		wp_enqueue_script( 'gma-validate', GMA_URI . 'assets-public/js/validate.min.js', [], $script_version, true );
		wp_enqueue_style( 'gma-animate', GMA_URI . 'assets-public/css/animate.min.css');
		wp_enqueue_script( 'gma-animate', GMA_URI . 'assets-public/js/animate.js', [], $script_version, true );
		wp_enqueue_script( 'gma-core', GMA_URI . '/assets-admin/js/gma-core.js', [], $script_version, true );
		wp_localize_script( 'gma-core', 'gma_globals', $this->get_core_vars() );

		$question_category = get_query_var( 'gma-question_category' );

		if ( $question_category ) {
			$question_category_rewrite = $gma_options['question-category-rewrite'] ? $gma_options['question-category-rewrite'] : 'question-category';
			$gma_sript_vars['taxonomy'][$question_category_rewrite] = $question_category;
		}

		$question_tag = get_query_var( 'gma-question_tag' );

		if ( $question_tag ) {
			$question_tag_rewrite = $gma_options['question-tag-rewrite'] ? $gma_options['question-tag-rewrite'] : 'question-category';
			$gma_sript_vars['taxonomy'][$question_tag_rewrite] = $question_tag;
		}


		// Load tagify
		if ( $post ) {
			if( is_singular( 'gma-question' ) || gma_has_shortcode( $post->ID, 'gma-submit-question-form' ) ) {
                wp_enqueue_script( GMA_PREFIX . 'edit-content', GMA_URI . 'assets-public/js/gma-edit-content.js', ['jquery'], '', true );
				wp_enqueue_style( GMA_PREFIX . 'tagify' );
				wp_enqueue_script( GMA_PREFIX . 'tagify' );
			}
		}

		// Single question
		if( is_single() && 'gma-question' == get_post_type() ) {
			global $gma_general_settings;
			wp_enqueue_script( 'gma-single-question', GMA_URI . 'assets-admin/js/gma-single-question.js', array(GMA_PREFIX . 'tippy', GMA_PREFIX . 'popper'), $script_version, true );
			$single_script_vars = $gma_sript_vars;
			$single_script_vars['question_id']          = get_the_ID();
			$single_script_vars['is_anonymous']         = is_user_logged_in() ? 0 : 1;
			$single_script_vars['comment_min_length']   = $gma_general_settings[ 'comment' ][ 'min-length' ];
			$single_script_vars['captcha_status']       = $gma_general_settings[ 'captcha-in-single-question' ] == 1 ? 'on' : 'off';
			$single_script_vars['captcha_type']         = $gma_general_settings[ 'captcha-type' ];
			$single_script_vars['editor']               = $gma_general_settings[ 'editor' ][ 'answer' ];
			$single_script_vars['answer_moderation']    = $gma_general_settings[ 'answer' ][ 'moderation' ] && !gma_is_admin() ? 'on' : 'off';
			$single_script_vars[ 'l10n' ]  = [
				'words'                 => [
					'answer'            => __('answer', 'give-me-answer-lite'),
					'question'          => __('question', 'give-me-answer-lite'),
					'shareinsocials'    => __('Share', 'give-me-answer-lite'),
				],
				'delete-question'      => __('Do you want to delete this question ?', 'give-me-answer-lite'),
				'delete-answer'        => __('Do you want to delete this answer ?', 'give-me-answer-lite'),
				'delete-comment'       => __('Do you want to delete this comment ?', 'give-me-answer-lite'),
				'err-delete-answer'    => __('An error occurred while deleting the answer', 'give-me-answer-lite'),
				'server-not-available' => __('Error communicating with server', 'give-me-answer-lite'),
				'operation-error'      => __('An error has occurred', 'give-me-answer-lite'),
				'validation'           => [
					'name-required'        => __('Name is required.', 'give-me-answer-lite'),
					'email-required'       => __('Email is required.', 'give-me-answer-lite'),
					'email-not-valid'      => __('Email is not valid.', 'give-me-answer-lite'),
					'comment-required'     => __('Comment is required.', 'give-me-answer-lite'),
					'min-comment-length'   => __('Minimum comment length is %s character(s).', 'give-me-answer-lite'),
				],
			];
			wp_localize_script( 'gma-single-question', 'gma', $single_script_vars );

			wp_enqueue_script(GMA_PREFIX . 'user-summary');
		}

		if ( $post && gma_has_shortcode( $post->ID, 'gma-user-profile' ) && ( isset( $_GET[ 'user' ] ) && absint( $_GET['user'] ) && gma_can_user_change_profile_pic($_GET['user']) ) ) {
			wp_enqueue_script( GMA_PREFIX . 'editprofile', GMA_URI . 'assets-admin/js/gma-edit-profile.js', [], $script_version, true );
			wp_localize_script( GMA_PREFIX . 'editprofile', 'gma_params', [
				'ajax_url'            => admin_url( 'admin-ajax.php' ),
				'_wpnonce'            => wp_create_nonce( '_gma-edit-profile' ),
				'user_id'             => gma_get_user_id(),
				'max_upload_size_kb'  => $gma_avatar['max-size-kb'],
				'l10n'     => [
					'saved'               => __('Saved !', 'give-me-answer-lite'),
					'max_upload_size_err' => sprintf( __('Error : Max image size is %s kb', 'give-me-answer-lite'), $gma_avatar['max-size-kb'] ),
				],
			] );
		}

        if ( $post && gma_has_shortcode( $post->ID, 'gma-users' ) ) {
            wp_enqueue_script(GMA_PREFIX . 'user-summary');
        }

		if ( gma_get_user_id() && ( isset( $_GET['section'] ) && $_GET['section'] == 'wall') ) {

			$jsdata= [
				'ajaxurl'   => admin_url( 'admin-ajax.php' ),
				'user_id'   => gma_get_user_id(),
				'l10n'      => [
					'remove' => __('Want to delete message?', 'give-me-answer-lite'),
				],
			];

			if ( false === gma_stop_generating_nonce() ) {
				$jsdata['_wpnonce']  = wp_create_nonce( '_gma_user_wall' );
			}

			wp_enqueue_script( GMA_PREFIX . 'userwall', GMA_URI . 'assets-admin/js/gma-userwall.js', [], $script_version, true );
			wp_localize_script( GMA_PREFIX . 'userwall', 'gma_params', $jsdata );
		}

		wp_enqueue_style(GMA_PREFIX . 'tippy-light', 'https://unpkg.com/tippy.js@4/themes/light.css');
		wp_enqueue_style(GMA_PREFIX . 'tippy-light-border', 'https://unpkg.com/tippy.js@4/themes/light-border.css');
		wp_enqueue_style(GMA_PREFIX . 'tippy-google', 'https://unpkg.com/tippy.js@4/themes/google.css');
		wp_enqueue_style(GMA_PREFIX . 'tippy-translucent', 'https://unpkg.com/tippy.js@4/themes/translucent.css');

		// Tippy.js and Popper.js
        wp_enqueue_script(GMA_PREFIX . 'popper', 'https://unpkg.com/popper.js@1', ['jquery'], '', true);
        wp_enqueue_script(GMA_PREFIX . 'tippy', 'https://unpkg.com/tippy.js@4', ['jquery', GMA_PREFIX . 'popper'], '', true);

	}

	/**
	 * Check if we are in a plugin admin pages
	 *
	 * this is used for loading assets in correct page ( not all pages )
	 *
	 * @since 1.0
	 *
	 * @return bool
	 */
	private function is_gma_backend_page() {
		$gma_pages = [
			'give-me-answer-lite',
			'gma-blocked-users',
			'gma-settings',
			'gma-dashboard',
		];
		$gma_post_types = ['gma-question', 'gma-answer'];
		$gma_pages      = apply_filters( 'gma_backend_pages', $gma_pages );
		$gma_post_types = apply_filters( 'gma_post_types', $gma_post_types );

		if ( isset( $_GET['page'] ) && in_array( $_GET['page'], $gma_pages ) ) return true;
		if ( isset( $_GET[ 'post_type' ]) && in_array( $_GET['post_type'], $gma_post_types ) ) return true;

		return false;
	}

	/**
	 * Global js variables
	 *
	 * @since 1.0
	 *
	 * @return array
	 */
	private function get_core_vars() {
		return [
			'l10n' => [
				'timeout'       => __('The server is not responding. Please try again after a few moments.', 'give-me-answer-lite'),
				'success'       => __('Done !', 'give-me-answer-lite'),
				'failure'       => __('Error in operation. Please try again', 'give-me-answer-lite'),
				'loading'       => __('Please wait', 'give-me-answer-lite'),
				'gotit'         => __('Got it!', 'give-me-answer-lite'),
				'yes'           => __('Yes', 'give-me-answer-lite'),
				'no'            => __('No', 'give-me-answer-lite'),
				'cancel'        => __('Cancel', 'give-me-answer-lite'),
				'operation'     => [
					'update'    => __('The information was successfully edited', 'give-me-answer-lite'),
					'delete'    => __('The information was successfully deleted', 'give-me-answer-lite'),
					'insert'    => __('The information was successfully saved', 'give-me-answer-lite'),
				],
			],
			'is_rtl'        => is_rtl() ? 1 : 0,
		];
	}


}