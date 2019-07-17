<?php
defined( 'ABSPATH' ) || exit;

// Callback for gma-general-settings Option
function gma_question_registration_setting_display() {
	global  $gma_general_settings;
	?>
	<p><input type="checkbox" name="gma_options[answer-registration]" value="true" <?php checked( true, isset( $gma_general_settings['answer-registration'] ) ? (bool ) $gma_general_settings['answer-registration'] : false ); ?> id="gma_option_answer_registation">
	<label for="gma_option_answer_registation"><span class="description"><?php _e( 'Login required. No anonymous post allowed','give-me-answer-lite' ); ?></span></label></p>
	<?php
}

function gma_pages_settings_display() {
	global  $gma_general_settings;
	$archive_question_page = isset( $gma_general_settings['pages']['archive-question'] ) ? $gma_general_settings['pages']['archive-question'] : 0;
	?>
    <p>
        <?php
        wp_dropdown_pages( array(
            'name'              => 'gma_options[pages][archive-question]',
            'show_option_none'  => __( 'Select Archive Question Page','give-me-answer-lite' ),
            'option_none_value' => 0,
            'selected'          => $archive_question_page,
        ) );
        ?><br>
        <span class="description">
            <?php _e( 'A page where displays all questions. The <code>[gma-list-questions]</code> short code must be on this page.','give-me-answer-lite' ) ?>
        </span>
    </p>

	<?php
}

function gma_question_new_time_frame_display() {
	global  $gma_general_settings;
	echo '<p><input type="text" name="gma_options[question-new-time-frame]" id="gma_options_question_new_time_frame" value="'.( isset( $gma_general_settings['question-new-time-frame'] ) ? $gma_general_settings['question-new-time-frame'] : 4 ).'" class="small-text" /><span class="description"> '.__( 'hours','give-me-answer-lite' ).'<span title="'.__( 'A period of time in which new questions are highlighted and marked as New','give-me-answer-lite' ).'">( ? )</span></span></p>';
}

function gma_question_overdue_time_frame_display() {
	global  $gma_general_settings;
	echo '<p><input type="text" name="gma_options[question-overdue-time-frame]" id="gma_options_question_new_time_frame" value="'.( isset( $gma_general_settings['question-overdue-time-frame'] ) ? $gma_general_settings['question-overdue-time-frame'] : 2 ).'" class="small-text" /><span class="description"> '.__( 'days','give-me-answer-lite' ).'<span title="'.__( 'A Question will be marked as overdue if it passes this period of time, starting from the time the question was submitted','give-me-answer-lite' ).'">( ? )</span></span></p>';
}

function gma_submit_question_page_display(){
	global  $gma_general_settings;
	$submit_question_page = isset( $gma_general_settings['pages']['submit-question'] ) ? $gma_general_settings['pages']['submit-question'] : 0;
	?>
        <p>
            <?php
            wp_dropdown_pages( array(
                'name'              => 'gma_options[pages][submit-question]',
                'show_option_none'  => __( 'Select Submit Question Page','give-me-answer-lite' ),
                'option_none_value' => 0,
                'selected'          => $submit_question_page,
            ) );
            ?><br>
            <span class="description"><?php _e( 'A page where users can submit questions. The <code>[gma-submit-question-form]</code> short code must be on this page.','give-me-answer-lite' ) ?></span>
        </p>
	<?php
}

function gma_user_profile_page_display(){
	global  $gma_general_settings;
	$user_profile_page = isset( $gma_general_settings['pages']['user-profile'] ) ? $gma_general_settings['pages']['user-profile'] : 0;
	?>
    <p>
		<?php
		wp_dropdown_pages( array(
			'name'              => 'gma_options[pages][user-profile]',
			'show_option_none'  => __( 'Select User Profile Page','give-me-answer-lite' ),
			'option_none_value' => 0,
			'selected'          => $user_profile_page,
		) );
		?><br>
        <span class="description"><?php _e( 'A page where user profile displayed. The <code>[gma-user-profile]</code> short code must be on this page.','give-me-answer-lite' ) ?></span>
    </p>
	<?php
}


function gma_404_page_display(){
	global  $gma_general_settings;
	$submit_question_page = isset( $gma_general_settings['pages']['404'] ) ? $gma_general_settings['pages']['404'] : 0;
	?>
	<p>
		<?php
			wp_dropdown_pages( array(
				'name'              => 'gma_options[pages][404]',
				'show_option_none'  => __( 'Select 404 GMA Page','give-me-answer-lite' ),
				'option_none_value' => 0,
				'selected'          => $submit_question_page,
			) );
		?>
		<span class="description"><?php _e( 'This page will be redirected when users without authority click on a private question. You can customize the message of this page in.If not, a default 404 page will be used.','give-me-answer-lite' ) ?></span>
	</p>
	<?php
}
function gma_email_template_settings_display(){
	global $gma_options;
	$editor_content = isset( $gma_options['subscribe']['email-template'] ) ? $gma_options['subscribe']['email-template'] : '';
	wp_editor( $editor_content, 'gma_email_template_editor', array(
		'textarea_name' => 'gma_options[subscribe][email-template]'
	) );
}


function gma_subscrible_enable_new_question_notification(){
	echo '<th>'.__( 'Enable?','give-me-answer-lite' ).'</th><td><input type="checkbox" value="1" '.checked( 1, get_option( 'gma_subscrible_enable_new_question_notification', 1 ), false ).' name="gma_subscrible_enable_new_question_notification" id="gma_subscrible_enable_new_question_notification" ><span class="description">'.__( 'Enable notification for new question.', 'give-me-answer-lite' ).'</span></td>';
}
// New Question - Enable Notification

function gma_subscrible_new_question_email_subject_display(){
	echo '<th>'.__( 'Email subject','give-me-answer-lite' ).'</th><td><input type="text" id="gma_subscrible_new_question_email_subject" name="gma_subscrible_new_question_email_subject" value="'.get_option( 'gma_subscrible_new_question_email_subject' ).'" class="regular-text" /></span></td>';
}
// New Question - Email subject

function gma_subscrible_new_question_email_display(){
	echo '<th for="gma_subscrible_new_question_email">'.__( 'Email Content','give-me-answer-lite' ).'</th>';
	echo '<td>';
	$content = gma_get_mail_template( 'gma_subscrible_new_question_email', 'new-question' );
	wp_editor( $content, 'gma_subscrible_new_question_email', array(
		'wpautop'   => false,
		'tinymce' => array( 'content_css' => GMA_URI . 'assets-admin/css/email-template-editor.css' ),
	) );
	//echo '<p><input data-template="new-question.html" type="button" class="button gma-reset-email-template" value="Reset Template"></p>';
	echo '<div class="description gma-parameters-wrapper" style="margin-top: 15px;">
        <p class="gma-available-parameters">' . __('Available parameters', 'give-me-answer-lite') . '</p>
  
		<strong>{site_name}</strong> - ' . __('Your site name', 'give-me-answer-lite') . '<br />
		<strong>{username}</strong> - ' . __('Question Author Name', 'give-me-answer-lite') . '<br />
		<strong>{user_link}</strong> - ' . __('Question Author Posts Link', 'give-me-answer-lite') . '<br />
		<strong>{question_title}</strong> - ' . __('Question Title', 'give-me-answer-lite') . '<br />
		<strong>{question_link}</strong> - ' . __('Question Link', 'give-me-answer-lite') . '<br />
		<strong>{question_content}</strong> - ' . __('Question Content', 'give-me-answer-lite') . '<br />
	</div>';
	echo '</td>';
}
// New Question - Email Content


function gma_subscrible_enable_new_answer_notification(){
	echo '<th>'.__( 'Enable?','give-me-answer-lite' ).'</th><td><input type="checkbox" value="1" '.checked( 1, get_option( 'gma_subscrible_enable_new_answer_notification', 1 ), false ).' name="gma_subscrible_enable_new_answer_notification" id="gma_subscrible_enable_new_answer_notification" ></td>';
}
// New Answer - Enable Notification

function gma_subscrible_new_answer_email_subject_display(){
	echo '<th>'.__( 'Email subject','give-me-answer-lite' ).'</th><td><input type="text" id="gma_subscrible_new_answer_email_subject" name="gma_subscrible_new_answer_email_subject" value="'.get_option( 'gma_subscrible_new_answer_email_subject' ).'" class="regular-text" /></span></td>';
}
// New Answer - Email Subject

function gma_subscrible_new_answer_email_display(){
	echo '<th>'.__( 'Email Content','give-me-answer-lite' ).'</th>';
	echo '<td>';
	$content = gma_get_mail_template( 'gma_subscrible_new_answer_email', 'new-answer' );
	wp_editor( $content, 'gma_subscrible_new_answer_email', array(
		'wpautop'   => false,
		'tinymce' => array( 'content_css' => GMA_URI . 'assets-admin/css/email-template-editor.css' ),
	) );
	//echo '<p><input data-template="new-answer.html" type="button" class="button gma-reset-email-template" value="Reset Template"></p>';
	echo '<div class="description gma-parameters-wrapper">
		<p class="gma-available-parameters">' . __('Available parameters', 'give-me-answer-lite') . '</p>
		<strong>{site_name}</strong> - ' . __('Your site name', 'give-me-answer-lite') . '<br />
		<strong>{answer_author}</strong> - ' . __('Answer Author Name', 'give-me-answer-lite') . '<br />
		<strong>{answer_author_link}</strong> - ' . __('Answer Author Link', 'give-me-answer-lite') . '<br />
		<strong>{question_title}</strong> - ' . __('Question Title', 'give-me-answer-lite') . '<br />
		<strong>{question_link}</strong> - ' . __('Question Link', 'give-me-answer-lite') . '<br />
		<strong>{answer_content}</strong> - ' . __('Answer Content', 'give-me-answer-lite') . '<br />
	</div>';
	echo '</td>';
}
// New Answer - Email Content

function gma_subscrible_enable_new_answer_followers_notification(){
	echo '<th>'.__( 'Enable?','give-me-answer-lite' ).'</th><td><input type="checkbox" value="1" '.checked( 1, get_option( 'gma_subscrible_enable_new_answer_followers_notification', 1 ), false ).' name="gma_subscrible_enable_new_answer_followers_notification" id="gma_subscrible_enable_new_answer_followers_notification" ></td>';
}
// New Answer - Follow - Enable Notification

function gma_subscrible_new_answer_followers_email_subject_display(){
	echo '<th>'.__( 'Email subject','give-me-answer-lite' ).'</th><td><input type="text" id="gma_subscrible_new_answer_followers_email_subject" name="gma_subscrible_new_answer_followers_email_subject" value="'.get_option( 'gma_subscrible_new_answer_followers_email_subject' ).'" class="regular-text" /></span></td>';
}
// New Answer - Follow - Email Subject

function gma_subscrible_new_answer_followers_email_display(){
	echo '<th>'.__( 'Email Content','give-me-answer-lite' ).'</th>';
	echo '<td>';
	$content = gma_get_mail_template( 'gma_subscrible_new_answer_followers_email', 'new-answer-followers' );
	wp_editor( $content, 'gma_subscrible_new_answer_followers_email', array(
		'wpautop'   => false,
		'tinymce' => array( 'content_css' => GMA_URI . 'assets-admin/css/email-template-editor.css' ),
	) );
	//echo '<p><input data-template="new-answer-followers.html" type="button" class="button gma-reset-email-template" value="Reset Template"></p>';
    echo '<div class="description gma-parameters-wrapper">
		<p class="gma-available-parameters">' . __('Available parameters', 'give-me-answer-lite') . '</p>
		<strong>{site_name}</strong> - ' . __('Your site name', 'give-me-answer-lite') . '<br />
		<strong>{answer_author}</strong> - ' . __('Answer Author Name', 'give-me-answer-lite') . '<br />
		<strong>{answer_author_link}</strong> - ' . __('Answer Author Link', 'give-me-answer-lite') . '<br />
		<strong>{question_title}</strong> - ' . __('Question Title', 'give-me-answer-lite') . '<br />
		<strong>{question_link}</strong> - ' . __('Question Link', 'give-me-answer-lite') . '<br />
		<strong>{answer_content}</strong> - ' . __('Answer Content', 'give-me-answer-lite') . '<br />
	</div>';
	echo '</td>';
}
// New Answer - Follow - Email Content

function gma_subscrible_enable_new_comment_question_notification(){
	echo '<th>'.__( 'Enable?','give-me-answer-lite' ).'</th><td><input type="checkbox" '.checked( 1, get_option( 'gma_subscrible_enable_new_comment_question_notification', 1 ), false ).' value="1" name="gma_subscrible_enable_new_comment_question_notification" id="gma_subscrible_enable_new_comment_question_notification" ></td>';
}
// New Comment - Question - Enable Notification

function gma_subscrible_new_comment_question_email_subject_display(){
	echo '<th>'.__( 'Email subject','give-me-answer-lite' ).'</th><td><input type="text" id="gma_subscrible_new_comment_question_email_subject" name="gma_subscrible_new_comment_question_email_subject" value="'.get_option( 'gma_subscrible_new_comment_question_email_subject' ).'" class="regular-text" /></td>';
}
// New Comment - Question - Email subject

function gma_subscrible_new_comment_question_email_display(){
	echo '<th>'.__( 'Email Content','give-me-answer-lite' ).'</th><td>';
	$content = gma_get_mail_template( 'gma_subscrible_new_comment_question_email', 'new-comment-question' );
	wp_editor( $content, 'gma_subscrible_new_comment_question_email', array(
		'wpautop'   => false,
		'tinymce' => array( 'content_css' => GMA_URI . 'assets-admin/css/email-template-editor.css' ),
	) );
	//echo '<p><input data-editor="gma_subscrible_new_comment_question_email" data-template="new-comment-question.html" type="button" class="button gma-reset-email-template" value="Reset Template"></p>';
	echo '<div class="description gma-parameters-wrapper">
		<p class="gma-available-parameters">' . __('Available parameters', 'give-me-answer-lite') . '</p>
		<strong>{site_name}</strong> - ' . __('Your site name', 'give-me-answer-lite') . '<br />
		<strong>{question_author}</strong> - ' . __('Question Author Name', 'give-me-answer-lite') . '<br />
		<strong>{comment_author}</strong> - ' . __('Comment Author Name', 'give-me-answer-lite') . '<br />
		<strong>{comment_author_link}</strong> - ' . __('Comment Author Link', 'give-me-answer-lite') . '<br />
		<strong>{question_title}</strong> - ' . __('Question Title', 'give-me-answer-lite') . '<br />
		<strong>{question_link}</strong> - ' . __('Question Link', 'give-me-answer-lite') . '<br />
		<strong>{comment_content}</strong> - ' . __('Comment Content', 'give-me-answer-lite') . '<br />
	</div>';
	echo '</td>';
}
// New Comment - Question - Email Content

function gma_subscrible_enable_new_comment_question_followers_notification(){
	echo '<th>'.__( 'Enable?','give-me-answer-lite' ).'</th><td><input type="checkbox" '.checked( 1, get_option( 'gma_subscrible_enable_new_comment_question_followers_notify', 1 ), false ).' value="1" name="gma_subscrible_enable_new_comment_question_followers_notify" id="gma_subscrible_enable_new_comment_question_followers_notify" ></td>';
}
// New Comment - Question - Follow - Enable Notification

function gma_subscrible_new_comment_question_followers_email_subject_display(){
	echo '<th>'.__( 'Email subject','give-me-answer-lite' ).'</th><td><input type="text" id="gma_subscrible_new_comment_question_followers_email_subject" name="gma_subscrible_new_comment_question_followers_email_subject" value="'.get_option( 'gma_subscrible_new_comment_question_followers_email_subject' ).'" class="widefat" /></td>';
}
// New Comment - Question - Follow - Email subject

