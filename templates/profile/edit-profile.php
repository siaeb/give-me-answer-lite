<?php
    global $wp_query;

    $user_id    = gma_get_user_id();
    $user       = get_user_by( 'id', $user_id );

    $picture    = gma_get_profile_picture( $user->ID );
    $university = get_user_meta( $user->ID, 'gma_university', true );
    $mobile     = get_user_meta( $user->ID, 'gma_mobile', true );
    $about      = get_user_meta( $user->ID, 'gma_about', true );
?>
<div class="gma-edit-profile border-0">
    <?php if ( false === gma_stop_generating_nonce() ) { ?>
        <input type="hidden" name="_wpnonce" value="<?php echo wp_create_nonce( '_gma-edit-profile' ); ?>">
    <?php } ?>

    <fieldset class="form-fieldset px-0">
        <div class="input-group mb-1">
            <span class="input-group-prepend" id="basic-addon3">
              <span class="input-group-text"><?php _e('Username', 'give-me-answer-lite'); ?></span>
            </span>
            <input type="text" class="form-control mx-0" name="userlogin" readonly value="<?php echo $user->user_login; ?>" >
        </div>

        <div class="input-group mb-1">
              <span class="input-group-prepend" id="basic-addon3">
                <span class="input-group-text">
                    <?php _e('Firstname', 'give-me-answer-lite'); ?>
                </span>
              </span>
            <input
                type="text"
                class="form-control mx-0"
                name="firstname"
                placeholder="<?php _e('Enter your firstname', 'give-me-answer-lite'); ?>"
                value="<?php echo $user->first_name; ?>"
            >
        </div>

        <div class="input-group  mb-1">
          <span class="input-group-prepend" id="basic-addon3">
            <span class="input-group-text">
                <?php _e('Lastname', 'give-me-answer-lite'); ?>
            </span>
          </span>
            <input
                type="text"
                class="form-control mx-0"
                name="lastname"
                placeholder="<?php _e('Enter your lastname', 'give-me-answer-lite'); ?>"
                value="<?php echo $user->last_name; ?>"
            >
        </div>

        <div class="input-group  mb-1">
              <span class="input-group-prepend" id="basic-addon3">
                <span class="input-group-text">
                    <?php _e('University/Highschool', 'give-me-answer-lite'); ?>
                </span>
              </span>
            <input
                type="text"
                class="form-control mx-0"
                name="university"
                placeholder="<?php _e('University/Highschool', 'give-me-answer-lite'); ?>"
                value="<?php echo $university; ?>"
            >
        </div>

        <div class="input-group  mb-1">
                      <span class="input-group-prepend" id="basic-addon3">
                        <span class="input-group-text">
                        <?php _e('Mobile number', 'give-me-answer-lite'); ?>
                        </span>
                      </span>
            <input type="text" class="form-control mx-0" name="mobile" placeholder="<?php _e('Enter your mobile number', 'give-me-answer-lite'); ?>" value="<?php echo $mobile; ?>" >
        </div>

        <div class="input-group mb-1">
                      <span class="input-group-prepend" id="basic-addon3">
                        <span class="input-group-text">
        <?php _e('About me', 'give-me-answer-lite'); ?>
                        </span>
                      </span>
            <input type="text" class="form-control mx-0" name="aboutme" placeholder="<?php _e('Write a few words about yourself', 'give-me-answer-lite'); ?>" value="<?php echo $about; ?>" >
        </div>

        <!-- change password -->
        <div class="input-group mb-1">
                      <span class="input-group-prepend" id="basic-addon3">
                        <span class="input-group-text">
        <?php _e('Current password', 'give-me-answer-lite'); ?>
                        </span>
                      </span>
            <input type="password" class="form-control mx-0" name="oldpass" placeholder="<?php _e('Current password', 'give-me-answer-lite'); ?>">
        </div>

        <div class="input-group mb-1">
                      <span class="input-group-prepend" id="basic-addon3">
                        <span class="input-group-text">
        <?php _e('New password', 'give-me-answer-lite'); ?>
                        </span>
                      </span>
            <input type="password" class="form-control mx-0" name="newpass" placeholder="<?php _e('New password', 'give-me-answer-lite'); ?>" >
        </div>

        <div class="input-group mb-1">
                      <span class="input-group-prepend" id="basic-addon3">
                        <span class="input-group-text">
        <?php _e('Retype new password', 'give-me-answer-lite'); ?>
                        </span>
                      </span>
            <input type="password" class="form-control mx-0" name="newpassagain" placeholder="<?php _e('Retype new password', 'give-me-answer-lite'); ?>" >
        </div>



        <button class="btn btn-primary btn-responsive-block mt-2 save-profile">
            <?php _e('Save', 'give-me-answer-lite'); ?>
        </button>
    </fieldset>
</div>