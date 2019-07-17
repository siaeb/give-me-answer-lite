<?php
defined( 'ABSPATH' ) || exit;


class GMA_Initializer {

	public function __construct() {
		add_action( 'init', array( $this, 'init' ) );

		add_action( 'admin_init', array( $this, 'admin_init' ) );

		add_action( 'widgets_init', array( $this, 'widgets_init' ) );

		add_filter( 'plugin_action_links', array( $this, 'go_pro' ), 10, 2 );

		add_filter( 'user_profile_url', function( $url ) {
			return gma_get_edit_profile_url( get_current_user_id() );
		}, 11 );

		add_filter( 'get_terms_orderby', function($orderby, $queryvars, $taxonomies) {
			if ( in_array( 'gma-question_tag', $taxonomies) ) {
				return 'count';
			}
			return $orderby;
		}, 999999,3 );

		add_action( 'gma_update_question', function($new_question_id, $old_post, $new_post) {
			global $wpdb;
			$wpdb->query( $wpdb->prepare( "UPDATE `{$wpdb->posts}` SET post_modified = %s, post_modified_gmt = %s WHERE ID = %d", $old_post->post_modified, $old_post->post_modified_gmt, $new_question_id ) );
		}, 11, 3 );

		add_action( 'post_updated', [$this, 'edit_question_date_modified'], 11, 3 );

		add_action( 'gma_before_comment_submit_button', [$this, 'comment_form_description'] );

		// Dont show q2a comments in admin comments menu
		add_action( 'current_screen', function($screen) {
			if ( $screen->id == 'edit-comments' ) {
				add_filter( 'comments_clauses', function($clauses, $wp_comment_query) {
					global $wpdb;

					if ( ! $clauses['join'] ) {
						$clauses['join'] = "JOIN $wpdb->posts ON $wpdb->posts.ID = $wpdb->comments.comment_post_ID";
					}

					if ( ! $wp_comment_query->query_vars['post_type' ] ) {
						$clauses['where'] .= $wpdb->prepare( " AND {$wpdb->posts}.post_type NOT IN (%s, %s)", 'gma-question', 'gma-answer' );
					}

					return $clauses;
				}, 10, 2 );
			}
		});

        add_action( 'admin_bar_menu', function(WP_Admin_Bar $wp_admin_bar ) {
            if ( ! is_user_logged_in() ) {
                return false;
            }
            $logout_menu = $wp_admin_bar->get_node( 'logout' );
            $wp_admin_bar->remove_menu( 'logout' );
            $wp_admin_bar->add_menu(
                array(
                    'id'     => 'gma-profile',
                    'parent' => 'user-actions',
                    'title'  => __('Give Me Answer Profile', 'give-me-answer-lite'),
                    'href'   => gma_get_edit_profile_url( get_current_user_id() ),
                    'meta'   => array(
                        'tabindex' => 1,
                    ),
                )
            );

            $wp_admin_bar->add_menu( $logout_menu );
        }, 10 );

        add_action( 'admin_bar_menu', [$this, 'wp_admin_bar_gma_menu'], 31 );

        add_filter( 'genesis_pre_get_option_content_archive', [$this, 'gma_genesis_intergrate_genesis'], 9999, 2 );

        add_filter( 'genesis_pre_get_option_features_on_front', [$this, 'gma_genesis_feature_on_first_page'], 9999, 2 );

        add_filter( 'the_excerpt', [$this, 'gma_the_excerpt'] );

        add_filter( 'advanced-ads-ad-select-args', [$this, 'gma_advanced_ads_select_args'], 99 );

        add_filter( 'get_post_metadata', [$this, 'gma_disable_wpdevart_facebook_comment'], 10, 3 );

        add_filter( 'the_content', [$this, 'gma_disable_facebook_comments_plugin'], 10 );

        add_action( 'admin_init', [$this, 'handle_admin_settings_tools'] );

        add_action( 'admin_menu', function() {
            global $submenu;
            foreach ( $submenu as $parent_name => &$childs ) {
                if ( 'edit.php?post_type=gma-question' == $parent_name ) {

                    // Questions notification count
                    if ( $noticount = gma_lite()->utility->count_pending_questions() ) {
                        if (isset( $childs[5][0] )) {
                            $childs[5][0] .= sprintf( ' <span class="awaiting-mod count-1"><span class="pending-count" aria-hidden="true">%s</span></span>', $noticount );
                        }
                    }

                    // Answers notification count
                    if ( $noticount = gma_lite()->utility->count_pending_questions() ) {
                        if ( isset( $childs[6][0] ) ) {
                            $childs[6][0] .= sprintf( ' <span class="awaiting-mod count-1"><span class="pending-count" aria-hidden="true">%s</span></span>', $noticount );
                        }
                    }
                }
            }
        } );

        add_filter( 'parent_file', function ($parent_file) {
            global $submenu_file, $current_screen, $pagenow;

            # Set the submenu as active/current while anywhere in your Custom Post Type
            if ( in_array($current_screen->post_type, ['gma-question', 'gma-answer'])) {

                if ( $current_screen->taxonomy == 'gma-question_tag' ) {
                    $submenu_file = 'edit-tags.php?taxonomy=gma-question_tag&post_type=' . $current_screen->post_type;
                }

                if ( $current_screen->taxonomy == 'gma-question_category' ) {
                    $submenu_file = 'edit-tags.php?taxonomy=gma-question_category&post_type=' . $current_screen->post_type;
                }

                return 'give-me-answer-lite';

            }

            return $parent_file;

        }, 999 );

        add_action( 'bp_include', array($this,'integrate_buddypress'), 10 );

        add_filter('private_title_format', [$this, 'show_private_badge'], 11, 2);

        add_action('gma_before_archive_question_title', [$this, 'show_icons']);

        add_filter('gma_prepare_answers', [$this, 'sort_answers']);
	}

