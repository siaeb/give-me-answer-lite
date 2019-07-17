<?php
/**
 * The template for displaying single questions
 *
 * @package Give Me Answer
 * @since Give Me Answer 1.0
 */
global $gma_general_settings, $gma_avatar;
$post_date = get_post_field( 'post_date', get_the_ID() );
$user_id   = get_post_field( 'post_author', get_the_ID() ) ? get_post_field( 'post_author', get_the_ID() ) : false;
$tags      = wp_get_post_terms( get_the_ID(), 'gma-question_tag' );
$author    = gma_get_author_info( get_the_ID() );

$answer_avatar_size = $gma_avatar['size']['answer'];
$question           = get_post( get_the_ID() );
$vote_count         = gma_vote_count( false, false );
$vote_count_format  = gma_the_vote_count( false );
$question_col       = apply_filters('gma_question_wrapper_col', 'col-10 col-sm-11', get_the_ID());
do_action( 'gma_before_single_question_content' );
$show_votebox = apply_filters( 'gma_show_single_question_vote_box', true);
?>
<?php if ( 'pending' === $question->post_status ) { ?>
    <div class="alert alert-warning d-flex align-items-center justify-content-between">
        <?php _e('The question is awaiting approval', 'give-me-answer-lite'); ?>

        <?php if ( gma_is_admin() ) { ?>
            <button class="btn btn-outline-success btn-sm gma-approve-question" data-post="<?php echo get_the_ID(); ?>">
                <?php _e('Publish', 'give-me-answer-lite'); ?>
            </button>
        <?php } ?>

    </div>
<?php } ?>

