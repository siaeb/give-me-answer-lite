<?php
defined( 'ABSPATH' ) || exit;


class GMA_DB_Statistics {

	/**
	 * Get last questions
	 *
	 * @since 1.0
	 *
	 * @param int $limit limit number of returned questions
	 *
	 * @return array|object|null
	 */
	public function last_questions( $limit = 10 ) {
		global $wpdb;
		$query = "
			SELECT p.ID `question_id`, 
				   p.post_title `question_title`, 
				   p.post_date  `question_date`,
				   p.post_status `question_status`,
				   u.ID `question_author_ID`, 
				   u.user_login `question_author`, 
				   um1.meta_value `author_picture` FROM `{$wpdb->posts}` p
				INNER JOIN {$wpdb->users} u ON u.ID = p.post_author
			    LEFT OUTER JOIN {$wpdb->usermeta} um1 ON um1.user_id = u.ID and um1.meta_key = 'gma_picture_url'
				WHERE p.post_type = 'gma-question' AND p.post_status <> 'draft' AND p.post_status <> 'auto-draft'
			        ORDER BY p.ID DESC
			        LIMIT %d
		";
		return $wpdb->get_results( $wpdb->prepare( $query, $limit ) );
	}

	/**
	 * Get last comments
	 *
	 * @since 1.0
	 *
	 * @param int $limit
	 *
	 * @return array|object|null
	 */
	public function last_comments( $limit = 10 ) {
		global $wpdb;
		$query = "
			SELECT c.comment_ID `comment_id`, 
			c.comment_author, 
			c.user_id `comment_author_ID`, 
			c.comment_date, 
			c.comment_post_ID, 
			c.comment_content FROM `{$wpdb->comments}` c 
				INNER JOIN {$wpdb->posts} p ON p.ID = c.comment_post_ID
			        WHERE p.post_type = 'gma-question' OR p.post_type = 'gma-answer' AND comment_approved = 1
			        ORDER BY comment_date DESC
			        LIMIT %d
		";
		return $wpdb->get_results( $wpdb->prepare( $query, $limit ) );
	}


	/**
	 * Total q2a published questions count
	 *
	 * @since 1.0
	 *
	 * @return mixed
	 */
	public function count_total_questions() {
		$result = wp_count_posts( 'gma-question' );
		return $result->publish;
	}

	/**
	 * Total questions that has at least one answer
	 *
	 * @since 1.0
	 *
	 * @return string|null
	 */
	public function count_answered_questions() {
		global $wpdb;
		$query = "
			SELECT count(p1.id) `count` FROM `{$wpdb->posts}` p1
				WHERE exists ( SELECT 1 FROM {$wpdb->posts} p2 WHERE p2.post_parent = p1.ID) AND p1.post_type = 'gma-question' and p1.post_status = 'publish'
		";
		return $wpdb->get_var( $query );
	}

	/**
	 * Count questions that has not answer
	 *
	 * @return string|null
	 */
	public function count_unanswered_questions() {
		global $wpdb;
		$query = "
			SELECT count(p1.id) `count` FROM `{$wpdb->posts}` p1
				WHERE not exists ( SELECT 1 FROM {$wpdb->posts} p2 WHERE p2.post_parent = p1.ID) AND p1.post_type = 'gma-question' and p1.post_status = 'publish'
		";
		return $wpdb->get_var( $query );
	}

	/**
	 * Count questions that has best answers
	 *
	 * @since 1.0
	 *
	 * @return string|null
	 */
	public function count_has_best_answer_questions() {
		global $wpdb;
		$query = "
			SELECT count(*) `count` FROM `{$wpdb->posts}` p1
				inner join {$wpdb->postmeta} pm ON pm.post_id = p1.ID AND pm.meta_key = '_gma_best_answer' AND pm.meta_key != ''
			    	WHERE p1.post_status = 'publish'
		";
		return $wpdb->get_var( $query );
	}

	/**
	 * Get count of questions by month
	 *
	 * @since 1.0
	 *
	 * @return string|null
	 */
	public function count_questions_by_month() {
		global $wpdb;
		$query = "SELECT count(*) `count`, post_date FROM `{$wpdb->posts}` WHERE post_Type = %s AND post_status = %s group by MONTH(post_date)";
		$result = $wpdb->get_results( $wpdb->prepare($query, 'gma-question', 'publish') );
		foreach ( $result as &$item ) {
			$item->monthname = date_i18n( 'Y F', strtotime( $item->post_date ) );
		}
		array_map( function( $item ) { unset( $item->post_date); }, $result );
		return $result;
	}

}