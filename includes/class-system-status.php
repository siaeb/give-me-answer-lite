<?php
/**
 * Give Me Answer System Status
 *
 *
 * @package Give Me Answer
 * @since   1.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * System status
 *
 * @package Give Me Answer
 */
class GMA_System_Status{

    /**
     * Get array of environment information. Includes thing like software
     * versions, and various server settings.
     *
     * @return array
     */
    public function get_environment_info() {
        global $wpdb;

        // Figure out cURL version, if installed.
        $curl_version = '';
        if ( function_exists( 'curl_version' ) ) {
            $curl_version = curl_version();
            $curl_version = $curl_version['version'] . ', ' . $curl_version['ssl_version'];
        } elseif ( extension_loaded( 'curl' ) ) {
            $curl_version = __( 'cURL installed but unable to retrieve version.', 'give-me-answer-lite' );
        }

        // WP memory limit.
        $wp_memory_limit = gma_lite()->utility->let_to_num( WP_MEMORY_LIMIT );
        if ( function_exists( 'memory_get_usage' ) ) {
            $wp_memory_limit = max( $wp_memory_limit, gma_lite()->utility->let_to_num( @ini_get( 'memory_limit' ) ) );
        }

        $database_version = gma_lite()->utility->get_server_database_version();

        // Return all environment info. Described by JSON Schema.
        return array(
            'home_url'                  => get_option( 'home' ),
            'site_url'                  => get_option( 'siteurl' ),
            'version'                   => gma_lite()->version,
            'wp_version'                => get_bloginfo( 'version' ),
            'wp_multisite'              => is_multisite(),
            'wp_memory_limit'           => $wp_memory_limit,
            'wp_debug_mode'             => ( defined( 'WP_DEBUG' ) && WP_DEBUG ),
            'wp_cron'                   => ! ( defined( 'DISABLE_WP_CRON' ) && DISABLE_WP_CRON ),
            'language'                  => get_locale(),
            'external_object_cache'     => wp_using_ext_object_cache(),
            'server_info'               => isset( $_SERVER['SERVER_SOFTWARE'] ) ? gma_lite()->utility->clean( wp_unslash( $_SERVER['SERVER_SOFTWARE'] ) ) : '',
            'php_version'               => phpversion(),
            'php_post_max_size'         => gma_lite()->utility->let_to_num( ini_get( 'post_max_size' ) ),
            'php_max_execution_time'    => ini_get( 'max_execution_time' ),
            'php_max_input_vars'        => ini_get( 'max_input_vars' ),
            'curl_version'              => $curl_version,
            'suhosin_installed'         => extension_loaded( 'suhosin' ),
            'max_upload_size'           => wp_max_upload_size(),
            'mysql_version'             => $database_version['number'],
            'mysql_version_string'      => $database_version['string'],
            'default_timezone'          => date_default_timezone_get(),
            'fsockopen_or_curl_enabled' => ( function_exists( 'fsockopen' ) || function_exists( 'curl_init' ) ),
            'soapclient_enabled'        => class_exists( 'SoapClient' ),
            'domdocument_enabled'       => class_exists( 'DOMDocument' ),
            'gzip_enabled'              => is_callable( 'gzopen' ),
            'mbstring_enabled'          => extension_loaded( 'mbstring' ),
        );
    }

    /**
     * Add prefix to table.
     *
     * @param string $table Table name.
     * @return stromg
     */
    protected function add_db_table_prefix( $table ) {
        global $wpdb;
        return $wpdb->prefix . $table;
    }

