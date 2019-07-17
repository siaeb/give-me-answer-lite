<?php
/**
 * The template for displaying answers
 *
 * @package Give Me Answer
 * @since Give Me Answer 1.0
 */
global $gma_general_settings;
$question_id    = gma_get_post_parent_id( get_the_ID() );
$question       = get_post( $question_id );
$question_url   = get_permalink($question->ID);
$best_answer    = gma_get_the_best_answer( $question_id );
$user_answers_count = gma_get_answers_count( $question->ID, get_current_user_id() );
$total_answers_count = gma_question_answers_count( get_the_ID() );
?>

<div class="gma-answers">
	<?php do_action( 'gma_before_answers' ) ?>

    <ul class="nav nav-tabs mt-2 border-bottom-0 gma-answers-subheader" <?php if ( ! gma_has_answers() ) echo 'style="display: none;"'; ?>>

        <a name="tab-top"></a>

        <li class="nav-item">
            <div class="h5 text-black-50 gma-answers-title text-left m-0">
                <?php echo $total_answers_count . ' ' . _n('Answer', 'Answers', $total_answers_count, 'give-me-answer-lite'); ?>
            </div>
        </li>

        <li class="nav-item ml-auto">
            <a class="nav-link <?php if (!isset($_GET['activetab']) || $_GET['activetab'] == 'oldest') echo 'active' ?>" href="<?php echo add_query_arg(['activetab' => 'oldest'], $question_url); ?>#tab-top"><?php _e('Oldest', 'give-me-answer-lite'); ?></a>
        </li>

        <li class="nav-item">
            <a class="nav-link <?php if (isset($_GET['activetab']) && $_GET['activetab'] == 'newest') echo 'active' ?>" href="<?php echo add_query_arg(['activetab' => 'newest'], $question_url); ?>#tab-top"><?php _e('Newest', 'give-me-answer-lite'); ?></a>
        </li>

        <li class="nav-item">
            <a class="nav-link <?php if (isset($_GET['activetab']) && $_GET['activetab'] == 'votes') echo 'active' ?>" href="<?php echo add_query_arg(['activetab' => 'votes'], $question_url); ?>#tab-top"><?php _e('Votes', 'give-me-answer-lite'); ?></a>
        </li>

    </ul>



    <span style="display: none;" itemprop="answerCount"><?php echo $total_answers_count; ?></span>
	<div class="gma-answers-list">
		<?php do_action( 'gma_before_answers_list' ) ?>
			<?php while ( gma_has_answers() ) : gma_the_answers(); ?>
				<?php $question_id = gma_get_post_parent_id( get_the_ID() ); ?>
				<?php if (
      				        ( 'private' == get_post_status() &&
                            ( gma_current_user_can( 'edit_answer', get_the_ID() ) ||
                            gma_current_user_can( 'edit_question', $question_id ) ) ) || 'publish' == get_post_status()  ||
                            ( get_post_status() == 'pending' && gma_get_current_user_id() == get_post_field( 'post_author', get_the_ID() )  )
                         )
                        :
                ?>
					<?php gma_load_template( 'content', 'single-answer' ); ?>
				<?php endif; ?>
			<?php endwhile; ?>
			<?php wp_reset_postdata(); ?>
		<?php do_action( 'gma_after_answers_list' ) ?>
	</div>
    <?php do_action( 'gma_after_answers' ); ?>
    <?php
     if ( gma_current_user_can( 'post_answer' ) && !gma_is_closed( get_the_ID() )) {
        if (!($best_answer && $gma_general_settings['close-has-best-answer-question']) || gma_is_admin()) {
            if (!gma_count_user_pending_answers($question_id) || !$gma_general_settings['answer']['moderation'] || gma_is_admin()) {
                if (apply_filters('gma_show_answer_form', true)) {
                    gma_load_template('answer', 'submit-form');
                }
            }
        }
    }
    ?>
</div>


<?php include_once GMA_DIR . 'templates/modal-share.php'; ?>
