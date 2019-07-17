<?php
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class GMA_Answer_List_Table extends WP_List_Table {

	function __construct() {
		parent::__construct();
		add_filter( 'wp_trim_excerpt', array( $this, 'trim_exceprt_more' ) );
	}
	/**
	 * Prepares the list of items for displaying.
	 * @uses WP_List_Table::set_pagination_args()
	 *
	 * @since 3.1.0
	 * @access public
	 * @abstract
	 */
	function prepare_items() {
		global $avail_post_stati, $wp_query, $per_page, $mode, $post;

		$columns = $this->get_columns();
		$hidden = array();
		$sortable = $this->get_sortable_columns();
		
		
		/**
		 * REQUIRED. Finally, we build an array to be used by the class for column 
		 * headers. The $this->_column_headers property takes an array which contains
		 * 3 other arrays. One for all columns, one for hidden columns, and one
		 * for sortable columns.
		 */
		$this->_column_headers = array( $columns, $hidden, $sortable );

		$post_type = 'gma-answer';
		$per_page = $this->get_items_per_page( 'edit_' . $post_type . '_per_page' );

		/** This filter is documented in wp-admin/includes/post.php */
 		$per_page = apply_filters( 'edit_posts_per_page', $per_page, $post_type );

		$question_id = $post->ID;
		$args = array(
			'post_type' => 'gma-answer',
			'posts_per_page' => $per_page,
			'order'      => 'ASC',
			'post_parent' => $question_id,
		   	'post_status' => 'publish',
	 	);
		$data = get_posts( $args );
		$this->items = $data;
	}

		/**
	 * Display the table
	 *
	 * @since 3.1.0
	 * @access public
	 */
	function display() {
		$args = wp_parse_args( $this->_args,  array( 'dev' => false ) );
		$this->prepare_items();
		?>
		<table class="wp-list-table gma-answer-list-table <?php echo implode( ' ', $this->get_table_classes() ); ?>">
			<tbody id="the-list"<?php if ( $args[ 'singular' ] ) echo " data-wp-lists='list:" . $args[ 'singular' ] ."'"; ?>>
				<?php $this->display_rows_or_placeholder(); ?>
			</tbody>
		</table>
		<?php
	}

	function get_columns() {
		$columns = array( 
			'author'    => __( 'Author', 'give-me-answer-lite' ),
			'detail'    => __( 'Detail', 'give-me-answer-lite' ),
		);
		return $columns;
	}

	function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			case 'author':
				$user_info = get_userdata( $item->post_author );
				if ( ! $user_info ) {
					echo '<strong>'.__( 'Anonymous','give-me-answer-lite' ).'</strong>';
				} else {
					echo '<strong>'.get_avatar( $item->post_author, $size = '32' ) . ' ' .$user_info->display_name . '</strong>';
				}
				break;
			case 'detail':
				global $post;
				setup_postdata( $item );
				?>
				<div class="submitted-on"><?php _e( 'Answered on ', 'give-me-answer-lite' ) ?><a href="<?php echo get_permalink( $item->ID ) ?>"><?php echo $item->post_date ?></a></div>
				<?php the_excerpt(); ?>
				<?php
				break;
			default:
				return print_r( $item,true );
		}
	}

	function trim_exceprt_more( $excerpt ) {
		if ( $excerpt ) {
			return str_replace( '[...]', '<a href="'.get_permalink().'" title="'.__( 'Read more', 'give-me-answer-lite' ).'" >...</a>', $excerpt );
		}
		return $excerpt;
	}

}

?>