	function sort_answers($args) {

	    if (isset($_GET['activetab'])) {

	        $active_tab = strtolower($_GET['activetab']);

            if ( $active_tab == 'newest' ) {
                $args['order']   = 'DESC';
                $args['orderby'] = 'post_date';
            }

            if ($active_tab == 'votes') {
                $args['order']    = 'DESC';
                $args['orderby']  = '_gma_votes';
                $args['meta_key'] = '_gma_votes';
            }
        }

	    return $args;
    }

	function show_icons() {
	    // If Question closed
        if ( gma_question_status() == 'close' ) {
          echo '<i class="fa fa-lock mr-1"></i>';
        }

        if (gma_is_sticky()) {
            echo '<i class="fa fa-thumbtack text-danger mr-1"></i>';
        }
    }

	function show_private_badge($private_format, $post) {
	    if (! is_admin()) {
            return '<span class="fa fa-user-secret text-danger" title="' . __('Private', 'give-me-answer-lite') .'"></span> %s';
        }
	    return $private_format;
    }

    /**
     * Integrate plugin with BuddyPress
     *
     * @since 1.0
     * @return void
     */
    function integrate_buddypress() {
        require( GMA_DIR . 'includes/Extension/BuddyPress/class-buddypress.php' );
        new GMA_BuddyPress();
    }
    
	function handle_admin_settings_tools() {
	    if ( isset( $_GET['action'], $_GET['page'] ) && $_GET['page'] = 'gma-settings' ) {

	        // Check nonce
            if ( ! isset( $_GET[ '_wpnonce' ] ) || ! wp_verify_nonce( $_GET['_wpnonce'], '_gma_tools_nonce' ) ) {
                wp_die( __('Permission Error', 'give-me-answer-lite') );
            }

            switch ( strtolower( $_GET[ 'action' ] ) ) {
                case 'clear_transients':
                    gma_lite()->utility->remove_gma_transients();
                    $message = __('Question/Answer transients cleared', 'give-me-answer-lite');
                    break;
                case 'clear_expired_transients':
                    gma_lite()->utility->remove_expired_wp_transients();
                    $message = __('WordPress expired transients cleared', 'give-me-answer-lite');
                    break;
                case 'install_pages':
                    gma_lite()->utility->delete_trash_pages();
                    delete_transient('gma_create_pages' );
                    $this->create_pages();
                    $message = __('GiveMeAnswer pages has been created again.', 'give-me-answer-lite');
                    break;
            }

            add_action( 'gma_settings_tools_messages', function() use ($message) {
                echo sprintf( '<div class="updated inline is-dismissible mt-1 mb-1">%s</div>', $message );
            } );
        }
    }

