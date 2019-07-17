<?php
/**
 * The template for displaying answers
 *
 * @package Give Me Answer
 * @since Give Me Answer 1.0
 */

global $gma_general_settings;
$sort   = isset( $_GET['sort'] ) ? esc_html( $_GET['sort'] ) : '';
$filter = isset( $_GET['filter'] ) ? esc_html( $_GET['filter'] ) : 'all';
if ( isset(  $gma_general_settings['pages']['archive-question'] ) ) {
    $base_url = get_permalink( $gma_general_settings['pages']['archive-question'] );
}
?>
<div class="d-flex justify-content-between gma-question-filter-wrapper bg-light my-3">
    <?php do_action('gma_before_filters'); ?>
    <div class="d-flex flex-wrap align-items-center gma-question-filter btn-group mb-0">
        <?php if ( ! isset( $_GET['user'] ) ) : ?>
            <a href="<?php echo esc_url( add_query_arg( array( 'filter' => 'newest' ), $base_url ) ); ?>" class="p-2 <?php echo ('newest' == $filter || 'all' == $filter ) && $sort == '' ? 'btn-info text-white' : 'btn-outline-secondary' ?>"><?php _e( 'Newest', 'give-me-answer-lite' ); ?></a>
            <a href="<?php echo esc_url( add_query_arg( array( 'filter' => 'unanswered' ), $base_url ) ); ?>" class="p-2 <?php echo 'unanswered' == $filter ? 'btn-info text-white' : 'btn-outline-secondary' ?>"><?php _e( 'Unanswered', 'give-me-answer-lite' ); ?></a>
            <a href="<?php echo esc_url( add_query_arg( array( 'filter' => 'answered' ), $base_url ) ); ?>" class="p-2 <?php echo 'answered' == $filter ? 'btn-info text-white' : 'btn-outline-secondary' ?>"><?php _e( 'Answered', 'give-me-answer-lite' ); ?></a>
            <a href="<?php echo esc_url( add_query_arg( array( 'sort'   => 'hot' ), $base_url )); ?>" class="p-2 <?php echo 'hot' == $sort ? 'btn-info text-white' : 'btn-outline-secondary' ?>"><?php _e( 'Hot', 'give-me-answer-lite' ); ?></a>
            <?php if ( is_user_logged_in() ) : ?>
                <a href="<?php echo esc_url( add_query_arg( array( 'filter' => 'my-questions' ), $base_url ) ); ?>" class="p-2 <?php echo 'my-questions' == $filter ? 'btn-info text-white' : 'btn-outline-secondary' ?>"><?php _e( 'My Questions', 'give-me-answer-lite' ); ?></a>
                <a href="<?php echo esc_url( add_query_arg( array( 'filter' => 'my-subscribes' ), $base_url ) ); ?>" class="p-2 <?php echo 'my-subscribes' == $filter ? 'btn-info text-white' : 'btn-outline-secondary' ?>"><?php _e( 'My Subscribes', 'give-me-answer-lite' ); ?></a>
            <?php endif; ?>
        <?php else : ?>
            <a href="<?php echo esc_url( add_query_arg( array( 'filter' => 'all' ), $base_url ) ); ?>" class="p-2 <?php echo 'all' == $filter ? 'btn-info text-white' : 'btn-outline-secondary' ?>"><?php _e( 'Questions', 'give-me-answer-lite' ); ?></a>
            <a href="<?php echo esc_url( add_query_arg( array( 'filter' => 'subscribes', $base_url ) ) ); ?>" class="p-2 <?php echo 'subscribes' == $filter ? 'btn-info text-white' : 'btn-outline-secondary' ?>"><?php _e( 'Subscribes', 'give-me-answer-lite' ); ?></a>
        <?php endif; ?>
    </div>
    <div class="d-flex">
        <?php do_action('gma_before_rss'); ?>
        <span class="d-flex align-items-center px-1 gma-rss-wrapper"><a href="<?php echo get_feed_link('give-me-answer-lite'); ?>" class="text-warning"><i class="fa fa-rss"></i></a></span>
        <?php do_action('gma_after_rss'); ?>
    </div>
</div>