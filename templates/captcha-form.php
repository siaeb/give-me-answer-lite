<?php
/**
 * The template for displaying captcha form
 *
 * @package Give Me Answer
 * @since Give Me Answer 1.0
 */
?>

<?php if ( ( 'gma-question' == get_post_type() && gma_is_captcha_enable_in_single_question() ) || ( gma_is_ask_form() && gma_is_captcha_enable_in_submit_question() ) ) : ?>
<p class="gma-captcha">
	<?php 
	$number_1 = mt_rand( 0, 20 );
	$number_2 = mt_rand( 0, 20 );
	?>
	<span class="gma-number-one"><?php echo esc_attr( $number_1 ) ?></span>
	<span class="gma-plus">&#43;</span>
	<span class="gma-number-two"><?php echo esc_attr( $number_2 ) ?></span>
	<span class="gma-plus">&#61;</span>
	<input type="text" class="form-control mx-0 mb-1" name="gma-captcha-result" id="gma-captcha-result" value="" placeholder="<?php _e( 'Enter the result', 'give-me-answer-lite' ) ?>">
	<input type="hidden" name="gma-captcha-number-1" id="gma-captcha-number-1" value="<?php echo esc_attr( $number_1 ) ?>">
	<input type="hidden" name="gma-captcha-number-2" id="gma-captcha-number-2" value="<?php echo esc_attr( $number_2 ) ?>">
</p>
<?php endif; ?>