<?php
/**
 * The template for sharing question and answer
 *
 * @package Give Me Answer
 * @since Give Me Answer 1.0
 */
global $gma_general_settings;
$question = get_post(absint($_GET['queid']));
?>

<div class="gma">

    <div class="card">
        <div class="card-header"><?php echo $question->post_title; ?></div>
        <div class="card-body">
            <?php echo $question->post_content; ?>
        </div>
        <div class="card-footer">
            <form method="post">
                <input type="hidden" name="action" value="close-question">
                <input type="hidden" name="question-id" value="<?php echo $question->ID; ?>">
                <div class="input-group my-1">
                    <input type="text" name="close-reason" class="form-control" placeholder="<?php _e('Close Reason ...', 'give-me-answer-lite'); ?>">
                    <div class="input-group-prepend">
                        <button class="btn btn-outline-primary" type="submit">
                            <?php _e('Submit', 'give-me-answer-lite'); ?>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>


</div>
