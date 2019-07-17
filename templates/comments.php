<?php
/**
 * The template for displaying comments form
 *
 * @package Give Me Answer
 * @since Give Me Answer 1.0
 */

global $gma_avatar, $gma_options, $gma_general_settings;
$hidden_comments_count = gma_get_hidden_comments();
$is_reporting_disabled = isset( $gma_general_settings['general']['disable-report-system'] ) && $gma_general_settings['general']['disable-report-system'];
?>

<?php if ( comments_open() ) : ?>
<div class="gma-comments">
	<?php do_action( 'gma_before_comments' ) ?>
	<div class="gma-comments-list" data-remaining-comments-count="<?php echo $hidden_comments_count; ?>">
		<?php do_action( 'gma_before_comments_list' ); ?>
		<?php if ( have_comments() ) : ?>
		<?php wp_list_comments( array( 'callback' => 'gma_question_comment_callback' ) ); ?>
		<?php endif; ?>
		<?php do_action( 'dqwa_after_comments_list' ); ?>
        <script id="gma-comment-template" type="text/template">

            <div class="gma-comment" id="comment-{comment-id}" itemscope itemtype="http://schema.org/Comment">
                <div class="row gma-comment-main mx-0">
                    <div class="d-flex gma-comment-actions pr-0">
                        <div class="col px-0 gma-comment-vote-count text-center">
                            <span class="gma-cm-vote-count" title="<?php _e("number of 'useful comment' votes received", 'give-me-answer-lite'); ?>"></span>
                        </div>
                        <div class="d-flex flex-column col pl-0 pr-1">

                            <a class="m-0 comment-up comment-up-off disabled-content" data-comment="{comment-id}">
                                <svg aria-hidden="true" class="svg-icon iconArrowUp" width="18" height="18" viewBox="0 0 18 18"><path d="M1 13h16L9 5z"></path></svg>
                            </a>

                        </div>
                    </div>
                    <div class="col pl-1 pr-0 pr-sm-1">
                        <input type="hidden" class="gma-comment-id" data-wpnonce="" value="">
                        <p class="gma-comment-content" itemprop="text"></p>
                        <div class="gma-comment-actions" itemscope itemtype="http://schema.org/Person">
                            <a class="text-info badge badge-default font-weight-bold gma-comment-author m-0" href="" itemprop="url">
								<?php if ( $gma_avatar[ 'show-on-comment' ] ) { ?>
                                    <span class="avatar avatar-vsm gma-comment-user-avatar <?php echo gma_get_avatar_classes(); ?> mr-1" style="background-image: url()"></span>
								<?php } ?>
                                <span class="author-name" itemprop="name"></span>
                            </a>
                            <span class="gma-comment-date text-black-50">
                                <time style="display: none;" itemprop="dateCreated" datetime=""></time>
                            </span>
                            <a class="text-black-50 ml-1 gma-edit-comment" href=""><?php _e('Edit', 'give-me-answer-lite'); ?></a>
                            <a class="gma-delete-comment text-black-50 ml-1 gma-delete-comment" href=""><?php _e('Delete', 'give-me-answer-lite'); ?></a>
                        </div>
                    </div>
                </div>
                <div class="gma-comment-form gma-comment-form-ajax" style="display: none;"></div>
            </div>
        </script>
	</div>
	<?php
        if ( ! gma_is_closed( get_the_ID() ) && gma_current_user_can( 'post_comment' ) ) {
            if (apply_filters('gma_show_comment', true ) ) {
                $args = array(
                    'id_form'              => 'comment_form_' . get_the_ID(),
                    'label_submit'         => __('Send', 'give-me-answer-lite'),
                    'logged_in_as'         => '<p class="comment-form-comment"><textarea id="comment" name="comment" class="form-control" placeholder="' . __('Use comments to ask for more information or suggest improvements. Avoid answering questions in comments.', 'give-me-answer-lite') .'" rows="2" aria-required="true"></textarea></p>',
                    'comment_notes_before' => '<p class="comment-form-comment"><textarea id="comment" name="comment" class="form-control" placeholder="' . __('Use comments to ask for more information or suggest improvements. Avoid answering questions in comments.', 'give-me-answer-lite') .'" rows="2" aria-required="true"></textarea></p>',
                );
                gma_comment_form( $args );
            }
         }
    ?>

    <div class="gma-leave-comment-parent text-black-50 py-2 text-left">

        <?php if ( ! gma_is_closed( get_the_ID() ) && gma_current_user_can( 'post_comment' ) ) { ?>
                
            <?php if ( apply_filters('gma_show_comment', true) ) { ?>
                <a href="<?php echo add_query_arg(['action' => 'addcomment', 'quoraid' => get_the_ID()]); ?>" class="gma-leave-comment gma-cursor-pointer text-small pr-1 <?php if ( $hidden_comments_count ) echo 'border-right'; ?>" data-post="<?php echo get_the_ID(); ?>">
                    <?php _e('add a comment', 'give-me-answer-lite'); ?>
                </a>
            <?php } ?>

        <?php } ?>


	    <?php if ( $hidden_comments = gma_get_hidden_comments() ) { ?>
            <a href="#" class="gma-show-hidden-comments text-small  pl-2" title="<?php _e('Expand to show all comments on this post', 'give-me-answer-lite'); ?>" data-post="<?php echo get_the_ID(); ?>">
			    <?php printf( __('Show %s more comments', 'give-me-answer-lite'), $hidden_comments ); ?>
            </a>
	    <?php } ?>

    </div>

	<?php do_action( 'gma_after_comments' ); ?>
</div>
<?php endif; ?>
