<?php
defined( 'ABSPATH' ) || exit;


class GMA_Utility {


    /**
     * Update question answers count
     *
     * @since 1.0
     * @param integer $question_id
     * @return bool
     */
    public function update_question_answers_count( $question_id ) {
        $question = get_post( $question_id );
        if ( $question ) {
            $total_answers = gma_question_answer_count_by_status( $question->ID );
            if ( (int) $total_answers < 0 ) {
                $total_answers = intval( 0 );
            }
            update_post_meta( $question->ID, '_gma_answers_count', $total_answers );
            return true;
        }
        return false;
    }


    /**
     * Update question date
     *
     * @since 1.0
     * @param integer $question_id
     * @return bool
     */
    public function update_question_date( $question_id ) {
        $question = get_post( $question_id );
        if ( $question ) {
            $total_answers = gma_question_answer_count_by_status( $question->ID );
            if ( ! $total_answers ) {
                $qdate_created = get_post_meta( $question_id, '_gma_created_date', true );
                gma_update_post_modified( $question_id , $qdate_created, $qdate_created);
                return true;
            }

            // Get last answer date
            $last_answer = $this->get_last_answer( $question_id );
            gma_update_post_modified( $question_id , $last_answer->post_date, $last_answer->post_date);
            return true;
        }
        return false;
    }


    /**
     * Get last question answer
     *
     * @since 1.0
     * @param integer $question_id
     * @return object|WP_Post
     */
    public function get_last_answer( $question_id ) {
        global $wpdb;
        $query = "SELECT * FROM {$wpdb->posts} WHERE post_type = 'gma-answer' AND post_status = 'publish' AND post_parent = %d ORDER BY post_date LIMIT 1";
        return $wpdb->get_row( $wpdb->prepare( $query, $question_id ) );
    }

    /**
     * Check if question or answer submitted by anonymous
     *
     * @since 1.0
     * @param integer $quora_id
     * @return bool
     */
    public function is_anonymous( $quora_id = '' ) {
        if ( ! $quora_id ) {
            $quora_id = get_the_ID();
        }
        $quora = get_post( $quora_id );
        if ( $quora ) {

            $anonymous = get_post_meta( $quora_id, '_gma_is_anonymous', true );
            if ( $anonymous ) {
                return true;
            }

        }
        // Post not found
        return false;
    }


    /**
     * Count notifications
     *
     * @since 1.0
     * @access public
     *
     * @return int
     */
    public function count_notifications() {
        $pending_qs  = $this->count_pending_questions();
        $pending_ans = $this->count_pending_answers();
        return [
            'total'     => (int)$pending_qs + (int)$pending_ans,
            'questions' => $pending_qs,
            'answers'   => $pending_ans,
        ];
    }

    /**
     * Count pending answers
     *
     * @since 1.0
     * @access public
     * @return int
     */
    public function count_pending_answers() {
        global $wpdb;
        return $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'gma-answer' AND post_status = 'pending'");
    }

    /**
     * Count pending questions
     *
     * @since 1.0
     * @access public
     * @return int
     */
    public function count_pending_questions() {
        global $wpdb;
        return $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'gma-question' AND post_status = 'pending'" );
    }

    /**
     * Notation to numbers.
     *
     * This function transforms the php.ini notation for numbers (like '2M') to an integer.
     *
     * @param  string $size Size value.
     * @return int
     */
    function let_to_num( $size ) {
        $l   = substr( $size, -1 );
        $ret = (int) substr( $size, 0, -1 );
        switch ( strtoupper( $l ) ) {
            case 'P':
                $ret *= 1024;
            // No break.
            case 'T':
                $ret *= 1024;
            // No break.
            case 'G':
                $ret *= 1024;
            // No break.
            case 'M':
                $ret *= 1024;
            // No break.
            case 'K':
                $ret *= 1024;
            // No break.
        }
        return $ret;
    }

    /**
     * Retrieves the MySQL server version. Based on $wpdb.
     *
     * @since 1.0
     * @return array Vesion information.
     */
    function get_server_database_version() {
        global $wpdb;

        if ( empty( $wpdb->is_mysql ) ) {
            return array(
                'string' => '',
                'number' => '',
            );
        }

        if ( $wpdb->use_mysqli ) {
            $server_info = mysqli_get_server_info( $wpdb->dbh ); // @codingStandardsIgnoreLine.
        } else {
            $server_info = mysql_get_server_info( $wpdb->dbh ); // @codingStandardsIgnoreLine.
        }

        return array(
            'string' => $server_info,
            'number' => preg_replace( '/([^\d.]+).*/', '', $server_info ),
        );
    }

