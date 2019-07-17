<?php

defined( 'ABSPATH' ) || exit;

abstract class GMA_BaseTable {

	/**
	 * The current list of items
	 *
	 * @since 1.0
	 * @access public
	 * @var array
	 */
	public $items;


	/**
	 * Various information about the current table.
	 *
	 * @since 1.0
	 * @access protected
	 * @var array
	 */
	protected $_args;


	/**
	 * Various information needed for displaying the pagination.
	 *
	 * @since 1.0
	 * @access protected
	 * @var array
	 */
	protected $_pagination_args = array();


	/**
	 * Cached pagination output.
	 *
	 * @since 1.0
	 * @access private
	 * @var string
	 */
	private $_pagination;


	/**
	 * Stores the value returned by ->get_column_info().
	 *
	 * @since 1.0
	 * @access protected
	 * @var array
	 */
	protected $_column_headers;


	/**
	 * {@internal Missing Summary}
	 *
	 * @access protected
	 * @var array
	 */
	protected $compat_fields = array( '_args', '_pagination_args', '_pagination' );

	/**
	 * {@internal Missing Summary}
	 *
	 * @access protected
	 * @var array
	 */
	protected $compat_methods = array( 'set_pagination_args',
		'get_items_per_page', 'pagination',
		'get_sortable_columns', 'get_column_info', 'get_table_classes', 'display_tablenav', 'extra_tablenav',
		'single_row_columns' );


	/**
	 * Whether to show top pagination or not
	 *
	 * @since 1.0
	 * @access public
	 * @var bool
	 */
	public $show_top_pagination = true;

	/**
	 * Whether to show bottom pagination or not
	 *
	 * @since 1.0
	 * @access public
	 * @var bool
	 */
	public $show_bottom_pagination = true;

	/**
	 * Total items per page
	 *
	 * @since 1.0
	 * @access public
	 * @var bool
	 */
	protected $_items_per_page = 15;

	protected $_alignment_class = 't-a-c';

	protected $_direction_class = 'r-t-l';

	/**
	 * Total number of found users for the current query
	 *
	 * @since 1.0
	 * @access private
	 * @var int
	 */
	protected $total_rows = 0;

	/**
	 * Constructor.
	 *
	 * The child class should call this constructor from its own constructor to override
	 * the default $args.
	 *
	 * @since 1.0
	 * @access public
	 *
	 * @param array|string $args {
	 *     Array or string of arguments.
	 *
	 *     @type string $plural   Plural value used for labels and the objects being listed.
	 *                            This affects things such as CSS class-names and nonces used
	 *                            in the list table, e.g. 'posts'. Default empty.
	 *     @type string $singular Singular label for an object being listed, e.g. 'post'.
	 *                            Default empty
	 *     @type bool   $ajax     Whether the list table supports Ajax. This includes loading
	 *                            and sorting data, for example. If true, the class will call
	 *                            the _js_vars() method in the footer to provide variables
	 *                            to any scripts handling Ajax events. Default false.
	 * }
	 */
	public function __construct( $args = array() ) {
		$args = wp_parse_args( $args, array(
			'plural' => '',
			'singular' => '',
		) );


		$args['plural'] = sanitize_key( $args['plural'] );
		$args['singular'] = sanitize_key( $args['singular'] );

		$this->_args = $args;
	}

	/**
	 * Make private properties readable for backward compatibility.
	 *
	 * @since 1.0
	 * @access public
	 *
	 * @param string $name Property to get.
	 * @return mixed Property.
	 */
	public function __get( $name ) {
		if ( in_array( $name, $this->compat_fields ) ) {
			return $this->$name;
		}
	}

	/**
	 * Make private properties settable for backward compatibility.
	 *
	 * @since 1.0
	 * @access public
	 *
	 * @param string $name  Property to check if set.
	 * @param mixed  $value Property value.
	 * @return mixed Newly-set property.
	 */
	public function __set( $name, $value ) {
		if ( in_array( $name, $this->compat_fields ) ) {
			return $this->$name = $value;
		}
	}

	/**
	 * Make private properties checkable for backward compatibility.
	 *
	 * @since 1.0
	 * @access public
	 *
	 * @param string $name Property to check if set.
	 * @return bool Whether the property is set.
	 */
	public function __isset( $name ) {
		if ( in_array( $name, $this->compat_fields ) ) {
			return isset( $this->$name );
		}
	}

	/**
	 * Make private properties un-settable for backward compatibility.
	 *
	 * @since 1.0
	 * @access public
	 *
	 * @param string $name Property to unset.
	 */
	public function __unset( $name ) {
		if ( in_array( $name, $this->compat_fields ) ) {
			unset( $this->$name );
		}
	}

