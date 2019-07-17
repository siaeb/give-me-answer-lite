<?php

defined( 'ABSPATH' ) || exit;

class GMA_DB_Profile_Visit extends GMA_BaseDb {
	/**
	 * Get things started
	 *
	 * @access  public
	 * @since   1.0
	 */
	public function __construct() {

		global $wpdb;

		$this->table_name  = $wpdb->prefix . 'gma_profile_visit';
		$this->primary_key = 'id';
		$this->version     = '1.0';

		$this->register_table();

        parent::__construct();
	}

	/**
	 * Register the table with $wpdb so the metadata api can find it
	 *
	 * @access  public
	 * @since   1.0
	 */
	public function register_table() {
		global $wpdb;
		$wpdb->gma_profile_visit = $this->table_name;
	}


	/**
	 * Get table columns and data types
	 *
	 * @access  public
	 * @since   1.0
	 */
	public function get_columns() {
		return array(
			'id'         => '%d',
			'visitor_id' => '%d',
			'visited_id' => '%d',
			'created'    => '%s',
		);
	}


	public function get_user_visited( $visited_id, $order_by = 'created', $order = 'DESC', $limit = 10 ) {
		global $wpdb;
		$query = "
			SELECT pv.visitor_id `userid`, u.user_login `username`, CONCAT(um3.meta_value, ' ', um4.meta_value ) `fullname`, um.meta_value `picture`, um2.meta_value `about`
				FROM `{$this->table_name}` pv
			        INNER JOIN wp_users u ON u.ID = pv.visitor_id        
			        LEFT OUTER JOIN {$wpdb->usermeta} um ON um.user_id = u.ID AND um.meta_key = 'gma_picture_url'
			        LEFT OUTER JOIN {$wpdb->usermeta} um2 ON um2.user_id = u.ID AND um2.meta_key = 'gma_about'
			        LEFT OUTER JOIN {$wpdb->usermeta} um3 ON um3.user_id = u.ID AND um3.meta_key = 'first_name'
			        LEFT OUTER JOIN {$wpdb->usermeta} um4 ON um4.user_id = u.ID AND um4.meta_key = 'last_name'
			            WHERE pv.visited_id = %d	
			            	ORDER BY %s, %s	
			            		LIMIT %d	        
		";
		return $wpdb->get_results( $wpdb->prepare( $query, $visited_id, $order_by, $order, $limit ) );
	}

	/**
	 * Check if user visited
	 *
	 * @since 1.0
	 *
	 * @param integer $visitor_id
	 * @param integer $visited_id
	 *
	 * @return string|null
	 */
	public function is_visit( $visitor_id, $visited_id ) {
		global $wpdb;
		$query = "
			SELECT 1 FROM {$this->table_name} WHERE visitor_id = %d AND visited_id = %d;
		";
		return $wpdb->get_var( $wpdb->prepare( $query, $visitor_id, $visited_id ) );
	}

	/**
	 * Delete all visits
	 *
	 * @since 1.0
	 *
	 * @param integer $visitor_id Vistior ID
	 * @param integer $visited_id Visited ID
	 *
	 * @return bool|int
	 */
	public function delete_all_visit( $visitor_id, $visited_id ) {
		global $wpdb;
		$query = "DELETE FROM {$this->table_name} WHERE visitor_id = %d AND visited_id = %d";
		return $wpdb->query( $wpdb->prepare( $query, $visitor_id, $visited_id ) );
	}

	/**
	 * Create the table
	 *
	 * @access  public
	 * @since   1.0
	 */
	public function create_table() {
		global $wpdb;
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		$sql = "
			CREATE TABLE IF NOT EXISTS `{$this->table_name}` 
			( 
				`id` BIGINT(20) NOT NULL AUTO_INCREMENT , 
				`visitor_id` BIGINT(20) NOT NULL , 
				`visited_id` BIGINT(20) NOT NULL , 
				`created` DATETIME NOT NULL , 
				PRIMARY KEY (`id`)
			) {$wpdb->get_charset_collate()};
		";

		dbDelta( $sql );
	}
}