<div class="gma-question-item row mx-0">
	<?php
	$gma_user_vote_id = '';
	if ( is_user_logged_in() ) {
		global $current_user;
		$gma_user_vote_id = $current_user->ID;
	} else {
		global $gma_general_settings;
		if ( isset( $gma_general_settings['allow-anonymous-vote'] ) && $gma_general_settings['allow-anonymous-vote'] ) {
			$gma_user_vote_id = gma_get_current_user_session();
		}
	}
	?>

    <input type="hidden" id="question-id" value="<?php echo get_the_ID(); ?>">

    <div <?php echo !$show_votebox ? 'style="display: none;"' : ''; ?> class="col-2 col-sm-1 px-0 gma-question-vote <?php echo $show_votebox ? 'align-items-center d-flex flex-column' : ''; ?>" data-nonce="<?php echo wp_create_nonce( '_gma_question_vote_nonce' ) ?>" data-post="<?php the_ID(); ?>">


        <a class="gma-vote gma-vote-up <?php if ( gma_is_user_voted( get_the_ID(), 1, $gma_user_vote_id ) ) echo 'border-bottom-orange'; ?>" href="#"></a>
        <span class="gma-vote-count <?php if ( $vote_count ) echo 'gma-cursor-pointer'; ?>" data-value="<?php echo $vote_count; ?>" itemprop="<?php if ( $vote_count >= 0 ) echo 'upvoteCount'; else echo 'downvoteCount'; ?>">
            <?php echo $vote_count; ?>
        </span>
        <a class="gma-vote gma-vote-down <?php if ( gma_is_user_voted( get_the_ID(), - 1, $gma_user_vote_id ) ) {
            echo 'border-top-orange';
        } ?>" href="#"></a>


		<?php if ( is_user_logged_in() ) { ?>
			<?php
			$question_followers = get_post_meta( get_the_ID(), '_gma_followers', false );
			$question_followers = ! empty( $question_followers ) ? count( $question_followers ) : 0;
			?>
            <span
                title="<?php _e( 'Click to mark as favorite question', 'give-me-answer-lite' ); ?>"
                data-nonce="<?php echo wp_create_nonce( '_gma_follow_question' ); ?>"
                data-post="<?php echo get_the_ID(); ?>"
                class="gma-favorites <?php if ( gma_is_followed( get_the_ID() ) ) {
                    echo 'text-warning';
                } else {
                    echo 'text-muted';
                } ?>">
                <svg aria-hidden="true" class="svg-icon iconStar" width="22" height="22" viewBox="0 0 18 18">
                    <path d="M9 12.65l-5.29 3.63 1.82-6.15L.44 6.22l6.42-.17L9 0l2.14 6.05 6.42.17-5.1 3.9 1.83 6.16z"></path>
                </svg>
            </span>

		<?php } ?>
    </div>


    <div class="<?php echo $question_col; ?> pr-0 px-md-0">
        <div class="gma-question-content" itemprop="text"><?php the_content(); ?></div>

        <?php
        do_action('gma_after_single_question_body', get_the_ID());
        $question_status = gma_lite()->utility->get_question_status(get_the_ID());
        if ( $question_status['status'] == 'close' ) {
        ?>
            <div class="alert alert-danger rounded-0">
                <?php _e('Close Reason : ', 'give-me-answer-lite'); ?>
                <?php echo esc_html($question_status['reason']); ?>
            </div>
        <?php } ?>

		<?php if ( $tags ) { ?>
            <div class="gma-question-tags tags mt-2 mb-3 d-flex flex-wrap">
				<?php foreach ( $tags as $item ) { ?>
					<?php $link = get_term_link( $item, 'gma-question_tag' ); ?>
                    <a href="<?php echo $link; ?>" class="tag gma-badge-stack mr-1 mb-2 mb-sm-0">
						<?php echo $item->name; ?>
                    </a>
				<?php } ?>
            </div>
		<?php } ?>

        <div class="mb-0">
            <div class="d-flex justify-content-between flex-column align-items-sm-center flex-sm-row pb-0 pb-md-3 my-2">
                <div class="d-flex justify-content-between justify-content-sm-start qadetail p-2 p-sm-0 flex-wrap">
                    <div class="d-flex align-items-center pr-2 gma-user-summary" data-user-id="<?php echo $user_id; ?>" data-nonce="<?php echo wp_create_nonce('_gma_user_summary_nonce'); ?>">
                        <div class="user-gravatar32">
		                    <?php if ( ! $author['anonymous'] ) { ?>
                            <a href="<?php echo gma_get_author_link( $user_id ); ?>">
			                    <?php } ?>
			                    <?php
                                    if ( isset( $gma_avatar['show-on-single-question'] ) && $gma_avatar['show-on-single-question'] ) {
                                        gma_user_avatar( [
                                            'user_id' => $user_id,
                                            'size'    => $gma_avatar['size']['question']
                                        ] );
                                    }
			                    ?>
			                    <?php if ( ! $author['anonymous'] ) { ?>
                            </a>
	                    <?php } ?>
                        </div>
                        <div class="text-truncate user-details" itemprop="author" itemscope itemtype="http://schema.org/Person">
		                    <?php
		                    if ( $author['anonymous'] ) {
			                    echo sprintf( '<span itemprop="name">%s</span>', esc_html( $author['display_name'] ) );
		                    } else {
			                    ?>
                                <a itemprop="name" class="font-weight-bold text-info px-1" href="<?php echo gma_get_author_link( $user_id ); ?>">
				                    <?php echo gma_user_displayname( $user_id ); ?>
                                </a>
		                    <?php } ?>
                        </div>
                    </div>
                    <div class="user-action-time text-black-50 d-flex align-items-center">
						<?php echo gma_display_date( $post_date ); ?>
                    </div>
                    <time style="display: none;" itemprop="dateCreated" datetime="<?php echo date('Y-m-d\TH:i:s', strtotime(get_post_field('post_date', get_the_ID()))); ?>"></time>
                </div>

                <div class="d-flex align-items-baseline order-first order-sm-1 mb-2 mb-sm-0">

                    <?php include GMA_DIR . 'templates/includes/share-btn.php'; ?>

					<?php if ( is_user_logged_in() ) { ?>

						<?php if ( gma_current_user_can( 'edit_question' )) { ?>
                            <!-- Edit Question -->
                            <a class="gma-question-operation mr-2 gma_edit_question"
                               href="<?php echo add_query_arg( array( 'edit' => get_the_ID() ), get_permalink() ); ?>">
								<?php _e( 'Edit', 'give-me-answer-lite' ); ?>
                            </a>
						<?php } ?>

						<?php if ( gma_current_user_can( 'delete_question' ) ) { ?>
							<?php
							$wpnonce    = wp_create_nonce( '_gma_action_remove_question_nonce' );
							$action_url = add_query_arg( array('action' => 'gma_delete_question', 'question_id' => get_the_ID(), '_wpnonce'    => $wpnonce), admin_url( 'admin-ajax.php' ) );
							?>
                            <!-- Delete Question -->
                            <a class="gma-question-operation mr-2 gma_delete_question"
                               data-wpnonce="<?php echo $wpnonce; ?>" data-question="<?php echo get_the_ID(); ?>"
                               href="<?php echo $action_url; ?>">
								<?php _e( 'Delete', 'give-me-answer-lite' ); ?>
                            </a>
						<?php } ?>

					<?php } ?>
                </div>
            </div>

        </div>
		<?php
            do_action( 'gma_after_show_content_question', get_the_ID() );
            do_action( 'gma_before_single_question_comment' );
            comments_template();
            do_action( 'gma_after_single_question_comment' )
        ?>

    </div>
</div>

<?php do_action( 'gma_after_single_question_content' ); ?>


<script type="text/custom" id="answer-moderation-tmpl">

    <div class="alert alert-warning text-center p-3 my-2 gma-answer-is-pending">
        <?php _e('Your answer is waiting for administrator moderation.', 'give-me-answer-lite'); ?>
    </div>

</script>