	/**
	 * Make private/protected methods readable for backward compatibility.
	 *
	 * @since 1.0
	 * @access public
	 *
	 * @param callable $name      Method to call.
	 * @param array    $arguments Arguments to pass when calling.
	 * @return mixed|bool Return value of the callback, false otherwise.
	 */
	public function __call( $name, $arguments ) {
		if ( in_array( $name, $this->compat_methods ) ) {
			return call_user_func_array( array( $this, $name ), $arguments );
		}
		return false;
	}

	/**
	 * Checks the current user's permissions
	 *
	 * @since 1.0
	 * @access public
	 * @abstract
	 */
	public function ajax_user_can() {
		die( 'function WP_List_Table::ajax_user_can() must be over-ridden in a sub-class.' );
	}

	/**
	 * Prepares the list of items for displaying.
	 * @uses BaseTable::set_pagination_args()
	 *
	 * @since 1.0
	 * @access public
	 * @abstract
	 */
	public function prepare_items() {
		die( 'function WP_List_Table::prepare_items() must be over-ridden in a sub-class.' );
	}

	/**
	 * An internal method that sets all the necessary pagination arguments
	 *
	 * @since 1.0
	 * @access protected
	 *
	 * @param array|string $args Array or string of arguments with information about the pagination.
	 */
	protected function set_pagination_args( $args ) {
		$args = wp_parse_args( $args, array(
			'total_items' => 0,
			'total_pages' => 0,
			'per_page' => 0,
		) );

		if ( !$args['total_pages'] && $args['per_page'] > 0 )
			$args['total_pages'] = ceil( $args['total_items'] / $args['per_page'] );

		// Redirect if page number is invalid and headers are not already sent.
		if ( ! headers_sent() && ! wp_doing_ajax() && $args['total_pages'] > 0 && $this->get_pagenum() > $args['total_pages'] ) {
			wp_redirect( add_query_arg( 'paged', $args['total_pages'] ) );
			exit;
		}

		$this->_pagination_args = $args;
	}

	/**
	 * Access the pagination args.
	 *
	 * @since 1.0
	 * @access public
	 *
	 * @param string $key Pagination argument to retrieve. Common values include 'total_items',
	 *                    'total_pages', 'per_page', or 'infinite_scroll'.
	 * @return int Number of items that correspond to the given pagination argument.
	 */
	public function get_pagination_arg( $key ) {
		if ( 'page' === $key ) {
			return $this->get_pagenum();
		}

		if ( isset( $this->_pagination_args[$key] ) ) {
			return $this->_pagination_args[$key];
		}
	}

	/**
	 * Whether the table has items to display or not
	 *
	 * @since 1.0
	 * @access public
	 *
	 * @return bool
	 */
	public function has_items() {
		return !empty( $this->items );
	}

	/**
	 * Message to be displayed when there are no items
	 *
	 * @since 3.1.0
	 * @access public
	 */
	public function no_items() {
		_e( 'No items found.' );
	}

	/**
	 * Get the current page number
	 *
	 * @since 1.0
	 * @access public
	 *
	 * @return int
	 */
	public function get_pagenum() {
		$pagenum = isset( $_REQUEST['paged'] ) ? absint( $_REQUEST['paged'] ) : 0;

		if ( isset( $this->_pagination_args['total_pages'] ) && $pagenum > $this->_pagination_args['total_pages'] )
			$pagenum = $this->_pagination_args['total_pages'];

		return max( 1, $pagenum );
	}

	/**
	 * Get number of items to display on a single page
	 *
	 * @since 1.0
	 * @access protected
	 *
	 * @param string $option
	 * @param int    $default
	 * @return int
	 */
	protected function get_items_per_page( $option, $default = 20 ) {
		$per_page = (int) get_user_option( $option );
		if ( empty( $per_page ) || $per_page < 1 )
			$per_page = $default;

		/**
		 * Filters the number of items to be displayed on each page of the list table.
		 *
		 * The dynamic hook name, $option, refers to the `per_page` option depending
		 * on the type of list table in use. Possible values include: 'edit_comments_per_page',
		 * 'sites_network_per_page', 'site_themes_network_per_page', 'themes_network_per_page',
		 * 'users_network_per_page', 'edit_post_per_page', 'edit_page_per_page',
		 * 'edit_{$post_type}_per_page', etc.
		 *
		 * @since 2.9.0
		 *
		 * @param int $per_page Number of items to be displayed. Default 20.
		 */
		return (int) apply_filters( "{$option}", $per_page );
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
		if ( empty( $this->_pagination_args ) ) return;
		$total_items = $this->_pagination_args['total_items'];
		$paginator = new GMA_Paginator( $total_items, $this->_items_per_page, $this->get_pagenum() );
		$paginator->setMaxPagesToShow( 5 );
		$paginator->setPreviousText( __('Prev', 'give-me-answer-lite') );
		$paginator->setNextText( __('Next', 'give-me-answer-lite') );
		$this->_pagination = $paginator->toHtml();
		echo $this->_pagination;
	}