    /**
     * Get latest version of a theme by slug.
     *
     * @param  object $theme WP_Theme object.
     * @return string Version number if found.
     */
    public function get_latest_theme_version( $theme ) {
        include_once ABSPATH . 'wp-admin/includes/theme.php';

        $api = themes_api(
            'theme_information',
            array(
                'slug'   => $theme->get_stylesheet(),
                'fields' => array(
                    'sections' => false,
                    'tags'     => false,
                ),
            )
        );

        $update_theme_version = 0;

        // Check .org for updates.
        if ( is_object( $api ) && ! is_wp_error( $api ) ) {
            $update_theme_version = $api->version;
        } elseif ( strstr( $theme->{'Author URI'}, 'woothemes' ) ) { // Check WooThemes Theme Version.
            $theme_dir          = substr( strtolower( str_replace( ' ', '', $theme->Name ) ), 0, 45 ); // @codingStandardsIgnoreLine.
            $theme_version_data = get_transient( $theme_dir . '_version_data' );

            if ( false === $theme_version_data ) {
                $theme_changelog = wp_safe_remote_get( 'http://dzv365zjfbd8v.cloudfront.net/changelogs/' . $theme_dir . '/changelog.txt' );
                $cl_lines        = explode( "\n", wp_remote_retrieve_body( $theme_changelog ) );
                if ( ! empty( $cl_lines ) ) {
                    foreach ( $cl_lines as $line_num => $cl_line ) {
                        if ( preg_match( '/^[0-9]/', $cl_line ) ) {
                            $theme_date         = str_replace( '.', '-', trim( substr( $cl_line, 0, strpos( $cl_line, '-' ) ) ) );
                            $theme_version      = preg_replace( '~[^0-9,.]~', '', stristr( $cl_line, 'version' ) );
                            $theme_update       = trim( str_replace( '*', '', $cl_lines[ $line_num + 1 ] ) );
                            $theme_version_data = array(
                                'date'      => $theme_date,
                                'version'   => $theme_version,
                                'update'    => $theme_update,
                                'changelog' => $theme_changelog,
                            );
                            set_transient( $theme_dir . '_version_data', $theme_version_data, DAY_IN_SECONDS );
                            break;
                        }
                    }
                }
            }

            if ( ! empty( $theme_version_data['version'] ) ) {
                $update_theme_version = $theme_version_data['version'];
            }
        }

        return $update_theme_version;
    }

    /**
     * Clean variables using sanitize_text_field. Arrays are cleaned recursively.
     * Non-scalar values are ignored.
     *
     * @param string|array $var Data to sanitize.
     * @return string|array
     */
    function clean( $var ) {
        if ( is_array( $var ) ) {
            return array_map( [$this, 'clean'], $var );
        } else {
            return is_scalar( $var ) ? sanitize_text_field( $var ) : $var;
        }
    }


    /**
     * Remove all "GMA" transients
     *
     * @since 1.0
     * @access public
     * @return void
     */
    public function remove_gma_transients() {
        global $wpdb;
        $wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '\_transient\_gma-%'" );
        $wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '\_site\_transient\_gma-%'" );
        $wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '\_transient\_timeout\_gma-%'" );
        $wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '\_site\_transient\_timeout\_gma-%'" );
    }

