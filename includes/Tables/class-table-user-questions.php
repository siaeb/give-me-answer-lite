<?php

defined( 'ABSPATH' ) || exit;

class GMA_User_Questions extends GMA_BaseTable {

	public function __construct() {

		$args = array();

		if (isset($_GET['screen_baseClass']) && !empty($_GET['screen_baseClass'])) {
			$args['screen'] = $_GET['screen_baseClass'];
		}

		parent::__construct( $args );

		$this->_alignment_class = 't-a-r';
		$this->_direction_class = 'r-t-l';

	}

	public function get_columns() {}

	public function column_default( $row, $colname ) {}

	protected function get_sortable_columns() {}


	/**
	 * Get the current page number
	 *
	 * @since 1.0
	 * @access public
	 *
	 * @return int
	 */
	public function get_pagenum() {
		global $wp_query;

		if ( isset( $wp_query->query_vars[ 'page' ] ) ) {
			$pagenum = absint( $wp_query->query_vars[ 'page' ] );
		} else {
			$pagenum = 0;
		}

		if ( isset( $this->_pagination_args['total_pages'] ) && $pagenum > $this->_pagination_args['total_pages'] )
			$pagenum = $this->_pagination_args['total_pages'];

		return max( 1, $pagenum );
	}


	/**
	 * Display the pagination.
	 *
	 * @since 1.0
	 * @access protected
	 *
	 * @param string $which
	 */
	protected function pagination( $which ) {
		global $post, $wp_query;
		if ( empty( $this->_pagination_args ) ) return;
		$total_items = $this->_pagination_args['total_items'];
		$paginator = new GMA_Paginator( $total_items, $this->_items_per_page, $this->get_pagenum() );
		$paginator->setMaxPagesToShow( 5 );
		$paginator->setPreviousText( __('Prev', 'give-me-answer-lite') );
		$paginator->setNextText( __('Next', 'give-me-answer-lite') );
		$paginator->setUrlPattern( gma_get_user_questions_url( gma_get_user_id() ) . '&page=(:num)' );
		$paginator->setCurrentPage( $this->get_pagenum() );
		echo $paginator->toHtml();
	}

	public function display() {

		// show top pagination
		if ( $this->show_top_pagination ) {
			$this->display_tablenav( 'top' );
		}

		echo '<div class="gma-container">';
			echo '<div class="gma-questions-list bg-white mx-0 mb-2">';

			$this->display_rows_or_placeholder();

			echo '</div>';
		echo '</div>';

		// show bottom pagination
		if ( $this->show_bottom_pagination ) {
			$this->display_tablenav( 'bottom' );
		}

	}

	/**
	 * Generate the table navigation above or below the table
	 *
	 * @since 1.0
	 * @access protected
	 * @param string $which
	 */
	protected function display_tablenav( $which ) {
		echo '<div class="row pagination-container mt-1">';
		echo '<div class="col-12 justify-content-center">';
		$this->pagination( $which );
		echo '</div>';
		echo '</div>';
	}

	/**
	 * Handle an incoming ajax request (called from admin-ajax.php)
	 *
	 * @since 1.0
	 * @access public
	 */
	public function ajax_response( $return_result = true ) {
		$this->prepare_items();
		extract( $this->_args );
		extract( $this->_pagination_args, EXTR_SKIP );
		ob_start();
		if ( ! empty( $_REQUEST['no_placeholder'] ) )
			$this->display_rows();
		else
			$this->display_rows_or_placeholder();
		$rows = ob_get_clean();
		ob_start();
		$this->pagination('top');
		$pagination_top = ob_get_clean();
		ob_start();
		$this->pagination('bottom');
		$pagination_bottom = ob_get_clean();
		$response = array( 'rows' => $rows );
		$response['pagination']['top'] = $pagination_top;
		$response['pagination']['bottom'] = $pagination_bottom;
		if ( isset( $total_items ) )
			$response['total_items_i18n'] = sprintf( _n( '1 item', '%s items', $total_items ), number_format_i18n( $total_items ) );
		if ( isset( $total_pages ) ) {
			$response['total_pages'] = $total_pages;
			$response['total_pages_i18n'] = number_format_i18n( $total_pages );
		}
		if ( $return_result ) return $response;
		die( json_encode( $response ) );
	}