function gma_subscrible_new_comment_question_followers_email_display(){
	echo '<th>'.__( 'Email Content','give-me-answer-lite' ).'</th><td>';
	$content = gma_get_mail_template( 'gma_subscrible_new_comment_question_followers_email', 'new-comment-question-followers' );
	wp_editor( $content, 'gma_subscrible_new_comment_question_followers_email', array(
		'wpautop'   => false,
		'tinymce' => array( 'content_css' => GMA_URI . 'assets-admin/css/email-template-editor.css' ),
	) );
	//echo '<p><input data-template="new-comment-question-followers.html" type="button" class="button gma-reset-email-template" value="Reset Template"></p>';
	echo '<div class="description gma-parameters-wrapper">
		<p class="gma-available-parameters">' . __('Available parameters', 'give-me-answer-lite') . '</p>
		<strong>{site_name}</strong> - ' . __('Your site name', 'give-me-answer-lite') .'<br />
		<strong>{question_author}</strong> - ' . __('Question Author Name', 'give-me-answer-lite') .'<br />
		<strong>{comment_author}</strong> - ' . __('Comment Author Name', 'give-me-answer-lite') .'<br />
		<strong>{comment_author_link}</strong> - ' . __('Comment Author Link', 'give-me-answer-lite') .'<br />
		<strong>{question_title}</strong> - ' . __('Question Title', 'give-me-answer-lite') .'<br />
		<strong>{question_link}</strong> - ' . __('Question Link', 'give-me-answer-lite') .'<br />
		<strong>{comment_content}</strong> - ' . __('Comment Content', 'give-me-answer-lite') .'<br />
	</div>';
	echo '</td>';
}
// New Comment - Question - Follow - Email Content

function gma_subscrible_enable_new_comment_answer_notification(){
	echo '<th>'.__( 'Enable?','give-me-answer-lite' ).'</th><td><input type="checkbox" '.checked( 1, get_option( 'gma_subscrible_enable_new_comment_answer_notification', 1 ), false ).' value="1" name="gma_subscrible_enable_new_comment_answer_notification" id="gma_subscrible_enable_new_comment_answer_notification" ></td>';
}
// New Comment - Answer - Enable Notification

function gma_subscrible_new_comment_answer_email_subject_display(){
	echo '<th>'.__( 'Email subject','give-me-answer-lite' ).'</th><td><input type="text" id="gma_subscrible_new_comment_answer_email_subject" name="gma_subscrible_new_comment_answer_email_subject" value="'.get_option( 'gma_subscrible_new_comment_answer_email_subject' ).'" class="regular-text" /></td>';
}
// New Comment - Answer - Email Subject

function gma_subscrible_new_comment_answer_email_display(){
	echo '<th>'.__( 'Email Content','give-me-answer-lite' ).'</th><td>';
	$content = gma_get_mail_template( 'gma_subscrible_new_comment_answer_email', 'new-comment-answer' );
	wp_editor( $content, 'gma_subscrible_new_comment_answer_email', array(
		'wpautop'   => false,
		'tinymce' => array( 'content_css' => GMA_URI . 'assets-admin/css/email-template-editor.css' ),
	) );
	//echo '<p><input data-template="new-comment-answer.html" type="button" class="button gma-reset-email-template" value="Reset Template"></p>';
	echo '<div class="description gma-parameters-wrapper">
		<p class="gma-available-parameters">' . __('Available parameters', 'give-me-answer-lite') . '</p>
		<strong>{site_name}</strong> - ' . __('Your site name', 'give-me-answer-lite') .'<br />
		<strong>{question_author}</strong> - ' . __('Question Author Name', 'give-me-answer-lite') .'<br />
		<strong>{comment_author}</strong> - ' . __('Comment Author Name', 'give-me-answer-lite') .'<br />
		<strong>{comment_author_link}</strong> - ' . __('Comment Author Link', 'give-me-answer-lite') .'<br />
		<strong>{question_title}</strong> - ' . __('Question Title', 'give-me-answer-lite') .'<br />
		<strong>{question_link}</strong> - ' . __('Question Link', 'give-me-answer-lite') .'<br />
		<strong>{comment_content}</strong> - ' . __('Comment Content', 'give-me-answer-lite') .'<br />
	</div>';
	echo '</td>';
}
// New Comment - Answer - Email Content

function gma_subscrible_enable_new_comment_answer_followers_notification(){
	echo '<th>'.__( 'Enable?','give-me-answer-lite' ).'</th><td><input type="checkbox" '.checked( 1, get_option( 'gma_subscrible_enable_new_comment_answer_followers_notification', 1 ), false ).' value="1" name="gma_subscrible_enable_new_comment_answer_followers_notification" id="gma_subscrible_enable_new_comment_answer_followers_notification" ></td>';
}
// New Comment - Answer - Follow - Enable Notification

function gma_subscrible_new_comment_answer_followers_email_subject_display(){
	echo '<th>'.__( 'Email subject','give-me-answer-lite' ).'</th><td><input type="text" id="gma_subscrible_new_comment_answer_followers_email_subject" name="gma_subscrible_new_comment_answer_followers_email_subject" value="'.get_option( 'gma_subscrible_new_comment_answer_followers_email_subject' ).'" class="regular-text" /></td>';
}
// New Comment - Answer - Follow - Email Subject

function gma_subscrible_new_comment_answer_followers_email_display(){
	echo '<th>'.__( 'Email Content','give-me-answer-lite' ).'</th><td>';
	$content = gma_get_mail_template( 'gma_subscrible_new_comment_answer_followers_email', 'new-comment-answer-followers' );
	wp_editor( $content, 'gma_subscrible_new_comment_answer_followers_email', array(
		'wpautop'   => false,
		'tinymce' => array( 'content_css' => GMA_URI . 'assets-admin/css/email-template-editor.css' ),
	) );
	//echo '<p><input data-template="new-comment-answer-followers.html" type="button" class="button gma-reset-email-template" value="Reset Template"></p>';
	echo '<div class="description gma-parameters-wrapper">
		<p class="gma-available-parameters">' . __('Available parameters', 'give-me-answer-lite') . '</p>
		<strong>{site_name}</strong> - ' . __('Your site name', 'give-me-answer-lite') .'<br />
		<strong>{question_author}</strong> - ' . __('Question Author Name', 'give-me-answer-lite') .'<br />
		<strong>{comment_author}</strong> - ' . __('Comment Author Name', 'give-me-answer-lite') .'<br />
		<strong>{comment_author_link}</strong> - ' . __('Comment Author Link', 'give-me-answer-lite') .'<br />
		<strong>{question_title}</strong> - ' . __('Question Title', 'give-me-answer-lite') .'<br />
		<strong>{question_link}</strong> - ' . __('Question Link', 'give-me-answer-lite') .'<br />
		<strong>{comment_content}</strong> - ' . __('Comment Content', 'give-me-answer-lite') .'<br />
	</div>';
	echo '</td>';
}
// New Comment - Answer - Follow - Email Content

// End email setting html 

function gma_question_rewrite_display(){
	global  $gma_general_settings;
	echo '<p><input type="text" name="gma_options[question-rewrite]" id="gma_options_question_rewrite" value="'.( isset( $gma_general_settings['question-rewrite'] ) ? $gma_general_settings['question-rewrite'] : 'question' ).'" class="regular-text" /></p>';
}

function gma_question_category_rewrite_display(){
	global  $gma_general_settings;
	echo '<p><input type="text" name="gma_options[question-category-rewrite]" id="gma_options_question_category_rewrite" value="'.( isset( $gma_general_settings['question-category-rewrite'] ) ? $gma_general_settings['question-category-rewrite'] : 'question-category' ).'" class="regular-text" /></p>';
}

function gma_question_tag_rewrite_display(){
	global  $gma_general_settings;
	echo '<p><input type="text" name="gma_options[question-tag-rewrite]" id="gma_options_question_tag_rewrite" value="'.( isset( $gma_general_settings['question-tag-rewrite'] ) ? $gma_general_settings['question-tag-rewrite'] : 'question-tag' ).'" class="regular-text" /></p>';
}

function gma_permission_display(){
	global $gma;
	$perms = gma_lite()->permission->perms;
	$roles = get_editable_roles();
	?>
	<input type="hidden" id="reset-permission-nonce" name="reset-permission-nonce" value="<?php echo wp_create_nonce( '_gma_reset_permission' ); ?>">
	<h3><?php _e( 'Questions','give-me-answer-lite' ) ?></h3>
	<table class="table widefat gma-permission-settings">
		<thead>
			<tr>
				<th class="gma-col-w20"></th>
				<th><?php _e( 'Read','give-me-answer-lite' ) ?></th>
				<th><?php _e( 'Post','give-me-answer-lite' ) ?></th>
				<th><?php _e( 'Edit','give-me-answer-lite' ) ?></th>
				<th><?php _e( 'Delete','give-me-answer-lite' ) ?></th>
			</tr>
		</thead>
		<tbody>
		<?php foreach ( $roles as $key => $role ) : ?>
			<?php if ( $key == 'anonymous' ) continue; ?>
			<tr class="group available">
				<td><?php echo $roles[$key]['name'] ?></td>
				<td><input type="checkbox" <?php checked( true, ( isset( $perms[$key]['question']['read'] ) ? $perms[$key]['question']['read'] : false ) ); ?> name="gma_permission[<?php echo $key ?>][question][read]" value="1"></td>
				<td><input type="checkbox" <?php checked( true, ( isset( $perms[$key]['question']['post'] ) ? $perms[$key]['question']['post'] : false ) ); ?> name="gma_permission[<?php echo $key ?>][question][post]" value="1"></td>
				<td><input type="checkbox" <?php checked( true, ( isset( $perms[$key]['question']['edit'] ) ? $perms[$key]['question']['edit'] : false ) ); ?> name="gma_permission[<?php echo $key ?>][question][edit]" value="1"></td>
				<td><input type="checkbox" <?php checked( true, ( isset( $perms[$key]['question']['delete'] ) ? $perms[$key]['question']['delete'] : false ) ); ?> name="gma_permission[<?php echo $key ?>][question][delete]" value="1"></td>
			   
			</tr>
		<?php endforeach; ?>
			<tr class="group available">
				<td><?php _e( 'Anonymous','give-me-answer-lite' ) ?></td>

				<td><input type="checkbox" <?php checked( true, ( isset( $perms['anonymous']['question']['read'] ) ? $perms['anonymous']['question']['read'] : false ) ); ?> name="gma_permission[<?php echo 'anonymous' ?>][question][read]" value="1"></td>
				<td><input type="checkbox" <?php checked( true, ( isset( $perms['anonymous']['question']['post'] ) ? $perms['anonymous']['question']['post'] : false ) ); ?> name="gma_permission[<?php echo 'anonymous' ?>][question][post]" value="1"></td>
				<td><input type="checkbox" <?php checked( true, ( isset( $perms['anonymous']['question']['edit'] ) ? $perms['anonymous']['question']['edit'] : false ) ); ?> name="gma_permission[<?php echo 'anonymous' ?>][question][edit]" value="1" disabled="disabled"></td>
				<td><input type="checkbox" <?php checked( true, ( isset( $perms['anonymous']['question']['delete'] ) ? $perms['anonymous']['question']['delete'] : false ) ); ?> name="gma_permission[<?php echo 'anonymous' ?>][question][delete]" value="1" disabled="disabled"></td>
			</tr>
		</tbody>
	</table>
	<p class="reset-button-container align-right" style="text-align:right">
		<button data-type="question" class="button reset-permission" name="gma-permission-reset" value="question"><?php _e( 'Reset Default', 'give-me-answer-lite' ); ?></button>
	</p>
	<h3><?php _e( 'Answers', 'give-me-answer-lite' ); ?></h3>
	<table class="table widefat gma-permission-settings">
		<thead>
			<tr>
				<th class="gma-col-w20"></th>
				<th>Read</th>
				<th>Post</th>
				<th>Edit</th>
				<th>Delete</th>
			</tr>
		</thead>
		<tbody>
		<?php foreach ( $roles as $key => $role ) : ?>
			<?php if ( $key == 'anonymous' ) continue; ?>
			<tr class="group available">
				<td><?php echo $roles[$key]['name'] ?></td>
				<td><input type="checkbox" <?php checked( true, ( isset( $perms[$key]['answer']['read'] ) ? $perms[$key]['answer']['read'] : false ) ); ?> name="gma_permission[<?php echo $key ?>][answer][read]" value="1"></td>
				<td><input type="checkbox" <?php checked( true, ( isset( $perms[$key]['answer']['post'] ) ? $perms[$key]['answer']['post'] : false ) ); ?> name="gma_permission[<?php echo $key ?>][answer][post]" value="1"></td>
				<td><input type="checkbox" <?php checked( true, ( isset( $perms[$key]['answer']['edit'] ) ? $perms[$key]['answer']['edit'] : false ) ); ?> name="gma_permission[<?php echo $key ?>][answer][edit]" value="1"></td>
				<td><input type="checkbox" <?php checked( true, ( isset( $perms[$key]['answer']['delete'] ) ? $perms[$key]['answer']['delete'] : false ) ); ?> name="gma_permission[<?php echo $key ?>][answer][delete]" value="1"></td>

			</tr>
		<?php endforeach; ?>
			<tr class="group available">
				<td><?php _e( 'Anonymous','give-me-answer-lite' ) ?></td>

				<td><input type="checkbox" <?php checked( true, ( isset( $perms['anonymous']['answer']['read'] ) ? $perms['anonymous']['answer']['read'] : false ) ); ?> name="gma_permission[<?php echo 'anonymous' ?>][answer][read]" value="1"></td>
				<td><input type="checkbox" <?php checked( true, ( isset( $perms['anonymous']['answer']['post'] ) ? $perms['anonymous']['answer']['post'] : false ) ); ?> name="gma_permission[<?php echo 'anonymous' ?>][answer][post]" value="1"></td>
				<td><input type="checkbox" <?php checked( true, ( isset( $perms['anonymous']['answer']['edit'] ) ? $perms['anonymous']['answer']['edit'] : false ) ); ?> name="gma_permission[<?php echo 'anonymous' ?>][answer][edit]" value="1" disabled="disabled"></td>
				<td><input type="checkbox" <?php checked( true, ( isset( $perms['anonymous']['answer']['delete'] ) ? $perms['anonymous']['answer']['delete'] : false ) ); ?> name="gma_permission[<?php echo 'anonymous' ?>][answer][delete]" value="1" disabled="disabled"></td>
			</tr>
		</tbody>
	</table>
	<p class="reset-button-container align-right" style="text-align:right">
		<button data-type="answer" class="button reset-permission" name="gma-permission-reset" value="answer"><?php _e( 'Reset Default', 'give-me-answer-lite' ); ?></button>
	</p>
	<h3><?php _e( 'Comments','give-me-answer-lite' ) ?></h3>
	<table class="table widefat gma-permission-settings">
		<thead>
			<tr>
				<th class="gma-col-w20"></th>
				<th>Read</th>
				<th>Post</th>
				<th>Edit</th>
				<th>Delete</th>
			</tr>
		</thead>
		<tbody>
		<?php foreach ( $roles as $key => $role ) : ?>
			<?php if ( $key == 'anonymous' ) continue; ?>
			<tr class="group available">
				<td><?php echo $roles[$key]['name'] ?></td>
				<td><input type="checkbox" <?php checked( true, ( isset( $perms[$key]['comment']['read'] ) ? $perms[$key]['comment']['read'] : false ) ); ?> name="gma_permission[<?php echo $key ?>][comment][read]" value="1"></td>
				<td><input type="checkbox" <?php checked( true, ( isset( $perms[$key]['comment']['post'] ) ? $perms[$key]['comment']['post'] : false ) ); ?> name="gma_permission[<?php echo $key ?>][comment][post]" value="1"></td>
				<td><input type="checkbox" <?php checked( true, ( isset( $perms[$key]['comment']['edit'] ) ? $perms[$key]['comment']['edit'] : false ) ); ?> name="gma_permission[<?php echo $key ?>][comment][edit]" value="1"></td>
				<td><input type="checkbox" <?php checked( true, ( isset( $perms[$key]['comment']['delete'] ) ? $perms[$key]['comment']['delete'] : false ) ); ?> name="gma_permission[<?php echo $key ?>][comment][delete]" value="1"></td>
			</tr>
		<?php endforeach; ?>
			<tr class="group available">
				<td><?php _e( 'Anonymous','give-me-answer-lite' ) ?></td>
				<td><input type="checkbox" <?php checked( true, ( isset( $perms['anonymous']['comment']['read'] ) ? $perms['anonymous']['comment']['read'] : false ) ); ?> name="gma_permission[<?php echo 'anonymous' ?>][comment][read]" value="1"></td>
				<td><input type="checkbox" <?php checked( true, ( isset( $perms['anonymous']['comment']['post'] ) ? $perms['anonymous']['comment']['post'] : false ) ); ?> name="gma_permission[<?php echo 'anonymous' ?>][comment][post]" value="1"></td>
				<td><input type="checkbox" <?php checked( true, ( isset( $perms['anonymous']['comment']['edit'] ) ? $perms['anonymous']['comment']['edit'] : false ) ); ?> name="gma_permission[<?php echo 'anonymous' ?>][comment][edit]" value="1" disabled="disabled"></td>
				<td><input type="checkbox" <?php checked( true, ( isset( $perms['anonymous']['comment']['delete'] ) ? $perms['anonymous']['comment']['delete'] : false ) ); ?> name="gma_permission[<?php echo 'anonymous' ?>][comment][delete]" value="1" disabled="disabled"  ></td>
			</tr>
		</tbody>
	</table>

	<p class="reset-button-container align-right" style="text-align:right">
		<button data-type="comment" class="button reset-permission" name="gma-permission-reset" value="comment"><?php _e( 'Reset Default', 'give-me-answer-lite' ); ?></button>
	</p>
	<?php
}

