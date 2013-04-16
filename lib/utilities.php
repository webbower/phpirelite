<?php

/**
 * UTILITY FUNCTIONS
 * 
 */

function get($key=null) {
	if(!is_string($key) && !is_null($key)) {
		Logger::error(__FUNCTION__ . '() expecting string or null as first arg. ' . gettype($key) . ' passed instead.');
		return;
	}

	if(is_null($key)) {
		return $_GET;
	}

	return get_array_value($_GET, $key);
}

function post($key=null) {
	if(!is_string($key) && !is_null($key)) {
		Logger::error(__FUNCTION__ . '() expecting string or null as first arg. ' . gettype($key) . ' passed instead.');
		return;
	}

	if(is_null($key)) {
		return $_POST;
	}

	return get_array_value($_POST, $key);
}

function in_clause($values, $wrap_in_quotes=false) {
	if(is_scalar($values)) {
		$values = array($values);
	}
	
	if(!is_array($values)) {
		Logger::error(__FUNCTION__ . '() expecting array or scalar as first arg. ' . gettype($values) . ' passed instead.');
		return;
	}
	
	$glue = $wrap_in_quotes ? "','" : ",";
	$wrapper = $wrap_in_quotes ? "'" : "";
	
	return " IN ({$wrapper}" . implode($glue, $valuse) . "{$wrapper})";
}

function debug($var=null) {
	if(DEBUG) {
		echo '<pre style="text-align:left;background-color:#FFF;font-size:12px;">';
		var_dump($var);
		echo '</pre>';
	}
}

// Verify a path is not outside the base directory
function path_is_allowed($path) {
	$allowed = (strpos($path, BASE_DIR) !== false);

	if(!$allowed) {
		Logger::error("Script attempted to load {$path}");
	}

	return $allowed;
}

function parse_pl_path($path) {
	return str_replace(':', DS, $path);
}

// Get the current environment
function get_env() {
	return PL_ENV;
}

// Convenience methods for checking the environment type
function is_dev() {
	return get_env() === 'dev';
}

function is_stage() {
	return get_env() === 'stage';
}

function is_prod() {
	return get_env() === 'prod';
}

// Used for dependency checking
// function pl_version_check($version) {
// 	$regex = '!((\d+)\.(\d+)\.(\d+))(a|b|rc)?(\d+)?!';
// 
// 	// Parse out PHPireLite version number
// 	if(preg_match($regex, PL_VERSION, $pl_version_parts) === false) {
// 		$message = "PHPireLite version number is improperly formatted";
// 		if(class_exists('Logger')) {
// 			Logger::error($message);
// 		}
// 		else {
// 			error_log("[ERROR] {$message}");
// 		}
// 		
// 		return false;
// 	}
// 
// 	// Parse out passed in version number
// 	if(preg_match($regex, $version, $version_parts) === false) {
// 		$message = "Version number passed in to pl_version_check() is improperly formatted";
// 		if(class_exists('Logger')) {
// 			Logger::error($message);
// 		}
// 		else {
// 			error_log("[ERROR] {$message}");
// 		}
// 		
// 		return false;
// 	}
// 
// 	$pl_full = $pl_version_parts[0];
// 	$pl_version = $pl_version_parts[1];
// 	$pl_major = (int) $pl_version_parts[2];
// 	$pl_minor = (int) $pl_version_parts[3];
// 	$pl_bugfix = (int) $pl_version_parts[4];
// 	$pl_pre = get_array_value($pl_version_parts, 5);
// 	$pl_pre_version = get_array_value($pl_version_parts, 6);
// 	
// 	if(!is_null($pl_pre_version)) {
// 		$pl_pre_version = (int) $pl_pre_version;
// 	}
// 	
// 	var_export($pl_full);
// 	echo "<br />";
// 	var_export($pl_version);
// 	echo "<br />";
// 	var_export($pl_major);
// 	echo "<br />";
// 	var_export($pl_minor);
// 	echo "<br />";
// 	var_export($pl_bugfix);
// 	echo "<br />";
// 	var_export($pl_pre);
// 	echo "<br />";
// 	var_export($pl_pre_version);
// 
// 	$full = $version_parts[0];
// 	$version = $version_parts[1];
// 	$major = (int) $version_parts[2];
// 	$minor = (int) $version_parts[3];
// 	$bugfix = (int) $version_parts[4];
// 	$pre = get_array_value($version_parts, 5);
// 	$pre_version = get_array_value($version_parts, 6);
// 	
// 	if(!is_null($pre_version)) {
// 		$pre_version = (int) $pre_version;
// 	}
// }