	/**
	 * Get a list of columns. The format is:
	 * 'internal-name' => 'Title'
	 *
	 * @since 3.1.0
	 * @access public
	 * @abstract
	 *
	 * @return array
	 */
	public function get_columns() {
		die( 'function WP_List_Table::get_columns() must be over-ridden in a sub-class.' );
	}

	/**
	 * Get a list of sortable columns. The format is:
	 * 'internal-name' => 'orderby'
	 * or
	 * 'internal-name' => array( 'orderby', true )
	 *
	 * The second format will make the initial sorting order be descending
	 *
	 * @since 1.0
	 * @access protected
	 *
	 * @return array
	 */
	protected function get_sortable_columns() {
		return array();
	}

	/**
	 * Gets the name of the default primary column.
	 *
	 * @since 1.0
	 * @access protected
	 *
	 * @return string Name of the default primary column, in this case, an empty string.
	 */
	protected function get_default_primary_column_name() {
		$columns = $this->get_columns();
		$column = '';

		if ( empty( $columns ) ) {
			return $column;
		}

		// We need a primary defined so responsive views show something,
		// so let's fall back to the first non-checkbox column.
		foreach ( $columns as $col => $column_name ) {
			if ( 'cb' === $col ) {
				continue;
			}

			$column = $col;
			break;
		}

		return $column;
	}



	/**
	 * Gets the name of the primary column.
	 *
	 * @since 1.0
	 * @access protected
	 *
	 * @return string The name of the primary column.
	 */
	protected function get_primary_column_name() {
		$columns = get_column_headers( $this->screen );
		$default = $this->get_default_primary_column_name();

		/**
		 * Filters the name of the primary column for the current list table.
		 *
		 * @since 4.3.0
		 *
		 * @param string $default Column name default for the specific list table, e.g. 'name'.
		 * @param string $context Screen ID for specific list table, e.g. 'plugins'.
		 */
		$column  = apply_filters( 'list_table_primary_column', $default, $this->screen->id );

		if ( empty( $column ) || ! isset( $columns[ $column ] ) ) {
			$column = $default;
		}

		return $column;
	}

	/**
	 * Get a list of all, hidden and sortable columns, with filter applied
	 *
	 * @since 1.0
	 * @access protected
	 *
	 * @return array
	 */
	protected function get_column_info() {
		// $_column_headers is already set / cached
		if ( isset( $this->_column_headers ) && is_array( $this->_column_headers ) ) {
			// Back-compat for list tables that have been manually setting $_column_headers for horse reasons.
			// In 4.3, we added a fourth argument for primary column.
			$column_headers = array( array(), array(), array() );
			foreach ( $this->_column_headers as $key => $value ) {
				$column_headers[ $key ] = $value;
			}

			return $column_headers;
		}

		$columns = get_column_headers( $this->screen );
		$hidden = get_hidden_columns( $this->screen );

		$sortable_columns = $this->get_sortable_columns();
		$sortable = array();
		foreach ( $sortable_columns as $id => $data ) {
			if ( empty( $data ) )
				continue;

			$data = (array) $data;
			if ( !isset( $data[1] ) )
				$data[1] = false;

			$sortable[$id] = $data;
		}

		$this->_column_headers = array( $columns, $hidden, $sortable );

		return $this->_column_headers;
	}

	/**
	 * Return number of visible columns
	 *
	 * @since 1.0
	 * @access public
	 *
	 * @return int
	 */
	public function get_column_count() {
		list ( $columns, $hidden ) = $this->get_column_info();
		$hidden = array_intersect( array_keys( $columns ), array_filter( $hidden ) );
		return count( $columns ) - count( $hidden );
	}

