<?php
defined( 'ABSPATH' ) || exit;

/**
 *  Inlucde all function for filter of Give Me Answer plugin
 */
class GMA_Filter {


    public function __construct(){
        add_action( 'bp_gma_before_questions_list', array( $this, 'prepare_archive_posts' ) );
        add_action( 'gma_after_questions_list', array( $this, 'after_archive_posts' ) );
        add_action( 'gma_after_question_stickies', array( $this, 'after_archive_posts' ) );

        //Prepare answers for single questions
        add_action( 'the_posts', array( $this, 'prepare_answers' ), 10, 2 );
        add_filter( 'parse_query', array( $this, 'gma_theme_fix_category_page' ) );
    }

	public function prepare_archive_posts( $args ) {
		global $wp_query,$gma_general_settings;

		$perpage        = isset( $gma_general_settings['pagination']['archive'] ) ?  $gma_general_settings['pagination']['archive'] : 15;
		$user           = isset( $_GET['user'] ) && !empty( $_GET['user'] ) ? urldecode( $_GET['user'] ) : false;
		$filter         = isset( $_GET['filter'] ) && !empty( $_GET['filter'] ) ? sanitize_text_field( $_GET['filter'] ) : 'all';
		$search_text    = isset( $_GET['qs'] ) ? sanitize_text_field( $_GET['qs'] ) : false;
		$sort           = isset( $_GET['sort'] ) ? sanitize_text_field( $_GET['sort'] ) : '';
		$query = array(
			'post_type'      => 'gma-question',
			'posts_per_page' => $perpage,
			'orderby'	     => 'modified',
			'order'	         => 'DESC',
		);
		$page_text      = gma_is_front_page() ? 'page' : 'paged';
		$paged          = get_query_var( $page_text );
		$query['paged'] = $paged ? $paged : 1;

		// filter by category
		$cat = get_query_var( 'gma-question_category' ) ? get_query_var( 'gma-question_category' ) : false;
		if ( $cat ) {
			$query['tax_query'][] = array(
				'taxonomy' => 'gma-question_category',
				'terms' => $cat,
				'field' => 'slug'
			);
		}

		// filter by tags
		$tag = get_query_var( 'gma-question_tag' ) ? get_query_var( 'gma-question_tag' ) : false;
		if ( $tag ) {
			$query['tax_query'][] = array(
				'taxonomy' => 'gma-question_tag',
				'terms' => $tag,
				'field' => 'slug'
			);
		}

		if ( isset( $args[ 'meta_key' ] ) && $args[ 'meta_key' ] == '_gma_views' )  {
			$sort = 'views';
		}

		// filter by user
		if ( $user ) {
			$query['author_name'] = esc_html( $user );
		}

		switch ( $sort ) {
			// sort by views count
			case 'views':
				$query['meta_key'] = '_gma_views';
				$query['orderby'] = 'meta_value_num';
				break;

			// sort by answers count
			case 'answers':
				$query['meta_key'] = '_gma_answers_count';
				$query['orderby'] = 'meta_value_num';
				break;

			// sort by votes count
			case 'votes':
				$query['meta_key'] = '_gma_votes';
				$query['orderby'] = 'meta_value_num';
				break;
		}

		// filter by status
		switch ( $filter ) {
			case 'all':
				$query['meta_query'][] = array(
					'key' => '_gma_status',
					'value' => array( 'open', 're-open', 'resolved', 'closed', 'close', 'answered' ),
					'compare' => 'IN',
				);
				break;
			case 'open':
				$query['meta_query'][] = array(
				   'key' => '_gma_status',
				   'value' => array( 'open', 're-open' ),
				   'compare' => 'IN',
				);
				break;
			case 'resolved':
				$query['meta_query'][] = array(
				   'key'        => '_gma_status',
				   'value'      => array( 'resolved' ),
				   'compare'    => 'IN',
				);
				break;
			case 'closed':
				$query['meta_query'][] = array(
				   'key' => '_gma_status',
				   'value' => array( 'closed', 'close' ),
				   'compare' => 'IN',
				);
				break;
			case 'unanswered':
				$query['meta_query'][] = array(
				   'key' => '_gma_answers_count',
				   'value' => array( '0' ),
				   'compare' => 'IN',
				);
				break;
			case 'answered':
				$query['meta_query'][] = array(
					'key'       => '_gma_status',
					'value'     => array( 'answered' ),
					'compare'   => 'IN',
				);
				break;
			case 'subscribes':
				if ( $user ) {
					$query['meta_query'][]      = array(
						'key'					=> '_gma_followers',
						'value'					=> $user->ID,
						'compare'				=> '='
					);
				}
				break;
			case 'my-questions':
				if ( is_user_logged_in() ) {
					$query['author__in'] = [get_current_user_id()];
				}
				break;
			case 'my-subscribes':
				if ( is_user_logged_in() ) {
					$query['meta_query'][] = array(
						'key'					=> '_gma_followers',
						'value'					=> get_current_user_id(),
						'compare'				=> '='
					);
				}
				break;
		}

		// search
		if ( $search_text ) {
			$search = sanitize_text_field( $search_text );
			preg_match_all( '/#\S*\w/i', $search, $matches );
			if ( $matches && is_array( $matches ) && count( $matches ) > 0 && count( $matches[0] ) > 0 ) {
				$query['tax_query'][] = array(
					'taxonomy' => 'gma-question_tag',
					'field' => 'slug',
					'terms' => $matches[0],
					'operator'  => 'IN',
				);
				$search = preg_replace( '/#\S*\w/i', '', $search );
			}

			$query['search_question_title'] = $search;
		}

		if ( is_user_logged_in() ) {
			$query['post_status'] = array( 'publish', 'private' );
		}

		$query = wp_parse_args( $args, $query );

		$query = apply_filters( 'gma_prepare_archive_posts', $query );

		unset( $query[ 'nopaging' ], $query[ 'no_found_rows' ] );

		if(isset($query['search_question_title']) && $query['search_question_title'] != ''){
			add_filter( 'posts_where', array($this, 'questions_where'), 10, 1 );
			$wp_query->gma_questions = new WP_Query( $query );
			remove_filter( 'posts_where', array($this, 'questions_where'), 10, 1 );
		} else {
			$wp_query->gma_questions = new WP_Query( $query );
		}

		// sticky question
		$sticky_questions = get_option( 'gma_sticky_questions' );
		if ( !empty( $sticky_questions ) && 'all' == $filter && ! $sort && !$search_text && $query['paged'] == 1 ) {

			if ( $cat ) {
				foreach( $sticky_questions as $key => $id ) {
					$terms = wp_get_post_terms( $id, 'gma-question_category' );
					if ( empty( $terms ) || $cat !== $terms[0]->slug ) {
						unset( $sticky_questions[ $key ] );
					}
				}
			}

			if ( $tag ) {
				foreach( $sticky_questions as $key => $id ) {
					$terms = wp_get_post_terms( $id, 'gma-question_tag' );
					if ( empty( $terms ) || $tag !== $terms[0]->slug ) {
						unset( $sticky_questions[ $key ] );
					}
				}
			}

			if ( $user ) {
				foreach( $sticky_questions as $key => $id ) {
					if ( $user->ID !== get_post_field( 'post_author', $id ) ) {
						unset( $sticky_questions[ $key ] );
					}
				}
			}

			if ( !is_array( $sticky_questions ) ) {
				$sticky_questions = array( $sticky_questions );
			}
			$num_posts = count( $wp_query->gma_questions->posts );
			$stickies_offset = 0;

			for ( $i = 0; $i < $num_posts; $i++ ) {
				if ( in_array( $wp_query->gma_questions->posts[ $i ]->ID, $sticky_questions ) ) {
					$sticky_post = $wp_query->gma_questions->posts[$i];

					array_splice( $wp_query->gma_questions->posts, $i, 1 );
					array_splice( $wp_query->gma_questions->posts, $stickies_offset, 0, array( $sticky_post ) );

					$stickies_offset++;

					$offset = array_search( $sticky_post->ID, $sticky_questions );
					unset( $sticky_questions[$offset] );
				}
			}

			if ( !empty( $sticky_questions ) ) {
				$stickies = get_posts( array(
					'post__in' 		=> $sticky_questions,
					'post_type' 	=> 'gma-question',
					'post_status' 	=> 'publish',
					'nopaging'		=> true
				) );

				foreach( $stickies as $sticky_post ) {
					array_splice( $wp_query->gma_questions->posts, $stickies_offset, 0, array( $sticky_post ) );
					$stickies_offset++;
				}
			}
			$wp_query->gma_questions->post_count = count( $wp_query->gma_questions->posts );
		}
	}