    /** Advanced Ads **/
    function gma_advanced_ads_select_args( $args ) {
        if ( 'gma-answer' == get_post_type() || 'gma-question' == get_post_type() ) {
            $args['post']['post_type'] = get_post_type();
        }

        return $args;
    }

    /** Facebook Comments **/
    function gma_disable_wpdevart_facebook_comment( $value, $post_id, $meta_key ) {
        $gma_options = get_option( 'gma_options', array() );
        if (
            '_disabel_wpdevart_facebook_comment' == $meta_key
            &&
            (
                'gma-question' == get_post_type( $post_id ) // is single question
                ||
                'gma-answer' == get_post_type( $post_id ) // is single answer
                ||
                (int) $gma_options['pages']['submit-question'] == (int) $post_id // is submit question page
                ||
                (int) $gma_options['pages']['archive-question'] == (int) $post_id // is archive page
            )
        ) {
            $value = 'disable';
        }

        return $value;
    }

    function gma_disable_facebook_comments_plugin( $content ) {
        if ( 'gma-question' == get_post_type() || 'gma-answer' == get_post_type() ) {
            remove_filter('the_content', 'fbcommentbox', 100);
        }
        return $content;
    }

    function gma_genesis_intergrate_genesis( $value, $setting ) {
        if ( is_tax( 'gma-question_category' ) || is_tax( 'gma-question_tag' ) ) {
            return 'full';
        }

        return $value;
    }

    function gma_genesis_feature_on_first_page( $value, $setting ) {
        if ( is_tax( 'gma-question_category' ) || is_tax( 'gma-question_tag' ) ) {
            $gma_options = get_option( 'gma_options', array() );
            return isset( $gma_options['posts-per-page'] ) ? $gma_options['posts-per-page'] : 15;
        }

        return $value;
    }

    /**
     * Show shortcode when page or page template when using the_excerpt()
     *
     * @param string $content
     * @return string
     */
    function gma_the_excerpt( $content ) {
        global $post;

        $gma_options = get_option( 'gma_options' );

        if (
            isset( $post->ID )
            &&
            (
                (int) $post->ID == (int) $gma_options['pages']['archive-question']
                ||
                (int) $post->ID == (int) $gma_options['pages']['submit-question']
            )
        ) {
            $content = apply_filters( 'the_content', $post->post_content );
        }

        if ( is_singular( 'gma-question' ) ) {
            $content = apply_filters( 'the_content', $post->post_content );
        }

        return $content;
    }

