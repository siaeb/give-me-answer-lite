<?php  

function gma_init_tinymce_editor( $args = array() ) {
	global $gma;
	gma_lite()->editor->display( $args );
}

function gma_paste_srtip_disable( $mceInit ){
	$mceInit['paste_strip_class_attributes'] = 'none';
	return $mceInit;
}

class GMA_Editor {

	public function __construct() {

		add_action( 'init', array( $this, 'tinymce_addbuttons' ) );

		add_filter( 'gma_prepare_edit_answer_content', 'wpautop' );
		add_filter( 'gma_prepare_edit_question_content', 'wpautop' );
	}
	
	public function tinymce_addbuttons() {
		if ( get_user_option( 'rich_editing' ) == 'true' && ! is_admin() ) {
			add_filter( 'mce_external_plugins', array( $this, 'add_custom_tinymce_plugin' ) );
			add_filter( 'mce_buttons', array( $this, 'register_custom_button' ) );
		}
	}

	public function register_custom_button( $buttons ) {
		array_push( $buttons, '|', 'gmaCodeEmbed' );
		return $buttons;
	} 

	public function add_custom_tinymce_plugin( $plugin_array ) {
		global $gma_options;
		if ( is_singular( 'gma-question' ) || ( $gma_options['pages']['submit-question'] && is_page( $gma_options['pages']['submit-question'] ) ) ) {
			$plugin_array['gmaCodeEmbed'] = GMA_URI . 'assets-admin/js/code-edit-button.js';
		}
		return $plugin_array;
	}

	public function display( $args ) {
		extract( wp_parse_args( $args, array(
				'content'       => '',
				'id'            => 'gma-custom-content-editor',
				'textarea_name' => 'custom-content',
				'rows'          => 5,
				'wpautop'       => false,
				'media_buttons' => false,
		) ) );

		$gma_tinymce_css = apply_filters( 'gma_editor_style', GMA_URI . 'assets-public/css/editor-style.css' );
		$toolbar1 = apply_filters( 'gma_tinymce_toolbar1', 'bold,italic,underline,|,' . 'bullist,numlist,blockquote,|,' . 'link,unlink,|,' . 'image,code,|,'. 'spellchecker,fullscreen,embed,gmaCodeEmbed,|,' );
		wp_editor( $content, $id, array(
			'wpautop'       => $wpautop,
			'media_buttons' => $media_buttons,
			'textarea_name' => $textarea_name,
			'textarea_rows' => $rows,
			'tinymce' => array(
					'toolbar1' => $toolbar1,
					'toolbar2'   => '',
					'content_css' => $gma_tinymce_css
			),
			'quicktags'     => true,
		) );
	}

	public function toolbar_buttons() {

	}
}

?>