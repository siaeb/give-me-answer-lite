<?php
/**
 * The template for displaying question archive pages
 *
 * @package Give Me Answer
 * @since Give Me Answer 1.0
 */
global $gma_general_settings, $wp_query;

?>

<div class="gma">

    <?php
        do_action('gma_archive_page_top');
        gma_error_if_in_maintenance();
    ?>

    <?php if ( array_key_exists( 'gma-question_tag', $wp_query->query_vars ) ) { ?>
        <?php $curtag = get_term_by( 'slug', $wp_query->query_vars[ 'gma-question_tag' ], 'gma-question_tag' ); ?>

        <h2 class="d-flex justify-content-between flex-column flex-sm-row gma-tag-name text-secondary">
            <div><?php echo sprintf( __('Questions tagged [%s]', 'give-me-answer-lite'), $curtag->name ); ?></div>
            <div class="gma-tags-count"><?php echo sprintf(_n('%s Question', '%s Questions', $curtag->count, 'give-me-answer-lite'), $curtag->count); ?></div>
        </h2>

        <div class="gma-tag-description pb-4 border-bottom"><?php echo $curtag->description; ?></div>

    <?php } else if ( array_key_exists( 'gma-question_category', $wp_query->query_vars ) ) {?>
        <?php $curcat = get_term_by( 'slug', $wp_query->query_vars[ 'gma-question_category' ], 'gma-question_category' ); ?>

        <h2 class="d-flex justify-content-between flex-column flex-sm-row gma-cat-name text-secondary">
            <div><?php echo sprintf( __('Category [%s]', 'give-me-answer-lite'), $curcat->name ); ?></div>
            <div class="gma-category-count"><?php echo sprintf(_n('%s Question', '%s Questions', $curcat->count, 'give-me-answer-lite'), $curcat->count); ?></div>
        </h2>

        <div class="gma-tag-description pb-4 border-bottom"><?php echo $curcat->description; ?></div>
    <?php } else { ?>

        <!-- New question and searchbox -->
        <div class="gma-questions-archive <?php if ( gma_in_maintenance_mode() && ! gma_is_admin() ) echo 'disabled-content'; ?> mt-1">
        <?php if ( ! is_user_logged_in() && ! gma_current_user_can( 'post_question' ) ) { ?>
            <div class="alert alert-warning text-center rounded-0">
                <?php _e('Please sign up for posting a new question', 'give-me-answer-lite'); ?>
            </div>
        <?php } ?>

	    <?php do_action( 'gma_before_questions_archive' ) ?>

    <?php } ?>

    <div class="gma-questions-list-wrapper">
        <div class="gma-questions-list">
        <?php do_action( 'gma_before_questions_list' ) ?>
        <?php if ( gma_has_question() ) : ?>
            <?php while ( gma_has_question() ) : gma_the_question(); ?>
                <?php if ( get_post_status() == 'publish' || ( get_post_status() == 'private' && gma_current_user_can( 'edit_question', get_the_ID() ) ) ):?>
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
    </div>

	<?php
        do_action( 'gma_after_questions_archive' );
        do_action( 'gma_archive_page_bottom' );
    ?>

</div>

</div>


