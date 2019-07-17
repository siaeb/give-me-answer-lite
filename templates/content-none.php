<?php
/**
 * The template for displaying a message that questions cannot be found
 *
 * @package Give Me Answer
 * @since Give Me Answer 1.0
 */
?>
<?php if ( ! gma_current_user_can( 'read_question' ) ) : ?>
	<div class="gma-alert gma-alert-info text-left"><?php _e( 'You do not have permission to view questions', 'give-me-answer-lite' ) ?></div>
<?php else : ?>
	<div class="gma-alert gma-alert-info text-left"><?php _e( 'Sorry, but nothing matched your filter', 'give-me-answer-lite' ) ?></div>
<?php endif; ?>
