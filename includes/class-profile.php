<?php

defined( 'ABSPATH' ) || exit;

class GMA_Profile {

	public function __construct() {
		add_filter( 'query_vars', function( $query_vars ) {
			$query_vars[] = 'p';
			$query_vars[] = 'page';
			$query_vars[] = 'paged';
			$query_vars[] = 'gma-user';
			$query_vars[] = 'gma-answers';
			$query_vars[] = 'gma-questions';
			$query_vars[] = 'gma-editprofile';
			$query_vars[] = 'gma-favorites';
			$query_vars[] = 'gma-userwall';
			return $query_vars;
		} );

		add_filter( 'pre_get_document_title', function( $title ) {
			global $wp_query;

			$user_displayname = '';
			$user           = isset( $wp_query->query_vars[ 'gma-user' ] ) ? sanitize_text_field( $wp_query->query_vars['gma-user'] ) : '';
			$answers        = isset( $wp_query->query_vars[ 'gma-answers' ] ) ? sanitize_text_field( $wp_query->query_vars['gma-answers'] ) : '';
			$questions      = isset( $wp_query->query_vars[ 'gma-questions' ] ) ? sanitize_text_field( $wp_query->query_vars['gma-questions'] ) : '';
			$edit_profile   = isset( $wp_query->query_vars[ 'gma-editprofile' ] ) ? sanitize_text_field( $wp_query->query_vars['gma-editprofile'] ) : '';
			$favorites      = isset( $wp_query->query_vars[ 'gma-favorites' ] ) ? sanitize_text_field( $wp_query->query_vars['gma-favorites'] ) : '';
			$user_wall      = isset( $wp_query->query_vars[ 'gma-userwall' ] ) ? sanitize_text_field( $wp_query->query_vars['gma-userwall'] ) : '';

			if ( $user ) {
				$user_displayname = gma_user_displayname( $user );
			}

			if ( $user_displayname && $answers ) {
				return $user_displayname . ' - ' . __('Answers', 'give-me-answer-lite');
			}

			if ( $user_displayname && $questions ) {
				return $user_displayname . ' - ' . __('Questions', 'give-me-answer-lite');
			}

			if ( $user_displayname && $edit_profile ) {
				return $user_displayname . ' - ' . __('Edit Profile', 'give-me-answer-lite');
			}

			if ( $user_displayname && $favorites ) {
				return $user_displayname . ' - ' . __('Favorites', 'give-me-answer-lite');
			}

			if ( $user_displayname && $user_wall ) {
				return $user_displayname . ' - ' . __('Wall', 'give-me-answer-lite');
			}

			return $title;
		}, 1000);

	}

}