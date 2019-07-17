<?php
/**
 * Template Name: Custom RSS Template - YourCustomFeedName
 */
global $gma_general_settings;
$postCount = get_option('posts_per_rss'); // The number of posts to show in the feed
$postType = 'gma-question'; // post type to display in the feed
query_posts( array( 'post_type' => $postType, 'showposts' => $postCount ) );
$charset = get_option( 'blog_charset' );
header( 'Content-Type: ' . feed_content_type( 'rss-http' ) . '; charset=' . $charset, true );

$feedwriter = new \FeedWriter\RSS2();
$feedwriter->setTitle(sprintf('%s - %s', get_bloginfo('title'), __('Recent questions and answers', 'give-me-answer-lite')) );
$feedwriter->setDescription(__('Powered by give me answer.', 'give-me-answer-lite'));
$feedwriter->setDate(date('Y-m-d H:i:s'));
$feedwriter->setLink(get_permalink($gma_general_settings['pages']['archive-question']));

while (have_posts()) {
    the_post();
    $latest_answer = gma_get_latest_answer(get_the_ID());
    $newitem = $feedwriter->createNewItem();
    if ($latest_answer) {
        $newitem->setTitle(_x('Answered : ', 'rss', 'give-me-answer-lite') . get_the_title());
        $newitem->setDescription($latest_answer->post_content);
    } else {
        $newitem->setTitle(get_the_title());
        $newitem->setDescription(get_the_content());
    }
    $newitem->setAuthor(get_the_author());
    $newitem->setLink(get_permalink());
    $newitem->setId(get_permalink(get_the_ID()), true);
    $newitem->setDate(get_post_field('post_date', get_the_ID()));
    $feedwriter->addItem($newitem);
}

echo $feedwriter->generateFeed();
wp_reset_query();
?>
