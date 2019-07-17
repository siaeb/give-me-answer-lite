<?php
defined( 'ABSPATH' ) || exit;

class GMA_PerPageManager {
    static public function save( $user_id, $type, $value = 10 ) {
        if ( empty( $user_id ) ) $user_id = get_current_user_id();
        update_user_meta( $user_id, $type, $value );
        return true;
    }

    static public function get( $user_id, $type, $default = 10 ) {
        if ( !$user_id ) $user_id = get_current_user_id();
        $value = get_user_meta( $user_id, $type, true );
        if ( !empty( $value ) ) return $value;
        return $default;
    }

}