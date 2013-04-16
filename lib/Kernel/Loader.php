<?php

class Loader extends Base {
	// Internal tracker for loaded files
	protected static $loaded_files = array();
	
	// Keep track of the include paths
	protected static $include_path = array();
	
	// Sets the include path. Clobbers whatever existed before.
	public static function set_include_path($path) {
		if(!is_string($path) && !is_array($path)) {
			Logger::error(__METHOD__ . '() expects string or array as first arg. Recieved ' . var_export($path, true));
			return false;
		}
		
		if(is_string($path)) {
			$path = array($path);
		}
		
		self::$include_path = $path;
	}
	
	// Load a file from a "lib" folder and log it internally
	public static function load($file, $log_only=false) {
		if(!is_string($file)) {
			Logger::error(__METHOD__ . '() expects string as first arg. Recieved ' . var_export($file, true));
		}

		if(!!$log_only) {
			array_push(self::$loaded_files, $file);
			return true;
		}

		if(!self::is_loaded($file)) {
			foreach(self::$include_path as $path) {
				$fullpath = realpath($path . DS . parse_pl_path($file));
				if($fullpath && path_is_allowed($path) && file_exists($fullpath)) {
					require_once($fullpath);
					array_push(self::$loaded_files, $file);
					return true;
				}
			}
			return false;
		} else {
			Logger::warn(__METHOD__ . "() - {$file} was already loaded");
			return false;
		}
	}
	
	// Check if a lib file is already loaded
	public static function is_loaded($file) {
		if(!is_string($file)) {
			Logger::error(__METHOD__ . '() expects string as first arg. Recieved ' . var_export($file, true));
			return false;
		}
		
		return in_array($file, self::$loaded_files, true);
	}

	// Adds the module to the include path and loads the utilities.php file if it exists
	public static function add_module($name, $include_util=true) {
		// Check if the module folder exists
		$fullpath = realpath(BASE_DIR . DS . $name);
		if(!is_dir($fullpath)) {
			Logger::error(__METHOD__ . "() - Unable to find directory for module '{$name}' in " . BASE_DIR);
			return false;
		}

		// Check if the "lib" folder exists in the module
		$libpath = $fullpath . DS . 'lib';
		if(!is_dir($libpath)) {
			Logger::error(__METHOD__ . "() - Unable to find lib directory for module '{$name}'");
			return false;
		}
		
		// Add the path to the module to the include path
		array_push(self::$include_path, $libpath);

		if(!!$include_util) {
			// Load the utilities.php file associated with the module if one exists
			$utilpath = realpath($libpath . DS . 'utilities.php');
			if(path_is_allowed($utilpath) && file_exists($utilpath)) {
				require_once $utilpath;
			}
		}

		return true;
	}

	public static function dump($log=true) {
		if(DEBUG) {
			parent::dump($log);
		
			echo '<pre style="text-align:left;background-color:#FFF;font-size:12px;">';
			var_dump(self::$include_path);
			var_dump(self::$loaded_files);
			echo '</pre>';
		}
	}
}