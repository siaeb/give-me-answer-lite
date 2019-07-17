<?php
global $wp_roles;
$is_admin = in_array('administrator', $user->roles);
?>
<div class="gma">
    <div class="gma-user-summary-wrapper d-flex">
        <img class="gma-user-summary-wrapper__image" src="<?php echo $image; ?>" width="70px" height="70px">
        <div class="gma-user-summary-wrapper__info d-flex flex-column ml-2">
            <p class="gma-user-summary-wrapper__info__name text-left mb-0 pb-0">
                <a class="text-white" href="<?php echo gma_get_author_link($user->ID); ?>">
                    <?php echo esc_html($user->display_name); ?>
                </a>
            </p>
            <p class="gma-user-summary-wrapper__info__role badge <?php if ($is_admin) echo 'badge-success'; else echo 'badge-warning'; ?> my-2">
                <?php echo ucfirst(translate_user_role( $wp_roles->roles[$user->roles[0]]['name'] )); ?>
            </p>
            <p class="gma-user-summary-wrapper__registered text-left mb-1"><?php echo date('d F,Y', strtotime($user->user_registered)); ?></p>
        </div>
    </div>
    <p class="gma-user-summary-wrapper__about text-left mt-2"><?php echo esc_html($user->about); ?></p>
</div>
