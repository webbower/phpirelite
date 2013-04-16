<?php

define('PL_VERSION', '0.1.0');

// These utility functions are needed before we can safely include utilities.php
function get_array_value(array $array, $key, $default=null) {
	if(!is_string($key) && !is_int($key)) {
		Logger::error(__FUNCTION__ . '() expecting string or integer as second arg. ' . gettype($key) . ' passed instead.');
		return;
	}
	
	if(isset($array[$key])) {
		return $array[$key];
	}
	
	return $default;
}

function relative_app_path($path) {
	return str_replace(BASE_DIR, '{BASE}', $path);
}

// Include baseline to get everything up and running
require_once(LIB_DIR . DS . 'lib' . DS . 'Kernel' . DS . 'Base.php');
require_once(LIB_DIR . DS . 'lib' . DS . 'Kernel' . DS . 'Logger.php');
require_once(LIB_DIR . DS . 'lib' . DS . 'utilities.php');
require_once(LIB_DIR . DS . 'lib' . DS . 'Kernel' . DS . 'Loader.php');

// Set the default inclusion path
Loader::set_include_path(array(
	LIB_DIR . DS . 'lib',
	APP_DIR . DS . 'lib',
));

$foo = new stdClass();

Loader::set_include_path($foo);

// Register that these classes have been loaded already
Loader::load('Kernel:Base.php', true);
Loader::load('Kernel:Logger.php', true);
Loader::load('Kernel:Loader.php', true);

// Include environment file
require_once(BASE_DIR . DS . 'pl_env.php');

ini_set('display_errors', PL_DISPLAY_ERRORS);

// Load App base class
Loader::load('Kernel:App.php');

// Load App Config
require_once(APP_DIR . DS . 'lib' . DS . 'config.php');

App::run();