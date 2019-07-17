<?php
/**
 * The template for editing question and answer
 *
 * @package Give Me Answer
 * @since Give Me Answer 1.0
 */

global $gma_general_settings;
$comment_id = isset( $_GET['comment_edit'] ) && is_numeric( $_GET['comment_edit'] ) ? intval( $_GET['comment_edit'] ) : false;
$edit_id = isset( $_GET['edit'] ) && is_numeric( $_GET['edit'] ) ? intval( $_GET['edit'] ) : ( $comment_id ? $comment_id : false );
if ( !$edit_id ) return;
$type = $comment_id ? 'comment' : ( 'gma-question' == get_post_type( $edit_id ) ? 'question' : 'answer' );
if ( $type == 'answer' ) {
    $question_id   = gma_get_question_from_answer_id( $edit_id );
    $question      = get_post( $question_id );
    $answers       = gma_get_answers( $question->ID, $edit_id );
}

do_action( 'gma_before_edit_form' );
?>

<form method="post" class="gma-content-edit-form" enctype="multipart/form-data">
	<?php if ( 'gma-question' == get_post_type( $edit_id ) ) : ?>
	<?php $title = gma_question_get_edit_title( $edit_id ) ?>
	<p>
		<label for="question_title"><?php _e( 'Title', 'give-me-answer-lite' ) ?></label>
		<input type="text" class="form-control mx-0" name="question_title" value="<?php echo $title ?>" tabindex="1">
	</p>
	<?php endif; ?>

	<?php $content = call_user_func( 'gma_' . $type . '_get_edit_content', $edit_id ); ?>
    <?php if ( ( $type == 'answer' && $gma_general_settings['editor']['answer'] == 'tinymce' ) || ('tinymce' == $gma_general_settings['editor']['question'] && $type == 'question') ) { ?>
        <p><?php gma_init_tinymce_editor( array( 'content' => $content, 'textarea_name' => $type . '_content', 'wpautop' => true ) ) ?></p>
    <?php } else { ?>
        <textarea
            id="gma-custom-content-editor"
            data-is-answer="<?php if ( 'answer' == $type ) echo 'yes';else echo 'no'; ?>"
            name="<?php echo $type . '_content'; ?>"
            class="form-control" rows="6"><?php echo $content; ?></textarea>
    <?php } ?>

    <!-- answer to comment -->
	<?php if ( $type == 'answer' ) { ?>
        <div class="my-3 text-left">


            <div class="custom-control custom-checkbox mb-2">
                <input type="checkbox" class="custom-control-input" id="customCheck1" name="commenton" value="on">
                <label class="custom-control-label" for="customCheck1"><?php _e('Convert this answer into a comment', 'give-me-answer-lite'); ?></label>
            </div>

            <select class="form-control" name="commenton-post" style="display: none;">
                <option value="<?php echo $question->ID ?>">
                    <?php _e('Question : ', 'give-me-answer-lite'); ?>
                    <?php echo esc_html( wp_trim_words( $question->post_content, 15, ' ... ' )); ?>
                </option>
                <?php foreach ( $answers as $item ) { ?>
                    <option value="<?php echo $item->ID; ?>">
                        <?php _e('Answer : ', 'give-me-answer-lite'); ?>
                        <?php echo wp_trim_words( $item->post_content, 15, ' ... ' ); ?>
                    </option>
                <?php } ?>
            </select>
        </div>
	<?php } ?>

	<?php if ( 'gma-question' == get_post_type( $edit_id ) ) : ?>
        <p class="mt-2">
            <label for="question-category"><?php _e( 'Category', 'give-me-answer-lite' ) ?></label>
            <?php $category = wp_get_post_terms( $edit_id, 'gma-question_category' ); ?>
            <?php
                wp_dropdown_categories( array(
                    'name'          => 'question-category',
                    'id'            => 'question-category',
                    'class'         => 'form-control',
                    'taxonomy'      => 'gma-question_category',
                    'show_option_none' => __( 'Select question category', 'give-me-answer-lite' ),
                    'hide_empty'    => 0,
                    'quicktags'     => array( 'buttons' => 'strong,em,link,block,del,ins,img,ul,ol,li,code,spell,close' ),
                    'selected'      => isset( $category[0]->term_id ) ? $category[0]->term_id : false,
                ) );
            ?>
        </p>

        <p>
            <label for="question-tag"><?php _e( 'Tags', 'give-me-answer-lite' ) ?></label>
            <input type="text" class="form-control" name="question-tag" placeholder="<?php _e('Please select tags', 'give-me-answer-lite'); ?>" value="<?php gma_get_tag_list( get_the_ID(), true ); ?>" >
        </p>
	<?php endif; ?>
	<?php
    do_action('gma_after_show_content_edit', $edit_id);
    do_action( 'gma_before_edit_submit_button' )
    ?>
	<input type="hidden" name="<?php echo $type; ?>_id" value="<?php echo $edit_id ?>">
	<?php wp_nonce_field( '_gma_edit_' . $type ) ?>
	<input
        type="submit"
        class="btn btn-primary my-2 btn-responsive-block"
        name="gma-edit-<?php echo $type ?>-submit"
        value="<?php _e( 'Save changes', 'give-me-answer-lite' ) ?>"
    >
</form>

<?php do_action( 'gma_after_edit_form' ); ?>

