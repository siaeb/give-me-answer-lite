<?php

defined( 'ABSPATH' ) || exit;

/**
 * Created by PhpStorm.
 * User: siavash
 * Date: 05/20/2019
 * Time: 11:46 AM
 */
class GMA_Comments {

	static public function display() {
		$table = new GMA_List_Table_Comments( ['screen' => 'gma-comments'] );
		$table->prepare_items();
		$table->display();
		wp_comment_reply( '-1', true, 'detail' );
		wp_comment_trashnotice();
	}

}