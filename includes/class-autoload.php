<?php  
function gma_autoload_function($className) {
	$classPath = explode('_', $className);



	if ( $classPath[0] != 'GMA' ) {
		return;
	}

	if ( $classPath[1] == 'Widgets' || $classPath[1] == 'widgets' ) {
		return;
	}
	// Drop 'Google', and maximum class file path depth in this project is 3.
	$classPathSlice = array_slice($classPath, 1, 2);
	if ( count( $classPath ) > 3 ) {
		for ($i=3; $i < count( $classPath ); $i++) { 
			$classPathSlice[1] .= '_' . $classPath[$i];
		}
	}

	if ( isset( $classPathSlice[ 0 ] ) && strtolower( $classPathSlice[ 0 ] ) == 'posts' ) {
	    $filePath = GMA_DIR . 'includes/PostTypes/class-posts-' . $classPathSlice[ 1 ] . '.php';
    } else {
        $filePath = GMA_DIR . 'includes/class-' . implode('-', $classPathSlice) . '.php';
    }

	if ( ! file_exists( $filePath ) ) {
		$filePath = GMA_DIR . 'includes/class-' . implode('/', $classPathSlice) . '.php';
	}

	if (file_exists($filePath)) {
		require_once($filePath);
	}
}
try {
    spl_autoload_register('gma_autoload_function');
} catch(Exception $exception ) {

}


?>