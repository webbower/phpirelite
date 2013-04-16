<?php

class App extends Base {
	private static $app_class;

	private static $app_instance;

	private static $initialized = false;
	
	public function __construct() {
		parent::__construct();
	}
	
	public static function run() {
		if(!self::$app_class) {
			self::set_app();
		}
		
		if(!class_exists(self::$app_class)) {
			Logger::critical(__METHOD__ . '() - Unable to find class ' . self::$app_class);
			// error_log('[CRITICAL] Unable to find class ' . self::$app_class);
		}

		$class = self::$app_class;

		self::$app_instance = new $class();
		
		self::$app_instance->main();
	}

	public static function set_app($class='App') {
		if($class !== 'App' && !is_subclass_of($class, 'App')) {
			Logger::critical(__METHOD__ . "() - {$class} is not a subclass of App");
			return false;
		}
		
		self::$app_class = $class;
	}
	
	protected function main() {
		echo <<<HTML
		<!DOCTYPE html>
		<html lang="en">
		<head>
			<title>Welcome to PHPirelite</title>
		</head>
		<body>
			<h1>Welcome to PHPirelite</h1>
			
			<p>Congratulations. You&#3;re site is working. This is the default app powered by the {$this->class} class.</p>
		</body>
		</html>
HTML;
	}
}