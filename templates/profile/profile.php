
<?php
$section                = isset( $_GET[ 'section' ] ) ? sanitize_text_field( $_GET[ 'section' ] ) : 'questions';
include GMA_DIR . 'templates/profile/auth.php';
?>


	<div class="gma">

        <?php gma_error_if_in_maintenance(); ?>

		<div class="row mx-0 <?php if ( gma_in_maintenance_mode() && ! gma_is_admin() ) echo 'disabled-content'; ?>">

			<div class="col-12 px-0">
                <?php include GMA_DIR . 'templates/profile/nav.php'; ?>
            </div>

            <div class="col-12 bg-white px-0">
				<?php
				if ( $section == 'editprofile' && ( ($user->ID == get_current_user_id() || gma_is_admin()) ) ) {
					include GMA_DIR . 'templates/profile/edit-profile.php';
				} else if ( $section == 'questions' ) {
					$table = new GMA_User_Questions();
					$table->show_top_pagination = false;
					$table->show_bottom_pagination = true;
					$table->prepare_items();
					$table->display();
				} else if (  $section == 'answers' ) {
					$table = new GMA_User_Answers();
					$table->show_top_pagination = false;
					$table->show_bottom_pagination = true;
					$table->prepare_items();
					$table->display();
				} else if ( $section == 'favorites' ) {
					$table = new GMA_Favorite_Questions();
					$table->show_top_pagination = false;
					$table->show_bottom_pagination = true;
					$table->prepare_items();
					$table->display();
				} else if ( $section == 'wall' ) {
					include GMA_DIR . 'templates/profile/wall.php';
				}
				?>
            </div>
		</div>

	</div>