    /**
     * Add the "Give Me Answer" archive question page link to admin bar menu
     *
     * @since 3.3.0
     *
     * @param WP_Admin_Bar $wp_admin_bar
     */
	function wp_admin_bar_gma_menu( $wp_admin_bar ) {
	    global $gma_general_settings;

        // Don't show for logged out users.
        if ( ! is_user_logged_in() ) {
            return;
        }

        // Show only when the user is a member of this site, or they're a super admin.
        if ( ! is_user_member_of_blog() && ! current_user_can( 'manage_network' ) ) {
            return;
        }

        $wp_admin_bar->add_menu( [
            'parent' => 'site-name',
            'id'     => 'view-gma',
            'title'  => __( 'Give Me Answer', 'give-me-answer-lite' ),
            'href'   => get_permalink( $gma_general_settings['pages']['archive-question'] ),
            'meta'   => [
                'target' => '_blank'
            ],
        ] );

        // Check if user is an administrator
        if ( current_user_can( 'manage_options' ) ) {

            $adminbar_menus = [
                [
                    'parent' => '',
                    'id'     => 'give-me-answer-lite',
                    'title'  => __( 'Give Me Answer', 'give-me-answer-lite' ),
                    'href'   => get_permalink( gma_shortcode_postid( 'gma-list-questions' ) ),
                    'meta'   => [
                        'target' => '_blank',
                    ],
                    'childs' => [
                        [
                            'id'     => 'view-gma-dashboard',
                            'parent' => 'give-me-answer-lite',
                            'title'  => __('Dashboard', 'give-me-answer-lite'),
                            'href'   => admin_url( 'admin.php?page=give-me-answer' ),
                        ],
                        [
                            'id'     => 'view-gma-questions',
                            'parent' => 'give-me-answer-lite',
                            'title'  => __('Questions', 'give-me-answer-lite'),
                            'href'   => admin_url( 'edit.php?post_type=gma-question' ),
                        ],
                        [
                            'id'     => 'view-gma-answers',
                            'parent' => 'give-me-answer-lite',
                            'title'  => __('Answers', 'give-me-answer-lite'),
                            'href'   => admin_url( 'edit.php?post_type=gma-answer' ),
                        ],
                        [
                            'id'     => 'view-gma-comments',
                            'parent' => 'give-me-answer-lite',
                            'title'  => __('Comments', 'give-me-answer-lite'),
                            'href'   => admin_url( 'admin.php?page=gma-comments' ),
                        ],
                        [
                            'id'     => 'view-gma-settings',
                            'parent' => 'give-me-answer-lite',
                            'title'  => __('Settings', 'give-me-answer-lite'),
                            'href'   => admin_url( 'admin.php?page=gma-settings' ),
                        ],
                    ]
                ],
            ];

            $notifications = gma_lite()->utility->count_notifications();
            // Check if plugin has notification
            if ( $notifications['total'] ) {
                $adminbar_menus[0]['title'] .= sprintf(' <span style="display:inline-block;background-color:#d54e21;color:#fff;font-size:9px;line-height:17px;font-weight:600;border-radius:10px;padding:0 6px;">%s</span>', $notifications['total']);
            }

            $adminbar_menus = apply_filters( 'gma_adminbar_menus', $adminbar_menus );

            // Add menus to wordpress adminbar
            foreach ( $adminbar_menus as $menu ) {
                $wp_admin_bar->add_menu( $menu );
                if ( isset( $menu['childs'] ) && count( $menu['childs'] ) ) {
                    foreach ( $menu['childs'] as $childmenu ) {

                        // Questions notification count
                        if ( 'view-gma-questions' ==  $childmenu['id'] && $notifications['questions'] ) {
                            $childmenu['title'] .= sprintf(' <span style="display:inline-block;background-color:#d54e21;color:#fff;font-size:9px;line-height:17px;font-weight:600;border-radius:10px;padding:0 6px;">%s</span>', $notifications['questions']);
                        }

                        // Answer notification count
                        if ( 'view-gma-answers' ==  $childmenu['id'] && $notifications['answers'] ) {
                            $childmenu['title'] .= sprintf(' <span style="display:inline-block;background-color:#d54e21;color:#fff;font-size:9px;line-height:17px;font-weight:600;border-radius:10px;padding:0 6px;">%s</span>', $notifications['answers']);
                        }

                        $wp_admin_bar->add_menu( $childmenu );
                    }
                }
            }

        }

    }


	function comment_form_description() {
		global $gma_general_settings;
		$show_description = isset( $gma_general_settings[ 'customize' ]['comment']['bottom'][ 'show-html' ] )
				&&
				$gma_general_settings[ 'customize' ]['comment']['bottom'][ 'show-html' ] ? true : false;
		if ( $show_description ) {
			echo stripslashes( $gma_general_settings[ 'customize' ]['comment']['bottom']['html'] );
		}
	}

	/**
	 * Edit question modified date
	 *
	 * @since 1.0
	 *
	 * @param integer $post_id
	 * @param WP_Post $post_before
	 * @param WP_Post $post_after
	 */
	function edit_question_date_modified( $post_id, $post_before, $post_after ) {
		if ( $post_before->post_type == 'gma-question' ) {
			$created_date = get_post_meta( $post_id, '_gma_created_date', true );
			gma_update_post_modified( $post_id, $created_date, $created_date );
		}
	}

	public function init() {
		global $gma_sript_vars, $gma_template, $gma_general_settings;

		$active_template = gma_lite()->template->get_template();

		//Scripts var
		$question_category_rewrite = $gma_general_settings['question-category-rewrite'];
		$question_category_rewrite = $question_category_rewrite ? $question_category_rewrite : 'question-category';
		$question_tag_rewrite = $gma_general_settings['question-tag-rewrite'];
		$question_tag_rewrite = $question_tag_rewrite ? $question_tag_rewrite : 'question-tag';
		$gma_sript_vars = array(
			'ajax_url' => admin_url( 'admin-ajax.php' ),
		);

		// Automatically create gma pages
		$this->create_pages();

		$this->flush_rules();
	}