    /**
     * Remove all expired WordPress transients
     *
     * @since 1.0
     * @access public
     * @return bool|int
     */
    public function remove_expired_wp_transients() {
        global $wpdb;
        return $wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->options} WHERE option_name LIKE ('%\_transient\_%') AND option_value < %d", time() ) );
    }

    /**
     * Remove all GMA pages that are in trash
     *
     * @since 1.0
     * @access public
     * @return bool|int
     */
    public function delete_trash_pages() {
        global $wpdb;
        $pagecontents = [
            '[gma-list-questions]',
            '[gma-submit-question-form]',
            '[gma-popular-questions]',
            '[gma-latest-answers]',
            '[gma-question-followers]',
            '[gma-users]',
            '[gma-tags]',
            '[gma-user-profile]',
        ];
        $pagecontents = apply_filters( 'gma_shortcodes', $pagecontents );
        $query = "DELETE FROM {$wpdb->posts} WHERE post_status = 'trash' AND (";
        foreach ( $pagecontents as $shortcode ) {
            $query .= "post_content LIKE '%{$shortcode}%' OR ";
        }
        // Remove last 'OR'
        $query = rtrim( $query, ' OR ' );
        $query .= ')';
        return $wpdb->query( $query );
    }

    /**
     * Check if value is integer
     *
     * @since 1.0
     * @param string $value
     * @return bool
     */
    public function is_digit( $value ) {
        return filter_var( $value, FILTER_VALIDATE_INT, ['options' => [ 'min_range' => 0 ]] );
    }


    public function can_post_comment($post_id) {
        global $gma_general_settings;
        $post = get_post( $post_id );
        if ( $post ) {
            if ( $post->post_type == 'gma-question' ) {
                return $gma_general_settings['comment']['comment-on-qs'];
            }

            if ( $post->post_type == 'gma-answer' ) {
                $is_pending   = get_post_status(get_the_ID()) == 'pending' && gma_get_current_user_id() == get_post_field('post_author', get_the_ID());
                if ( $is_pending ) return false;
                return $gma_general_settings['comment']['comment-on-as'];
            }
        }
        return false;
    }

    /**
     * Close question
     *
     * @since 1.0
     *
     * @param integer $question_id
     * @param string $reason
     * @return bool
     */
    public function close_question($question_id, $reason = '') {

        if ( ! $question_id || ! absint($question_id) ) return false;

        update_post_meta( $question_id, '_gma_status', 'close' );
        update_post_meta( $question_id, '_gma_close_reason', sanitize_text_field( $reason ) );


        return true;
    }

    /**
     * Open question
     *
     * @param integer $question_id
     * @return bool
     */
    public function open_question($question_id) {
        if ( ! $question_id || ! absint( $question_id ) ) return false;
        update_post_meta( $question_id, '_gma_status', 'open' );
        update_post_meta( $question_id, '_gma_close_reason', '' );
        return true;
    }

    /**
     * Check if question is closed or not
     *
     * @since 1.0
     *
     * @param integer $question_id
     * @return bool
     */
    public function get_question_status($question_id) {
        if ( ! $question_id || ! absint( $question_id ) ) return false;
        return [
            'status' => get_post_meta($question_id, '_gma_status', true),
            'reason' => get_post_meta($question_id, '_gma_close_reason', true),
        ];
    }


    /**
     * Check if current request is an ajax or not
     *
     * @since 1.0
     *
     * @return bool
     */
    public function is_ajax_request() {
        return ! empty( $_SERVER[ 'HTTP_X_REQUESTED_WITH' ] ) && strtolower( $_SERVER[ 'HTTP_X_REQUESTED_WITH' ]) == 'xmlhttprequest' ;
    }

    

    /**
     * Show Page title
     * @param string $title
     */
    public function show_page_title( $title = '' ) {

        //Check if $title not Set
        if ( empty( $title ) and function_exists( 'get_admin_page_title' ) ) {
            $title = get_admin_page_title();
        }

        //show Page title
        echo '<div class="d-flex align-items-center py-4">';
        echo '<img src="' . GMA_URI . '/assets-public/img/quora.png' . '" class="gma-page-title"><h2 class="gma-page-title ml-1">' . $title . '</h2>';
        echo '</div>';
    }

    /**
     * Format domain name
     *
     * @since 1.0
     * @param string $domain_name
     * @return mixed|string
     */
    public function format_domain_name($domain_name) {
        $search_for = [
            'http://',
            'https://',
        ];
        $domain_name = str_replace($search_for, '', $domain_name);
        $domain_name = ltrim($domain_name, 'www.');
        $domain_name = 'www.' . $domain_name;
        return $domain_name;
    }

    /**
     * Get question from answer or question id
     *
     * @since 1.0
     * @param integer $quora_id
     * @return array|bool|WP_Post|null
     */
    public function get_question($quora_id) {
        $quora = get_post($quora_id);
        if (!$quora) return false;
        if (get_post_type($quora->ID) == 'gma-answer') {
            return get_post($quora->post_parent);
        }
        return $quora;
    }

    /**
     * Filter bad words
     *
     * @since 1.0
     * @param string $content
     * @return mixed
     */
    public function filter_text($content) {
        global $gma_general_settings;
        if (empty($gma_general_settings['filter']['words'])) return $content;
        $badwords = str_replace(' ', ',', $gma_general_settings['filter']['words']);
        $badwords = explode(',', $badwords);
        $badwords = array_map(function ($word){
            return trim($word);
        }, $badwords);
        return str_ireplace($badwords, '*', $content);
    }

    /**
     * Get question or answer link
     *
     * @since 1.0
     * @param integer $quora_id
     * @return bool|false|string
     */
    public function get_link($quora_id) {
        if (empty($quora_id)) return false;
        $quora = get_post($quora_id);
        if ($quora->post_type == 'gma-question') {
            return get_permalink($quora->ID);
        }

        // parameter is an answer !
        $question = get_post($quora->post_parent);
        return get_permalink($question->ID) . '#answer-' . $quora->ID;
    }

}