	/**
	 * Generate the tbody element for the list table.
	 *
	 * @since 1.0
	 * @access public
	 */
	public function display_rows_or_placeholder() {
		if ( $this->has_items() ) {
			$this->display_rows();
		} else {
			$this->no_items();
		}
	}

	/**
	 * Generate the table rows
	 *
	 * @since 1.0
	 * @access public
	 */
	public function display_rows() {
		foreach ( $this->items as $item ) {
			$this->single_row( $item );
		}
	}

	function no_items() {
		echo '<div class="alert alert-warning col-12 text-danger nosellers d-flex align-items-center justify-content-center font-weight-bold text-center mb-2">';
		echo __('Question not found', 'give-me-answer-lite');
		echo '</div>';
	}

	/**
	 * Generates content for a single row of the table
	 *
	 * @since 1.0
	 * @access public
	 *
	 * @param object $item The current item
	 */
	public function single_row( $item ) {
		$GLOBALS['post'] = get_post($item[ 'ID' ] );
		include GMA_DIR . 'templates/content-question.php';
	}


	public function prepare_items() {
		global $gma_general_settings;

		if ( ! isset( $_REQUEST['order'] ) ) $_REQUEST['order'] = 'DESC';
		if ( ! isset( $_REQUEST['orderby'] ) ) $_REQUEST['orderby'] = 'ID';

		// sanitization
		$order   = $this->_parse_order( $_REQUEST['order'] );
		$orderby = $this->_parse_orderby( $_REQUEST['orderby'] );

		$usersearch = isset( $_REQUEST['s'] ) ? wp_unslash( trim( $_REQUEST['s'] ) ) : '';
		$usersearch = trim( $usersearch );

		$category = isset( $_REQUEST['category'] ) ? $_REQUEST['category'] : '';

		$per_page = isset($gma_general_settings['pagination']['user-profile']) ? $gma_general_settings['pagination']['user-profile'] : 15;
		$per_page = is_numeric($per_page) ? $per_page : 15;
		$paged    = $this->get_pagenum();

		$data = array(
			'order'          => $order,
			'orderby'        => $orderby,
			'items_per_page' => $per_page,
			'page'           => $paged,
			'category'       => $category,
			'search'         => $usersearch,
			'count_total'    => true,
		);

		$this->query( $data );

		$this->_items_per_page = $per_page;

		$this->set_pagination_args( array(
			'per_page'      => $per_page,
			'total_items'   => $this->total_rows,
			'total_pages'	=> ceil( $this->total_rows / $per_page ),
			'orderby'	    => $orderby,
			'order'		    => $order,
		) );
	}

	function prepare_query( $data ) {
		global $wpdb, $wp_query;


		$fields = array( '*' );
		$fields_section = implode( ',', $fields );

		if ( $data['count_total'] )  {
			$fields_section = 'SQL_CALC_FOUND_ROWS ' . $fields_section;
		}

		$order_query = ' ORDER BY ID DESC ';

		$where_query = '';
		if ( gma_get_user_id() ) {
			$where_query = ' WHERE post_author = ' . esc_sql( gma_get_user_id() ) . ' AND post_type = "gma-question" AND post_status = "publish"';
		}


		$per_page = $data['items_per_page'];
		$current_page = $data['page'];
		$offset = ($current_page - 1) * $per_page;
		$limit_query = sprintf(" LIMIT %s,%s", $offset, $per_page);

		$query = "
			SELECT {$fields_section} FROM {$wpdb->posts} 	                
             {$where_query} 
             {$order_query}
             {$limit_query}
		";


		return $query;
	}
}