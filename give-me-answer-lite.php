<?php
/**
 * Plugin Name: Give Me Answer Lite
 * Description: A WordPress plugin to build an Question Answer system for support, asking and communicate with your customer
 * Author: Siavash Ebrahimi
 * Author URI: http://www.siaeb.com/
 * Version: 1.0
 * Text Domain: give-me-answer-lite
 * Domain Path: languages/
 * @since 1.0
 */
class Give_Me_Answer_Lite {

    private $last_update = 1563261015; //last update time of the plugin

	private function __construct() {

		$this->define_constants();
		$this->includes();

		$this->dir            = GMA_DIR;
		$this->uri            = GMA_URI;
		$this->temp_dir       = GMA_TEMP_DIR;
		$this->temp_uri       = GMA_TEMP_URL;
		$this->stylesheet_dir = GMA_STYLESHEET_DIR;
		$this->stylesheet_uri = GMA_STYLESHEET_URL;

		$this->version = '1.0';

		$this->load_plugin_textdomain();

		// load posttype
        new GMA_MenuManager();

		$this->question       = new GMA_Posts_Question();
		$this->answer         = new GMA_Posts_Answer();
		$this->comment        = new GMA_Posts_Comment();
		$this->ajax           = new GMA_Ajax();
		$this->handle         = new GMA_Handle();
		$this->permission     = new GMA_Permission();
		$this->status         = new GMA_Status();
		$this->shortcode      = new GMA_Shortcode();
		$this->template       = new GMA_Template();
		$this->settings       = new GMA_Settings();
		$this->notifications  = new GMA_Notifications();
        $this->editor         = new GMA_Editor();

		$this->filter         = new GMA_Filter();
		$this->session        = new GMA_Session();
		$this->transient      = new GMA_Transient();

		$this->metaboxes      = new GMA_Metaboxes();

		$this->profile_visit  = new GMA_DB_Profile_Visit();
		$this->statistics     = new GMA_DB_Statistics();
        $this->utility        = new GMA_Utility();
        $this->system_status  = new GMA_System_Status();

		new GMA_AssetsLoader();
		new GMA_Dashboard();
		new GMA_Profile();
		new GMA_Initializer();
		new GMA_Comments();
		new GMA_Addons();
		new GMA_Schema();
        new GMA_Feed();

		register_activation_hook( __FILE__, array( $this, 'activate_hook' ) );
		register_deactivation_hook( __FILE__, array( $this, 'deactivate_hook' ) );
	}

    /**
     * Class implements singleton pattern,
     * this method return an instance of class
     *
     * @since 1.0
     * @access public
     * @static
     * @return Give_Me_Answer_Lite
     */
	public static function instance() {
		static $instance = null;

		if ( is_null( $instance ) ) {
			$instance = new self();
		}

		return $instance;
	}

