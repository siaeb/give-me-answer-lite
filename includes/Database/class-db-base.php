<?php
defined( 'ABSPATH' ) || exit;

class GMA_BaseDb {
	/**
	 * The name of our database table
	 *
	 * @access  public
	 * @since   1.0
	 */
	public $table_name;

	/**
	 * The version of our database table
	 *
	 * @access  public
	 * @since   1.0
	 */
	public $version;

	/**
	 * The name of the primary column
	 *
	 * @access  public
	 * @since   1.0
	 */
	public $primary_key;

	/**
	 * Get things started
	 *
	 * @access  public
	 * @since   1.0
	 */
	public function __construct() {}

	/**
	 * Whitelist of columns
	 *
	 * @access  public
	 * @since   1.0
	 * @return  array
	 */
	public function get_columns() {
		return array();
	}

	/**
	 * Default column values
	 *
	 * @access  public
	 * @since   1.0
	 * @return  array
	 */
	public function get_column_defaults() {
		return array();
	}

	/**
	 * Retrieve a row by the primary key
	 *
	 * @access  public
	 * @since   1.0
	 * @return  object
	 */
	public function get( $row_id ) {
		global $wpdb;
		return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $this->table_name WHERE $this->primary_key = %s LIMIT 1;", $row_id ) );
	}

	/**
	 * Retrieve a row by a specific column / value
	 *
	 * @access  public
	 * @since   1.0
	 * @return  object
	 */
	public function get_by( $column, $row_id ) {
		global $wpdb;
		$column = esc_sql( $column );
		return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $this->table_name WHERE $column = %s LIMIT 1;", $row_id ) );
	}

	/**
	 * Retrieve a specific column's value by the primary key
	 *
	 * @access  public
	 * @since   1.0
	 * @return  string
	 */
	public function get_column( $column, $row_id ) {
		global $wpdb;
		$column = esc_sql( $column );
		return $wpdb->get_var( $wpdb->prepare( "SELECT $column FROM $this->table_name WHERE $this->primary_key = %s LIMIT 1;", $row_id ) );
	}

	/**
	 * Retrieve a specific column's value by the the specified column / value
	 *
	 * @access  public
	 * @since   1.0
	 * @return  string
	 */
	public function get_column_by( $column, $column_where, $column_value) {
		global $wpdb;
		$column_where = esc_sql( $column_where );
		$column       = esc_sql( $column );
		return $wpdb->get_var( $wpdb->prepare( "SELECT $column FROM $this->table_name WHERE $column_where = %s LIMIT 1;", $column_value ) );
	}

	/**
	 * Get distinct values in column
	 *
	 * @since 1.0
	 * @access public
	 *
	 * @param $column column name
	 *
	 * @return array|null
	 */
	public function get_column_values( $column ) {
		global $wpdb;
		$column = esc_sql( $column );
		return $wpdb->get_col( "SELECT {$column} FROM {$this->table_name}" );
	}

	/**
	 * Get distinct values in column
	 *
	 * @since 1.0
	 * @access public
	 *
	 * @param $column column name
	 *
	 * @return array|null
	 */
	public function get_column_values_by( $column, $column_where, $column_value ) {
		global $wpdb;
		$column = esc_sql( $column );
		$column_where = esc_sql( $column_where );
		$column_value = esc_sql( $column_value );
		return $wpdb->get_col( $wpdb->prepare("SELECT {$column} FROM {$this->table_name} WHERE {$column_where} = %s", $column_value) );
	}

	/**
	 * Get custom column values
	 *
	 * @param $column_names
	 *
	 * @return array|null|object
	 */
	public function select( $column_names ) {
		global $wpdb;
		foreach ( $column_names as &$item ) {
			$item = esc_sql( $item );
		}
		$columns = implode( ',', $column_names );
		return $wpdb->get_results( $wpdb->prepare("SELECT %s FROM {$this->table_name}", $columns) );
	}

	/**
	 * Get custom column values with condition
	 *
	 * @param $column_names
	 * @param $column_name
	 * @param $column_value
	 * @param string $orderby
	 * @param string $order
	 *
	 * @return array|null|object
	 */
	public function select_with_where( $column_names, $column_name, $column_value, $orderby = '', $order='DESC' ) {
		global $wpdb;

		foreach ( $column_names as &$item ) {
			$item = esc_sql( $item );
		}
		$columns = implode( ',', $column_names );

		if ( $order )   $order     = esc_sql( $order );
		if ( $orderby ) $orderby = esc_sql( $orderby );

		$query = "SELECT {$columns} FROM {$this->table_name} WHERE {$column_name} = ";
		if ( is_string( $column_value ) )
			$query .= '%s';
		else
			$query .= '%d';
		if ( $orderby ) $query .= " ORDER BY {$orderby} {$order}";

		return $wpdb->get_results( $wpdb->prepare($query, $column_value ) );
	}

