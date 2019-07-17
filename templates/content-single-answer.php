<?php
/**
 * The template for displaying single answers
 *
 * @package Give Me Answer
 * @since Give Me Answer 1.0
 */
/**
 * The template for displaying single answers
 *
 * @package Give Me Answer
 * @since Give Me Answer 1.0
 */

global $gma_avatar, $gma_general_settings;
$user_id = get_post_field( 'post_author', get_the_ID() ) ? get_post_field( 'post_author', get_the_ID() ) : 0;
$answer_author = gma_get_author_info( get_the_ID() );
$is_best_answer   = gma_is_the_best_answer();
$is_pending       = get_post_status(get_the_ID()) == 'pending' && gma_get_current_user_id() == get_post_field('post_author', get_the_ID());
$vote_count         = gma_vote_count( false, false );
$vote_count_format  = gma_the_vote_count( false );
?>

<div id="answer-<?php echo get_the_ID(); ?>" class="py-2 <?php echo gma_post_class() ?> <?php if ($vote_count <= -3) echo 'gma-negative-answer'; ?>" itemprop="<?php if ( $is_best_answer ) echo 'acceptedAnswer'; else echo 'suggestedAnswer'; ?>" itemscope itemtype="http://schema.org/Answer">
	<?php
        $gma_user_vote_id = '';
        if ( is_user_logged_in( ) ) {
            global $current_user;
            $gma_user_vote_id = $current_user->ID;
        }else{
            global $gma_general_settings;
            if(isset($gma_general_settings['allow-anonymous-vote']) && $gma_general_settings['allow-anonymous-vote']){
                $gma_user_vote_id = gma_get_current_user_session();
            }
        }
	?>
    <div class="col-2 col-sm-1 px-0 align-items-center d-flex gma-answer-vote flex-column" data-nonce="<?php echo wp_create_nonce( '_gma_answer_vote_nonce' ); ?>" data-post="<?php the_ID(); ?>">

        <a class="gma-vote gma-vote-up <?php if (gma_is_user_voted( get_the_ID(), 1, $gma_user_vote_id )) echo 'border-bottom-orange'; ?>" href="#"></a>
        <span class="gma-vote-count <?php if ( $vote_count ) echo 'gma-cursor-pointer'; ?>" itemprop="<?php if ( $vote_count ) echo 'upvoteCount'; else echo 'downvoteCount'; ?>">
            <?php echo $vote_count; ?>
        </span>
        <a class="gma-vote gma-vote-down <?php if (gma_is_user_voted( get_the_ID(), -1, $gma_user_vote_id ) ) echo 'border-top-orange'; ?>" href="#"></a>

        <?php if ( gma_current_user_can( 'edit_question', gma_get_question_from_answer_id() ) && is_user_logged_in() ) { ?>
            <?php
                $action           = $is_best_answer ? 'gma-unvote-best-answer' : 'gma-vote-best-answer' ;
                $wpnonce          = wp_create_nonce('_gma_vote_best_answer');
                $best_answer_args = gma_get_best_answer_args();
            ?>
            <a
                class="gma-pick-best-answer <?php echo $action; ?>"
                data-post="<?php echo get_the_ID(); ?>"
                data-wpnonce="<?php echo $wpnonce; ?>"
                <?php if ( $is_best_answer ) echo 'title="' . $best_answer_args['title'] . '"'; ?>
                href="<?php echo esc_url( wp_nonce_url( add_query_arg( array( 'answer' => get_the_ID(), 'action' => $action ), admin_url( 'admin-ajax.php' ) ), '_gma_vote_best_answer' ) ) ?>">
                <svg aria-hidden="true" class="svg-icon iconCheckmarkLg" width="36" height="36" viewBox="0 0 36 36">
                    <path d="M6 14l8 8L30 6v8L14 30l-8-8z"></path>
                </svg>
            </a>
        <?php } else if ( $is_best_answer ) { ?>
            <a class="gma-pick-best-answer gma-unvote-best-answer no-click">
                <svg aria-hidden="true" class="svg-icon iconCheckmarkLg" width="36" height="36" viewBox="0 0 36 36">
                    <path d="M6 14l8 8L30 6v8L14 30l-8-8z"></path>
                </svg>
            </a>
        <?php } ?>
    </div>

    <div class="col-10 col-sm-11 pr-0 px-md-0 gma-answer-content-wrapper">
	    <div class="gma-answer-content" itemprop="text"><?php the_content(); ?></div>
        <div class="d-flex justify-content-between flex-column flex-sm-row pb-3 my-2">
        <div class="d-flex justify-content-between justify-content-sm-start qadetail p-2 p-sm-0 flex-wrap">

            <div class="d-flex align-items-center gma-user-summary pr-2" data-user-id="<?php echo $user_id; ?>" data-nonce="<?php echo wp_create_nonce('_gma_user_summary_nonce'); ?>">
                <div class="user-gravatar32">
		            <?php if ( ! $answer_author[ 'anonymous' ] ) { ?>
                    <a href="<?php echo gma_get_author_link( $user_id ); ?>">
			            <?php } ?>

			            <?php
			            if ( $gma_avatar[ 'show-on-single-answer' ] ) {
				            gma_user_avatar( [ 'user_id' => $user_id, 'size' => $gma_avatar[ 'size' ][ 'answer' ] ] );
			            }
			            ?>

			            <?php if ( ! $answer_author[ 'anonymous' ] ) { ?>
                    </a>
	            <?php } ?>
                </div>
                <div class="text-truncate user-details" itemprop="author" itemscope itemtype="http://schema.org/Person">
		            <?php
		            if ( $answer_author[ 'anonymous' ] ) {
			            echo  sprintf( '<span itemprop="name">%s</span>', esc_html( $answer_author[ 'display_name' ] ) );
		            } else {
			            ?>
                        <a class="font-weight-bold text-info px-1" href="<?php echo gma_get_author_link( $user_id ); ?>" itemprop="name">
				            <?php echo gma_user_displayname( $user_id ); ?>
                        </a>
		            <?php } ?>
                </div>
            </div>

            <div class="user-action-time text-black-50 d-flex align-items-center">
				<?php echo gma_display_date( get_post_field( 'post_date', get_the_ID() ) ); ?>
            </div>
            <time style="display: none;" itemprop="dateCreated" datetime="<?php echo date('Y-m-d\TH:i:s', strtotime(get_post_field('post_date', get_the_ID()))); ?>"></time>
        </div>
        <div class="d-flex align-items-baseline order-first order-sm-1 mb-2 mb-sm-0">

            <?php include GMA_DIR . 'templates/includes/share-btn.php'; ?>

	        <?php if ( is_user_logged_in() ) { ?>

		        <?php if ( gma_current_user_can( 'edit_answer' ) ) { ?>
			        <?php $parent_id = gma_get_question_from_answer_id(); ?>
                    <a class="gma-question-operation gma_edit_answer mr-2" href="<?php echo add_query_arg( array( 'edit' => get_the_ID() ), get_permalink( $parent_id ) ); ?>">
                        <?php _e('Edit', 'give-me-answer-lite'); ?>
                    </a>
		        <?php } ?>

		        <?php if ( gma_current_user_can( 'delete_question' ) ) { ?>
			        <?php
                        $delete_answer_nonce = wp_create_nonce( '_gma_action_remove_answer_nonce' );
                        $action_url = add_query_arg( array( 'action' => 'gma-ajax-delete-answer',
                                                            'answerID' => get_the_ID(),
                                                            '_wpnonce' =>  $delete_answer_nonce ),
                                                            admin_url( 'admin-ajax.php' )
                         );
                    ?>
                    <form action="<?php echo admin_url( 'admin-ajax.php' ); ?>" method="post">
                        <input type="hidden" name="action" value="gma-ajax-delete-answer">
                        <input type="hidden" name="answerID" value="<?php echo get_the_ID(); ?>">
                        <input type="hidden" name="_wpnonce" value="<?php echo $delete_answer_nonce; ?>" >
                        <button type="submit" class="gma-question-operation gma_delete_answer border-0 bg-white px-0 mr-2" data-answer="<?php echo get_the_ID(); ?>" data-wpnonce="<?php echo $delete_answer_nonce; ?>">
                            <?php _e('Delete', 'give-me-answer-lite'); ?>
                        </button>
                    </form>
		        <?php } ?>

	        <?php } ?>
        </div>
    </div>

        <?php
            do_action('gma_after_show_content_answer', get_the_ID());
            comments_template();
        ?>

        <?php if ( $is_pending ) : ?>
            <div class="alert alert-warning text-center p-3">
                <?php _e('Your answer is waiting for administrator moderation.', 'give-me-answer-lite'); ?>
            </div>
        <?php endif; ?>

    </div>
</div>
