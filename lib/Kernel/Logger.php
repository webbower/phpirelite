<?php

define('PL_LOGGER_INFO', 1);
define('PL_LOGGER_WARN', 2);
define('PL_LOGGER_ERROR', 4);
define('PL_LOGGER_CRITICAL', 8);

class Logger extends Base {
	protected static $prefixes = array(
		1 => 'INFO',
		2 => 'WARN',
		4 => 'ERROR',
		8 => 'CRITICAL',
	);

	// Level for which logs are sent to the error log
	protected static $log_level;

	// Level for which logs are sent to the browser
	protected static $output_level;
	
	// Sets the level for which logs are sent to the error log
	public static function set_log_level($level) {
		self::$log_level = $level;
	}

	// Sets the level for which logs are sent to the browser
	public static function set_output_level($level) {
		self::$output_level = $level;
	}
	
	// Send the error to the error log
	protected static function log_error($message='') {
		error_log("{$message}");
	}

	// Output the error to the browser
	protected static function display_error($message='') {
		echo $message;
	}
	
	// Generic log and alert handler
	public static function raise($message, $level=null) {
		$stack = debug_backtrace();
		
		for($stack_offset = 0, $il = sizeof($stack); $stack_offset < $il; $stack_offset++) {
			if(get_array_value($stack[$stack_offset], 'class') !== 'Logger') {
				$stack_offset--;
				break;
			}
		}
		
		$stack_meta = sprintf('%s%s%s() %s on line %s',
			isset($stack[$stack_offset + 1]['class']) ? $stack[$stack_offset + 1]['class'] : '', // Caller class
			isset($stack[$stack_offset + 1]['type']) ? $stack[$stack_offset + 1]['type'] : '', // Caller relation
			isset($stack[$stack_offset + 1]['function']) ? $stack[$stack_offset + 1]['function'] : '', // Caller method
			relative_app_path($stack[$stack_offset]['file']), // App-relative path to file called in
			$stack[$stack_offset]['line'] // Line number
		);

		// If a level is set, determine where to output logging message (sms, email, log, output)...
		if($level) {
			$prefix = self::$prefixes[$level];
			
			$log_msg = "[LOG:{$prefix}] {$message} ({$stack_meta})";

			// Output
			if($level >= self::$output_level) {
				self::display_error($log_msg);
			}

			// Log
			if($level >= self::$log_level) {
				self::log_error($log_msg);
			}
		}
		// ...otherwise, just be a pass-through to error_log
		else {
			self::log_error("[LOG] {$message} {$stack_meta}");
		}
	}

	///// Convenience logging methods for Logger::raise
	public static function info($message) {
		return self::raise($message, PL_LOGGER_INFO);
	}

	public static function warn($message) {
		return self::raise($message, PL_LOGGER_WARN);
	}

	public static function error($message) {
		return self::raise($message, PL_LOGGER_ERROR);
	}

	public static function critical($message) {
		return self::raise($message, PL_LOGGER_CRITICAL);
	}
}