<script type="text/custom" id="answer-template">
    <div id="answer-{answer-id}" class="py-2 gma-answer-item row mx-0" itemprop="suggestedAnswer" itemscope itemtype="http://schema.org/Answer">


    <div class="col-2 col-sm-1 px-0 align-items-center d-flex gma-answer-vote flex-column" data-nonce="{answer-vote-nonce}" data-post="{answer-id}">

        <a class="gma-vote gma-vote-up" href="#"></a>
        <span class="gma-vote-count">0</span>
        <a class="gma-vote gma-vote-down" href="#"></a>

        <a class="gma-pick-best-answer gma-vote-best-answer" data-post="{answer-id}" data-wpnonce="{best-answer-nonce}" href="#">
            <svg aria-hidden="true" class="svg-icon iconCheckmarkLg" width="36" height="36" viewBox="0 0 36 36"><path d="M6 14l8 8L30 6v8L14 30l-8-8z"></path></svg>
        </a>
    </div>

    <div class="col-10 col-sm-11 pr-0 px-md-0 gma-answer-content-wrapper">
	    <div class="gma-answer-content">{answer-content}</div>
        <div class="d-flex justify-content-between flex-column flex-sm-row pb-3 my-2">
        <div class="d-flex justify-content-between justify-content-sm-start qadetail p-2 p-sm-0 flex-wrap">
            <div class="d-flex align-items-center gma-user-summary pr-2" data-user-id="{author-id}" data-nonce="{user-summary-nonce}">
                <?php if ( $gma_avatar['show-on-single-answer'] ) { ?>
                <div class="user-gravatar32">
                    <a href="{author-url}">
                        <div class="gravatar-wrapper-32 mr-1">
                            <span
                            class="avatar avatar-sm mt-0 <?php echo gma_get_avatar_classes(); ?>"
                            style="background-image: url({author-image});width: <?php echo $answer_avatar_size; ?>px;height:<?php echo $answer_avatar_size; ?>px"></span>
                        </div>
                    </a>
                </div>
                <?php } ?>
                <div class="text-truncate user-details" itemprop="author" itemscope itemtype="http://schema.org/Person">
                    <a class="font-weight-bold text-info px-1" href="{author-url}" itemprop="name">
                        {author-name}
                    </a>
                </div>
            </div>
            <div class="user-action-time text-black-50 d-flex align-items-center">
				{answer-date}
            </div>
        </div>
        <div class="d-flex align-items-baseline order-first order-sm-1 mb-2 mb-sm-0">
            <?php if ( ! isset($gma_general_settings['general']['disable-share-socials']) || ! $gma_general_settings['general']['disable-share-socials']) { ?>
                <a class="gma-question-operation gma-share mr-2" data-post="{answer-id}">
                    <?php _e('Share', 'give-me-answer-lite'); ?>
                </a>
            <?php } ?>

            <a href="{edit-url}" class="gma-question-operation gma_edit_answer mr-2">
                <?php _e( 'Edit', 'give-me-answer-lite' ); ?>
            </a>

            <form action="<?php echo admin_url( 'admin-ajax.php' ); ?>" method="post">
                <input type="hidden" name="action" value="gma-ajax-delete-answer">
                <input type="hidden" name="answerID" value="{answer-id}">
                <input type="hidden" name="_wpnonce" value="{delete-answer-nonce}" >
                <button type="submit" class="gma-question-operation gma_delete_answer border-0 bg-white px-0 mr-2" data-answer="{answer-id}" data-wpnonce="{delete-answer-nonce}">
                    <?php _e('Delete', 'give-me-answer-lite'); ?>
                </button>
            </form>
        </div>
    </div>

	<div class="gma-comments">
        <div class="gma-comments-list"></div>
            <p class="text-black-50 py-2 gma-leave-comment-parent">
                <a href="#" class="gma-leave-comment text-small">
                    <?php _e( 'add a comment', 'give-me-answer-lite' ); ?>
                </a>
            </p>
            <div id="gma-respond" class="gma-comment-form" style="display: none;">
                <form method="post" id="comment_form_{answer-id}" class="comment-form gma-comment-show-button">
                    <p class="comment-form-comment">
                        <textarea
                            id="comment"
                            name="comment"
                            class="form-control"
                            placeholder="<?php _e( 'Use comments to ask for more information or suggest improvements. Avoid answering questions in comments.', 'give-me-answer-lite' ); ?>"
                            rows="2"
                            aria-required="true"
                            style="height: 118px;"></textarea>
                    </p>
                    <?php
                        /**
                         * Fires before comment submit button
                         *
                         * @since 1.0
                         *
                         */
                        do_action( 'gma_before_comment_submit_button' );
                    ?>


                    <input class="btn btn-primary" name="comment-submit" type="submit" id="submit" value="<?php _e( 'Send', 'give-me-answer-lite' ); ?>" >
                    <input type="hidden" name="comment_post_ID" value="{answer-id}" id="comment_post_{answer-id}_ID">
                    <input type="hidden" name="comment_parent" id="comment_{question-id}_parent" value="0">
                </form>
            </div><!-- #respond -->
    </div>
    </div>
</div>

</script>