	public function select_multiple( $column_names, $where_col_name, $where_col_values, $operator='OR' ) {
		global $wpdb;
		if ( ! $where_col_values ) return [];
		foreach ( $column_names as &$item ) {
			$item = esc_sql( $item );
		}
		$columns = implode( ',', $column_names );

		$query = "SELECT {$columns} FROM {$this->table_name} WHERE ";
		foreach ( $where_col_values as $col_value ) {
			$query .= "{$where_col_name} = ";

			if ( is_string( $col_value ) )
				$query .= '%s ';
			else
				$query .= '%d ';

			$query .= $operator . ' ';
		}

		// delete last operator
		$query = substr( $query, 0, strlen( $query ) - (strlen( $operator ) + 1) );

		return $wpdb->get_results( $wpdb->prepare( $query, $where_col_values ) );
	}


	/**
	 * Count rows with specific condition
	 *
	 * @param string  $column_name column name
	 * @param integer $column_value column value
	 *
	 * @since 1.0
	 * @access public
	 *
	 * @return null|string
	 */
	public function count_with_where( $column_name, $column_value ) {
		global $wpdb;
		return $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$this->table_name} WHERE {$column_name} = %d", $column_value ) );
	}

	/**
	 * Count total rows
	 *
	 * @return null|string
	 */
	public function count_all() {
		global $wpdb;
		return $wpdb->get_var( "SELECT COUNT(*) FROM {$this->table_name}" );
	}

	/**
	 * Insert a new row
	 *
	 * @access  public
	 * @since   1.0
	 * @return  int
	 */
	public function insert( $data ) {
		global $wpdb;

		// Set default values
		$data = wp_parse_args( $data, $this->get_column_defaults() );


		// Initialise column format array
		$column_formats = $this->get_columns();

		// Force fields to lower case
		$data = array_change_key_case( $data );


		// White list columns
		$data = array_intersect_key( $data, $column_formats );

		// Reorder $column_formats to match the order of columns given in $data
		$data_keys = array_keys( $data );
		$column_formats = array_merge( array_flip( $data_keys ), $column_formats );

		$wpdb->insert( $this->table_name, $data, $column_formats );

		return $wpdb->insert_id;
	}

	/**
	 * Update a row
	 *
	 * @access  public
	 * @since   1.0
	 * @return  bool
	 */
	public function update( $row_id, $data = array(), $where = '' ) {

		global $wpdb;

		// Row ID must be positive integer
		$row_id = absint( $row_id );

		if( empty( $row_id ) ) {
			return false;
		}

		if( empty( $where ) ) {
			$where = $this->primary_key;
		}

		// Initialise column format array
		$column_formats = $this->get_columns();

		// Force fields to lower case
		$data = array_change_key_case( $data );

		// White list columns
		$data = array_intersect_key( $data, $column_formats );

		// Reorder $column_formats to match the order of columns given in $data
		$data_keys = array_keys( $data );
		$column_formats = array_merge( array_flip( $data_keys ), $column_formats );

		if ( false === $wpdb->update( $this->table_name, $data, array( $where => $row_id ), $column_formats ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Delete a row identified by the primary key
	 *
	 * @access  public
	 * @since   1.0
	 * @return  bool
	 */
	public function delete( $row_id = 0 ) {

		global $wpdb;

		// Row ID must be positive integer
		$row_id = absint( $row_id );

		if( empty( $row_id ) ) {
			return false;
		}

		if ( false === $wpdb->query( $wpdb->prepare( "DELETE FROM $this->table_name WHERE $this->primary_key = %d", $row_id ) ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Delete by specific column value
	 *
	 *
	 * @since 1.0
	 * @access public
	 *
	 * @param $column_name
	 * @param $column_value
	 *
	 * @return bool
	 */
	public function delete_by( $column_name, $column_value ) {
		global $wpdb;

		$column_name = esc_sql( $column_name );
		$column_value = esc_sql( $column_value );

		if ( false === $wpdb->query( $wpdb->prepare( "DELETE FROM $this->table_name WHERE {$column_name} = %d", $column_value ) ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Check if specific value exists or not
	 *
	 * @since 1.0
	 * @access public
	 *
	 * @param $col_name column name
	 * @param $col_value column value
	 */
	public function exists( $col_name, $col_value ) {
		global $wpdb;

		if ( $wpdb->get_var( $wpdb->prepare( "SELECT 1 FROM {$this->table_name} WHERE {$col_name} = %s", $col_value ) ) )
			return true;

		return false;

	}




	/**
	 * Check if the given table exists
	 *
	 * @since  1.0
	 * @param  string $table The table name
	 * @return bool          If the table name exists
	 */
	public function table_exists( $table ) {
		global $wpdb;
		$table = sanitize_text_field( $table );

		return $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE '%s'", $table ) ) === $table;
	}

	/**
	 * Check if the table was ever installed
	 *
	 * @since  1.0
	 * @return bool Returns if the customers table was installed and upgrade routine run
	 */
	public function installed() {
		return $this->table_exists( $this->table_name );
	}

    /**
     * Delete table
     *
     * @since 1.0
     * @access public
     * @return bool|int
     */
    public function drop_table() {
        global $wpdb;
        return $wpdb->query( "DROP TABLE IF EXISTS {$this->table_name}" );
    }
}