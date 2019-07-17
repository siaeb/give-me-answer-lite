<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'GMA_BuddyPress' ) ) :

    class GMA_BuddyPress extends BP_Component {

        public function __construct() {
            parent::start('gma', __( 'GMA', 'give-me-answer-lite' ), GMA_DIR .'includes/Extension/BuddyPress/');

            add_action('gma_register_middle_setting_field', array($this, 'add_setting_field'));

            $this->includes();
            $this->define_constants();
            $this->fully_loaded();

            // Use BuddyPress profile
            add_filter( 'gma_get_author_link', [$this, 'redirect_to_bp_profile'], 10, 2 );

            // User BuddyPress Profile URL
            add_filter( 'gma_user_image_url', [$this, 'use_bp_profile_image'],10, 2 );

            add_filter('gma_user_about', [$this, 'change_user_about'], 10, 2);
            add_filter('gma_user_university', [$this, 'change_user_university'], 10, 2);
        }

        function redirect_to_bp_profile($url, $user_id) {
            return bp_core_get_user_domain($user_id);
        }

        function use_bp_profile_image( $profile_url,  $user_id ) {
            $bp_avatar = bp_core_fetch_avatar(['html' => false, 'item_id' => $user_id]);
            return $bp_avatar;
        }

        function change_user_about($about, $user_id) {
            return xprofile_get_field_data('about', $user_id);
        }

        function change_user_university ($university, $user_id) {
            return xprofile_get_field_data('university', $user_id);
        }

        public function add_setting_field(){
            add_settings_section(
                'gma-bp-settings',
                __( 'BuddyPress Settings', 'give-me-answer-lite' ),
                false,
                'gma-settings'
            );
            add_settings_field(
                'gma_options[gma-bp-name]',
                __( 'Tab name', 'give-me-answer-lite' ),
                function() {
                    global $gma_general_settings;
                    $bp_name = isset( $gma_general_settings['gma-bp-name'] ) ?  $gma_general_settings['gma-bp-name'] : '';
                    echo '<p><input id="gma_tab_name" type="text" name="gma_options[gma-bp-name]" class="medium-text" value="'.$bp_name.'" ></p>';
                },
                'gma-settings',
                'gma-bp-settings'
            );
        }


        public function includes( $includes = array() ) {
            $includes[] = 'class-buddypress-helpers.php';

            if ( bp_is_active( 'notifications' ) ) {
                $includes[] = 'class-buddypress-notifications.php';
            }

            parent::includes( $includes );
        }

        public function define_constants( $args = array() ) {
            global $gma_general_settings;

            $bp = buddypress();

            // define name
            if ( !defined( 'BP_GMA_NAME' ) ){
                $bp_name = isset( $gma_general_settings['gma-bp-name'] ) ?  $gma_general_settings['gma-bp-name'] : 'GMA';
                define( 'BP_GMA_NAME', $bp_name );
                define( 'BP_GMA_SLUG', sanitize_title( $bp_name, 'give-me-answer-lite') );//generate slug by name
            }

            // Define a slug, if necessary
            if ( !defined( 'BP_GMA_SLUG' ) ) {
                define( 'BP_GMA_SLUG', 'give-me-answer-lite' );
            }

            // define question, answer slug
            if (!defined( 'BP_GMA_SLUG_QUESTION' )) {
                define( 'BP_GMA_SLUG_QUESTION', BP_GMA_SLUG . '-' . sanitize_title(__('question', 'give-me-answer-lite') ), 'question');
            }

            if (!defined( 'BP_GMA_SLUG_ANSWER' )) {
                define( 'BP_GMA_SLUG_ANSWER', BP_GMA_SLUG . '-' . sanitize_title(__('answer', 'give-me-answer-lite') ), 'answer');
            }
            
            $args = array(
                'path'          => BP_PLUGIN_DIR,
                'slug'          => BP_GMA_SLUG,
                'root_slug'     => BP_GMA_SLUG,
                'has_directory' => false,
                'search_string' => __( 'Search '.BP_GMA_NAME.'...', 'give-me-answer-lite' ),
            );

            parent::setup_globals( $args );
        }

        public function setup_nav( $main_nav = array(), $sub_nav = array() ) {

            // Stop if there is no user displayed or logged in
            if ( !is_user_logged_in() && !bp_displayed_user_id() )
                return;

            // Define local variable(s)
            $user_domain = '';
            
            // Add 'GMA' to the main navigation
            $main_nav = array(
                'name'                => BP_GMA_NAME,
                'slug'                => $this->slug,
                'position'            => 80,
                'screen_function'     => 'dp_gma_screen_questions',
                'default_subnav_slug' => BP_GMA_SLUG_QUESTION,
                'item_css_id'         => $this->id
            );

            // Determine user to use
            if ( bp_displayed_user_id() ) {
                $user_domain = bp_displayed_user_domain();
            } else if ( bp_loggedin_user_domain() ) {
                $user_domain = bp_loggedin_user_domain();
            } else {
                return;
            }

            // User link
            $gma_link = trailingslashit( $user_domain . $this->slug );

            $sub_nav[] = array(
                'name'            => __( 'Questions', 'give-me-answer-lite' ),
                'slug'            => BP_GMA_SLUG_QUESTION,
                'parent_url'      => $gma_link,
                'parent_slug'     => $this->slug,
                'screen_function' => 'dp_gma_screen_questions',
                'position'        => 20,
                'item_css_id'     => 'topics'
            );

            $sub_nav[] = array(
                'name'            => __( 'Answers', 'give-me-answer-lite' ),
                'slug'            => BP_GMA_SLUG_ANSWER,
                'parent_url'      => $gma_link,
                'parent_slug'     => $this->slug,
                'screen_function' => 'dp_gma_screen_answers',
                'position'        => 20,
                'item_css_id'     => 'topics'
            );

            parent::setup_nav( $main_nav, $sub_nav );
        }

        /**
         * Set up the admin bar
         *
         */
        public function setup_admin_bar( $wp_admin_nav = array() ) {
            if ( !bp_use_wp_admin_bar() || defined( 'DOING_AJAX' ) ) return;
            // Menus for logged in user
            if ( is_user_logged_in() ) {

                // Setup the logged in user variables
                $user_domain = bp_loggedin_user_domain();
                $gma_link = trailingslashit( $user_domain . $this->slug );

                // Add the "My Account" sub menus
                $wp_admin_nav[] = array(
                    'parent' => buddypress()->my_account_menu_id,
                    'id'     => 'my-account-' . $this->id,
                    'title'  => BP_GMA_NAME,
                    'href'   => trailingslashit( $gma_link )
                );
                $wp_admin_nav[] = array(
                    'parent' => 'my-account-' . $this->id,
                    'id'     => 'my-account-' . $this->id.'-question',
                    'title'  => __( 'Questions', 'give-me-answer-lite' ),
                    'href'   => trailingslashit( $gma_link )
                );
                $wp_admin_nav[] = array(
                    'parent' => 'my-account-' . $this->id,
                    'id'     => 'my-account-' . $this->id.'-answer',
                    'title'  => __( 'Answers', 'give-me-answer-lite' ),
                    'href'   => trailingslashit( $gma_link ). 'gma-answer'
                );

            }

            parent::setup_admin_bar( $wp_admin_nav );
        }

        private function fully_loaded() {
            do_action_ref_array( 'bp_gma_buddypress_loaded', array( $this ) );
        }
    }

endif;