	public function after_archive_posts() {
		wp_reset_query();
		wp_reset_postdata();
	}

	public function prepare_answers( $posts, $query ) {
		global $gma_general_settings;

		if ( $query->is_single() && $query->query_vars['post_type'] == gma_lite()->question->get_slug() ) {
			$question       = $posts[0];
			$ans_cur_page   = isset( $_GET['ans-page'] ) ? intval( $_GET['ans-page'] ) : 1;
			$posts_per_page = isset( $gma_general_settings['answer-per-page'] ) ?  $gma_general_settings['answer-per-page'] : 5;
			// We will include the all answers of this question here;
			$args = array(
				'post_type' 		=> 'gma-answer',
				'order'      		=> 'ASC',
				'paged'				=> $ans_cur_page,
				'post_parent'		=> $question->ID,
				'post_status'       => array( 'publish', 'private', 'pending', 'draft' )
			);
			if ( isset( $gma_general_settings['show-all-answers-on-single-question-page'] ) && $gma_general_settings['show-all-answers-on-single-question-page'] ) {
				$args['nopaging'] = true;
			} else {
				$args['posts_per_page'] = $posts_per_page;
			}

			$best_answer = gma_get_the_best_answer( $question->ID );

			$args = apply_filters( 'gma_prepare_answers', $args );

			$query->gma_answers = new WP_Query( $args );

			// best answer
			if ( $best_answer && !empty( $best_answer ) ) {
				$sticky_posts = array( $best_answer );
				$num_posts = count( $query->gma_answers->posts );
				$best_offset = 0;

				for( $i = 0; $i < $num_posts; $i++ ) {
					if ( $query->gma_answers->posts[ $i ]->ID == $best_answer ) {
						$sticky_post = $query->gma_answers->posts[$i];

						array_splice($query->gma_answers->posts, $i, 1);

						array_splice($query->gma_answers->posts, $best_offset, 0, array($sticky_post));

						$best_offset++;

						$offset = array_search($sticky_post->ID, $sticky_posts);
						unset( $sticky_posts[$offset] );
					}
				}

				if ( !empty($sticky_posts) ) {
					$stickies = get_posts( array(
						'post__in' => $sticky_posts,
						'post_type' => 'gma-answer',
						'post_status' => 'publish',
						'nopaging' => true
					) );

					foreach ( $stickies as $sticky_post ) {
						array_splice( $query->gma_answers->posts, $best_offset, 0, array( $sticky_post ) );
						$best_offset++;
					}
				}
			}
			$query->gma_answers->post_count = count( $query->gma_answers->posts );
		}
		return $posts;
	}

	public function gma_theme_fix_category_page( $query ) {
		if ( isset( $query->query_vars['gma-question_tag'] ) || isset( $query->query_vars['gma-question_category'] ) ) {
			$gma_options = get_option( 'gma_options', array() );
			$query->query_vars['posts_per_page'] = isset( $gma_options['posts-per-page'] ) ? $gma_options['posts-per-page'] : 15;
		}
		return $query;
	}

	public function questions_where($where){
		$search_text = isset( $_GET['qs'] ) ? sanitize_text_field( $_GET['qs'] ) : false;
		$first = true;
		$s = explode( ' ', $search_text );
		if ( count( $s ) > 0 ) {
			$where .= ' AND (';
			foreach ( $s as $w ) {
				if ( ! $first ) {
					$where .= ' OR ';
				}
				$where .= "post_title REGEXP '".preg_quote( $w )."'";
				$first = false;
			}
			$where .= ' ) ';
		}
		return $where;
	}
}
?>