    /**
     * Includes necessary files()
     *
     * @since 1.0
     * @access public
     * @return void
     */
	public function includes() {
		require_once GMA_DIR . 'includes/class-autoload.php';
		require_once GMA_DIR . 'includes/helper-functions.php';

        require_once GMA_DIR . 'includes/class-ajax.php';
        require_once GMA_DIR . 'includes/class-handle.php';
        require_once GMA_DIR . 'includes/class-permission.php';
        require_once GMA_DIR . 'includes/class-status.php';
        require_once GMA_DIR . 'includes/class-shortcode.php';
        require_once GMA_DIR . 'includes/class-template.php';
        require_once GMA_DIR . 'includes/class-settings.php';
        require_once GMA_DIR . 'includes/class-notifications.php';
        require_once GMA_DIR . 'includes/class-filter.php';
        require_once GMA_DIR . 'includes/class-session.php';
        require_once GMA_DIR . 'includes/class-metaboxes.php';
        require_once GMA_DIR . 'includes/class-browser-detection.php';

        require_once GMA_DIR . 'includes/PostTypes/class-posts-base.php';
        require_once GMA_DIR . 'includes/PostTypes/class-posts-answer.php';
        require_once GMA_DIR . 'includes/PostTypes/class-posts-question.php';
        require_once GMA_DIR . 'includes/PostTypes/class-posts-comment.php';

		require_once GMA_DIR . 'includes/Widgets/class-widget-closed-questions.php';
		require_once GMA_DIR . 'includes/Widgets/class-widget-latest-questions.php';
		require_once GMA_DIR . 'includes/Widgets/class-widget-popular-questions.php';
		require_once GMA_DIR . 'includes/Widgets/class-widget-question-tags.php';
		require_once GMA_DIR . 'includes/Widgets/class-widget-related-questions.php';
		require_once GMA_DIR . 'includes/Widgets/class-widget-top-questioners.php';
		require_once GMA_DIR . 'includes/Widgets/class-widget-top-responders.php';
		require_once GMA_DIR . 'includes/Widgets/class-widget-ask-question.php';
		require_once GMA_DIR . 'includes/Widgets/class-widget-categories.php';
		require_once GMA_DIR . 'includes/Widgets/class-widget-rss.php';

		require_once GMA_DIR . 'includes/Database/class-db-base.php';
		require_once GMA_DIR . 'includes/Database/class-db-profile-visit.php';
		require_once GMA_DIR . 'includes/Database/class-db-statistics.php';

        require_once GMA_DIR . 'includes/Libraries/feed/FeedWriter.php';
        require_once GMA_DIR . 'includes/Libraries/Upload/class-upload.php';
        require_once GMA_DIR . 'includes/Libraries/cmb2/init.php';

		require_once GMA_DIR . 'includes/class-menu-manager.php';
		require_once GMA_DIR . 'includes/class-user.php';
		require_once GMA_DIR . 'includes/class-profile.php';
		require_once GMA_DIR . 'includes/class-perpagemanager.php';
		require_once GMA_DIR . 'includes/class-paginator.php';
		require_once GMA_DIR . 'includes/class-assets-loader.php';
		require_once GMA_DIR . 'includes/class-initializer.php';
		require_once GMA_DIR . 'includes/class-transient.php';
		require_once GMA_DIR . 'includes/class-editor.php';
		require_once GMA_DIR . 'includes/class-dashboard.php';
		require_once GMA_DIR . 'includes/class-comments.php';
		require_once GMA_DIR . 'includes/class-addons.php';
		require_once GMA_DIR . 'includes/class-utility.php';
		require_once GMA_DIR . 'includes/class-schema.php';
		require_once GMA_DIR . 'includes/class-system-status.php';
		require_once GMA_DIR . 'includes/class-feed.php';
		require_once GMA_DIR . 'includes/Tables/class-table-comments.php';


		require_once GMA_DIR . 'includes/Tables/class-table-base.php';
		require_once GMA_DIR . 'includes/Tables/class-table-user-questions.php';
		require_once GMA_DIR . 'includes/Tables/class-table-user-answers.php';
		require_once GMA_DIR . 'includes/Tables/class-table-favorite-questions.php';
		require_once GMA_DIR . 'includes/Tables/class-table-answer.php';

        require GMA_DIR . '/includes/Libraries/update-checker/plugin-update-checker.php';

	}

    /**
     * Define constants
     *
     * @since 1.0
     * @access public
     * @return void
     */
	public function define_constants() {
		$defines = array(
			'GMA_DIR'            => plugin_dir_path( __FILE__ ),
			'GMA_FILE'           => __FILE__,
			'GMA_URI'            => plugin_dir_url( __FILE__ ),
			'GMA_TEMP_DIR'       => trailingslashit( get_template_directory() ),
			'GMA_TEMP_URL'       => trailingslashit( get_template_directory_uri() ),
			'GMA_STYLESHEET_DIR' => trailingslashit( get_stylesheet_directory() ),
			'GMA_STYLESHEET_URL' => trailingslashit( get_stylesheet_directory_uri() ),
			'GMA_PREFIX'         => 'gma_',
		);

		foreach( $defines as $k => $v ) {
			if ( !defined( $k ) ) {
				define( $k, $v );
			}
		}
	}

	// Update rewrite url when active plugin
	public function activate_hook() {
		$this->permission->prepare_permission_caps();
		flush_rewrite_rules();
	}

    /**
     * Deactivation hook
     *
     * @since 1.0
     * @access public
     * @return void
     */
	public function deactivate_hook() {
		$this->permission->remove_permision_caps();

		wp_clear_scheduled_hook( 'gma_hourly_event' );

		// Remove all transients
		gma_lite()->transient->remove_all();

		flush_rewrite_rules();
	}


	public function get_last_update() {
		return $this->last_update;
	}


    /**
     * Load plugin text domain, for localization
     *
     * @since 1.0
     * @access private
     * @return void
     */
	private function load_plugin_textdomain() {
		$locale = get_locale();
		$mo = 'gma-' . $locale . '.mo';
		load_textdomain( 'give-me-answer-lite', WP_LANG_DIR . '/give-me-answer-lite/' . $mo );
		load_textdomain( 'give-me-answer-lite', plugin_dir_path( __FILE__ ) . 'languages/' . $mo );
		load_plugin_textdomain( 'give-me-answer-lite' );
	}

}

/**
 * Get an instance of Give_Me_Answer class
 * @since 1.0
 * @return Give_Me_Answer_Lite
 */
function gma_lite() {
	return Give_Me_Answer_Lite::instance();
}

$GLOBALS['gma'] = gma_lite();