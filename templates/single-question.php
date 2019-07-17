<?php
/**
 * The template for displaying all single questions
 *
 * @package Give Me Answer
 * @since Give Me Answer 1.0
 */
?>
<div class="gma">
    <?php gma_error_if_in_maintenance(); ?>
    <div itemprop="mainEntity" itemscope itemtype="http://schema.org/Question" class="gma-single-question <?php if ( gma_in_maintenance_mode() && ! gma_is_admin() ) echo 'disabled-content'; ?>">

    <?php
        if ( have_posts() ) {
            do_action( 'gma_before_single_question' );
            while ( have_posts() ) {
                the_post();

                if ( gma_is_add_comment() ) {
                    gma_load_template('content', 'add-comment');
                } else if ( gma_is_close() ) {
                    gma_load_template('content', 'close');
                } else if ( gma_is_share() ) {
                    gma_load_template('content', 'share');
                } else if ( gma_is_edit() ) {
                    gma_load_template( 'content', 'edit' );
                } else {
                    gma_load_template( 'content', 'single-question' );
                }

            }
            do_action( 'gma_after_single_question' );
        }
    ?>

    </div>
</div>
