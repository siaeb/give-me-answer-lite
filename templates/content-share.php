<?php
/**
 * The template for sharing question and answer
 *
 * @package Give Me Answer
 * @since Give Me Answer 1.0
 */

global $gma_general_settings;

$post_id = isset( $_GET['share'] ) && is_numeric($_GET['share']) ? absint($_GET['share']) : false;
if ( ! $post_id ) return;
$quora      = get_post($post_id);
$question   = $quora->post_parent ? get_post( $quora->post_parent ) : $quora;
$qurl       = get_permalink( $question->ID );
$qtitle     = $question->post_title;

// If post is an answer
if ( $quora->post_parent ) {
    $qurl   .= '#answer-' . $quora->ID;
    $qtitle .= __('Answer to ', 'give-me-answer-lite') . $qtitle;
}
$socials = gma_social_urls( urlencode( $qurl ), $qtitle );

?>

<div class="gma">
    <div class="card">
        <div class="card-header"><?php echo __('Share : ', 'give-me-answer-lite') . esc_html($quora->post_title); ?></div>
        <div class="card-body"><?php echo $quora->post_content; ?></div>
        <div class="card-footer">
            <div class="d-flex justify-content-around my-3">
                <a href="<?php echo esc_url($socials['facebook']); ?>" rel="nofollow" target="_blank" class="gma-share-btn gma-facebook">
                    <img src="<?php echo GMA_URI . 'assets-admin/img/facebook.png'; ?>" width="40" height="40">
                </a>
                <a href="<?php echo esc_url($socials['twitter']); ?>" rel="nofollow" target="_blank" class="gma-share-btn gma-twitter">
                    <img src="<?php echo GMA_URI . 'assets-admin/img/twitter.png'; ?>" width="40" height="40">
                </a>
                <a href="<?php echo esc_url($socials['whatsapp']); ?>" rel="nofollow" target="_blank" class="gma-share-btn gma-whatsapp">
                    <img src="<?php echo GMA_URI . 'assets-admin/img/whatsapp.png'; ?>" width="40" height="40">
                </a>
                <a href="<?php echo esc_url($socials['linkedin']); ?>" rel="nofollow" target="_blank" class="gma-share-btn gma-linkedin">
                    <img src="<?php echo GMA_URI . 'assets-admin/img/linkedin.png'; ?>" width="40" height="40">
                </a>
                <a href="<?php echo esc_url($socials['telegram']); ?>" rel="nofollow" target="_blank" class="gma-share-btn gma-telegram rounded">
                    <img src="<?php echo GMA_URI . 'assets-admin/img/telegram.png'; ?>" width="40" height="40">
                </a>
            </div>
        </div>
    </div>


</div>