//Captcha
function gma_captcha_in_question_display() {
	global $gma_general_settings;

	echo '<p><input type="checkbox" name="gma_options[captcha-in-question]"  id="gma_options_captcha_in_question" value="1" '.checked( 1, (isset($gma_general_settings['captcha-in-question'] ) ? $gma_general_settings['captcha-in-question'] : false ) , false ) .'></p>';
}

function gma_captcha_in_single_question_display() {
	global $gma_general_settings;
	
	echo '<p><input type="checkbox" name="gma_options[captcha-in-single-question]"  id="gma_options_captcha_in_question" value="1" '.checked( 1, (isset($gma_general_settings['captcha-in-single-question'] ) ? $gma_general_settings['captcha-in-single-question'] : false ) , false ) .'></p>';
}

function gma_captcha_google_pubic_key_display() {
	global $gma_general_settings;
	$public_key = isset( $gma_general_settings['captcha-google-public-key'] ) ?  $gma_general_settings['captcha-google-public-key'] : '';
	echo '<p><input type="text" name="gma_options[captcha-google-public-key]" value="'.$public_key.'" class="regular-text"></p>';
}

function gma_captcha_google_private_key_display() {
	global $gma_general_settings;
	$private_key = isset( $gma_general_settings['captcha-google-private-key'] ) ?  $gma_general_settings['captcha-google-private-key'] : '';
	echo '<p><input type="text" name="gma_options[captcha-google-private-key]" value="'.$private_key.'" class="regular-text"></p>';
}

function gma_captcha_select_type_display() {
	global $gma_general_settings;

	$types = apply_filters( 'gma_captcha_type', array( 'default' => __( 'Default', 'give-me-answer-lite' ) ) );
	$total = count( $types );
	$type_selected = isset( $gma_general_settings['captcha-type'] ) ? $gma_general_settings['captcha-type'] : 'default';
	echo '<select name="gma_options[captcha-type]">';
	foreach( $types as $key => $name ) {
		echo '<option '.selected( $key, $type_selected, false ).' value="'.$key.'">'.$name.'</option>';
	}
	echo '</select>';
}

function gma_tags_per_question_display(){
	global $gma_general_settings;
	$tags_per_question= isset( $gma_general_settings['tags-per-question'] ) ?  $gma_general_settings['tags-per-question'] : 5;
	echo '<p><input type="text" name="gma_options[tags-per-question]" class="small-text" value="'.$tags_per_question.'" ></p>';
}

function gma_max_image_size_display(){
	global $gma_general_settings;
	$max_image_size= isset( $gma_general_settings['max-image-size-kb'] ) ?  $gma_general_settings['max-image-size-kb'] : 100;
	echo '<p><input type="text" name="gma_options[max-image-size-kb]" class="small-text" value="'.$max_image_size.'" > <span class="description">'.__( 'kb','give-me-answer-lite' ).'</span></p>';
}

function gma_answer_per_page_display() {
	global $gma_general_settings;
	$posts_per_page = isset( $gma_general_settings['answer-per-page'] ) ?  $gma_general_settings['answer-per-page'] : 5;
	echo '<p><input id="gma_setting_answers_per_page" type="text" name="gma_options[answer-per-page]" class="small-text" value="'.$posts_per_page.'" > <span class="description">'.__( 'answer(s) per page','give-me-answer-lite' ).'</span></p>';
}

function gma_allow_anonymous_vote() {
	global $gma_general_settings;
	
	echo '<p><label for="gma_options_allow_anonymous_vote"><input type="checkbox" name="gma_options[allow-anonymous-vote]"  id="gma_options_allow_anonymous_vote" value="1" '.checked( 1, (isset($gma_general_settings['allow-anonymous-vote'] ) ? $gma_general_settings['allow-anonymous-vote'] : false ) , false ) .'></label></p>';
}


function gma_use_auto_closure() {
	global $gma_general_settings;
	
	echo '<p><label for="gma_options_use_auto_closure"><input type="checkbox" name="gma_options[use-auto-closure]"  id="gma_options_use_auto_closure" value="1" '.checked( 1, (isset($gma_general_settings['use-auto-closure'] ) ? $gma_general_settings['use-auto-closure'] : false ) , false ) .'><span class="description">'.__( 'Enable Auto Closure', 'give-me-answer-lite' ).'</span></label></p>';
}
function gma_number_day_auto_closure() {
	global $gma_general_settings;
	$number_day_auto_closure = isset( $gma_general_settings['number-day-auto-closure'] ) ?  $gma_general_settings['number-day-auto-closure'] : '';
	echo '<p><input id="gma_setting_number_day_auto_closure" type="text" name="gma_options[number-day-auto-closure]" class="medium-text" value="'.$number_day_auto_closure.'" > <span class="description">'.__( 'Days.(greater 0)','give-me-answer-lite' ).'</span></p>';
}



function gma_enable_review_question_mode() {
	global $gma_general_settings;
	
	echo '<p><label for="gma_options_enable_review_question"><input type="checkbox" name="gma_options[enable-review-question]"  id="gma_options_enable_review_question" value="1" '.checked( 1, (isset($gma_general_settings['enable-review-question'] ) ? $gma_general_settings['enable-review-question'] : false ) , false ) .'></label></p>';
}


function gma_show_all_answers() {
	global $gma_general_settings;

	echo '<p><label for="gma_options_gma_show_all_answers"><input type="checkbox" name="gma_options[show-all-answers-on-single-question-page]"  id="gma_options_gma_show_all_answers" value="1" '.checked( 1, (isset($gma_general_settings['show-all-answers-on-single-question-page'] ) ? $gma_general_settings['show-all-answers-on-single-question-page'] : false ) , false ) .'><span class="description">'.__( 'Show all answers on single question page.', 'give-me-answer-lite' ).'</span></label></p>';
}

function gma_single_template_options() {
	global $gma_general_settings;
	$selected = isset( $gma_general_settings['single-template'] ) ? $gma_general_settings['single-template'] : -1;
	$theme_path = trailingslashit( get_template_directory() );
	$files = scandir( $theme_path );
	?>
		<p><label for="gma_single_question_template">
				<select name="gma_options[single-template]" id="gma_single_question_template">
					<option <?php selected( $selected, -1 ); ?> value="-1"><?php _e( 'Select template for Single Quesiton page','give-me-answer-lite' ) ?></option>
					<?php foreach ( $files as $file ) : ?>
						<?php $ext = pathinfo( $file, PATHINFO_EXTENSION ); ?>
						<?php if ( is_dir( $file ) || strpos( $file, '.' === 0 ) || $ext != 'php' ) continue; ?>
					<option <?php selected( $selected, $file ); ?> value="<?php echo $file; ?>"><?php echo $file ?></option>
					<?php endforeach; ?>
				</select> <span class="description"><?php _e( 'By default, your single.php template file will be used if you do not choose any template', 'give-me-answer-lite' ) ?></span>
			</label>
		</p>
	<?php

}

function gma_permalink_section_layout() {
	printf( __( 'If you like, you may enter custom structure for your single question, question category and question tag URLs here. For example, using <code>topic</code> as your question base would make your question links like <code>%s</code>. If you leave these blank the default will be used.', 'give-me-answer-lite' ), home_url( 'topic/question-name/' ) );
}

function gma_get_rewrite_slugs() {
	global  $gma_general_settings;
	$gma_general_settings = get_option( 'gma_options' );
	
	$rewrite_slugs = array();

	$question_rewrite = get_option( 'gma-question-rewrite', 'question' );
	$question_rewrite = $question_rewrite ? $question_rewrite : 'question';
	if ( isset( $gma_general_settings['question-rewrite'] ) && $gma_general_settings['question-rewrite'] && $gma_general_settings['question-rewrite'] != $question_rewrite ) {
		$question_rewrite = $gma_general_settings['question-rewrite'];
		update_option( 'gma-question-rewrite', $question_rewrite );
	}

	$rewrite_slugs['question_rewrite'] = $question_rewrite;

	$question_category_rewrite = $gma_general_settings['question-category-rewrite'];
	$question_category_rewrite = $question_category_rewrite ? $question_category_rewrite : 'question-category';
	if ( isset( $gma_general_settings['question-category-rewrite'] ) && $gma_general_settings['question-category-rewrite'] && $gma_general_settings['question-category-rewrite'] != $question_category_rewrite ) {
		$question_category_rewrite = $gma_general_settings['question-category-rewrite'];
		update_option( 'gma-question-category-rewrite', $question_category_rewrite );
	}

	$rewrite_slugs['question_category_rewrite'] = $question_category_rewrite;

	$question_tag_rewrite = $gma_general_settings['question-tag-rewrite'];
	$question_tag_rewrite = $question_tag_rewrite ? $question_tag_rewrite : 'question-tag';
	if ( isset( $gma_general_settings['question-tag-rewrite'] ) && $gma_general_settings['question-tag-rewrite'] && $gma_general_settings['question-tag-rewrite'] != $question_tag_rewrite ) {
		$question_tag_rewrite = $gma_general_settings['question-tag-rewrite'];
		update_option( 'gma-question-tag-rewrite', $question_tag_rewrite );
	}
	$rewrite_slugs['question_tag_rewrite'] = $question_tag_rewrite;

	return $rewrite_slugs;
}


function gma_is_captcha_enable() {
	global $gma_general_settings;
	$public_key = isset( $gma_general_settings['captcha-google-public-key'] ) ?  $gma_general_settings['captcha-google-public-key'] : '';
	$private_key = isset( $gma_general_settings['captcha-google-private-key'] ) ?  $gma_general_settings['captcha-google-private-key'] : '';

	if ( ! $public_key || ! $private_key ) {
		return false;
	}
	return true;
}

function gma_is_captcha_enable_in_submit_question() {
	global $gma_general_settings;
	$captcha_in_question = isset( $gma_general_settings['captcha-in-question'] ) ? $gma_general_settings['captcha-in-question'] : false;
	
	if ( $captcha_in_question ) {
		return true;
	}
	return false;
}

function gma_is_captcha_enable_in_single_question() {
	global $gma_general_settings;
	$captcha_in_single_question = isset( $gma_general_settings['captcha-in-single-question'] ) ? $gma_general_settings['captcha-in-single-question'] : false;
	if ( $captcha_in_single_question ) {
		return true;
	} 
	return false;
}

function gma_is_enable_status() {
	global $gma_general_settings;

	if ( !isset( $gma_general_settings['disable-question-status'] ) || !$gma_general_settings['disable-question-status'] ) {
		return true;
	}

	return false;
}

