<?php
/**
 * The template for displaying single answers
 *
 * @package Give Me Answer
 * @since Give Me Answer 1.0
 */
global $gma_general_settings;

?>

<div class="gma">

    <?php
        gma_error_if_in_maintenance();

        do_action( 'gma_submit_question_page_top' );
    ?>

    <div class="gma-form-submit-question <?php if ( gma_in_maintenance_mode() && ! gma_is_admin() ) echo 'disabled-content'; ?>">
        <?php if ( gma_current_user_can( 'post_question' ) ) : ?>
        <?php do_action( 'gma_before_question_submit_form' ); ?>
        <form method="post" class="gma-content-edit-form" enctype="multipart/form-data">

            <?php $title = isset( $_POST['question-title'] ) ? wp_strip_all_tags( $_POST['question-title'] ) : ''; ?>

            <input
                type="text"
                class="form-control mb-2 ml-0"
                maxlength="<?php echo esc_attr( $gma_general_settings[ 'question' ][ 'max-length' ] ); ?>"
                placeholder="<?php _e('Please enter question title', 'give-me-answer-lite'); ?>"
                data-nonce="<?php echo wp_create_nonce( '_gma_filter_nonce' ) ?>"
                name="question-title"
                value="<?php echo esc_attr( $title ); ?>"
                tabindex="1"
            >

            <?php $content = isset( $_POST['question-content'] ) ? $_POST['question-content'] : ''; ?>
            <?php if ( 'tinymce' == $gma_general_settings['editor'][ 'question' ] ) { ?>
                <p><?php gma_init_tinymce_editor( array( 'content' => $content, 'textarea_name' => 'question-content', 'id' => 'question-content' ) ) ?></p>
            <?php } else { ?>
                <textarea id="question-content" name="question-content" class="form-control rounded-0" placeholder="<?php _e('Please enter question content', 'give-me-answer-lite'); ?>" rows="5"><?php echo $content; ?></textarea>
            <?php } ?>

            <?php if ( $gma_general_settings[ 'private-question' ] ) { ?>

                <div class="input-group my-2 ml-0">
                    <span class="input-group-append" id="basic-addon2">
                        <span class="input-group-text"><?php _e( 'Status', 'give-me-answer-lite' ) ?></span>
                    </span>
                    <select name="question-status" id="question-status" class="form-control ml-0 valid" aria-invalid="false">
                        <optgroup label="<?php _e( 'Who can see this?', 'give-me-answer-lite' ) ?>">
                            <option value="publish"><?php _e( 'Public', 'give-me-answer-lite' ) ?></option>
                            <option value="private"><?php _e( 'Only Me &amp; Admin', 'give-me-answer-lite' ) ?></option>
                        </optgroup>
                    </select>
                </div>

            <?php } ?>
                

            <?php if ( $gma_general_settings['submit-question-display-category'] ) { ?>
                <div class="input-group my-2 ml-0">
                    <span class="input-group-append" id="basic-addon2">
                        <span class="input-group-text"><?php _e( 'Category', 'give-me-answer-lite' ) ?></span>
                    </span>
                    <?php
                    wp_dropdown_categories( array(
                        'name'          => 'question-category',
                        'id'            => 'question-category',
                        'class'         => 'form-control ml-0',
                        'taxonomy'      => 'gma-question_category',
                        'show_option_none' => __( 'Select question category', 'give-me-answer-lite' ),
                        'hide_empty'    => 0,
                        'quicktags'     => array( 'buttons' => 'strong,em,link,block,del,ins,img,ul,ol,li,code,spell,close' ),
                        'selected'      => isset( $_POST['question-category'] ) ? sanitize_text_field( $_POST['question-category'] ) : false,
                    ) );
                    ?>
                </div>
            <?php } ?>


            <?php if ( $gma_general_settings[ 'tags-per-question' ] && $gma_general_settings['submit-question-display-tags'] ) { ?>
                <div class="input-group my-2 ml-0">
                    <span class="input-group-append" id="basic-addon2">
                        <span class="input-group-text"><?php _e( 'Tag', 'give-me-answer-lite' ) ?></span>
                    </span>
                    <?php $tags = isset( $_POST['question-tag'] ) ? $_POST['question-tag']: ''; ?>
                    <input
                        type="text"
                        class="form-control ml-0"
                        name="question-tag"
                        data-rule-required="true"
                        data-msg-required="<?php _e('Required', 'give-me-answer-lite'); ?>"
                        placeholder="<?php _e('Please select tags', 'give-me-answer-lite'); ?>"
                        value="<?php echo $tags ?>"
                    >
                </div>
            <?php } ?>

            <?php if ( gma_current_user_can( 'post_question' ) && !is_user_logged_in() ) : ?>

                <?php
                $email = isset( $_POST['_gma_anonymous_email'] ) ? sanitize_email( $_POST['_gma_anonymous_email'] ) : '';
                $name = isset( $_POST['_gma_anonymous_name'] ) ? sanitize_text_field( $_POST['_gma_anonymous_name'] ) : '';
                ?>
                <div class="input-group my-2 ml-0">
                    <span class="input-group-append" id="basic-addon2">
                        <span class="input-group-text"><?php _e( 'Email', 'give-me-answer-lite' ) ?></span>
                    </span>
                    <input type="email" class="form-control mx-0" name="_gma_anonymous_email" placeholder="<?php _e('Please write your email', 'give-me-answer-lite'); ?>" value="<?php echo esc_attr( $email ); ?>" >
                </div>

                <div class="input-group my-2 ml-0">
                    <span class="input-group-append" id="basic-addon2">
                        <span class="input-group-text"><?php _e( 'FirstName', 'give-me-answer-lite' ) ?></span>
                    </span>
                    <input
                        type="text"
                        class="form-control mx-0"
                        name="_gma_anonymous_name"
                        placeholder="<?php echo __('Please enter your firstname', 'give-me-answer-lite') ?>"
                        value="<?php echo esc_attr($name); ?>"
                    >
                </div>

            <?php endif; ?>

            <?php if ( false === gma_stop_generating_nonce() ) wp_nonce_field( '_gma_submit_question' ); ?>

            <?php
                gma_load_template( 'captcha', 'form' );
                do_action('gma_before_question_submit_button');
            ?>
            <input
                type="submit"
                class="btn gma-btn-primary btn-responsive-block d-block my-2"
                name="gma-question-submit"
                value="<?php _e( 'Send', 'give-me-answer-lite' ) ?>"
            >
        </form>
        <?php do_action( 'gma_after_question_submit_form' ); ?>
    <?php else : ?>
        <div class="alert"><?php _e( 'You do not have permission to submit a question','give-me-answer-lite' ) ?></div>
    <?php endif; ?>
    </div>

    <?php do_action( 'gma_submit_question_page_bottom' ); ?>
</div>
