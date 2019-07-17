<?php
defined( 'ABSPATH' ) || exit;

function gma_add_notice( $message, $type = 'success', $comment = false ) {
	gma_lite()->session->add( $message, $type, $comment );
}

function gma_clear_notices() {
	global $gma;
	gma_lite()->session->clear();
}

add_action( 'gma_before_edit_form', 'gma_print_notices' );
add_action( 'gma_before_question_submit_form', 'gma_print_notices' );
add_action( 'gma_before_add_comment_form', 'gma_print_notices' );
function gma_print_notices( $comment = false ) {
	echo gma_lite()->session->echo_notices( $comment );
}

function gma_count_notices( $type = '', $comment = false ) {
	return gma_lite()->session->count( $type, $comment );
}

function gma_add_wp_error_message( $errors, $comment = false ) {
	if ( is_wp_error( $errors ) ) {
		gma_add_notice( $errors->get_error_message(), 'error', $comment );
	}
}

class GMA_Session {
	protected $_data = array();
	protected $_dirty = false;

	public function __get( $key ) {
		return $this->get( $key );
	}

	public function __set( $key, $value ) {
		$this->set( $key, $value );
	}

	public function __isset( $key ) {
		return isset( $this->_data[ sanitize_title( $key ) ] );
	}

	public function __unset( $key ) {
		if ( isset( $this->_data[ $key ] ) ) {
			unset( $this->_data[ $key ] );
			$this->_dirty = true;
		}
	}

	public function get( $key, $default = '' ) {
		$key = sanitize_key( $key );
		return isset( $this->_data[ $key ] ) ? maybe_unserialize( $this->_data[ $key ] ) : $default;
	}

	public function set( $key, $value ) {
		if ( $value !== $this->get( $key ) ) {
			$this->_data[ sanitize_key( $key ) ] = maybe_serialize( $value );
			$this->_dirty = true;
		}
	}

	public function add( $message, $type = 'success', $comment = false ) {
		if ( ! did_action( 'init' ) ) {
			_doing_it_wrong( __FUNCTION__, __( 'This function should not be called before init.', 'give-me-answer-lite' ), '1.0.0' );
			return;
		}

		$key = $comment ? 'gma-comment-notices' : 'gma-notices';

		$notices = $this->get( $key, array() );

		$notices[ $type ][] = $message;

		$this->set( $key, $notices );
    }

	public function clear() {
		if ( ! did_action( 'init' ) ) {
			_doing_it_wrong( __FUNCTION__, __( 'This function should not be called before init.', 'give-me-answer-lite' ), '1.0' );
			return;
		}

		$this->set( 'gma-notices', null );
	}

	public function print_notices( $comment = false ) {
		if ( ! did_action( 'init' ) ) {
			_doing_it_wrong( __FUNCTION__, __( 'This function should not be called before init.', 'give-me-answer-lite' ), '1.0' );
			return;
		}

		$key = $comment ? 'gma-comment-notices' : 'gma-notices';
		$notices = $this->get( $key, array() );
		$types = array( 'error', 'success', 'info', 'warning' );

		foreach( $types as $type ) {
			if ( $this->count( $type, $comment ) > 0 ) {
				foreach( $notices[ $type ] as $message ) {
					return sprintf( '<p class="alert alert-%s text-center">%s</p>', $type, $message );
				}
			}
		}

		gma_clear_notices();
	}

    public function echo_notices( $comment = false ) {
        if ( ! did_action( 'init' ) ) {
            _doing_it_wrong( __FUNCTION__, __( 'This function should not be called before init.', 'give-me-answer-lite' ), '1.0' );
            return;
        }

        $key = $comment ? 'gma-comment-notices' : 'gma-notices';
        $notices = $this->get( $key, array() );
        $types = array( 'error', 'success', 'info', 'warning' );

        foreach( $types as $type ) {
            if ( $this->count( $type, $comment ) > 0 ) {
                echo sprintf('<div class="alert alert-%s">', $type);
                foreach( $notices[ $type ] as $message ) {
                    echo sprintf( '<p>%s</p>', $message );
                }
                echo '</div>';
            }
        }

        gma_clear_notices();
    }

	public function count( $type = '', $comment = false ) {
		if ( ! did_action( 'init' ) ) {
			_doing_it_wrong( __FUNCTION__, __( 'This function should not be called before init.', 'give-me-answer-lite' ), '1.0' );
			return;
		}

		$key = $comment ? 'gma-comment-notices' : 'gma-notices';
		$all_notices = $this->get( $key, array() );

		$count = 0;
		if ( isset( $all_notices[ $type ] ) ) {
			$count = absint( sizeof( $all_notices[ $type ] ) );
		} elseif ( empty( $type ) ) {
			foreach( $all_notices as $notices ) {
				$count += absint( sizeof( $notices ) );
			}
		}

		return $count;
	}
}