<?php
global $gma_general_settings;
$ansquestionid = gma_get_question_from_answer_id();
if (! isset($gma_general_settings['general']['disable-share-socials']) || ! $gma_general_settings['general']['disable-share-socials']) {
?>
    <a href="<?php echo get_permalink($ansquestionid) . '?share=' . get_the_ID(); ?>" class="gma-question-operation gma-share mr-2" data-post="<?php echo get_the_ID(); ?>">
        <?php _e('Share', 'give-me-answer-lite'); ?>
    </a>
<?php } ?>