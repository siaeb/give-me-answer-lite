<?php

defined( 'ABSPATH' ) || exit;

/**
 * This class is responsible for change structure of page
 * for better SEO. it convert single question page structure to QA Schema Markup.
 *
 *
 * @since 1.0
 * @package Give Me Answer
 */
class GMA_Schema {

    public function __construct() {
        add_filter( 'language_attributes', [$this, 'modify_html_root']);
    }

    function modify_html_root( $attr ) {
        global $wp_query;
        if ( isset( $wp_query->query_vars[ 'post_type' ] ) && $wp_query->query_vars[ 'post_type' ] == 'gma-question' ) {
            return sprintf( '%s %s %s', $attr, 'itemscope', 'itemtype="http://schema.org/QAPage"' );
        }
        return $attr;
    }

}