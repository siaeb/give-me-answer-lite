<?php
	global $wp_query;
	$user_id = isset( $_GET[ 'user' ] ) ? sanitize_text_field( $_GET[ 'user' ] ) : '';
	$user_id = absint( $user_id );
	if ( ! $user_id ) wp_redirect( home_url() );

	$user = gma_get_user_info( $user_id );
	if ( ! $user ) wp_redirect( home_url() );
?>