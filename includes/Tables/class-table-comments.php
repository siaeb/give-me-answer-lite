<?php

defined( 'ABSPATH' ) || exit;

if ( false == class_exists( 'WP_Comments_List_Table' ) ) {
	include_once ABSPATH . '/wp-admin/includes/class-wp-list-table.php';
	include_once ABSPATH . '/wp-admin/includes/class-wp-comments-list-table.php';
}

class GMA_List_Table_Comments extends WP_Comments_List_Table {



	/**
	 * @global int    $post_id
	 * @global string $comment_status
	 * @global string $search
	 * @global string $comment_type
	 */
	public function prepare_items() {
		global $post_id, $comment_status, $search, $comment_type;

		$comment_status = isset( $_REQUEST['comment_status'] ) ? $_REQUEST['comment_status'] : 'all';
		if ( ! in_array( $comment_status, array( 'all', 'mine', 'moderated', 'approved', 'spam', 'trash' ) ) ) {
			$comment_status = 'all';
		}

		$comment_type = ! empty( $_REQUEST['comment_type'] ) ? $_REQUEST['comment_type'] : '';

		$search = ( isset( $_REQUEST['s'] ) ) ? $_REQUEST['s'] : '';

		$post_type = ( isset( $_REQUEST['post_type'] ) ) ? sanitize_key( $_REQUEST['post_type'] ) : '';

		$user_id = ( isset( $_REQUEST['user_id'] ) ) ? $_REQUEST['user_id'] : '';

		$orderby = ( isset( $_REQUEST['orderby'] ) ) ? $_REQUEST['orderby'] : '';
		$order   = ( isset( $_REQUEST['order'] ) ) ? $_REQUEST['order'] : '';

		$comments_per_page = $this->get_per_page( $comment_status );

		$doing_ajax = wp_doing_ajax();

		if ( isset( $_REQUEST['number'] ) ) {
			$number = (int) $_REQUEST['number'];
		} else {
			$number = $comments_per_page + min( 8, $comments_per_page ); // Grab a few extra
		}

		$page = $this->get_pagenum();

		if ( isset( $_REQUEST['start'] ) ) {
			$start = $_REQUEST['start'];
		} else {
			$start = ( $page - 1 ) * $comments_per_page;
		}

		if ( $doing_ajax && isset( $_REQUEST['offset'] ) ) {
			$start += $_REQUEST['offset'];
		}

		$status_map = array(
			'mine'      => '',
			'moderated' => 'hold',
			'approved'  => 'approve',
			'all'       => '',
		);

		$args = array(
			'status'    => isset( $status_map[ $comment_status ] ) ? $status_map[ $comment_status ] : $comment_status,
			'search'    => $search,
			'user_id'   => $user_id,
			'offset'    => $start,
			'number'    => $number,
			'post_id'   => $post_id,
			'type'      => $comment_type,
			'orderby'   => $orderby,
			'order'     => $order,
			'post_type' => [ 'gma-question', 'gma-answer' ],
		);

		/**
		 * Filters the arguments for the comment query in the comments list table.
		 *
		 * @since 5.1.0
		 *
		 * @param array $args An array of get_comments() arguments.
		 */
		$args = apply_filters( 'comments_list_table_query_args', $args );

		$_comments = get_comments( $args );

		if ( is_array( $_comments ) ) {
			update_comment_cache( $_comments );

			$this->items       = array_slice( $_comments, 0, $comments_per_page );
			$this->extra_items = array_slice( $_comments, $comments_per_page );

			$_comment_post_ids = array_unique( wp_list_pluck( $_comments, 'comment_post_ID' ) );

			$this->pending_count = get_pending_comments_num( $_comment_post_ids );
		}

		$total_comments = get_comments(
			array_merge(
				$args,
				array(
					'count'  => true,
					'offset' => 0,
					'number' => 0,
				)
			)
		);

		$this->set_pagination_args(
			array(
				'total_items' => $total_comments,
				'per_page'    => $comments_per_page,
			)
		);
	}

}