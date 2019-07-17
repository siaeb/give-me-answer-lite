<?php

defined( 'ABSPATH' ) || exit;

class GMA_Transient {

	public function remove_all() {
		global $wpdb;
		$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s", '_transient_gma%') );
	}

}