    /**
     * Get array of database information. Version, prefix, and table existence.
     *
     * @return array
     */
    public function get_database_info() {
        global $wpdb;

        $database_table_sizes = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT
				    table_name AS 'name',
				    round( ( data_length / 1024 / 1024 ), 2 ) 'data',
				    round( ( index_length / 1024 / 1024 ), 2 ) 'index'
				FROM information_schema.TABLES
				WHERE table_schema = %s
				ORDER BY name ASC;",
                DB_NAME
            )
        );

        // WC Core tables to check existence of.
        $core_tables = apply_filters(
            'gma_database_tables',
            array(
                'woocommerce_sessions',
                'woocommerce_api_keys',
                'woocommerce_attribute_taxonomies',
                'woocommerce_downloadable_product_permissions',
                'woocommerce_order_items',
                'woocommerce_order_itemmeta',
                'woocommerce_tax_rates',
                'woocommerce_tax_rate_locations',
                'woocommerce_shipping_zones',
                'woocommerce_shipping_zone_locations',
                'woocommerce_shipping_zone_methods',
                'woocommerce_payment_tokens',
                'woocommerce_payment_tokenmeta',
                'woocommerce_log',
            )
        );

        /**
         * Adding the prefix to the tables array, for backwards compatibility.
         *
         * If we changed the tables above to include the prefix, then any filters against that table could break.
         */
        $core_tables = array_map( array( $this, 'add_db_table_prefix' ), $core_tables );

        /**
         * Organize WooCommerce and non-WooCommerce tables separately for display purposes later.
         *
         * To ensure we include all WC tables, even if they do not exist, pre-populate the WC array with all the tables.
         */
        $tables = array(
            'give-me-answer-lite' => array_fill_keys( $core_tables, false ),
            'other'       => array(),
        );

        $database_size = array(
            'data'  => 0,
            'index' => 0,
        );

        $site_tables_prefix = $wpdb->get_blog_prefix( get_current_blog_id() );
        $global_tables = $wpdb->tables( 'global', true );
        foreach ( $database_table_sizes as $table ) {
            // Only include tables matching the prefix of the current site, this is to prevent displaying all tables on a MS install not relating to the current.
            if ( is_multisite() && 0 !== strpos( $table->name, $site_tables_prefix ) && ! in_array( $table->name, $global_tables, true ) ) {
                continue;
            }
            $table_type = in_array( $table->name, $core_tables ) ? 'give-me-answer-lite' : 'other';

            $tables[ $table_type ][ $table->name ] = array(
                'data'  => $table->data,
                'index' => $table->index,
            );

            $database_size['data']  += $table->data;
            $database_size['index'] += $table->index;
        }

        // Return all database info. Described by JSON Schema.
        return array(
            'database_prefix'        => $wpdb->prefix,
            'database_tables'        => $tables,
            'database_size'          => $database_size,
        );
    }

    /**
     * Get array of counts of objects. Orders, products, etc.
     *
     * @return array
     */
    public function get_post_type_counts() {
        global $wpdb;

        $post_type_counts = $wpdb->get_results( "SELECT post_type AS 'type', count(1) AS 'count' FROM {$wpdb->posts} GROUP BY post_type;" );

        return is_array( $post_type_counts ) ? $post_type_counts : array();
    }

    /**
     * Get a list of plugins active on the site.
     *
     * @return array
     */
    public function get_active_plugins() {
        require_once ABSPATH . 'wp-admin/includes/plugin.php';

        if ( ! function_exists( 'get_plugin_data' ) ) {
            return array();
        }

        $active_plugins = (array) get_option( 'active_plugins', array() );
        if ( is_multisite() ) {
            $network_activated_plugins = array_keys( get_site_option( 'active_sitewide_plugins', array() ) );
            $active_plugins            = array_merge( $active_plugins, $network_activated_plugins );
        }

        $active_plugins_data = array();

        foreach ( $active_plugins as $plugin ) {
            $data                  = get_plugin_data( WP_PLUGIN_DIR . '/' . $plugin );
            $active_plugins_data[] = $this->format_plugin_data( $plugin, $data );
        }

        return $active_plugins_data;
    }

    /**
     * Get a list of inplugins active on the site.
     *
     * @return array
     */
    public function get_inactive_plugins() {
        require_once ABSPATH . 'wp-admin/includes/plugin.php';

        if ( ! function_exists( 'get_plugins' ) ) {
            return array();
        }

        $plugins        = get_plugins();
        $active_plugins = (array) get_option( 'active_plugins', array() );

        if ( is_multisite() ) {
            $network_activated_plugins = array_keys( get_site_option( 'active_sitewide_plugins', array() ) );
            $active_plugins            = array_merge( $active_plugins, $network_activated_plugins );
        }

        $plugins_data = array();

        foreach ( $plugins as $plugin => $data ) {
            if ( in_array( $plugin, $active_plugins, true ) ) {
                continue;
            }
            $plugins_data[] = $this->format_plugin_data( $plugin, $data );
        }

        return $plugins_data;
    }

    /**
     * Format plugin data, including data on updates, into a standard format.
     *
     * @since 3.6.0
     * @param string $plugin Plugin directory/file.
     * @param array  $data Plugin data from WP.
     * @return array Formatted data.
     */
    protected function format_plugin_data( $plugin, $data ) {
        require_once ABSPATH . 'wp-admin/includes/update.php';

        if ( ! function_exists( 'get_plugin_updates' ) ) {
            return array();
        }

        // Use WP API to lookup latest updates for plugins. WC_Helper injects updates for premium plugins.
        if ( empty( $this->available_updates ) ) {
            $this->available_updates = get_plugin_updates();
        }

        $version_latest = $data['Version'];

        // Find latest version.
        if ( isset( $this->available_updates[ $plugin ]->update->new_version ) ) {
            $version_latest = $this->available_updates[ $plugin ]->update->new_version;
        }

        return array(
            'plugin'            => $plugin,
            'name'              => $data['Name'],
            'version'           => $data['Version'],
            'version_latest'    => $version_latest,
            'url'               => $data['PluginURI'],
            'author_name'       => $data['AuthorName'],
            'author_url'        => esc_url_raw( $data['AuthorURI'] ),
            'network_activated' => $data['Network'],
        );
    }

    /**
     * Get a list of Dropins and MU plugins.
     *
     * @since 3.6.0
     * @return array
     */
    public function get_dropins_mu_plugins() {
        $dropins = get_dropins();
        $plugins = array(
            'dropins'    => array(),
            'mu_plugins' => array(),
        );
        foreach ( $dropins as $key => $dropin ) {
            $plugins['dropins'][] = array(
                'plugin' => $key,
                'name'   => $dropin['Name'],
            );
        }

        $mu_plugins = get_mu_plugins();
        foreach ( $mu_plugins as $plugin => $mu_plugin ) {
            $plugins['mu_plugins'][] = array(
                'plugin'      => $plugin,
                'name'        => $mu_plugin['Name'],
                'version'     => $mu_plugin['Version'],
                'url'         => $mu_plugin['PluginURI'],
                'author_name' => $mu_plugin['AuthorName'],
                'author_url'  => esc_url_raw( $mu_plugin['AuthorURI'] ),
            );
        }
        return $plugins;
    }

    /**
     * Get info on the current active theme, info on parent theme (if presnet)
     * and a list of template overrides.
     *
     * @return array
     */
    public function get_theme_info() {
        $active_theme = wp_get_theme();

        // Get parent theme info if this theme is a child theme, otherwise
        // pass empty info in the response.
        if ( is_child_theme() ) {
            $parent_theme      = wp_get_theme( $active_theme->template );
            $parent_theme_info = array(
                'parent_name'           => $parent_theme->name,
                'parent_version'        => $parent_theme->version,
                'parent_version_latest' => gma_lite()->utility->get_latest_theme_version( $parent_theme ),
                'parent_author_url'     => $parent_theme->{'Author URI'},
            );
        } else {
            $parent_theme_info = array(
                'parent_name'           => '',
                'parent_version'        => '',
                'parent_version_latest' => '',
                'parent_author_url'     => '',
            );
        }

        $active_theme_info = array(
            'name'                    => $active_theme->name,
            'version'                 => $active_theme->version,
            'version_latest'          => gma_lite()->utility->get_latest_theme_version( $active_theme ),
            'author_url'              => esc_url_raw( $active_theme->{'Author URI'} ),
        );

        return array_merge( $active_theme_info, $parent_theme_info );
    }

    /**
     * Returns security tips.
     *
     * @return array
     */
    public function get_security_info() {
        global $gma_general_settings;
        $check_page = isset( $gma_general_settings['paged']['archive-question'] ) ? get_permalink($gma_general_settings['paged']['archive-question']) : '';
        return array(
            'secure_connection' => 'https' === substr( $check_page, 0, 5 ),
            'hide_errors'       => ! ( defined( 'WP_DEBUG' ) && defined( 'WP_DEBUG_DISPLAY' ) && WP_DEBUG && WP_DEBUG_DISPLAY ) || 0 === intval( ini_get( 'display_errors' ) ),
        );
    }

    /**
     * Returns a mini-report on WC pages and if they are configured correctly:
     * Present, visible, and including the correct shortcode.
     *
     * @return array
     */
    public function get_pages() {
        global $gma_general_settings;
        // GMA pages to check against.
        $check_pages = array(
            _x( 'Archive Questions', 'Page setting', 'give-me-answer-lite' ) => array(
                'option'    => 'archive-question',
                'shortcode' => '[gma-list-questions]',
            ),
            _x( 'Submit Question Form', 'Page setting', 'give-me-answer-lite' ) => array(
                'option'    => 'submit-question',
                'shortcode' => '[gma-submit-question-form]',
            ),
            _x( 'Tags', 'Page setting', 'give-me-answer-lite' ) => array(
                'option'    => 'tags',
                'shortcode' => '[gma-tags]',
            ),
            _x( 'Users', 'Page setting', 'give-me-answer-lite' ) => array(
                'option'    => 'users',
                'shortcode' => '[gma-users]',
            ),
            _x( 'User Profile', 'Page setting', 'give-me-answer-lite' ) => array(
                'option'    => 'user-profile',
                'shortcode' => '[gma-user-profile]',
            ),
        );

        $pages_output = array();
        foreach ( $check_pages as $page_name => $values ) {
            $page_id            = $gma_general_settings['pages'][ $values['option'] ];
            $page_set           = false;
            $page_exists        = false;
            $page_visible       = false;
            $shortcode_present  = false;
            $shortcode_required = false;

            // Page checks.
            if ( $page_id ) {
                $page_set = true;
            }
            if ( get_post( $page_id ) ) {
                $page_exists = true;
            }
            if ( 'publish' === get_post_status( $page_id ) ) {
                $page_visible = true;
            }

            // Shortcode checks.
            if ( $values['shortcode'] && get_post( $page_id ) ) {
                $shortcode_required = true;
                $page               = get_post( $page_id );
                if ( strstr( $page->post_content, $values['shortcode'] ) ) {
                    $shortcode_present = true;
                }
            }

            // Wrap up our findings into an output array.
            $pages_output[] = array(
                'page_name'          => $page_name,
                'page_id'            => $page_id,
                'page_set'           => $page_set,
                'page_exists'        => $page_exists,
                'page_visible'       => $page_visible,
                'shortcode'          => $values['shortcode'],
                'shortcode_required' => $shortcode_required,
                'shortcode_present'  => $shortcode_present,
            );
        }

        return $pages_output;
    }


}