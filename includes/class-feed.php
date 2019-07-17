<?php


class GMA_Feed {

    public function __construct() {
        add_action( 'init', [$this, 'register_feed'] );
    }

    function register_feed() {
        add_feed('give-me-answer-lite', [$this, 'display_feed']);
    }

    function display_feed() {
        gma_load_template('rss', 'give-me-answer-lite');
    }

}