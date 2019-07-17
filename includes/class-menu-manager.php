<?php

defined( 'ABSPATH' ) || exit;

class GMA_MenuManager {

    public function __construct() {
        add_action( 'admin_menu', [$this, 'add_main_menu'] );

        add_filter( 'menu_order', [$this, 'change_plugin_menus_order'], 11 );

        add_filter( 'custom_menu_order', [$this, 'change_plugin_menus_order'], 11 );

    }

    function add_main_menu() {

        $noti_count  = gma_lite()->utility->count_notifications();

        add_menu_page(
            __('Give Me Answer Lite', 'give-me-answer-lite'),
            __('Give Me Answer Lite', 'give-me-answer-lite') . ($noti_count['total'] ? sprintf('<span class="awaiting-mod count-1"><span class="pending-count" aria-hidden="true">%s</span></span>', $noti_count['total']) : ''),
            'manage_options',
            'give-me-answer-lite',
            array( GMA_Dashboard::class, 'display'),
            GMA_URI . '/assets-public/img/quora.png'
        );

        add_submenu_page(
            'give-me-answer-lite',
            __('Questions','give-me-answer-lite'),
            __('Questions','give-me-answer-lite') . ($noti_count['questions'] ? sprintf('<span class="awaiting-mod count-1"><span class="pending-count" aria-hidden="true">%s</span></span>', $noti_count['questions']) : ''),
            'manage_options',
            'edit.php?post_type=gma-question'
        );

        add_submenu_page(
            'give-me-answer-lite',
            __('Answers','give-me-answer-lite'),
            __('Answers','give-me-answer-lite'). ($noti_count['answers'] ? sprintf('<span class="awaiting-mod count-1"><span class="pending-count" aria-hidden="true">%s</span></span>', $noti_count['answers']) : ''),
            'manage_options',
            'edit.php?post_type=gma-answer'
        );

        add_submenu_page(
            'give-me-answer-lite',
            __( 'Comments','give-me-answer-lite' ),
            __( 'Comments','give-me-answer-lite' ),
            'manage_options',
            'gma-comments',
            array( GMA_Comments::class, 'display' )
        );

        add_submenu_page(
            'give-me-answer-lite',
            __('Categories', 'give-me-answer-lite'),
            __('Categories', 'give-me-answer-lite'),
            'edit_posts',
            'edit-tags.php?taxonomy=gma-question_category&post_type=gma-question',
            false
        );

        add_submenu_page(
            'give-me-answer-lite',
            __('Tags', 'give-me-answer-lite'),
            __('Tags', 'give-me-answer-lite'),
            'edit_posts',
            'edit-tags.php?taxonomy=gma-question_tag&post_type=gma-question',
            false
        );
    }

    function findMenuPosition($menus, $menu_slug ) {

        if( is_array( $menus[ 'give-me-answer-lite' ] ) ) {
            foreach ( $menus[ 'give-me-answer-lite' ] as $key => $details ) {
                if ( $menu_slug == $details[2] ) {
                    return $key;
                }
            }
        }

        return -1;
    }

    /**
     * Change plugin menu order
     *
     * @since 1.0
     *
     * @param $menu_order
     *
     * @return mixed
     */
    function change_plugin_menus_order( $menu_order ) {
        global $submenu;

        if ( current_user_can( 'manage_options' ) ) {
            $plugin_menus        = &$submenu[ 'give-me-answer-lite' ];

            $settings_cur_index  = $this->findMenuPosition( $submenu,'gma-settings' );
            $addons_cur_index    = $this->findMenuPosition( $submenu,'gma-addons' );

            array_push( $plugin_menus, $plugin_menus[ $settings_cur_index ] );
            array_push( $plugin_menus, $plugin_menus[ $addons_cur_index ] );


            unset(
                $plugin_menus[$settings_cur_index],
                $plugin_menus[$addons_cur_index]
            );


            # Reorder the menu based on the keys in ascending order
            ksort( $plugin_menus );
        }

        return $menu_order;
    }

}