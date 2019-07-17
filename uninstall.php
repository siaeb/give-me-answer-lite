<?php

    // Exit if accessed directly.
    if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) exit();

    include_once 'give-me-answer-lite.php';

    global $gma_general_settings;

    // Remove all the Custom Post Types
    $gma_post_types = array( 'gma-answer', 'gma-question' );
    foreach ( $gma_post_types as $post_type ) {
        $items = get_posts( array( 'post_type' => $post_type, 'post_status' => 'any', 'numberposts' => -1, 'fields' => 'ids' ) );

        if ( $items ) {
            foreach ( $items as $item ) {
                wp_delete_post( $item, true);
            }
        }
    }


    // Remove all the Plugin Options
    $option_names = [
        'gma_options', 'gma_permission',
        'gma_enable_email_delay', 'gma-question_category_children',
        'widget_gma-closed-question', 'widget_gma-latest-question',
        'widget_gma-popular-question', 'widget_gma_widget_question_tags',
        'widget_gma_widget_relatedquestions',
        'gma-question_category_children',
        'gma_has_roles'
    ];
    foreach ( $option_names as $optname ) {
        delete_option( $optname );
    }

    /** Delete the Plugin Pages */
    $gma_created_pages = array( 'submit-question', 'archive-question', 'tags', 'users', 'user-profile' );
    foreach ( $gma_created_pages as $p ) {
        $page = $gma_general_settings[ 'pages' ][ $p ];
        if ( $page ) {
            wp_delete_post( $page, true );
        }
    }


    // Remove all the Database Tables
    gma_lite()->profile_visit->drop_table();


    // Cleanup Cron Events
    wp_clear_scheduled_hook( 'auto_closure' );

    // Remove any transients we've left behind
    $wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE '\_transient\_gma-%'" );
    $wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE '\_site\_transient\_gma-%'" );
    $wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE '\_transient\_timeout\_gma-%'" );
    $wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE '\_site\_transient\_timeout\_gma-%'" );