	/**
	 * Print column headers, accounting for hidden and sortable columns.
	 *
	 * @since 1.0
	 * @access public
	 *
	 * @staticvar int $cb_counter
	 *
	 * @param bool $with_id Whether to set the id attribute or not
	 */
	public function print_column_headers( $with_id = true ) {
		list( $columns, $hidden, $sortable ) = $this->get_column_info();

		$current_url = set_url_scheme( 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );
		$current_url = remove_query_arg( 'paged', $current_url );

		if ( isset( $_GET['orderby'] ) ) {
			$current_orderby = $_GET['orderby'];
		} else {
			$current_orderby = '';
		}

		if ( isset( $_GET['order'] ) && 'desc' === $_GET['order'] ) {
			$current_order = 'desc';
		} else {
			$current_order = 'asc';
		}

		if ( ! empty( $columns['cb'] ) ) {
			static $cb_counter = 1;
			$columns['cb']['display_name'] = '<input id="cb-select-all-' . $cb_counter . '" type="checkbox" />';
			$cb_counter++;
		}


		foreach ( $columns as $column_key => $column_info ) {

			$class = array( 'manage-column', "column-$column_key" );

			if ( in_array( $column_key, $hidden ) ) {
				$class[] = 'hidden';
			}

			if ( 'cb' === $column_key )
				$class[] = 'check-column';
			elseif ( in_array( $column_key, array( 'posts', 'comments', 'links' ) ) )
				$class[] = 'num';


			if ( isset( $sortable[$column_key] ) ) {
				list( $orderby, $desc_first ) = $sortable[$column_key];

				if ( $current_orderby === $orderby ) {
					$order = 'asc' === $current_order ? 'desc' : 'asc';
					$class[] = 'sorted';
					$class[] = $current_order;
				} else {
					$order = $desc_first ? 'desc' : 'asc';
					$class[] = 'sortable';
					$class[] = $desc_first ? 'asc' : 'desc';
				}

				$column_info['display_name'] =
					'<a class="no-text-decoration no-outline" href="' . esc_url( add_query_arg( compact( 'orderby', 'order' ), $current_url ) ) . '">' . $column_info['display_name'] . '</a>';
			}

			$class[] = 'no-outline';

			$tag = ( 'cb' === $column_key ) ? 'td' : 'th';
			$scope = ( 'th' === $tag ) ? 'scope="col"' : '';
			$id = $with_id ? "id='$column_key'" : '';

			if ( isset( $column_info['width'] ) && $column_info['width'] )
				$style = 'style="width: ' . $column_info['width'] .'"';
			else
				$style = '';

			if ( isset( $column_info['text_align'] ) && $column_info['text_align'] )
				$class = "class='" . $column_info['text_align'] . ' ' . join( ' ', $class ) . "'";
			else
				$class = "class='{$this->_alignment_class} " . join( ' ', $class ) . "'";

			echo "<$tag $scope $id $class $style>" . $column_info['display_name'] . "</$tag>";
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
            echo '<div class="col-12 d-flex justify-content-center">';
                $this->pagination( $which );
            echo '</div>';
		echo '</div>';
	}

	public function display() {

		// show top pagination
		if ( $this->show_top_pagination )
			$this->display_tablenav( 'top' );

		echo '<div class="row">';
		echo '<div class="col-md-12">';


		$table_classes = [
			'table',
			'card-table',
			'table-vcenter',
			'table-striped',
			'border',
			'text-nowrap',
		];

		if ( $this->_clickable_row ) $table_classes[] = 'table-clickable';

		$table_classes = array_merge( $table_classes, $this->get_table_classes() );

		?>
		<div class="table-responsive">
			<table class="<?php echo implode( ' ', $table_classes ); ?>">


				<thead>
					<tr>
						<?php $this->print_column_headers(); ?>
					</tr>
				</thead>

				<tbody>
					<?php $this->display_rows_or_placeholder(); ?>
				</tbody>

			</table>
		</div>
		<?php
		// end of col-md-12
		echo '</div>';
		// end of row
		echo '</div>';

		// show bottom pagination
		if ( $this->show_bottom_pagination )
			$this->display_tablenav( 'bottom' );

	}

	/**
	 * Get a list of CSS classes for the WP_List_Table table tag.
	 *
	 * @since 1.0
	 * @access protected
	 *
	 * @return array List of CSS classes for the table tag.
	 */
	protected function get_table_classes() {
		return array( $this->_args['plural'] );
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
			echo '<tr class="no-items"><td class="colspanchange" colspan="' . $this->get_column_count() . '">';
			$this->no_items();
			echo '</td></tr>';
		}
	}

	/**
	 * Generate the table rows
	 *
	 * @since 1.0
	 * @access public
	 */
	public function display_rows() {
		foreach ( $this->items as $item )
			$this->single_row( $item );
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
		if ( isset( $item['color'] ) )
			echo "<tr class='" . $item['color'] . "'>";
		else
			echo '<tr>';

		$this->single_row_columns( $item );
		echo '</tr>';
	}

	/**
	 *
	 * @param object $item
	 * @param string $column_name
	 */
	protected function column_default( $item, $column_name ) {}

