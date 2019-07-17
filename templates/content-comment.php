<?php
/**
 * The template for displaying content comment
 *
 * @package Give Me Answer
 * @since Give Me Answer 1.0
 */

global $comment, $gma_avatar, $gma_general_settings;
$author           = get_user_by( 'id', $comment->user_id );
$question         = gma_get_question_from_answer_id( get_the_ID() );
$question         = get_post( $question );
if ($author) {
    $is_post_owner    = ($author->ID == $question->post_author) ? true : false;
} else {
    $is_post_owner    = false;
}

$vote_count       = gma_comment_vote_count( get_comment_ID(), false );

?>

<div class="gma-comment" id="comment-<?php echo $comment->comment_ID; ?>" itemscope itemtype="http://schema.org/Comment">
    <div class="row mx-0 gma-comment-main">
        <div class="d-flex gma-comment-actions pr-0">

            <div class="col px-0 gma-comment-vote-count text-center">
                <span class="gma-cm-vote-count <?php if ( $vote_count ) echo 'gma-supernova'; ?>" title="<?php _e("number of 'useful comment' votes received", 'give-me-answer-lite'); ?>">
                    <?php echo $vote_count ? $vote_count : ''; ?>
                </span>
            </div>

            <div class="d-flex flex-column col pl-0 pr-1">
                <a class="m-0 <?php if ( $comment->user_id == get_current_user_id() || (! gma_anonymous_vote_status() && !is_user_logged_in()) ) echo 'disabled-content'; ?> <?php echo gma_is_user_voted_comment( get_comment_ID() ) ? 'comment-up-on comment-up-undo' : 'comment-up comment-up-off'; ?>" data-wpnonce="<?php echo wp_create_nonce( '_gma_cmvote' ); ?>" data-comment="<?php echo $comment->comment_ID; ?>">
                    <svg aria-hidden="true" class="svg-icon iconArrowUp" width="18" height="18" viewBox="0 0 18 18"><path d="M1 13h16L9 5z"></path></svg>
                </a>
            </div>
        </div>
        <div class="col pl-1 pr-0 pr-sm-1">
        <input
            type="hidden"
            class="gma-comment-id"
            data-wpnonce="<?php echo wp_create_nonce( '_gma_comment_nonce' ); ?>"
            value="<?php echo get_comment_ID(); ?>"
        >
        <div itemprop="text" style="display: inline-block;">
            <?php comment_text(); ?>
        </div>
        <div class="gma-comment-actions d-block d-md-inline mt-1 mt-md-0" itemscope itemtype="http://schema.org/Person">

            <?php if ( $author ) { ?>
                <a itemprop="url" class="text-info badge badge-default font-weight-bold gma-comment-author <?php if ( $is_post_owner ) echo 'gma-owner'; ?> m-0" href="<?php echo gma_get_author_link( $comment->user_id ); ?>">
                    <?php
                        if ( isset( $gma_avatar[ 'show-on-comment' ] ) && $gma_avatar[ 'show-on-comment' ] ) {
                            gma_user_avatar( [ 'user_id' => $comment->user_id, 'size' => $gma_avatar['size'][ 'comment' ] ] );
                        }
                    ?>
                    <span itemprop="name"><?php echo gma_user_displayname( $comment->user_id ); ?></span>
                </a>
            <?php } else { ?>
                <span itemprop="name" class="text-info gma-comment-author <?php if ( $is_post_owner ) echo 'gma-owner'; ?>">
                    <?php echo $comment->comment_author; ?>
                </span>
            <?php } ?>

            <span class="gma-comment-date text-black-50">
                <time style="display: none;" itemprop="dateCreated" datetime="<?php echo str_replace(' ', 'T', $comment->comment_date); ?>">
                    <?php echo gma_display_date($comment->comment_date); ?>
                </time>
            </span>

            <?php if ( is_user_logged_in() ) { ?>

                <!-- Edit comment -->
                <?php if ( gma_current_user_can( 'edit_comment', $comment->comment_ID ) || ($comment->user_id == get_current_user_id() && is_user_logged_in() ) ) { ?>
                    <a class="gma-edit-comment text-black-50 ml-1" href="<?php echo esc_url( add_query_arg( array( 'comment_edit' => $comment->comment_ID ) ) ); ?>"><?php _e('Edit', 'give-me-answer-lite'); ?></a>
                <?php } ?>

                <!-- Delete comment -->
                <?php if ( gma_current_user_can( 'delete_comment', $comment->comment_ID ) || ($comment->user_id == get_current_user_id() && is_user_logged_in()) ){ ?>
                    <a class="gma-delete-comment text-black-50 ml-1" href="<?php echo wp_nonce_url( add_query_arg( array( 'action' => 'gma-action-delete-comment', 'comment_id' => $comment->comment_ID ), admin_url( 'admin-ajax.php' ) ), '_gma_delete_comment' ); ?>">
                        <?php _e('Delete', 'give-me-answer-lite'); ?>
                    </a>
                <?php } ?>

            <?php } ?>

        </div>
    </div>
    </div>
    <div class="gma-comment-form gma-comment-form-ajax" style="display: none;"></div>
</div>

