<?php
/**
 * The template for displaying question archive pages
 *
 * @package Give Me Answer
 * @since Give Me Answer 1.0.1
 */
?>
<div class="gma-questions-archive mt-1">
	<?php do_action( 'gma_before_questions_archive' ) ?>
	
		<div class="gma-questions-list">
		<?php do_action( 'bp_gma_before_questions_list' ) ?>
		<?php if ( gma_has_question() ) : ?>
			<?php while ( gma_has_question() ) : gma_the_question(); ?>
				<?php if ( get_post_status() == 'publish' || ( get_post_status() == 'private' && ( gma_current_user_can( 'edit_question', get_the_ID() ) || gma_current_user_can( 'manage_question' ) || get_current_user_id() == get_post_field( 'post_author', get_the_ID() ) ) ) ) : ?>
					<?php gma_load_template( 'content', 'question' ) ?>
				<?php endif; ?>
			<?php endwhile; ?>
		<?php else : ?>
			<?php gma_load_template( 'content', 'none' ) ?>
		<?php endif; ?>
		<?php do_action( 'gma_after_questions_list' ) ?>
		</div>
		<div class="gma-questions-footer d-flex">
			<?php gma_question_paginate_link() ?>
		</div>

	<?php do_action( 'gma_after_questions_archive' ); ?>
</div>