	/**
	 *
	 * @param object $item
	 */
	protected function column_cb( $item ) {}

	/**
	 * Generates the columns for a single row of the table
	 *
	 * @since 1.0
	 * @access protected
	 *
	 * @param object $item The current item
	 */
	protected function single_row_columns( $item ) {
		list( $columns, $hidden, $sortable ) = $this->get_column_info();

		foreach ( $columns as $column_name => $column_info ) {
			$classes = "$column_name column-$column_name";

			if ( in_array( $column_name, $hidden ) ) {
				$classes .= ' hidden';
			}

			// Comments column uses HTML in the display name with screen reader text.
			// Instead of using esc_attr(), we strip tags to get closer to a user-friendly string.
			$data = 'data-colname="' . wp_strip_all_tags( $column_info['display_name'] ) . '"';

			if ( isset( $column_info['text_align'] ) && $column_info['text_align']  )
				$attributes = "class='" . $column_info['text_align'] ." {$this->_direction_class} $classes' $data";
			else
				$attributes = "class='{$this->_alignment_class} {$this->_direction_class} $classes' $data";

			if ( 'cb' === $column_name ) {
				echo '<th class="check-column ' . $this->_alignment_class . '">';
				echo $this->column_cb( $item );
				echo '</th>';
			} elseif ( method_exists( $this, '_column_' . $column_name ) ) {
				echo call_user_func(
					array( $this, '_column_' . $column_name ),
					$item,
					$classes,
					$data
				);
			} elseif ( method_exists( $this, 'column_' . $column_name ) ) {
				echo "<td $attributes>";
				echo call_user_func( array( $this, 'column_' . $column_name ), $item );
				echo "</td>";
			} else {
				echo "<td $attributes>";
				echo $this->column_default( $item, $column_name );
				echo "</td>";
			}
		}
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
		$this->print_column_headers();
		$headers = ob_get_clean();
		ob_start();
		$this->pagination('top');
		$pagination_top = ob_get_clean();
		ob_start();
		$this->pagination('bottom');
		$pagination_bottom = ob_get_clean();
		$response = array( 'rows' => $rows );
		$response['pagination']['top'] = $pagination_top;
		$response['pagination']['bottom'] = $pagination_bottom;
		$response['column_headers'] = $headers;
		if ( isset( $total_items ) )
			$response['total_items_i18n'] = sprintf( _n( '1 item', '%s items', $total_items ), number_format_i18n( $total_items ) );
		if ( isset( $total_pages ) ) {
			$response['total_pages'] = $total_pages;
			$response['total_pages_i18n'] = number_format_i18n( $total_pages );
		}
		if ( $return_result ) return $response;
		die( json_encode( $response ) );
	}

	protected function _parse_order( $order ) {
		if ( ! is_string( $order ) || empty( $order ) ) return 'DESC';
		if ( strtoupper( $order ) === 'ASC' ) return 'ASC';
		return 'DESC';
	}

	protected function _parse_orderby( $orderby ) {
		if ( empty( $orderby ) ) {
			$orderby = array( 'id' );
		} else {
			$orderby = preg_split( '/[,\s]+/', $orderby );
		}
		return $orderby;
	}


	/**
	 * Used internally to generate an SQL string for searching across multiple columns
	 *
	 * @access protected
	 * @since 1.0
	 *
	 * @global wpdb $wpdb WordPress database abstraction object.
	 *
	 * @param string $string
	 * @param array  $cols
	 * @param bool   $wild   Whether to allow wildcard searches. Default is false for Network Admin, true for single site.
	 *                       Single site allows leading and trailing wildcards, Network Admin only trailing.
	 * @return string
	 */
	protected function _get_search_sql( $string, $cols, $wild = false ) {
		global $wpdb;

		$searches = array();
		$leading_wild = ( 'leading' == $wild || 'both' == $wild ) ? '%' : '';
		$trailing_wild = ( 'trailing' == $wild || 'both' == $wild ) ? '%' : '';
		$like = $leading_wild . $wpdb->esc_like( $string ) . $trailing_wild;

		foreach ( $cols as $col ) {
			$searches[] = $col . ' LIKE "' . $like . '"';
		}

		return ' (' . implode(' OR ', $searches) . ')';
	}

	abstract function prepare_query( $data );

	protected function query( $data ) {
		global $wpdb;
		$data               = $wpdb->get_results( $this->prepare_query( $data ), ARRAY_A );
		$this->total_rows   = (int) $wpdb->get_var( 'SELECT FOUND_ROWS();' );
		$this->items        = $data;
	}

}