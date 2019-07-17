<?php
/**
 * The template for displaying answer submit form
 *
 * @package Give Me Answer
 * @since Give Me Answer 1.0
 */

global $gma_options;

$prev_question = gma_get_previous_question(get_the_ID());
$next_question = get_get_next_question(get_the_ID());

?>

<div class="gma-answer-form">
	<?php do_action( 'gma_before_answer_submit_form' ); ?>
	<h3 class="gma-answer-form-title text-left"><?php _e( 'Your Answer', 'give-me-answer-lite' ) ?></h3>
    <div class="error"></div>
	<form name="gma-answer-form" id="gma-answer-form" method="post" enctype="multipart/form-data">
		<?php gma_print_notices(); ?>
		<?php $content = isset( $_POST['answer-content'] ) ? sanitize_text_field( $_POST['answer-content'] ) : ''; ?>

		<?php if ( gma_current_user_can( 'post_answer' ) && !is_user_logged_in() ) : ?>

		<?php
            $email = isset( $_POST['user-email'] ) ? sanitize_email( $_POST['user-email'] ) : '';
			$name = isset( $_POST['user-name'] ) ? esc_html( $_POST['user-name'] ) : '';
		?>

        <div class="input-group my-2">
            <span class="input-group-append" id="basic-addon2">
                <span class="input-group-text"><?php _e('Name', 'give-me-answer-lite'); ?></span>
            </span>
            <input
                type="text"
                class="form-control"
                name="user-name"
                value="<?php echo $name; ?>"
                placeholder="<?php _e('Please enter your name', 'give-me-answer-lite'); ?>"
            >
        </div>

        <div class="input-group my-2">
            <span class="input-group-append" id="basic-addon2">
                <span class="input-group-text"><?php _e('Email', 'give-me-answer-lite'); ?></span>
            </span>
            <input
                type="text"
                class="form-control"
                name="user-email"
                value="<?php echo $email; ?>"
                placeholder="<?php _e('Please enter your email', 'give-me-answer-lite'); ?>"
            >
        </div>

		<?php endif; ?>

        <?php

            if ( 'tinymce' == $gma_options['editor'][ 'answer' ] ) {
                gma_init_tinymce_editor( array( 'content' => $content, 'textarea_name' => 'answer-content', 'id' => 'gma-answer-content' ) );
            } else {
                ?>
                    <textarea id="gma-answer-content" name="answer-content" class="form-control" placeholder="<?php _e('Write your answer ...', 'give-me-answer-lite'); ?>" rows="5"><?php echo $content; ?></textarea>
                <?php
            }

        ?>
		<?php gma_load_template( 'captcha', 'form' ); ?>

		<?php do_action('gma_before_answer_submit_button'); ?>
		<input type="submit" name="submit-answer" class="btn gma-btn-primary btn-responsive-block my-2" value="<?php _e( 'Post Your Answer', 'give-me-answer-lite' ) ?>">
		<input type="hidden" name="question_id" value="<?php the_ID(); ?>">
		<input type="hidden" name="gma-action" value="add-answer">
		<?php wp_nonce_field( '_gma_add_new_answer' ) ?>
	</form>
	<?php do_action( 'gma_after_answer_submit_form' ); ?>
</div>

<?php if ( isset($gma_options['general']['show-next-prev-qs']) && $gma_options['general']['show-next-prev-qs'] ) { ?>
<div class="d-flex justify-content-between gma-question-pager mt-2 border-top pt-2">
    <?php if ( $prev_question ) { ?>
        <a href="<?php echo get_permalink($prev_question->ID); ?>" class="btn btn-outline-primary"><?php _e('Previous', 'give-me-answer-lite'); ?></a>
    <?php } ?>
    <?php if ( $next_question ) { ?>
        <a href="<?php echo get_permalink($next_question->ID); ?>" class="btn btn-outline-primary"><?php _e('Next', 'give-me-answer-lite'); ?></a>
    <?php } ?>
</div>
<?php } ?>