    /**
     * Create database tables
     *
     * @since 1.0
     * @author Siavash Ebrahimi <vbnetgenius@gmail.com>
     * @return void
     */
	public function admin_init() {
		if ( gma_lite()->profile_visit->installed() == false ) {
			gma_lite()->profile_visit->create_table();
		}
	}

    /**
     * Register widgets
     *
     * @since 1.0
     * @author Siavash Ebrahimi <vbnetgenius@gmail.com>
     * @return void
     */
	public function widgets_init() {
		$widgets = [
		    'GMA_Closed_Question',
		    'GMA_Latest_Question',
		    'GMA_Popular_Question',
		    'GMA_Question_Tags',
		    'GMA_Related_Questions',
		    'GMA_Top_Questioners',
		    'GMA_Top_Responders',
		    'GMA_Ask_Question',
            'GMA_Categories',
            'GMA_Widget_RSS',
        ];

		$widgets = apply_filters( 'gma_before_register_widgets', $widgets );

		foreach( $widgets as $widget ) {
			register_widget( $widget );
		}
	}

	public function go_pro( $actions, $file ) {
		$file_name = plugin_basename( GMA_FILE );
		if ( $file == $file_name ) {
			$actions['gma_settings'] = '<a href="' . menu_page_url( 'gma-settings', false ) .'">' . __('Settings', 'give-me-answer-lite') .'</a>';
            $actions['gma_go_pro']   = '<a href="https://codecanyon.net/item/give-me-answer/24133030" style="color: red; font-weight: bold">Go Pro!</a>';
		}
		return $actions;
	}

	private function flush_rules() {
		if ( get_option( 'gma_plugin_activated', false ) || get_option( 'gma_plugin_upgraded', false ) ) {
			delete_option( 'gma_plugin_upgraded' );
			flush_rewrite_rules();
		}
	}

