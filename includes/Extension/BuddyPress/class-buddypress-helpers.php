<?php
if ( !defined( 'ABSPATH' ) ) exit;

function dp_gma_screen_questions() {
    add_action( 'bp_template_content', 'bp_gma_question_content' );
    bp_core_load_template( apply_filters( 'bp_gma_screen_question', 'members/single/plugins' ) );
}

function dp_gma_screen_answers() {
    add_action( 'bp_template_content', 'bp_gma_answer_content' );
    bp_core_load_template( apply_filters( 'bp_gma_screen_question', 'members/single/plugins' ) );
}

function dp_gma_screen_comments() {
    add_action( 'bp_template_content', 'bp_gma_comment_content' );
    bp_core_load_template( apply_filters( 'bp_gma_screen_question', 'members/single/plugins' ) );
}

//question
function bp_gma_question_content() {
    global $gma;
    add_filter('gma_prepare_archive_posts', 'dp_gma_question_filter_query',12);
    remove_action( 'gma_before_questions_archive', 'gma_search_form', 11 );
    remove_action( 'gma_before_questions_archive', 'gma_archive_question_filter_layout', 12 );
    $gma->template->load_template('bp-archive', 'question');
}

function dp_gma_question_filter_query($query){
    $bp_displayed_user_id = bp_displayed_user_id();
    $query['author'] = $bp_displayed_user_id;
    return $query;
}

//answer
function bp_gma_answer_content() {
    global $gma;
    remove_action( 'gma_before_questions_archive', 'gma_search_form', 11 );
    remove_action( 'gma_before_questions_archive', 'gma_archive_question_filter_layout', 12 );
    add_filter('gma_prepare_archive_posts', 'bp_gma_answer_filter_query',13);
    $gma->template->load_template('bp-archive', 'question');
}

function bp_gma_answer_filter_query($query){
    $bp_displayed_user_id = bp_displayed_user_id();
    $post__in = array();

    unset($query['meta_query']);
    $array = $query;
    $array['post_type'] = 'gma-answer';
    $array['author'] = $bp_displayed_user_id;

    // add_filter( 'posts_groupby', 'bp_dwqa_answers_groupby' );
    // use this function to fill per page
    while(count($post__in) < $query['posts_per_page']){
        $array['post__not_in'] = $post__in;
        $results = new WP_Query( $array );
        if($results->post_count > 0){
            foreach($results->posts as $result){
                $post__in[] = $result->post_parent;
            }
        }else{
            break;
        }
    }

    if(empty($post__in)){
        $post__in = array(0);
    }


    $query['post__in'] = $post__in;
    $query['orderby'] = 'post__in';

    return $query;
}
