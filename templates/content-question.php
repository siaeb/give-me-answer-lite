<?php
/**
 * The template for displaying question content
 *
 * @package Give Me Answer
 * @since Give Me Answer 1.0
*/
/**
 * The template for displaying question content
 *
 * @package Give Me Answer
 * @since Give Me Answer 1.0
*/

global        $post, $gma_avatar, $gma_general_settings;
$tags         = wp_get_post_terms( get_the_ID(), 'gma-question_tag' );
$is_closed    = gma_question_status() == 'close';
$close_reason = get_post_meta( get_the_ID(), '_gma_close_reason', true );
$show_tags    = $gma_general_settings[ 'show-archive-tags' ];
?>

<div class="row mx-0 py-3 gma-question-item align-items-center <?php echo gma_post_class(); ?>  <?php if ( $is_closed  ) echo ' closed '; ?>">

    <div class="gma-question-stats col-auto px-0 d-flex flex-column flex-sm-row align-items-center mr-2">

	    <?php if ( $gma_general_settings[ 'show-archive-votebox' ] ) { ?>
            <span class="gma-votes-count text-secondary">
                <?php $vote_count = gma_vote_count(); ?>
                <?php printf( __( '<strong>%1$s</strong> votes', 'give-me-answer-lite' ), $vote_count ); ?>
            </span>
	    <?php } ?>

        <?php if ( $gma_general_settings[ 'show-archive-answerbox' ] ) { ?>
        <span class="gma-answers-count text-secondary
		<?php
            if ( gma_get_the_best_answer() ) {
                echo ' gma-has-best-answer ';
            } else if ( gma_question_answers_count()  ) {
                echo ' gma-has-answer ';
            } else {
                echo ' gma-no-answer ';
            }
		?>">
			<?php $answers_count = gma_question_answers_count(); ?>
			<?php printf( __( '<strong>%1$s</strong> answers', 'give-me-answer-lite' ), $answers_count ); ?>
		</span>
        <?php } ?>

	    <?php if ( $gma_general_settings[ 'show-archive-viewbox' ] ) { ?>
            <span class="gma-views-count text-secondary d-none d-sm-block">
                <?php $views_count = gma_question_views_count() ?>
                <?php printf( __( '<strong>%1$s</strong> views', 'give-me-answer-lite' ), gma_convert_to_kilo($views_count) ); ?>
            </span>
	    <?php } ?>
    </div>

    <div class="col px-0">
        <div class="gma-question-title text-info line-height-normal d-flex align-items-center my-0">
            <?php do_action('gma_before_archive_question_title', get_the_ID()); ?>
            <a class="h6 m-0" href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
        </div>
        <div class="gma-question-meta text-secondary">
		<?php
			$user_id       = get_post_field( 'post_author', get_the_ID() ) ? get_post_field( 'post_author', get_the_ID() ) : false;
            $author        = get_user_by( 'id', $user_id );
            $time          = gma_display_date( get_post_field('post_date', get_the_ID() ));
            $text          = __(' asked ', 'give-me-answer-lite');
			$latest_answer = gma_get_latest_answer( get_the_ID() );
			if ( $latest_answer ) {
				$author = get_user_by( 'id', $latest_answer->post_author );
				$time   = gma_display_date( $latest_answer->post_date );
				$text   = '<a class="color-light-gray lightfont" href="' . get_permalink(get_the_ID()) . '#answer-' . $latest_answer->ID . '">';
				$text   .= __('answered ', 'give-me-answer-lite');
				$text   .= '</a>';
			}
            $quora_id = $latest_answer ? $latest_answer->ID : get_the_ID();
			$is_anonymous = gma_lite()->utility->is_anonymous( $quora_id);
            $author_info  = gma_get_anonymous_user( $quora_id );
		?>

        <div class="row tags mt-1">

            <div class="align-items-center col-12 d-flex flex-wrap <?php if( ( $show_tags && count( $tags ) ) ) echo 'mb-2';?>">
		        <?php

		        $question_meta = '';

		        if ( $gma_avatar[ 'show-on-archive' ] ) {
		            if ( false == $is_anonymous ) {
                        $question_meta .= gma_user_avatar([ 'user_id' => $author->ID, 'size' => $gma_avatar[ 'size' ] [ 'archive' ]], true);
                    } else {
		                // Answer or question submitted by anonymous
                        $question_meta .= gma_user_avatar([ 'user_id' => $author_info[ 'email' ], 'size' => $gma_avatar[ 'size' ] [ 'archive' ]], true);
                    }

		        }

		        $question_meta .= '<span class="question-meta-data">';

		        if ( $is_anonymous ) {
			        $question_meta .= '<span>'.$time.'</span> ';
			        $question_meta .= ' ';
			        $question_meta .= $author_info[ 'name' ];
			        $question_meta .= $text;
		        } else {
			        $question_meta .= '<span>'.$time.'</span> ';
			        $question_meta .= sprintf( '<a class="text-info" href="%s">%s</a> ',
                                                       gma_get_author_link( $author->ID ),
                                                       gma_user_displayname( $author->ID ));
			        $question_meta .= $text;
		        }

		        $question_meta .= '</span>';

		        if ( isset( $gma_general_settings['show-archive-question-category'] ) && $gma_general_settings['show-archive-question-category'] ) {
			        $question_meta .= get_the_term_list( get_the_ID(), 'gma-question_category', '<span class="gma-question-category">' . __( '&nbsp;&bull;&nbsp;', 'give-me-answer-lite' ), ', ', '</span>' );
		        }

		        echo $question_meta;
		        ?>
            </div>

            <?php if ( ($gma_general_settings['show-archive-tags'] && count( $tags ) ) || ( $is_closed && !empty( $close_reason ) ) ) { ?>
                <div class="col-12 d-flex align-items-center flex-wrap">

                    <?php if ( ( ($tags && !$is_closed ) || ( $tags && ( $is_closed && empty( $close_reason ) ) ) ) && $gma_general_settings[ 'show-archive-tags' ] )  { ?>
                        <?php foreach ( $tags as $item ) {?>
                            <a href="<?php echo get_term_link( $item, 'gma-question_tag' ); ?>" class="tag mb-1 mr-1 gma-badge-stack">
                                <?php echo $item->name; ?>
                            </a>
                        <?php } ?>
                    <?php } ?>

                    <?php if ( $is_closed && ! empty( $close_reason ) ) { ?>
                        <p class="mb-0 mt-0 text-danger gma-question-close-reason">
                            <span><?php _e('Close reason : ', 'give-me-answer-lite'); ?></span>
                            <span><?php echo esc_html( $close_reason ); ?></span>
                        </p>
                    <?php } ?>

                </div>
            <?php } ?>


        </div>

	</div>
    </div>
</div>