	private function create_pages() {

		if ( false === get_transient( 'gma_create_pages' ) ) {
			$options = get_option( 'gma_options' );

			if ( ! isset( $options['pages']['archive-question'] ) || ( isset( $options['pages']['archive-question'] ) && ! get_post( $options['pages']['archive-question'] ) ) ) {
				$args = array(
					'post_title'    => __( 'Give Me Answer - Archive', 'give-me-answer-lite' ),
					'post_type'     => 'page',
					'post_status'   => 'publish',
					'post_content'  => '[gma-list-questions]',
				);
				$question_page = get_page_by_path( sanitize_title( $args['post_title'] ) );
				if ( ! $question_page ) {
					$options['pages']['archive-question'] = wp_insert_post( $args );
				} else {
					// Page exists
					$options['pages']['archive-question'] = $question_page->ID;
				}
			}

			if ( ! isset( $options['pages']['submit-question'] ) || ( isset( $options['pages']['submit-question'] ) && ! get_post( $options['pages']['submit-question'] ) ) ) {
				$args = array(
					'post_title'    => __( 'Ask Question', 'give-me-answer-lite' ),
					'post_type'     => 'page',
					'post_status'   => 'publish',
					'post_content'  => '[gma-submit-question-form]',
					'post_parent'   => $options[ 'pages' ][ 'archive-question' ],
				);
				$ask_page = get_page_by_path( sanitize_title( $args['post_title'] ) );

				if ( ! $ask_page ) {
					$options['pages']['submit-question'] = wp_insert_post( $args );
				} else {
					// Page exists
					$options['pages']['submit-question'] = $ask_page->ID;
				}
			}

			if ( ! isset( $options['pages']['tags'] ) || ( isset( $options['pages']['tags'] ) && ! get_post( $options['pages']['tags'] ) ) ) {
				$args = array(
					'post_title'    => __( 'Tags', 'give-me-answer-lite' ),
					'post_type'     => 'page',
					'post_status'   => 'publish',
					'post_content'  => '[gma-tags]',
					'post_parent'   => $options[ 'pages' ][ 'archive-question' ],
				);
				$tags_page = gma_shortcode_postid( 'gma-tags' );

				if ( ! get_post( $tags_page ) ) {
					$options['pages']['tags'] = wp_insert_post( $args );
				} else {
					$options['pages']['tags'] = $tags_page;
				}
			}

			if ( ! isset( $options['pages']['users'] ) || ( isset( $options['pages']['users'] ) && ! get_post( $options['pages']['users'] ) ) ) {
				$args = array(
					'post_title'    => __( 'Users', 'give-me-answer-lite' ),
					'post_type'     => 'page',
					'post_status'   => 'publish',
					'post_content'  => '[gma-users]',
					'post_parent'   => $options[ 'pages' ][ 'archive-question' ],
				);
				$users_page = gma_shortcode_postid( 'gma-users' );

				if ( ! get_post( $users_page ) ) {
					$options['pages']['users'] = wp_insert_post( $args );
				} else {
					$options['pages']['users'] = $users_page;
				}
			}

			if ( ! isset( $options['pages']['user-profile'] ) || ( isset( $options['pages']['user-profile'] ) && ! get_post( $options['pages']['user-profile'] ) ) ) {
				$args = array(
					'post_title'    => __( 'User Profile', 'give-me-answer-lite' ),
					'post_type'     => 'page',
					'post_status'   => 'publish',
					'post_content'  => '[gma-user-profile]',
					'post_parent'   => $options[ 'pages' ][ 'archive-question' ],
				);
				$user_profile_page = gma_shortcode_postid( 'gma-user-profile' );

				if ( ! get_post( $user_profile_page ) ) {
					$options['pages']['user-profile'] = wp_insert_post( $args );
				} else {
					$options['pages']['user-profile'] = $user_profile_page;
				}
			}

			// Valid page content to ensure shortcode was inserted
			$questions_page_content = get_post_field( 'post_content', $options['pages']['archive-question'] );
			if ( strpos( $questions_page_content, '[gma-list-questions]' ) === false ) {
				$questions_page_content = str_replace( '[gma-submit-question-form]', '', $questions_page_content );
				wp_update_post( array(
					'ID'			=> $options['pages']['archive-question'],
					'post_content'	=> $questions_page_content . '[gma-list-questions]',
				) );
			}

			$submit_question_content = get_post_field( 'post_content', $options['pages']['submit-question'] );
			if ( strpos( $submit_question_content, '[gma-submit-question-form]' ) === false ) {
				$submit_question_content = str_replace( '[gma-list-questions]', '', $submit_question_content );
				wp_update_post( array(
					'ID'			=> $options['pages']['submit-question'],
					'post_content'	=> $submit_question_content . '[gma-submit-question-form]',
				) );
			}

			$tags_content = get_post_field( 'post_content', $options['pages']['tags'] );
			if ( strpos( $tags_content, '[gma-tags]' ) === false ) {
				$tags_content = str_replace( '[gma-tags]', '', $tags_content );
				wp_update_post( array(
					'ID'			=> $options['pages']['tags'],
					'post_content'	=> $tags_content . '[gma-tags]',
				) );
			}

			$users_content = get_post_field( 'post_content', $options['pages']['users'] );
			if ( strpos( $tags_content, '[gma-users]' ) === false ) {
				$users_content = str_replace( '[gma-users]', '', $users_content );
				wp_update_post( array(
					'ID'			=> $options['pages']['users'],
					'post_content'	=> $users_content . '[gma-users]',
				) );
			}

			$user_profile_content = get_post_field( 'post_content', $options['pages']['user-profile'] );
			if ( strpos( $tags_content, '[gma-user-profile]' ) === false ) {
				$user_profile_content = str_replace( '[gma-user-profile]', '', $user_profile_content );
				wp_update_post( array(
					'ID'			=> $options['pages']['user-profile'],
					'post_content'	=> $user_profile_content . '[gma-user-profile]',
				) );
			}

			update_option( 'gma_options', $options );

			set_transient( 'gma_create_pages', 1, 60 * 60 * 24 );
		}


		update_option( 'gma_plugin_activated', true );
		//update option delay email
		update_option('gma_enable_email_delay', true);
	}
}