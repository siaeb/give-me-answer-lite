<?php
include GMA_DIR . 'templates/profile/auth.php';
$profile_picture = gma_get_profile_picture( $user->ID );
$can_edit_profile = is_user_logged_in() && ($user->ID == get_current_user_id() || current_user_can( 'manage_options' ) );
gma_visit_profile( is_user_logged_in() ? get_current_user_id() : '', $user->ID );
$section = isset( $_GET[ 'section' ] ) ? sanitize_text_field( $_GET[ 'section' ] ) : 'questions';
?>

<div class="gma-profile-header">
    <div class="gma-profile-header-cover"></div>
    <div class="gma-profile-header-content d-flex">
        <div class="gma-profile-header-img">
            <img
                class="gma-userimage"
                <?php if ( gma_can_user_change_profile_pic($user->ID) ) echo 'style="cursor:pointer;"'; ?>
                src="<?php echo $profile_picture; ?>"
            >
        </div>
        <div class="pl-3">
            <h2 class="gma-username mb-0"><?php echo $user->full_name; ?></h2>

	        <?php if ( $user->university && false ) { ?>
                <p class="gma-useruniversity mb-0">
			        <?php if ( $user->university ) echo $user->university; ?>
                </p>
	        <?php } ?>

	        <?php if ( $user->about ) { ?>
                <p class="gma-userabout my-2"><?php echo esc_html( $user->about ); ?></p>
	        <?php } ?>

	        <?php if ( $can_edit_profile ) { ?>
                <a href="<?php echo gma_get_edit_profile_url( $user->ID ); ?>" class="btn btn-xs btn-success">
	                <?php _e('Edit Profile', 'give-me-answer-lite'); ?>
                </a>

                <input type="file" id="picture" style="display: none;">
	        <?php } ?>

        </div>
    </div>

    <ul class="gma-profile-header-tab nav nav-tabs pb-0">
	    <?php if ( $can_edit_profile && false ) { ?>
            <li class="nav-item">
                <a class="nav-link edit-profile <?php if ( $section == 'editprofile' ) echo 'active show'; ?>" href="<?php echo gma_get_edit_profile_url( $user->ID ); ?>">
                    <?php _e('Profile', 'give-me-answer-lite'); ?>
                </a>
            </li>
	    <?php } ?>

        <li class="nav-item">
             <a href="<?php echo gma_get_user_questions_url( $user->ID ); ?>" class="nav-link <?php if ( $section == 'questions' ) echo 'active show'; ?>"><?php _e('Questions', 'give-me-answer-lite'); ?></a>
        </li>

        <li class="nav-item">
            <a href="<?php echo gma_get_user_answers_url( $user->ID ); ?>" class="nav-link <?php if ( $section == 'answers' ) echo 'active show'; ?>"><?php _e('Answers', 'give-me-answer-lite'); ?></a>
        </li>

	    <?php if ( $can_edit_profile ) { ?>
            <li class="nav-item">
                <a href="<?php echo gma_get_user_favorites_url($user->ID); ?>" class="nav-link <?php if ( $section == 'favorites' ) echo 'active show'; ?>" href="<?php echo gma_get_user_favorites_url( $user->ID ); ?>">
                    <?php _e('Favorites', 'give-me-answer-lite'); ?>
                </a>
            </li>
	    <?php } ?>

        </ul>

</div>