class GMA_Settings {
	public function __construct(){
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		add_action( 'init', array( $this, 'init_options' ), 9 );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'updated_option', array( $this, 'update_options' ), 10, 3 );
		add_action( 'wp_loaded', array( $this, 'flush_rules' ) );
	}

	public function update_options( $option, $old_value, $value ) {
		if ( $option == 'gma_options' ) {
			if ( $old_value['pages']['archive-question'] != $value['pages']['archive-question']  ) {
				$questions_page_content = get_post_field( 'post_content', $value['pages']['archive-question'] );
				if ( strpos( $questions_page_content, '[gma-list-questions]' ) === false ) {
					$questions_page_content = str_replace( '[gma-submit-question-form]', '', $questions_page_content );
					wp_update_post( array(
						'ID'			=> $value['pages']['archive-question'],
						'post_content'	=> $questions_page_content . '[gma-list-questions]',
					) );
				}
			}

			if ( $old_value['pages']['submit-question'] != $value['pages']['submit-question'] ) {
				$submit_question_content = get_post_field( 'post_content', $value['pages']['submit-question'] );
				if ( strpos( $submit_question_content, '[gma-submit-question-form]' ) === false ) {
					$submit_question_content = str_replace( '[gma-list-questions]', '', $submit_question_content );
					wp_update_post( array(
						'ID'			=> $value['pages']['submit-question'],
						'post_content'	=> $submit_question_content . '[gma-submit-question-form]',
					) );
				}
			}
			
			// Flush rewrite when rewrite rule settings change
			flush_rewrite_rules();
		}
	}

	// Create admin menus for backend
	public function admin_menu(){
		global $gma_setting_page;
		$gma_setting_page = add_submenu_page( 'give-me-answer-lite', __( 'Plugin Settings','give-me-answer-lite' ), __( 'Settings','give-me-answer-lite' ), 'manage_options', 'gma-settings', array( $this, 'settings_display' )  );
	}

    /**
     * Initialize plugin options
     *
     * @since 1.0
     * @access public
     * @return void
     */
	public function init_options(){
		global $gma_options, $gma_general_settings, $gma_avatar;
		$gma_general_settings = $gma_options = wp_parse_args( get_option( 'gma_options' ), array(
		    'general'   => [
		        'maintenance'           => 0,
		        'disable-user-wall'     => 0,
		        'disable-share-socials' => 0,
		        'show-next-prev-qs'     => 0,
            ],
			'pages'     => array(
                'submit-question'   => 0,
                'archive-question'  => 0,
                'user-profile'      => 0,
			),
			'question'    => [
				'min-length'                     => 5,
				'max-length'                     => 150,
			],
			'answer'    => [
			    'moderation'                     => 0,
            ],
			'comment'   => [
				'min-length'                     => 5,
			],
			'vote'      => [
			    'status'    => [
			        'total'     => 1,
			        'question'  => 1,
			        'answer'    => 1,
			        'comment'   => 1,
                ],
            ],
			'pagination' => [
			    'archive'       => 15,
                'user-profile'  => 15,
            ],
			'avatar'                             => [
				'type'                      => 'square',
				'show-on-archive'           => 1,
				'show-on-single-question'   => 1,
				'show-on-single-answer'     => 1,
				'show-on-comment'           => 0,
				'max-size-kb'               => 100,
				'size'                      => [
					'archive'   => 24,
					'question'  => 24,
					'answer'    => 24,
					'comment'   => 12,
				],
            ],
			'editor'                             => [
			    'question'  => 'tinymce',
                'answer'    => 'tinymce',
                'comment'   => 'textarea',
            ],
			'question-category-rewrite'          => '',
			'question-tag-rewrite'               => '',
			'captcha-type'                       => 'simple',
			'captcha-in-single-question'         => false,
			'question-new-time-frame'            => 4,
			'max-image-size-kb'                  => 150,
			'show-archive-question-category'     => 1,
			'show-archive-filter'                => 1,
			'show-archive-search'                => 1,
			'show-archive-viewbox'               => 1,
			'show-archive-votebox'               => 1,
			'show-archive-answerbox'             => 1,
			'show-archive-tags'                  => 1,
			'use-predefined-tags-for-question'   => 0,
			'close-has-best-answer-question'     => 0,
			'tags-per-question'                          => 10,
			'min-tags-per-question'                      => 1,
			'posts-per-page'                             => 15,
			'show-all-answers-on-single-question-page'   => 1,
			'private-question'                   => 0,
			'submit-question-display-category'   => 1,
			'submit-question-display-tags'       => 1,
		) );

		$gma_avatar = $gma_general_settings['avatar'];
	}

    /**
     * Flush rewrite rules
     *
     * @since 1.0
     * @access public
     * @return void
     */
	public function flush_rules() {
		if ( isset( $_GET['page'] ) && 'gma-settings' == esc_html( $_GET['page'] ) ) {
			flush_rewrite_rules();
		}
	}

	public function current_email_tab() {
		if ( isset( $_GET['tab'] ) && 'email' == esc_html( $_GET['tab'] ) ) {
			return isset( $_GET['section'] ) ? esc_html( $_GET['section'] ) : 'general';
		}

		return false;
	}

    public function current_general_tab() {
		if ( isset( $_GET['tab'] ) && 'general' == esc_html( $_GET['tab'] ) ) {
			return isset( $_GET['section'] ) ? esc_html( $_GET['section'] ) : 'general';
		}
		return false;
	}

    public function current_status_tab() {
        if ( isset( $_GET['tab'] ) && 'status' == esc_html( $_GET['tab'] ) ) {
            return isset( $_GET['section'] ) ? esc_html( $_GET['section'] ) : 'system';
        }
        return false;
    }

    public function status_tabs() {
        $section = $this->current_status_tab();
        ob_start();
        ?>
        <ul class="subsubsub">
            <li class="<?php echo $section == 'system' ? 'active' : '' ?>"><a href="<?php echo add_query_arg( 'section', 'system', admin_url( 'admin.php?page=gma-settings&tab=status' ) ) ?>"><?php _e( 'System', 'give-me-answer-lite' ) ?></a> &#124; </li>
            <li class="<?php echo $section == 'tools' ? 'active' : '' ?>"><a href="<?php echo add_query_arg( 'section', 'tools', admin_url( 'admin.php?page=gma-settings&tab=status' ) ) ?>"><?php _e( 'Tools', 'give-me-answer-lite' ) ?></a></li>
            <?php do_action( 'gma_settings_status_tabs' ); ?>
        </ul>
        <div class="clear"></div>
        <?php
        return ob_get_clean();
    }

    public function email_tabs() {
        $section = $this->current_email_tab();
        ob_start();
        ?>
        <ul class="subsubsub">
            <li class="<?php echo $section == 'general' ? 'active' : '' ?>"><a href="<?php echo add_query_arg( 'section', 'general', admin_url( 'admin.php?page=gma-settings&tab=email' ) ) ?>"><?php _e( 'Email Settings', 'give-me-answer-lite' ) ?></a> &#124; </li>
            <li class="<?php echo $section == 'new-question' ? 'active' : '' ?>"><a href="<?php echo add_query_arg( 'section', 'new-question', admin_url( 'admin.php?page=gma-settings&tab=email' ) ) ?>"><?php _e( 'New Question', 'give-me-answer-lite' ) ?></a> &#124; </li>
            <li class="<?php echo $section == 'new-answer' ? 'active' : '' ?>"><a href="<?php echo add_query_arg( 'section', 'new-answer', admin_url( 'admin.php?page=gma-settings&tab=email' ) ) ?>"><?php _e( 'New Answer', 'give-me-answer-lite' ) ?></a> &#124; </li>
            <li class="<?php echo $section == 'new-comment' ? 'active' : '' ?>"><a href="<?php echo add_query_arg( 'section', 'new-comment', admin_url( 'admin.php?page=gma-settings&tab=email' ) ) ?>"><?php _e( 'New Comment', 'give-me-answer-lite' ) ?></a> &#124; </li>
            <?php do_action( 'gma_settings_email_tabs' ); ?>
        </ul>
        <div class="clear"></div>
        <?php
        return ob_get_clean();
    }

	public function general_tabs() {
		$section = $this->current_general_tab();
		ob_start();
		?>
        <ul class="subsubsub">
            <li class="<?php echo $section == 'general' || $section == '' ? 'active' : '' ?>"><a href="<?php echo add_query_arg( 'section', 'general', admin_url( 'admin.php?page=gma-settings&tab=general' ) ) ?>"><?php _e( 'General', 'give-me-answer-lite' ) ?></a> &#124; </li>
            <li class="<?php echo $section == 'pages' ? 'active' : '' ?>"><a href="<?php echo add_query_arg( 'section', 'pages', admin_url( 'admin.php?page=gma-settings&tab=general' ) ) ?>"><?php _e( 'Pages', 'give-me-answer-lite' ) ?></a> &#124; </li>
            <li class="<?php echo $section == 'archive' ? 'active' : '' ?>"><a href="<?php echo add_query_arg( 'section', 'archive', admin_url( 'admin.php?page=gma-settings&tab=general' ) ) ?>"><?php _e( 'Archive', 'give-me-answer-lite' ) ?></a> &#124; </li>
            <li class="<?php echo $section == 'question' ? 'active' : '' ?>"><a href="<?php echo add_query_arg( 'section', 'question', admin_url( 'admin.php?page=gma-settings&tab=general' ) ) ?>"><?php _e( 'Question', 'give-me-answer-lite' ) ?></a> &#124; </li>
            <li class="<?php echo $section == 'answer' ? 'active' : '' ?>"><a href="<?php echo add_query_arg( 'section', 'answer', admin_url( 'admin.php?page=gma-settings&tab=general' ) ) ?>"><?php _e( 'Answer', 'give-me-answer-lite' ) ?></a> &#124; </li>
            <li class="<?php echo $section == 'comment' ? 'active' : '' ?>"><a href="<?php echo add_query_arg( 'section', 'comment', admin_url( 'admin.php?page=gma-settings&tab=general' ) ) ?>"><?php _e( 'Comment', 'give-me-answer-lite' ) ?></a> &#124; </li>
            <li class="<?php echo $section == 'editor' ? 'active' : '' ?>"><a href="<?php echo add_query_arg( 'section', 'editor', admin_url( 'admin.php?page=gma-settings&tab=general' ) ) ?>"><?php _e( 'Editors', 'give-me-answer-lite' ) ?></a> &#124; </li>
            <li class="<?php echo $section == 'vote' ? 'active' : '' ?>"><a href="<?php echo add_query_arg( 'section', 'vote', admin_url( 'admin.php?page=gma-settings&tab=general' ) ) ?>"><?php _e( 'Vote', 'give-me-answer-lite' ) ?></a> &#124; </li>
            <li class="<?php echo $section == 'captcha' ? 'active' : '' ?>"><a href="<?php echo add_query_arg( 'section', 'captcha', admin_url( 'admin.php?page=gma-settings&tab=general' ) ) ?>"><?php _e( 'Captcha', 'give-me-answer-lite' ) ?></a> &#124; </li>
            <li class="<?php echo $section == 'pagination' ? 'active' : '' ?>"><a href="<?php echo add_query_arg( 'section', 'pagination', admin_url( 'admin.php?page=gma-settings&tab=general' ) ) ?>"><?php _e( 'Pagination', 'give-me-answer-lite' ) ?></a> &#124; </li>
            <li class="<?php echo $section == 'permalink' ? 'active' : '' ?>"><a href="<?php echo add_query_arg( 'section', 'permalink', admin_url( 'admin.php?page=gma-settings&tab=general' ) ) ?>"><?php _e( 'Permalink', 'give-me-answer-lite' ) ?></a></li>
            <?php do_action( 'gma_settings_general_tabs' ); ?>
        </ul>
        <div class="clear"></div>
		<?php
		return ob_get_clean();
	}


	public function register_settings(){
		global $gma_general_settings;

		//Register Setting Sections
		add_settings_section(
			'gma-general-settings',
			__( 'General Settings', 'give-me-answer-lite' ),
			null,
			'gma-settings'
		);

		add_settings_field(
			'gma_options[general][maintenance]',
			__('Maintenance Mode', 'give-me-answer-lite'),
			function() {
                global $gma_general_settings;
                ?>
                <label for="gma_options_maintenance">
                    <input
                            type="checkbox"
                            name="gma_options[general][maintenance]"
                            id="gma_options_maintenance"
                            value="1"
						<?php checked( $gma_general_settings[ 'general' ]['maintenance'], 1 ); ?>
                    >
                </label>
                <?php
            },
			'gma-settings',
			'gma-general-settings',
			[
				'label_for' => 'gma_options_maintenance',
			]
		);

        add_settings_field(
            'gma_options[general][disable-share-socials]',
            __('Disable share in socials', 'give-me-answer-lite'),
            function() {
                global $gma_general_settings;
                ?>

                <label for="gma_options_disable_share_socials">
                    <input
                        type="checkbox"
                        name="gma_options[general][disable-share-socials]"
                        id="gma_options_disable_share_socials"
                        value="1"
                        <?php checked( $gma_general_settings[ 'general' ]['disable-share-socials'], 1 ); ?>
                    >
                </label>
                <?php
            },
            'gma-settings',
            'gma-general-settings',
            [
                'label_for' => 'gma_options_disable_share_socials',
            ]
        );

        add_settings_field(
            'gma_options[general][show-next-prev-qs]',
            __('Show Next/Prev Question Button', 'give-me-answer-lite'),
            function() {
                global $gma_general_settings;
                ?>

                <label for="gma_options_show_next_prev_qs_btn">
                    <input
                        type="checkbox"
                        name="gma_options[general][show-next-prev-qs]"
                        id="gma_options_show_next_prev_qs_btn"
                        value="1"
                        <?php checked( $gma_general_settings[ 'general' ]['show-next-prev-qs'], 1 ); ?>
                    >
                </label>
                <?php
            },
            'gma-settings',
            'gma-general-settings'
        );

		do_action( 'gma_settings_general_general' );

		add_settings_section( 
			'gma-pages-settings',
			__( 'Page Settings', 'give-me-answer-lite' ),
			null, 
			'gma-settings'
		);

		add_settings_field( 
			'gma_options[pages][archive-question]',
			__('Archive Questions Page', 'give-me-answer-lite'),
			'gma_pages_settings_display',
			'gma-settings',
			'gma-pages-settings',
            [
               'label_for' => 'gma_options[pages][archive-question]',
            ]
		);

		add_settings_field(
			'gma_options[pages][submit-question]',
			__('Submit Question Page', 'give-me-answer-lite'),
			'gma_submit_question_page_display',
			'gma-settings',
			'gma-pages-settings',
			[
				'label_for' => 'gma_options[pages][submit-question]'
			]
		);

		add_settings_field(
			'gma_options[pages][user-profile]',
			__('User Profile Page', 'give-me-answer-lite'),
			'gma_user_profile_page_display',
			'gma-settings',
			'gma-pages-settings',
			[
				'label_for' => 'gma_options[pages][user-profile]'
			]
		);

		do_action( 'gma_settings_general_pages' );

		// Question Settings
		add_settings_section(
			'gma-archive-settings',
			__( 'Archive questions', 'give-me-answer-lite' ),
			false,
			'gma-settings'
		);


		add_settings_field(
			'gma_options[show-archive-filter]',
			__( 'Display Questions Filter','give-me-answer-lite' ),
			function() {
			    global $gma_general_settings;
                ?>
                <p>
                    <label for="gma_options_show_archive_filter">
                        <input
                           type="checkbox"
                           name="gma_options[show-archive-filter]"
                           id="gma_options_show_archive_filter"
                           value="1"
                           <?php checked( $gma_general_settings[ 'show-archive-filter' ], 1 ); ?>
                        >
                    </label>
                </p>
                <?php
            },
			'gma-settings',
			'gma-archive-settings'
		);

		add_settings_field(
			'gma_options[show-archive-search]',
			__( 'Display Search Box','give-me-answer-lite' ),
			function() {
			    global $gma_general_settings;
				?>
                <p>
                    <label for="gma_options_show_archive_search">
                        <input
                           type="checkbox"
                           name="gma_options[show-archive-search]"
                           id="gma_options_show_archive_search"
                           value="1"
	                       <?php checked( $gma_general_settings[ 'show-archive-search' ], 1 ); ?>
                        >
                    </label>
                </p>
				<?php
			},
			'gma-settings',
			'gma-archive-settings'
		);

		add_settings_field(
			'gma_options[show-archive-votebox]',
			__( 'Dispaly Vote Box','give-me-answer-lite' ),
			function() {
				global $gma_general_settings;
				?>
                <p>
                    <label for="gma_options_show_archive_votebox">
                        <input
                            type="checkbox"
                            name="gma_options[show-archive-votebox]"
                            id="gma_options_show_archive_votebox"
                            value="1"
							<?php checked( $gma_general_settings[ 'show-archive-votebox' ], 1 ); ?>
                        >
                    </label>
                </p>
				<?php
			},
			'gma-settings',
			'gma-archive-settings'
		);

		add_settings_field(
			'gma_options[show-archive-answerbox]',
			__( 'Display Answer Box','give-me-answer-lite' ),
			function() {
				global $gma_general_settings;
				?>
                <p>
                    <label for="gma_options_show_archive_answerbox">
                        <input
                            type="checkbox"
                            name="gma_options[show-archive-answerbox]"
                            id="gma_options_show_archive_answerbox"
                            value="1"
							<?php checked( $gma_general_settings[ 'show-archive-answerbox' ], 1 ); ?>
                        >
                    </label>
                </p>
				<?php
			},
			'gma-settings',
			'gma-archive-settings'
		);

		add_settings_field(
			'gma_options[show-archive-viewbox]',
			__( 'Display View Box','give-me-answer-lite' ),
			function() {
				global $gma_general_settings;
				?>
                <p>
                    <label for="gma_options_show_archive_viewbox">
                        <input
                                type="checkbox"
                                name="gma_options[show-archive-viewbox]"
                                id="gma_options_show_archive_viewbox"
                                value="1"
							<?php checked( $gma_general_settings[ 'show-archive-viewbox' ], 1 ); ?>
                        >
                    </label>
                </p>
				<?php
			},
			'gma-settings',
			'gma-archive-settings'
		);

		add_settings_field(
			'gma_options[show-archive-tags]',
			__( 'Display Tags','give-me-answer-lite' ),
			function() {
				global $gma_general_settings;
				?>
                <p>
                    <label for="gma_options_show_archive_tags">
                        <input
                            type="checkbox"
                            name="gma_options[show-archive-tags]"
                            id="gma_options_show_archive_tags"
                            value="1"
							<?php checked( $gma_general_settings[ 'show-archive-tags' ], 1 ); ?>
                        >
                    </label>
                </p>
				<?php
			},
			'gma-settings',
			'gma-archive-settings'
		);

		add_settings_field(
			'gma_options[show-archive-question-category]',
			__( 'Show Question Category','give-me-answer-lite' ),
			function() {
				global $gma_general_settings;
				?>
                <p>
                    <label for="gma_options_show_archive_question_category">
                        <input
                            type="checkbox"
                            name="gma_options[show-archive-question-category]"
                            id="gma_options_show_archive_question_category"
                            value="1"
							<?php checked( $gma_general_settings[ 'show-archive-question-category' ], 1 ); ?>
                        >
                    </label>
                </p>
				<?php
			},
			'gma-settings',
			'gma-archive-settings'
		);



		do_action( 'gma_settings_general_archive' );


        add_settings_section(
            'gma-pages-settings',
            __( 'Page Settings', 'give-me-answer-lite' ),
            null,
            'gma-settings'
        );


		// Question Settings
		add_settings_section(
			'gma-misc-settings',
			__( 'Question Settings', 'give-me-answer-lite' ),
			false,
			'gma-settings'
		);

		add_settings_field(
			'gma_options[question][min-length]',
			__( 'Minimum Title Length','give-me-answer-lite' ),
			function() {
			    global $gma_general_settings;
			    ?>
                <p>
                    <input
                        type="text"
                        name="gma_options[question][min-length]"
                        class="small-text"
                        value="<?php echo isset( $gma_general_settings[ 'question' ]['min-length'] ) ? $gma_general_settings['question']['min-length'] : '15'; ?>"
                    >
                    <span class="description"><?php _e('char(s)', 'give-me-answer-lite'); ?></span>
                </p>
                <?php
            },
			'gma-settings',
			'gma-misc-settings'
		);

		add_settings_field(
			'gma_options[question][max-length]',
			__( 'Maximum Title Length','give-me-answer-lite' ),
			function() {
				global $gma_general_settings;
				?>
                <p>
                    <input
                        type="text"
                        name="gma_options[question][max-length]"
                        class="small-text"
                        value="<?php echo isset( $gma_general_settings[ 'question' ]['max-length'] ) ? $gma_general_settings['question']['max-length'] : '120'; ?>"
                    >
                    <span class="description"><?php _e('char(s)', 'give-me-answer-lite'); ?></span>
                </p>
				<?php
			},
			'gma-settings',
			'gma-misc-settings'
		);

		add_settings_field(
			'gma_options[min-tags-per-question]',
			__( 'Minimum Tags','give-me-answer-lite' ),
			function() {
			    global $gma_general_settings;
			    ?>
                <p>
                    <input
                        type="text"
                        name="gma_options[min-tags-per-question]"
                        class="small-text"
                        value="<?php echo esc_attr( $gma_general_settings[ 'min-tags-per-question' ] ); ?>"
                    >
                </p>
                <?php
            },
			'gma-settings',
			'gma-misc-settings'
		);

		add_settings_field(
			'gma_options[tags-per-question]',
			__( 'Maximum Tags','give-me-answer-lite' ),
			'gma_tags_per_question_display',
			'gma-settings',
			'gma-misc-settings'
		);

		add_settings_field(
			'gma_options[max-image-size-kb]',
			__( 'Maximum size of image','give-me-answer-lite' ),
			'gma_max_image_size_display',
			'gma-settings',
			'gma-misc-settings'
		);



        add_settings_field(
            'gma_options[submit-question-display-category]',
            __( 'Display Category Box','give-me-answer-lite' ),
            function() {
                global $gma_general_settings;
                ?>
                <p>
                    <label for="gma_options_submit_question_display_category">
                        <input
                            type="checkbox"
                            name="gma_options[submit-question-display-category]"
                            id="gma_options_submit_question_display_category"
                            value="1"
                            <?php checked( $gma_general_settings[ 'submit-question-display-category' ], 1 ); ?>
                        >
                    </label>
                </p>
                <?php
            },
            'gma-settings',
            'gma-misc-settings'
        );

        add_settings_field(
            'gma_options[submit-question-display-tags]',
            __( 'Display Tags Box','give-me-answer-lite' ),
            function() {
                global $gma_general_settings;
                ?>
                <p>
                    <label for="gma_options_submit_question_display_tags">
                        <input
                                type="checkbox"
                                name="gma_options[submit-question-display-tags]"
                                id="gma_options_submit_question_display_tags"
                                value="1"
                            <?php checked( $gma_general_settings[ 'submit-question-display-tags' ], 1 ); ?>
                        >
                    </label>
                </p>
                <?php
            },
            'gma-settings',
            'gma-misc-settings'
        );

        add_settings_field(
            'gma_options[use-predefined-tags-for-question]',
            __( 'Use Predefined Tags','give-me-answer-lite' ),
            function() {
                global $gma_general_settings;
                ?>
                <p>
                    <label for="gma_options_use_predefined_tags_for_question">
                        <input
                                type="checkbox"
                                name="gma_options[use-predefined-tags-for-question]"
                                id="gma_options_use_predefined_tags_for_question"
                                value="1"
                            <?php checked( $gma_general_settings[ 'use-predefined-tags-for-question' ], 1 ); ?>
                        >
                    </label>
                </p>
                <?php
            },
            'gma-settings',
            'gma-misc-settings'
        );


        add_settings_field(
			'gma_options[enable-review-question]',
			__( 'Manually Approved Questions', 'give-me-answer-lite' ),
			'gma_enable_review_question_mode',
			'gma-settings',
			'gma-misc-settings'
		);

        add_settings_field(
            'gma_options[close-has-best-answer-question]',
            __( 'Close questions that have the best answer','give-me-answer-lite' ),
            function() {
                global $gma_general_settings;
                ?>
                <p>
                    <label for="gma_options_close_has_best_answer_question">
                        <input
                                type="checkbox"
                                name="gma_options[close-has-best-answer-question]"
                                id="gma_options_close_has_best_answer_question"
                                value="1"
                            <?php checked( $gma_general_settings[ 'close-has-best-answer-question' ], 1 ); ?>
                        >
                    </label>
                </p>
                <?php
            },
            'gma-settings',
            'gma-misc-settings'
        );

        add_settings_field(
            'gma_options[private-question]',
            __( 'Allow users to send private questions','give-me-answer-lite' ),
            function() {
                global $gma_general_settings;
                ?>
                <p>
                    <label for="gma_options_allow_users_to_send_private_questions">
                        <input
                            type="checkbox"
                            name="gma_options[private-question]"
                            id="gma_options_allow_users_to_send_private_questions"
                            value="1"
                            <?php checked( $gma_general_settings[ 'private-question' ], 1 ); ?>
                        >
                    </label>
                </p>
                <?php
            },
            'gma-settings',
            'gma-misc-settings'
        );

		do_action( 'gma_settings_general_ask_question' );

		// Answer Settings
		add_settings_section(
			'gma-answer-settings',
			__( 'Answer Settings', 'give-me-answer-lite' ),
			false,
			'gma-settings'
		);

		add_settings_field(
			'gma_options[show-all-answers-on-single-question-page]',
			__( 'Answer Listing', 'give-me-answer-lite' ),
			'gma_show_all_answers',
			'gma-settings',
			'gma-answer-settings'
		);

		add_settings_field( 
			'gma_options[answer-per-page]',
			false, 
			'gma_answer_per_page_display',
			'gma-settings',
			'gma-answer-settings'
		);

        add_settings_field(
            'gma_options[answer-moderation]',
            false,
            function() {
                global $gma_general_settings;
                $moderation = isset( $gma_general_settings[ 'answer' ]['moderation'] ) ? $gma_general_settings[ 'answer' ]['moderation'] : 0;
                ?>
                    <p>
                        <input
                            type="checkbox"
                            name="gma_options[answer][moderation]"
                            id="gma_options_answer_moderation"
                            value="1"
                            <?php checked( $moderation, 1 ); ?>
                        >
                        <label for="gma_options_answer_moderation">
                            <?php _e('Manually Approved Answers', 'give-me-answer-lite'); ?>
                        </label>
                    </p>
                <?php
            },
            'gma-settings',
            'gma-answer-settings'
        );

		do_action( 'gma_settings_general_answer' );

		// Comment Settings
		add_settings_section(
			'gma-comment-settings',
			__( 'Comment settings', 'give-me-answer-lite' ),
			false,
			'gma-settings'
		);

		add_settings_field(
			'gma_options[comment][min-length]',
			__('Minimum length of comment', 'give-me-answer-lite'),
			function() {
				global $gma_general_settings;
				?>
                <p>
                    <input
                            type="text"
                            name="gma_options[comment][min-length]"
                            class="small-text"
                            value="<?php echo isset($gma_general_settings['comment']['min-length']) ? $gma_general_settings['comment']['min-length'] : 10; ?>"
                    >
                    <span class="description">
                        <?php _e('character(s)', 'give-me-answer-lite'); ?>
                    </span>
                </p>
				<?php
			},
			'gma-settings',
			'gma-comment-settings'
		);


		do_action( 'gma_settings_general_comment' );


        // Comment Settings
        add_settings_section(
            'gma-editor-settings',
            __( 'Editor settings', 'give-me-answer-lite' ),
            false,
            'gma-settings'
        );

        add_settings_field(
            'gma_options[editor][question]',
            __( 'Question Editor','give-me-answer-lite' ),
            function() {
                global $gma_general_settings;
                ?>
                <p>
                    <label for="gma_options_qs_editor_name">
                        <select id="gma_options_qs_editor_name" name="gma_options[editor][question]">
                            <option value="simple" <?php selected( $gma_general_settings['editor']['question'], 'simple' ); ?>>
                                <?php _e('Simple', 'give-me-answer-lite'); ?>
                            </option>
                            <option value="tinymce" <?php selected( $gma_general_settings['editor']['question'], 'tinymce' ); ?>>
                                <?php _e('tinyMCE', 'give-me-answer-lite'); ?>
                            </option>
                            <?php do_action( 'gma_editors', 'question' ); ?>
                        </select>
                    </label>
                </p>
                <?php
            },
            'gma-settings',
            'gma-editor-settings'
        );

        add_settings_field(
            'gma_options[editor][answer]',
            __( 'Answer Editor','give-me-answer-lite' ),
            function() {
                global $gma_general_settings;
                ?>
                <p>
                    <label for="gma_options_ans_editor_name">
                        <select id="gma_options_ans_editor_name" name="gma_options[editor][answer]">
                            <option value="simple" <?php selected( $gma_general_settings['editor']['answer'], 'simple' ); ?>>
                                <?php _e('Simple', 'give-me-answer-lite'); ?>
                            </option>
                            <option value="tinymce" <?php selected( $gma_general_settings['editor']['answer'], 'tinymce' ); ?>>
                                <?php _e('tinyMCE', 'give-me-answer-lite'); ?>
                            </option>
                            <?php do_action( 'gma_editors', 'answer' ); ?>
                        </select>
                    </label>
                </p>
                <?php
            },
            'gma-settings',
            'gma-editor-settings'
        );

        add_settings_field(
            'gma_options[editor][comment]',
            __( 'Comment Editor','give-me-answer-lite' ),
            function() {
                global $gma_general_settings;
                ?>
                <p>
                    <label for="gma_options_cm_editor_name">
                        <select id="gma_options_cm_editor_name" name="gma_options[editor][comment]">
                            <option value="simple" <?php selected( $gma_general_settings['editor']['comment'], 'simple' ); ?>>
                                <?php _e('Simple', 'give-me-answer-lite'); ?>
                            </option>
                        </select>
                    </label>
                </p>
                <?php
            },
            'gma-settings',
            'gma-editor-settings'
        );

        // Vote Settings
		add_settings_section(
			'gma-vote-settings',
			__( 'Vote Settings', 'give-me-answer-lite' ),
			false,
			'gma-settings'
		);

		add_settings_field(
			'gma_options[allow-anonymous-vote]',
			__( 'Allow Anonymous Vote', 'give-me-answer-lite' ),
			'gma_allow_anonymous_vote',
			'gma-settings',
			'gma-vote-settings'
		);

		do_action( 'gma_settings_general_voting' );
		
		//Auto closure Settings
		add_settings_section(
			'gma-auto-closure-settings',
			__( 'Auto Closure Settings', 'give-me-answer-lite' ),
			false,
			'gma-settings'
		);

		add_settings_field(
			'gma_options[use-auto-closure]',
			__( 'Use Auto Closure', 'give-me-answer-lite' ),
			'gma_use_auto_closure',
			'gma-settings',
			'gma-auto-closure-settings'
		);
		add_settings_field(
			'gma_options[number-day-auto-closure]',
			__( 'Closure after', 'give-me-answer-lite' ),
			'gma_number_day_auto_closure',
			'gma-settings',
			'gma-auto-closure-settings'
		);
		
		//Captcha Setting
		add_settings_section( 
			'gma-captcha-settings',
			__( 'Captcha Settings','give-me-answer-lite' ),
			null, 
			'gma-settings'
		);

		add_settings_field( 
			'gma_options[captcha-type]',
			__( 'Type', 'give-me-answer-lite' ),
			'gma_captcha_select_type_display',
			'gma-settings',
			'gma-captcha-settings'
		);

		add_settings_field( 
			'gma_options[captcha-in-question]',
			__( 'Enable In Ask Question Page', 'give-me-answer-lite' ),
			'gma_captcha_in_question_display',
			'gma-settings',
			'gma-captcha-settings'
		);

		add_settings_field( 
			'gma_options[captcha-in-single-question]',
			__( 'Enable In Single Question Page', 'give-me-answer-lite' ),
			'gma_captcha_in_single_question_display',
			'gma-settings',
			'gma-captcha-settings'
		);

		do_action( 'gma_settings_general_captcha' );

        // Pagination
        add_settings_section(
            'gma-pagination-settings',
            __( 'Pagination Settings','give-me-answer-lite' ),
            false,
            'gma-settings'
        );

        add_settings_field(
            'gma_options[pagination][archive]',
            __( 'Archive Page', 'give-me-answer-lite' ),
            function() {
                global $gma_general_settings;
                $value = isset( $gma_general_settings['pagination']['archive'] ) ?  $gma_general_settings['pagination']['archive'] : 15;
                echo '<p><input type="text" name="gma_options[pagination][archive]" class="small-text" value="'.esc_attr( $value ).'" ></p>';
            },
            'gma-settings',
            'gma-pagination-settings'
        );

        add_settings_field(
            'gma_options[pagination][user-profile]',
            __( 'User Profile Page', 'give-me-answer-lite' ),
            function() {
                global $gma_general_settings;
                $value = isset( $gma_general_settings['pagination']['user-profile'] ) ?  $gma_general_settings['pagination']['user-profile'] : 15;
                echo '<p><input type="text" name="gma_options[pagination][user-profile]" class="small-text" value="'.esc_attr( $value ).'" ></p>';
            },
            'gma-settings',
            'gma-pagination-settings'
        );

		//Permalink
		add_settings_section( 
			'gma-permalink-settings',
			__( 'Permalink Settings','give-me-answer-lite' ),
			'gma_permalink_section_layout',
			'gma-settings'
		);

		add_settings_field( 
			'gma_options[question-rewrite]',
			__( 'Question Base', 'give-me-answer-lite' ),
			'gma_question_rewrite_display',
			'gma-settings',
			'gma-permalink-settings'
		);

		add_settings_field( 
			'gma_options[question-category-rewrite]',
			__( 'Question Category Base', 'give-me-answer-lite' ),
			'gma_question_category_rewrite_display',
			'gma-settings',
			'gma-permalink-settings'
		);

		add_settings_field( 
			'gma_options[question-tag-rewrite]',
			__( 'Question Tag Base', 'give-me-answer-lite' ),
			'gma_question_tag_rewrite_display',
			'gma-settings',
			'gma-permalink-settings'
		);

		do_action( 'gma_settings_general_permalink' );

		register_setting( 'gma-settings', 'gma_options' );
		
		add_settings_section( 
			'gma-subscribe-settings',
			false,
			false,
			'gma-email'
		);

		add_settings_section(
			'gma-subscribe-settings-new-question',
			false,
			false,
			'gma-email'
		);

        add_settings_section(
            'gma-subscribe-settings-new-answer',
            false,
            false,
            'gma-email'
        );

		add_settings_section(
			'gma-subscribe-settings-new-comment',
			false,
			false,
			'gma-email'
		);

		
		register_setting( 'gma-subscribe-settings-new-question', 'gma_subscrible_sendto_address' );

		register_setting( 'gma-subscribe-settings-new-question', 'gma_subscrible_cc_address' );

		register_setting( 'gma-subscribe-settings-new-question', 'gma_subscrible_bcc_address' );

		// Bcc address setting
		add_settings_field( 
			'gma_subscrible_from_address',
			__( 'From Email', 'give-me-answer-lite' ),
			array( $this, 'email_from_address_display' ), 
			'gma-email',
			'gma-subscribe-settings'
		);
		register_setting( 'gma-subscribe-settings', 'gma_subscrible_from_address' );

		//add delay email(need to speed up )
		add_settings_field( 
			'gma_enable_email_delay',
			false, 
			array( $this, 'enable_email_delay' ), 
			'gma-email',
			'gma-subscribe-settings'
		);
		register_setting( 'gma-subscribe-settings', 'gma_enable_email_delay' );

		// Send copy
		add_settings_field( 
			'gma_subscrible_send_copy_to_admin',
			false, 
			array( $this, 'email_send_copy_to_admin' ), 
			'gma-email',
			'gma-subscribe-settings'
		);
		register_setting( 'gma-subscribe-settings', 'gma_subscrible_send_copy_to_admin' );


		register_setting( 'gma-subscribe-settings', 'gma_subscrible_email_logo' );

		

		//New Question Email Notify
		register_setting( 'gma-subscribe-settings-new-question', 'gma_subscrible_new_question_email' );
		register_setting( 'gma-subscribe-settings-new-question', 'gma_subscrible_new_question_email_subject' );
		register_setting( 'gma-subscribe-settings-new-question', 'gma_subscrible_enable_new_question_notification' );

		// New Answer Email Notify
		register_setting( 'gma-subscribe-settings-new-answer', 'gma_subscrible_new_answer_email' );
		register_setting( 'gma-subscribe-settings-new-answer', 'gma_subscrible_new_answer_email_subject' );
		register_setting( 'gma-subscribe-settings-new-answer', 'gma_subscrible_enable_new_answer_notification' );
		register_setting( 'gma-subscribe-settings-new-answer', 'gma_subscrible_new_answer_forward' );
		// New Answer to Followers Email Notify
		register_setting( 'gma-subscribe-settings-new-answer', 'gma_subscrible_new_answer_followers_email' );
		register_setting( 'gma-subscribe-settings-new-answer', 'gma_subscrible_new_answer_followers_email_subject' );
		register_setting( 'gma-subscribe-settings-new-answer', 'gma_subscrible_enable_new_answer_followers_notification' );

		// New Comment for Question Notify
		register_setting( 'gma-subscribe-settings-new-comment', 'gma_subscrible_new_comment_question_email_subject' );
		register_setting( 'gma-subscribe-settings-new-comment', 'gma_subscrible_new_comment_question_email' );
		register_setting( 'gma-subscribe-settings-new-comment', 'gma_subscrible_enable_new_comment_question_notification' );

		register_setting( 'gma-subscribe-settings-new-comment', 'gma_subscrible_new_comment_question_forward' );

		// New Comment for Question to Followers Email Notify
		register_setting( 'gma-subscribe-settings-new-comment', 'gma_subscrible_new_comment_question_followers_email_subject' );
		register_setting( 'gma-subscribe-settings-new-comment', 'gma_subscrible_new_comment_question_followers_email' );
		register_setting( 'gma-subscribe-settings-new-comment', 'gma_subscrible_enable_new_comment_question_followers_notify' );

		// New Comment for Answer Email Notify
		register_setting( 'gma-subscribe-settings-new-comment', 'gma_subscrible_new_comment_answer_email_subject' );
		register_setting( 'gma-subscribe-settings-new-comment', 'gma_subscrible_new_comment_answer_email' );
		register_setting( 'gma-subscribe-settings-new-comment', 'gma_subscrible_enable_new_comment_answer_notification' );
		register_setting( 'gma-subscribe-settings-new-comment', 'gma_subscrible_new_comment_answer_forward' );

		// New Comment for Answer to Followers Email Notify
		register_setting( 'gma-subscribe-settings-new-comment', 'gma_subscrible_new_comment_answer_followers_email_subject' );
		register_setting( 'gma-subscribe-settings-new-comment', 'gma_subscrible_new_comment_answer_followers_email' );
		register_setting( 'gma-subscribe-settings-new-comment', 'gma_subscrible_enable_new_comment_answer_followers_notification' );

		add_settings_section( 
			'gma-permission-settings',
			__( 'Group Permission','give-me-answer-lite' ),
			false,
			'gma-permission'
		);

		add_settings_field( 
			'gma_permission',
			__( 'Group Permission','give-me-answer-lite' ),
			'gma_permission_display',
			'gma-permission',
			'gma-permission-settings'
		);

		register_setting( 'gma-permission-settings', 'gma_permission' );


		add_settings_section(
			'gma-sms-settings',
			false,
			false,
			'gma-sms'
		);
		add_settings_section(
			'gma-sms-settings-gateway',
			false,
			false,
			'gma-sms'
		);
		add_settings_section(
			'gma-sms-settings-notification',
			false,
			false,
			'gma-sms'
		);
		register_setting( 'gma-sms-settings', 'gma-admin-mobiles' );
		register_setting( 'gma-sms-settings-gateway', 'gma-smsgateway' );
		register_setting( 'gma-sms-settings-notification', 'gma-smsnoti' );


		add_settings_section(
		   'gma-settings-avatar',
            __('Avatar', 'give-me-answer-lite'),
            false,
            'gma-avatar'
        );

		add_settings_field(
			'gma_options[avatar][type]',
			_x('Type', 'avatar', 'give-me-answer-lite'),
			function() {
				global $gma_avatar;
				?>
                <label for="gma_options[avatar][type]">
                    <select id="gma_options[avatar][type]" name="gma_options[avatar][type]">
                        <option <?php selected( $gma_avatar['type'], 'circle' ) ?> value="circle"><?php _e('Circle', 'give-me-answer-lite'); ?></option>
                        <option <?php selected( $gma_avatar['type'], 'square' ) ?> value="square"><?php _e('Square', 'give-me-answer-lite'); ?></option>
                    </select>
                </label>
				<?php
			},
			'gma-avatar',
			'gma-settings-avatar'
		);

		add_settings_field(
		    'gma_options[avatar][show-on-archive]',
            __('Display on archive questions', 'give-me-answer-lite'),
            function() {
		        global $gma_avatar;
		        ?>
                <label for="gma_options[avatar][show-on-archive]">
                    <input
                        <?php checked( $gma_avatar[ 'show-on-archive'], 1 ); ?>
                        type="checkbox"
                        id="gma_options[avatar][show-on-archive]"
                        name="gma_options[avatar][show-on-archive]"
                        value="1"
                    >
                </label>
		        <?php
            },
            'gma-avatar',
            'gma-settings-avatar'
        );

		add_settings_field(
		    'gma_options[avatar][show-on-single-question]',
            __('Display on single question', 'give-me-answer-lite'),
            function() {
		        global $gma_avatar;
		        ?>
                <label for="gma_options[avatar][show-on-single-question]">
                    <input
                        <?php checked( $gma_avatar[ 'show-on-single-question'], 1 ); ?>
                        type="checkbox"
                        id="gma_options[avatar][show-on-single-question]"
                        name="gma_options[avatar][show-on-single-question]"
                        value="1"
                    >
                </label>
		        <?php
            },
            'gma-avatar',
            'gma-settings-avatar'
        );

		add_settings_field(
			'gma_options[avatar][show-on-single-answer]',
			__('Display on answer', 'give-me-answer-lite'),
			function() {
				global $gma_avatar;
				?>
                <label for="gma_options[avatar][show-on-single-answer]">
                    <input
						<?php checked( $gma_avatar[ 'show-on-single-answer'], 1 ); ?>
                            type="checkbox"
                            id="gma_options[avatar][show-on-single-answer]"
                            name="gma_options[avatar][show-on-single-answer]"
                            value="1"
                    >
                </label>
				<?php
			},
			'gma-avatar',
			'gma-settings-avatar'
		);

		add_settings_field(
			'gma_options[avatar][show-on-comment]',
			__('Display on comment', 'give-me-answer-lite'),
			function() {
				global $gma_avatar;
				?>
                <label for="gma_options[avatar][show-on-comment]">
                    <input
                        <?php checked( $gma_avatar[ 'show-on-comment'], 1 ); ?>
                        type="checkbox"
                        id="gma_options[avatar][show-on-comment]"
                        name="gma_options[avatar][show-on-comment]"
                        value="1"
                    >
                </label>
				<?php
			},
			'gma-avatar',
			'gma-settings-avatar'
		);

		add_settings_field(
			'gma_options[avatar][max-size-kb]',
			_x('Image size', 'avatar', 'give-me-answer-lite'),
			function() {
				global $gma_avatar;
				?>
                <p>
                    <label for="gma_options[avatar][max-size-kb]">
                        <input
                            type="text"
                            id="gma_options[avatar][max-size-kb]"
                            name="gma_options[avatar][max-size-kb]"
                            value="<?php echo esc_attr( $gma_avatar['max-size-kb'] ); ?>"
                            class="small-text"
                        >
                    </label>
                    <span class="description"><?php _e('kb', 'give-me-answer-lite'); ?></span>
                </p>

				<?php
			},
			'gma-avatar',
			'gma-settings-avatar'
		);

		register_setting( 'gma-settings-avatar', 'gma_avatar' );


		add_settings_section(
		   'gma-settings-avatar-size',
            __('Avatar Size', 'give-me-answer-lite'),
            false,
            'gma-avatar'
        );

		add_settings_field(
		    'gma_options[avatar][size][archive]',
            __('Archive Questions', 'give-me-answer-lite'),
            function() {
		        global $gma_avatar;
                $curvalue = isset( $gma_avatar[ 'size' ][ 'archive' ]  ) ? $gma_avatar[ 'size' ][ 'archive' ] : '';
		        ?>
                <p>
                    <input
                        type="text"
                        name="gma_options[avatar][size][archive]"
                        value="<?php echo esc_attr($curvalue); ?>"
                        class="small-text"
                    >
                    <span class="description"><?php _e('px', 'give-me-answer-lite'); ?></span>
                </p>
		        <?php
            },
            'gma-avatar',
            'gma-settings-avatar-size'
        );

		add_settings_field(
			'gma_options[avatar][size][question]',
			__('Question', 'give-me-answer-lite'),
			function() {
				global $gma_avatar;
				$curvalue = isset( $gma_avatar[ 'size' ][ 'question' ]  ) ? $gma_avatar[ 'size' ][ 'question' ] : '';
				?>
                <p>
                    <input
                        type="text"
                        name="gma_options[avatar][size][question]"
                        value="<?php echo $curvalue; ?>"
                        class="small-text"
                    >
                    <span class="description"><?php _e('px', 'give-me-answer-lite'); ?></span>
                </p>
				<?php
			},
			'gma-avatar',
			'gma-settings-avatar-size'
		);

		add_settings_field(
			'gma_options[avatar][size][answer]',
			__('Answer', 'give-me-answer-lite'),
			function() {
				global $gma_avatar;
				$curvalue = isset( $gma_avatar[ 'size' ][ 'answer' ]  ) ? $gma_avatar[ 'size' ][ 'answer' ] : '';
				?>
                <p>
                    <input type="text" name="gma_options[avatar][size][answer]" value="<?php echo esc_attr( $curvalue ); ?>" class="small-text">
                    <span class="description"><?php _e('px', 'give-me-answer-lite'); ?></span>
                </p>
				<?php
			},
			'gma-avatar',
			'gma-settings-avatar-size'
		);

		add_settings_field(
			'gma_options[avatar][size][comment]',
			__('Comment', 'give-me-answer-lite'),
			function() {
				global $gma_avatar;
				$curvalue = isset( $gma_avatar[ 'size' ][ 'comment' ]  ) ? $gma_avatar[ 'size' ][ 'comment' ] : '';
				?>
                <p>
                    <input
                        type="text"
                        name="gma_options[avatar][size][comment]"
                        value="<?php echo esc_attr( $curvalue ); ?>"
                        class="small-text"
                    >
                    <span class="description"><?php _e('px', 'give-me-answer-lite'); ?></span>
                </p>
				<?php
			},
			'gma-avatar',
			'gma-settings-avatar-size'
		);

		register_setting('gma-settings-avatar-size', 'gma_avatar[size]');
	}

	public function settings_display(){
		global $gma_general_settings;
		$general_section = $this->current_general_tab();
		$email_section   = $this->current_email_tab();
		$status_section  = $this->current_status_tab();
		?>
		<div class="wrap">
			<h2><?php _e( 'Give Me Answer Settings', 'give-me-answer-lite' ) ?></h2>
            <?php
                settings_errors();
                $active_tab = isset( $_GET[ 'tab' ] ) ? esc_html( $_GET['tab'] ) : 'general';
            ?>

			<h2 class="nav-tab-wrapper">
				<a href="?page=gma-settings&amp;tab=general" class="nav-tab <?php echo $active_tab == 'general' ? 'nav-tab-active' : ''; ?>"><?php _e( 'General','give-me-answer-lite' ); ?></a>
                <?php do_action( 'gma_settings_tab_after_points_tab' ); ?>
				<a href="?page=gma-settings&amp;tab=avatar" class="nav-tab <?php echo $active_tab == 'avatar' ? 'nav-tab-active' : ''; ?>"><?php _e( 'Avatar','give-me-answer-lite' ); ?></a>
				<a href="?page=gma-settings&amp;tab=email" class="nav-tab <?php echo $active_tab == 'email' ? 'nav-tab-active' : ''; ?>"><?php _e( 'Emails','give-me-answer-lite' ); ?></a>
				<a href="?page=gma-settings&amp;tab=permission" class="nav-tab <?php echo $active_tab == 'permission' ? 'nav-tab-active' : ''; ?>"><?php _e( 'Permissions','give-me-answer-lite' ); ?></a>
                <a href="?page=gma-settings&amp;tab=status" class="nav-tab <?php echo $active_tab == 'status' ? 'nav-tab-active' : ''; ?>"><?php _e( 'Status','give-me-answer-lite' ); ?></a>
                <a href="?page=gma-settings&amp;tab=tools" class="nav-tab <?php echo $active_tab == 'tools' ? 'nav-tab-active' : ''; ?>"><?php _e( 'Tools','give-me-answer-lite' ); ?></a>
                <?php do_action( 'gma_settings_tabs' ); ?>
			</h2>  
			  
			<form method="post" action="options.php">  
			<?php 

			switch ($active_tab) {
			    case 'general':
			        echo $this->general_tabs();
				    echo sprintf('<table class="form-table gma-general-settings %s"><tbody>', $general_section);
				    if ( $general_section == 'general' || $general_section == '' ) {
					    settings_fields( 'gma-general-settings' );
					    do_settings_fields( 'gma-settings', 'gma-general-settings' );
                    } else if ( $general_section == 'pages' ) {
				        settings_fields( 'gma-pages-settings' );
			            do_settings_fields('gma-settings', 'gma-pages-settings');
                    } else if ( $general_section == 'question' ) {
				        settings_fields( 'gma-misc-settings' );
				        do_settings_fields('gma-settings', 'gma-misc-settings');
                    }else if ( $general_section == 'archive' ) {
				        settings_fields( 'gma-archive-settings' );
				        do_settings_fields('gma-settings', 'gma-archive-settings');
			        }else if ( $general_section == 'answer' ) {
				        settings_fields( 'gma-answer-settings' );
				        do_settings_fields('gma-settings', 'gma-answer-settings');
			        }else if ( $general_section == 'comment' ) {
				        settings_fields( 'gma-comment-settings' );
				        do_settings_fields('gma-settings', 'gma-comment-settings');
			        } else if ( $general_section == 'editor' ) {
                        settings_fields( 'gma-editor-settings' );
                        do_settings_fields('gma-settings', 'gma-editor-settings');
                    } else if ( $general_section == 'vote' ) {
				        settings_fields( 'gma-vote-settings' );
				        do_settings_fields('gma-settings', 'gma-vote-settings');
			        } else if ( $general_section == 'captcha' ) {
				        settings_fields( 'gma-captcha-settings' );
				        do_settings_fields('gma-settings', 'gma-captcha-settings');
                    } else if ( $general_section == 'pagination' ) {
                        settings_fields( 'gma-captcha-settings' );
                        do_settings_fields('gma-settings', 'gma-pagination-settings');
                    } else if ( $general_section == 'permalink' ) {
				        settings_fields( 'gma-permalink-settings' );
				        do_settings_fields('gma-settings', 'gma-permalink-settings');
                    }
				    
				    do_action( 'gma_settings_genral_tab_content', $general_section );
				    
				    echo '</tbody></table>';
				    submit_button( __( 'Save','give-me-answer-lite' ), 'primary', 'save-settings' );
                    break;
                case 'status':
                    if ( 'system' == $status_section ) {
                        $system_status      = new GMA_System_Status();
                        $environment        = $system_status->get_environment_info();
                        $database           = $system_status->get_database_info();
                        $post_type_counts   = $system_status->get_post_type_counts();
                        $active_plugins     = $system_status->get_active_plugins();
                        $inactive_plugins   = $system_status->get_inactive_plugins();
                        $dropins_mu_plugins = $system_status->get_dropins_mu_plugins();
                        $theme              = $system_status->get_theme_info();
                        $security           = $system_status->get_security_info();
                        $wp_pages           = $system_status->get_pages();
                        ?>
                        <!-- WordPress Status -->
                        <table class="gma_status_table widefat" cellspacing="0" id="status">
                            <thead>
                            <tr>
                                <th colspan="3" data-export-label="WordPress Environment"><h2>WordPress environment</h2></th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td data-export-label="Home URL"><?php _e('Home URL:', 'give-me-answer-lite'); ?></td>
                                
                                <td><?php echo esc_html( $environment['home_url'] ); ?></td>
                            </tr>
                            <tr>
                                <td data-export-label="Site URL"><?php _e('Site URL:', 'give-me-answer-lite'); ?></td>
                                
                                <td><?php echo esc_html( $environment['site_url'] ); ?></td>
                            </tr>
                            <tr>
                                <td data-export-label="WC Version"><?php _e('GMA Version:', 'give-me-answer-lite'); ?></td>
                                
                                <td><?php echo esc_html( gma_lite()->version ); ?></td>
                            </tr>
                            <tr>
                                <td data-export-label="WP Version"><?php _e('WordPress Version:', 'give-me-answer-lite'); ?></td>
                                
                                <td>
                                    <?php
                                    $latest_version = get_transient( 'gma_system_status_wp_version_check' );

                                    if ( false === $latest_version ) {
                                        $version_check = wp_remote_get( 'https://api.wordpress.org/core/version-check/1.7/' );
                                        $api_response  = json_decode( wp_remote_retrieve_body( $version_check ), true );

                                        if ( $api_response && isset( $api_response['offers'], $api_response['offers'][0], $api_response['offers'][0]['version'] ) ) {
                                            $latest_version = $api_response['offers'][0]['version'];
                                        } else {
                                            $latest_version = $environment['wp_version'];
                                        }
                                        set_transient( 'gma_system_status_wp_version_check', $latest_version, DAY_IN_SECONDS );
                                    }

                                    if ( version_compare( $environment['wp_version'], $latest_version, '<' ) ) {
                                        /* Translators: %1$s: Current version, %2$s: New version */
                                        echo '<mark class="error"><span class="dashicons dashicons-warning"></span> ' . sprintf( esc_html__( '%1$s - There is a newer version of WordPress available (%2$s)', 'give-me-answer-lite' ), esc_html( $environment['wp_version'] ), esc_html( $latest_version ) ) . '</mark>';
                                    } else {
                                        echo '<mark class="yes">' . esc_html( $environment['wp_version'] ) . '</mark>';
                                    }
                                    ?>
                                </td>
                            </tr>
                            <tr>
                                <td data-export-label="WP Multisite"><?php _e('WordPress multisite:', 'give-me-answer-lite'); ?></td>
                                
                                <td><?php echo ( $environment['wp_multisite'] ) ? '<span class="dashicons dashicons-yes"></span>' : '&ndash;'; ?></td>
                            </tr>
                            <tr>
                                <td data-export-label="WP Memory Limit"><?php _e('WordPress memory limit:', 'give-me-answer-lite'); ?></td>
                                
                                <td>
                                    <?php
                                    if ( $environment['wp_memory_limit'] < 67108864 ) {
                                        /* Translators: %1$s: Memory limit, %2$s: Docs link. */
                                        echo '<mark class="error"><span class="dashicons dashicons-warning"></span> ' . sprintf( esc_html__( '%1$s - We recommend setting memory to at least 64MB. See: %2$s', 'give-me-answer-lite' ), esc_html( size_format( $environment['wp_memory_limit'] ) ), '<a href="https://codex.wordpress.org/Editing_wp-config.php#Increasing_memory_allocated_to_PHP" target="_blank">' . esc_html__( 'Increasing memory allocated to PHP', 'give-me-answer-lite' ) . '</a>' ) . '</mark>';
                                    } else {
                                        echo '<mark class="yes">' . esc_html( size_format( $environment['wp_memory_limit'] ) ) . '</mark>';
                                    }
                                    ?>
                                </td>
                            </tr>
                            <tr>
                                <td data-export-label="WP Debug Mode"><?php _e('WordPress debug mode:', 'give-me-answer-lite'); ?></td>
                                
                                <td>
                                    <?php if ( $environment['wp_debug_mode'] ) : ?>
                                        <mark class="yes"><span class="dashicons dashicons-yes"></span></mark>
                                    <?php else : ?>
                                        <mark class="no">&ndash;</mark>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <td data-export-label="WP Cron"><?php _e('WordPress cron:', 'give-me-answer-lite'); ?></td>
                                
                                <td>
                                    <?php if ( $environment['wp_cron'] ) : ?>
                                        <mark class="yes"><span class="dashicons dashicons-yes"></span></mark>
                                    <?php else : ?>
                                        <mark class="no">&ndash;</mark>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <td data-export-label="Language"><?php _e('Language:', 'give-me-answer-lite'); ?></td>
                                
                                <td><?php echo esc_html( $environment['language'] ); ?></td>
                            </tr>
                            <tr>
                                <td data-export-label="External object cache"><?php _e('External object cache:', 'give-me-answer-lite'); ?></td>
                                <td>
                                    <?php if ( $environment['external_object_cache'] ) : ?>
                                        <mark class="yes"><span class="dashicons dashicons-yes"></span></mark>
                                    <?php else : ?>
                                        <mark class="no">&ndash;</mark>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                        <br>
                        <!-- Server environment -->
                        <table class="gma_status_table widefat" cellspacing="0">
                            <thead>
                            <tr>
                                <th colspan="3" data-export-label="Server Environment"><h2><?php _e('Server environment', 'give-me-answer-lite'); ?></h2></th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td data-export-label="Server Info"><?php _e('Server info:', 'give-me-answer-lite'); ?></td>
                                
                                <td><?php echo esc_html( $environment['server_info'] ); ?></td>
                            </tr>
                            <tr>
                                <td data-export-label="PHP Version">PHP version:</td>
                                
                                <td><?php echo esc_html( $environment['php_version'] ); ?></td>
                            </tr>
                            <tr>
                                <td data-export-label="PHP Post Max Size"><?php _e('PHP post max size:', 'give-me-answer-lite'); ?></td>
                                
                                <td><?php echo esc_html( size_format( $environment['php_post_max_size'] ) ); ?></td>
                            </tr>
                            <tr>
                                <td data-export-label="PHP Time Limit"><?php _e('PHP time limit:', 'give-me-answer-lite'); ?></td>
                                
                                <td><?php echo esc_html( $environment['php_max_execution_time'] ); ?></td>
                            </tr>
                            <tr>
                                <td data-export-label="PHP Max Input Vars"><?php _e('PHP max input vars:', 'give-me-answer-lite'); ?></td>
                                
                                <td><?php echo esc_html( $environment['php_max_input_vars'] ); ?></td>
                            </tr>
                            <tr>
                                <td data-export-label="cURL Version"><?php _e('cURL version:', 'give-me-answer-lite'); ?></td>
                                
                                <td><?php echo esc_html( $environment['curl_version'] ); ?></td>
                            </tr>
                            <tr>
                                <td data-export-label="SUHOSIN Installed"><?php _e('SUHOSIN installed:', 'give-me-answer-lite'); ?></td>
                                
                                <td><?php echo $environment['suhosin_installed'] ? '<span class="dashicons dashicons-yes"></span>' : '&ndash;'; ?></td>
                            </tr>

                            <?php
                                if ( $environment['mysql_version'] ) :
                                    ?>
                                    <tr>
                                        <td data-export-label="MySQL Version"><?php esc_html_e( 'MySQL version', 'give-me-answer-lite' ); ?>:</td>
                                        <td>
                                            <?php
                                            if ( version_compare( $environment['mysql_version'], '5.6', '<' ) && ! strstr( $environment['mysql_version_string'], 'MariaDB' ) ) {
                                                /* Translators: %1$s: MySQL version, %2$s: Recommended MySQL version. */
                                                echo '<mark class="error"><span class="dashicons dashicons-warning"></span> ' . sprintf( esc_html__( '%1$s - We recommend a minimum MySQL version of 5.6. See: %2$s', 'give-me-answer-lite' ), esc_html( $environment['mysql_version_string'] ), '<a href="https://wordpress.org/about/requirements/" target="_blank">' . esc_html__( 'WordPress requirements', 'give-me-answer-lite' ) . '</a>' ) . '</mark>';
                                            } else {
                                                echo '<mark class="yes">' . esc_html( $environment['mysql_version_string'] ) . '</mark>';
                                            }
                                            ?>
                                        </td>
                                    </tr>
                                <?php endif; ?>


                            <tr>
                                <td data-export-label="Max Upload Size"><?php _e('Max upload size:', 'give-me-answer-lite'); ?></td>
                                <td><?php echo esc_html( size_format( $environment['max_upload_size'] ) ); ?></td>
                            </tr>
                            <tr>
                                <td data-export-label="Default Timezone is UTC"><?php _e('Default timezone is UTC:', 'give-me-answer-lite'); ?></td>
                                
                                <td>
                                    <?php
                                    if ( 'UTC' !== $environment['default_timezone'] ) {
                                        /* Translators: %s: default timezone.. */
                                        echo '<mark class="error"><span class="dashicons dashicons-warning"></span> ' . sprintf( esc_html__( 'Default timezone is %s - it should be UTC', 'give-me-answer-lite' ), esc_html( $environment['default_timezone'] ) ) . '</mark>';
                                    } else {
                                        echo '<mark class="yes"><span class="dashicons dashicons-yes"></span></mark>';
                                    }
                                    ?>
                                </td>
                            </tr>
                            <tr>
                                <td data-export-label="fsockopen/cURL"><?php _e('fsockopen/cURL:', 'give-me-answer-lite'); ?></td>
                                <td>
                                    <?php
                                    if ( $environment['fsockopen_or_curl_enabled'] ) {
                                        echo '<mark class="yes"><span class="dashicons dashicons-yes"></span></mark>';
                                    } else {
                                        echo '<mark class="error"><span class="dashicons dashicons-warning"></span> ' . esc_html__( 'Your server does not have fsockopen or cURL enabled - PayPal IPN and other scripts which communicate with other servers will not work. Contact your hosting provider.', 'give-me-answer-lite' ) . '</mark>';
                                    }
                                    ?>
                                </td>
                            </tr>
                            <tr>
                                <td data-export-label="SoapClient"><?php _e('SoapClient:', 'give-me-answer-lite'); ?></td>
                                
                                <td>
                                    <?php
                                    if ( $environment['soapclient_enabled'] ) {
                                        echo '<mark class="yes"><span class="dashicons dashicons-yes"></span></mark>';
                                    } else {
                                        /* Translators: %s classname and link. */
                                        echo '<mark class="error"><span class="dashicons dashicons-warning"></span> ' . sprintf( esc_html__( 'Your server does not have the %s class enabled - some gateway plugins which use SOAP may not work as expected.', 'give-me-answer-lite' ), '<a href="https://php.net/manual/en/class.soapclient.php">SoapClient</a>' ) . '</mark>';
                                    }
                                    ?>
                                </td>
                            </tr>
                            <tr>
                                <td data-export-label="DOMDocument"><?php _e('DOMDocument:', 'give-me-answer-lite'); ?></td>
                                
                                <td>
                                    <?php
                                    if ( $environment['domdocument_enabled'] ) {
                                        echo '<mark class="yes"><span class="dashicons dashicons-yes"></span></mark>';
                                    } else {
                                        /* Translators: %s: classname and link. */
                                        echo '<mark class="error"><span class="dashicons dashicons-warning"></span> ' . sprintf( esc_html__( 'Your server does not have the %s class enabled - HTML/Multipart emails, and also some extensions, will not work without DOMDocument.', 'give-me-answer-lite' ), '<a href="https://php.net/manual/en/class.domdocument.php">DOMDocument</a>' ) . '</mark>';
                                    }
                                    ?>
                                </td>
                            </tr>
                            <tr>
                                <td data-export-label="GZip"><?php _e('GZip:', 'give-me-answer-lite'); ?></td>
                                
                                <td>
                                    <?php
                                    if ( $environment['gzip_enabled'] ) {
                                        echo '<mark class="yes"><span class="dashicons dashicons-yes"></span></mark>';
                                    } else {
                                        /* Translators: %s: classname and link. */
                                        echo '<mark class="error"><span class="dashicons dashicons-warning"></span> ' . sprintf( esc_html__( 'Your server does not support the %s function - this is required to use the GeoIP database from MaxMind.', 'give-me-answer-lite' ), '<a href="https://php.net/manual/en/zlib.installation.php">gzopen</a>' ) . '</mark>';
                                    }
                                    ?>
                                </td>
                            </tr>
                            <tr>
                                <td data-export-label="Multibyte String"><?php _e('Multibyte string:', 'give-me-answer-lite'); ?></td>
                                
                                <td>
                                    <?php
                                    if ( $environment['mbstring_enabled'] ) {
                                        echo '<mark class="yes"><span class="dashicons dashicons-yes"></span></mark>';
                                    } else {
                                        /* Translators: %s: classname and link. */
                                        echo '<mark class="error"><span class="dashicons dashicons-warning"></span> ' . sprintf( esc_html__( 'Your server does not support the %s functions - this is required for better character encoding. Some fallbacks will be used instead for it.', 'give-me-answer-lite' ), '<a href="https://php.net/manual/en/mbstring.installation.php">mbstring</a>' ) . '</mark>';
                                    }
                                    ?>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                        <br>
                        <!-- PostTypes count -->
                        <table class="gma_status_table widefat" cellspacing="0">
                            <thead>
                            <tr>
                                <th colspan="3" data-export-label="Post Type Counts"><h2><?php _e('Post Type Counts', 'give-me-answer-lite'); ?></h2></th>
                            </tr>
                            </thead>
                            <tbody>
                                <?php
                                foreach ( $post_type_counts as $ptype ) {
                                    ?>
                                    <tr>
                                        <td><?php echo esc_html( $ptype->type ); ?></td>
                                        <td><?php echo absint( $ptype->count ); ?></td>
                                    </tr>
                                    <?php
                                }
                                ?>
                            </tbody>
                        </table>
                        <br>
                        <!-- Security -->
                        <table class="gma_status_table widefat" cellspacing="0">
                            <thead>
                            <tr>
                                <th colspan="3" data-export-label="Security"><h2><?php _e('Security', 'give-me-answer-lite'); ?></h2></th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td data-export-label="Secure connection (HTTPS)"><?php _e('Secure connection (HTTPS):', 'give-me-answer-lite'); ?></td>
                                <td>
                                    <?php if ( $security['secure_connection'] ) : ?>
                                        <mark class="yes"><span class="dashicons dashicons-yes"></span></mark>
                                    <?php else : ?>
                                        <mark class="error"><span class="dashicons dashicons-warning"></span>
                                            <?php
                                            echo wp_kses_post( __( 'Your website is not using HTTPS.', 'give-me-answer-lite' ));
                                            ?>
                                        </mark>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <td data-export-label="Hide errors from visitors"><?php _e('Hide errors from visitors', 'give-me-answer-lite'); ?></td>
                                <td>
                                    <?php if ( $security['hide_errors'] ) : ?>
                                        <mark class="yes"><span class="dashicons dashicons-yes"></span></mark>
                                    <?php else : ?>
                                        <mark class="error"><span class="dashicons dashicons-warning"></span><?php esc_html_e( 'Error messages should not be shown to visitors.', 'give-me-answer-lite' ); ?></mark>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                        <br>
                        <!-- GMA Pages -->
                        <table class="gma_status_table widefat" cellspacing="0">
                            <thead>
                            <tr>
                                <th colspan="3" data-export-label="GMA Pages"><h2><?php _e('Give Me Answer pages', 'give-me-answer-lite'); ?></h2></th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php
                            $alt = 1;
                            foreach ( $wp_pages as $_page ) {
                                $found_error = false;

                                if ( $_page['page_id'] ) {
                                    /* Translators: %s: page name. */
                                    $page_name = '<a href="' . get_edit_post_link( $_page['page_id'] ) . '" aria-label="' . sprintf( esc_html__( 'Edit %s page', 'give-me-answer-lite' ), esc_html( $_page['page_name'] ) ) . '">' . esc_html( $_page['page_name'] ) . '</a>';
                                } else {
                                    $page_name = esc_html( $_page['page_name'] );
                                }

                                echo '<tr><td data-export-label="' . esc_attr( $page_name ) . '">' . wp_kses_post( $page_name ) . '</td><td>';
                                // Page ID check.
                                if ( ! $_page['page_set'] ) {
                                    echo '<mark class="error"><span class="dashicons dashicons-warning"></span> ' . esc_html__( 'Page not set', 'give-me-answer-lite' ) . '</mark>';
                                    $found_error = true;
                                } elseif ( ! $_page['page_exists'] ) {
                                    echo '<mark class="error"><span class="dashicons dashicons-warning"></span> ' . esc_html__( 'Page ID is set, but the page does not exist', 'give-me-answer-lite' ) . '</mark>';
                                    $found_error = true;
                                } elseif ( ! $_page['page_visible'] ) {
                                    /* Translators: %s: docs link. */
                                    echo '<mark class="error"><span class="dashicons dashicons-warning"></span> ' . wp_kses_post( sprintf( __( 'Page visibility should be <a href="%s" target="_blank">public</a>', 'give-me-answer-lite' ), 'https://codex.wordpress.org/Content_Visibility' ) ) . '</mark>';
                                    $found_error = true;
                                } else {
                                    // Shortcode check.
                                    if ( $_page['shortcode_required'] ) {
                                        if ( ! $_page['shortcode_present'] ) {
                                            echo '<mark class="error"><span class="dashicons dashicons-warning"></span> ' . sprintf( esc_html__( 'Page does not contain the shortcode.', 'give-me-answer-lite' ), esc_html( $_page['shortcode'] ) ) . '</mark>';
                                            $found_error = true;
                                        }
                                    }
                                }
                                if ( ! $found_error ) {
                                    echo '<mark class="yes">#' . absint( $_page['page_id'] ) . ' - ' . esc_html( str_replace( home_url(), '', get_permalink( $_page['page_id'] ) ) ) . '</mark>';
                                }
                                echo '</td></tr>';
                            }
                            ?>
                            </tbody>
                        </table>
                        <?php
                    }
                    break;
                case 'tools':
                    do_action( 'gma_settings_tools_messages' );
                    ?>
                        <table class="gma_status_table gma_status_table--tools widefat" cellspacing="0">
                        <tbody class="tools">
                        <tr class="clear_transients">
                            <th>
                                <strong class="name"><?php _e('GiveMeAnswer transients', 'give-me-answer-lite'); ?></strong>
                                <p class="description"><?php _e('This tool will clear the question/answer transients cache.', 'give-me-answer-lite'); ?></p>
                            </th>
                            <td class="run-tool">
                                <a href="<?php echo wp_nonce_url( admin_url( 'admin.php?page=gma-settings&tab=tools&action=clear_transients' ), '_gma_tools_nonce' ); ?>" class="button button-large clear_transients"><?php _e('Clear transients', 'give-me-answer-lite'); ?></a>
                            </td>
                        </tr>
                        <tr class="clear_expired_transients">
                            <th>
                                <strong class="name"><?php _e('Expired transients', 'give-me-answer-lite'); ?></strong>
                                <p class="description"><?php _e('This tool will clear ALL expired transients from WordPress.', 'give-me-answer-lite'); ?></p>
                            </th>
                            <td class="run-tool">
                                <a href="<?php echo wp_nonce_url(admin_url( 'admin.php?page=gma-settings&tab=tools&action=clear_expired_transients' ), '_gma_tools_nonce'); ?>" class="button button-large clear_expired_transients"><?php _e('Clear transients', 'give-me-answer-lite'); ?></a>
                            </td>
                        </tr>
                        <tr class="install_pages">
                            <th>
                                <strong class="name"><?php _e('Create default GiveMeAnswer pages', 'give-me-answer-lite'); ?></strong>
                                <p class="description"><strong class="red"><?php _e('Note:', 'give-me-answer-lite'); ?></strong> <?php _e('This tool will install all the missing GiveMeAnswer pages. Pages already defined and set up will not be replaced.', 'give-me-answer-lite'); ?></p>
                            </th>
                            <td class="run-tool">
                                <a href="<?php echo wp_nonce_url(admin_url( 'admin.php?page=gma-settings&tab=tools&action=install_pages' ), '_gma_tools_nonce');?>" class="button button-large install_pages">
                                    <?php _e('Create pages', 'give-me-answer-lite'); ?>
                                </a>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                    <?php
                    break;
				case 'email':
					
					echo '<div class="gma-notification-settings">';
					echo $this->email_tabs();


					echo '<div class="gma-mail-templates">';
					echo '<div class="progress-bar"><div class="progress-bar-inner"></div></div>';

					echo '<div class="tab-content">';

					// email setup section
					if ( $email_section === 'general' ) :
						settings_fields( 'gma-subscribe-settings' );
						echo '<h3>'.__( 'Email settings','give-me-answer-lite' ).'</h3>';
						do_settings_sections( 'gma-email' );
					endif;

					if ( $email_section == 'new-question' ) :
						settings_fields( 'gma-subscribe-settings-new-question' );
						echo '<div id="new-question" class="tab-pane active">';
						echo '<h3>'.__( 'New Question Notifications (to Admin)','give-me-answer-lite' ) . '</h3>';
						echo '<table class="form-table">';
						echo '<tr>';
						gma_subscrible_enable_new_question_notification();
						echo '</tr>';
						echo '<tr>';
						gma_subscrible_new_question_email_subject_display();
						echo '</tr>';
						echo '<tr>';
						gma_subscrible_new_question_email_display();
						echo '</tr>';
						echo '<tr>';
						$this->email_sendto_address_display();
						echo '</tr>';
						echo '</table>';
						echo '</div>'; //End tab for New Question Notification
					endif;

					// new answer section
					if ( $email_section == 'new-answer' ) :

						settings_fields( 'gma-subscribe-settings-new-answer' );
						// new answer to follower section
						echo '<div id="new-answer-followers" class="tab-pane">';
						echo '<h3>'.__( 'New Answer Notifications (to Followers)','give-me-answer-lite' ). '</h3>';
						echo '<table class="form-table">';
						echo '<tr>';
						gma_subscrible_enable_new_answer_followers_notification();
						echo '</tr>';
						echo '<tr>';
						gma_subscrible_new_answer_followers_email_subject_display();
						echo '</tr>';
						echo '<tr>';
						gma_subscrible_new_answer_followers_email_display();
						echo '</tr>';
						echo '</table>';
						echo '<hr>';
						echo '</div>';//End tab for New Answer Notification To Followers

						echo '<div id="new-answer" class="tab-pane">';
						echo '<h3>'.__( 'New Answer Notifications (to Author)','give-me-answer-lite' ). '</h3>';
						echo '<table class="form-table">';
						echo '<tr>';
						gma_subscrible_enable_new_answer_notification();
						echo '<tr>';
						gma_subscrible_new_answer_email_subject_display();
						echo '<tr>';
						gma_subscrible_new_answer_email_display();
						echo '</tr>';
						echo '<tr>';
						$this->new_answer_forward();
						echo '</tr>';
						echo '</table>';
						echo '</div>';//End tab for New Answer Notification

					endif;

					if ( $email_section == 'new-comment' ) :
						settings_fields( 'gma-subscribe-settings-new-comment' );
						echo '<div id="new-comment-question-followers" class="tab-pane">';
						echo '<h3>'.__( 'New Comment to Question Notifications (to Followers)','give-me-answer-lite' ). '</h3>';
						echo '<table class="form-table">';
						echo '<tr>';
						gma_subscrible_enable_new_comment_question_followers_notification();
						echo '</tr>';
						echo '<tr>';
						gma_subscrible_new_comment_question_followers_email_subject_display();
						echo '</tr>';
						echo '<tr>';
						gma_subscrible_new_comment_question_followers_email_display();
						echo '</tr>';
						echo '</table>';
						echo '<hr>';
						echo '</div>'; //End tab for New Comment to Question Notification


						echo '<div id="new-comment-question" class="tab-pane">';
						echo '<h3>'.__( 'New Comment to Question Notifications (to Admin)','give-me-answer-lite' ). '</h3>';
						echo '<table class="form-table">';
						echo '<tr>';
						gma_subscrible_enable_new_comment_question_notification();
						echo '</tr>';
						echo '<tr>';
						gma_subscrible_new_comment_question_email_subject_display();
						echo '</tr>';
						echo '<tr>';
						gma_subscrible_new_comment_question_email_display();
						echo '</tr>';
						echo '<tr>';
						$this->new_comment_question_forward();
						echo '</tr>';
						echo '</table>';
						echo '<hr>';
						echo '</div>'; //End tab for New Comment to Question Notification

						
						echo '<div id="new-comment-answer-followers" class="tab-pane">';
						echo '<h3>'.__( 'New Comment to Answer Notifications (to Followers)','give-me-answer-lite' ). '</h3>';
						echo '<table class="form-table">';
						echo '<tr>';
						gma_subscrible_enable_new_comment_answer_followers_notification();
						echo '</tr>';
						echo '<tr>';
						gma_subscrible_new_comment_answer_followers_email_subject_display();
						echo '</tr>';
						echo '<tr>';
						gma_subscrible_new_comment_answer_followers_email_display();
						echo '</tr>';
						echo '</table>';
						echo '<hr>';
						echo '</div>'; //End tab for New Comment to Answer Notification

						
						echo '<div id="new-comment-answer" class="tab-pane">';
						echo '<h3>'.__( 'New Comment to Answer Notifications (to Admin)','give-me-answer-lite' ). '</h3>';
						echo '<table class="form-table">';
						echo '<tr>';
						gma_subscrible_enable_new_comment_answer_notification();
						echo '</tr>';
						echo '<tr>';
						gma_subscrible_new_comment_answer_email_subject_display();
						echo '</tr>';
						echo '<tr>';
						gma_subscrible_new_comment_answer_email_display();
						echo '</tr>';
						echo '<tr>';
						$this->new_comment_answer_forward();
						echo '</tr>';
						echo '</table>';
						echo '</div>'; //End tab for New Comment to Answer Notification
					endif;

					do_action( 'gma_settings_email_tab_content', $email_section );

					submit_button( __( 'Save','give-me-answer-lite' ) );
					echo '</div>'; //End wrap mail template settings

					echo '</div>'; //End wrap tab content

					echo '</div>'; //The End
					break;
                case 'avatar':
                    echo '<div class="gma-settings-avatar">';
                    settings_fields( 'gma-settings-avatar' );
                    do_settings_sections( 'gma-avatar' );
                    echo '</div>';
                    submit_button(__('Save', 'give-me-answer-lite'), 'primary', 'save-avatar-settings');
                    break;
				case 'permission':
					settings_fields( 'gma-permission-settings' );
					gma_permission_display();
					submit_button();
					break;
				default:
                    do_action( 'gma_settings_tab_content' );
			}

			?>
			</form>

            <a href="https://codecanyon.net/item/give-me-answer/24133030" target="_blank">
                <img src="<?php echo GMA_URI . '/assets-admin/img/premium.png' ?>">
            </a>
		</div>
		<?php
	}

	private function hidden( $value, $value_for_hide ) {
	    if ( $value == $value_for_hide ) echo ' style="display: none;" ';
    }


	public function new_answer_forward() {
		echo '<th>'.__( 'Forward to', 'give-me-answer-lite' ).'</th>';
		$this->textarea_field( 'gma_subscrible_new_answer_forward', false, __('Each email is written in a separate line', 'give-me-answer-lite') );
	}

	public function new_comment_question_forward() {
		echo '<th>'.__( 'Forward to', 'give-me-answer-lite' ).'</th>';
		$this->textarea_field( 'gma_subscrible_new_comment_question_forward' );
	}

	public function new_comment_answer_forward() {
		echo '<th>'.__( 'Forward to', 'give-me-answer-lite' ).'</th>';
		$this->textarea_field( 'gma_subscrible_new_comment_answer_forward', false, __('Each email is written in a separate line', 'give-me-answer-lite') );
	}

	public function email_sendto_address_display(){
		echo '<th>'.__( 'Forward to', 'give-me-answer-lite' ).'</th>';
		$this->textarea_field( 'gma_subscrible_sendto_address', false, __('Each email is written in a separate line', 'give-me-answer-lite') );
	}

	public function email_cc_address_display(){
		echo '<p>'.__( 'Cc', 'give-me-answer-lite' ).'</p>';
		$this->input_text_field( 'gma_subscrible_cc_address' );
	}

	public function email_bcc_address_display(){
		echo '<p>'.__( 'Bcc', 'give-me-answer-lite' ).'</p>';
		$this->input_text_field( 'gma_subscrible_bcc_address' );
	}

	public function email_from_address_display(){
		$this->input_text_field( 'gma_subscrible_from_address', false, __( 'This address will be used as the sender of the outgoing emails.','give-me-answer-lite' ) );
	}

	public function email_send_copy_to_admin(){
		$this->input_checkbox_field( 
			'gma_subscrible_send_copy_to_admin',
			__( 'Send a copy of every email to admin.','give-me-answer-lite' )
		);
	}

	public function enable_email_delay(){
		$this->input_checkbox_field( 
			'gma_enable_email_delay',
			__( 'Email Delay*','give-me-answer-lite' )
		);
	}

	public function admin_mobiles_display(){
	    $title  = __('Enter the mobile number of the site administrators.', 'give-me-answer-lite');
	    $title .= __('<br>', 'give-me-answer-lite');
	    $title .= __('Separate numbers with dash ( - ).', 'give-me-answer-lite');
		$this->input_text_field( 'gma_sms_admin_mobiles', false, $title );
	}

	public function input_text_field( $option, $label = false, $description = false, $class = false ){
		echo '<p><label for="'.$option.'"><input type="text" id="'.$option.'" name="'.$option.'" value="'.get_option( $option ).'" class="regular-text" />';
		if ( $description ) {
			echo '<br><span class="description">'.$description.'</span>';
		}
		echo '</label></p>';
	}

	public function textarea_field( $option, $lable = false, $description = false, $class = false ) {
		echo '<td><textarea type="text" id="'.$option.'" name="'.$option.'" rows="5" class="widefat" >'.get_option( $option ).'</textarea>';
		if ( $description ) {
			echo '<br><span class="description">'.$description.'</span>';
		}
		echo '<td>';
	}

	public function input_checkbox_field( $option, $description = false ){
		echo '</p><label for="'.$option.'"><input id="'.$option.'" name="'.$option.'" type="checkbox" '.checked( true, (bool ) get_option( $option ), false ).' value="true"/>';
		if ( $description ) {
			echo '<span class="description">'.$description.'</span>';
		}
		echo '</label></p>';
	}

}

?>
