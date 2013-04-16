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

	// TODO To be implemented
	// Sets the level for which logs are sent to SMS
	// protected static $sms_level;

	// TODO To be implemented
	// Sets the level for which logs are sent to email
	// protected static $email_level;

	// Sets the level for which logs are sent to the error log
	protected static $log_level;

	// Sets the level for which logs are sent to the browser
	protected static $output_level;
	
	// TODO To be implemented
	// protected static $sms_numbers = array();
	
	// TODO To be implemented
	// protected static $email_addresses = array();

	// TODO To be implemented
	// public static function add_sms($number, $level) {
	// 	if(!is_int($level)) {
	// 		error_log('[USE LOGGER] Logger::add_sms expected integer for second arg, instead received ' . var_export($level, true));
	// 	}
	// 	
	// 	if(!isset(self::$sms_numbers[$level])) {
	// 		self::$sms_numbers[$level] = array();
	// 	}
	// 	
	// 	if(in_array($number, self::$sms_numbers[$level])) {
	// 		error_log("[USE LOGGER] {$number} already set for Logger level {$level}");
	// 		return false;
	// 	}
	// 	
	// 	array_push(self::$sms_numbers[$level], $number);
	// 	return true;
	// }

	// TODO To be implemented
	// public static function add_email($email, $level) {
	// 	if(!is_int($level)) {
	// 		error_log('[USE LOGGER] Logger::add_email expected integer for second arg, instead received ' . var_export($level, true));
	// 	}
	// 	
	// 	if(!isset(self::$email_addresses[$level])) {
	// 		self::$email_addresses[$level] = array();
	// 	}
	// 	
	// 	if(in_array($number, self::$email_addresses[$level])) {
	// 		error_log("[USE LOGGER] {$email} already set for Logger level {$level}");
	// 		return false;
	// 	}
	// 	
	// 	array_push(self::$email_addresses[$level], $number);
	// 	return true;
	// }
	
	// TODO To be implemented
	// public static function set_sms_level($level) {
	// 	self::$sms_level = $level;
	// }

	// TODO To be implemented
	// public static function set_email_level($level) {
	// 	self::$email_level = $level;
	// }

	public static function set_log_level($level) {
		self::$log_level = $level;
	}

	public static function set_output_level($level) {
		self::$output_level = $level;
	}
	
	// TODO To be implemented
	// Send an SMS alert
	// protected static function send_sms($message='') {}

	// TODO To be implemented
	// Send an Email alert
	// protected static function send_email($message='') {}

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

			// SMS - To be implemented
			// if($level >= self::$sms_level) {
			// 	self::send_sms($log_msg);
			// }

			// Email - To be implemented
			// if($level >= self::$email_level) {
			// 	self::send_email($log_msg);
			// }

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