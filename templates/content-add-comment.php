<?php
global $gma_general_settings;
$quora = get_post(absint($_GET['quoraid']));
?>

<div class="gma">
    
    <?php do_action( 'gma_before_add_comment_form', true ); ?>
    
    <div class="card">
        <div class="card-header text-bold"><?php _e('Add comment', 'give-me-answer-lite'); ?></div>
        <div class="card-body"><?php echo $quora->post_content; ?></div>
        <div class="card-footer px-2">
            <form method="post" class="gma-add-comment-form">
                <input type="hidden" name="comment_post_ID" value="<?php echo $quora->ID; ?>">
                <input type="hidden" name="_wpnonce" value="<?php echo wp_create_nonce('_gma_add_comment'); ?>">

                <?php if ( ! is_user_logged_in() ) { ?>
                    <div class="input-group mb-2">
                        <div class="input-group-prepend">
                            <span class="input-group-text" id="name"><?php _e('Name', 'give-me-answer-lite'); ?></span>
                        </div>
                        <input type="text" class="form-control" name="name" value="<?php echo isset($_POST['name']) ? esc_attr($_POST['name']) : ''; ?>">
                    </div>

                    <div class="input-group mb-2">
                        <div class="input-group-prepend">
                            <span class="input-group-text" id="email"><?php _e('Email', 'give-me-answer-lite'); ?></span>
                        </div>
                        <input type="text" class="form-control" name="email" value="<?php echo isset($_POST['email']) ? esc_attr($_POST['email']) : ''; ?>">
                    </div>
                <?php } ?>

                <div class="input-group mb-2">
                    <div class="input-group-prepend">
                        <span class="input-group-text" id="comment"><?php _e('Comment', 'give-me-answer-lite'); ?></span>
                    </div>
                    <input type="text" class="form-control" id="comment" name="comment" value="<?php echo isset($_POST['comment']) ? esc_attr($_POST['comment']) : ''; ?>">
                </div>

                <button class="btn btn-primary btn-responsive-block rounded-0" name="comment-submit" type="submit">
                    <?php _e('Submit', 'give-me-answer-lite'); ?>
                </button>
            </form>
        </div>
    </div>
</div>
