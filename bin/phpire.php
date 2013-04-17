<?php

define('PL_CLI', true);

// Shorthand for OS-aware directory separator
define('DS', DIRECTORY_SEPARATOR);

// Full path to the framework folder
define('LIB_DIR', dirname(dirname(__FILE__)));

// Full path to the framework folder
define('LIB_NAME', basename(LIB_DIR));

// Full path to the base directory that stores the framework folder, and all the app and module folders
define('BASE_DIR', dirname(LIB_DIR));

function writeln($message) {
	echo "{$message}\n";
}

// PHPire it up!!! ;-)
// Include baseline to get everything up and running
// require_once(LIB_DIR . DS . 'lib' . DS . 'Kernel' . DS . 'Base.php');
// require_once(LIB_DIR . DS . 'lib' . DS . 'Kernel' . DS . 'Logger.php');
// require_once(LIB_DIR . DS . 'lib' . DS . 'utilities.php');
// require_once(LIB_DIR . DS . 'lib' . DS . 'Kernel' . DS . 'Loader.php');

// Set the default inclusion path
// Loader::set_include_path(array(
// 	LIB_DIR . DS . 'lib',
// ));

$opts = getopt(
	'hd',
	array(
		'help',
	)
);

$debug = (isset($opts['d']) || isset($opts['debug']));

if($debug) {
	echo "\nOpts\n";
	print_r($opts);

	echo "\nARGS\n";
	echo "\nNumber of args: {$argc}\n";
	print_r($argv);
}

// Figure out a better way to detect showing Usage and shorting out with lack of arguments
if($argc < 2 || isset($opts['h']) || isset($opts['help'])) {
	usage();
}

$script = $argv[0];
$action = $argv[1];

// No sub-action
if(in_array($action, array('init'))) {
	$env = isset($argv[2]) && in_array($argv[2], array('dev','stage','prod')) ? $argv[2] : 'dev';
}
// Sub-action
else if(in_array($action, array('app'))) {
	$subaction = $argv[2];
	$target = $argv[3];
}
else {
	usage();
}

if($action === 'app') {
	switch($subaction) {
		case 'new':
			new_app($target);
			break;
	
		default:
			
			break;
	}
}
else if ($action === 'init') {
	init($env);
}

function usage() {
	echo <<<USAGE

phpire <action> <subaction> ...

Usage:
phpire -h
-OR-
phpire --help

App:
phpire app new <name>

Creates a new app skeleton

USAGE;
	exit;
}

function init($env='dev') {
	$env_file = BASE_DIR . DS . 'pl_env.php';
	
	if(file_exists($env_file)) {
		writeln('[INFO] Env file already exists. Aborting.');
		exit;
	}
	
	$out = <<<ENV
<?php

// Environment Configuration
// This file should be different on each machine and generally NOT included in source control

// PL_ENV expected values: dev, stage, prod
define('PL_ENV', '{$env}');

ENV;

	$dev_env = <<<DEV
define('DEBUG', true);
define('PL_DISPLAY_ERRORS', true);

Logger::set_log_level(PL_LOGGER_INFO);
Logger::set_output_level(PL_LOGGER_ERROR);
DEV;

	$stage_env = <<<STAGE
define('DEBUG', false);
define('PL_DISPLAY_ERRORS', false);

Logger::set_log_level(PL_LOGGER_WARN);
Logger::set_output_level(PL_LOGGER_ERROR);
STAGE;

	$prod_env = <<<PROD
define('DEBUG', false);
define('PL_DISPLAY_ERRORS', false);

Logger::set_log_level(PL_LOGGER_ERROR);
PROD;

	switch($env) {
		case 'prod':
			$out .= $prod_env;
			break;

		case 'stage':
			$out .= $stage_env;
			break;

		case 'dev':
		default:
			$out .= $dev_env;
			break;
	}

	chmod(LIB_DIR . DS . 'bin' . DS . 'phpire', 0744);
	writeln('[INFO] Permissions set on phpire CLI tool.');

	file_put_contents(BASE_DIR . DS . 'pl_env.php', $out);
	writeln('[INFO] Env file created.');

	writeln('[SUCCESS] Init successful.');

	exit;
}

function new_app($name) {
	$libname = LIB_NAME;
	$app_class = ucwords($name);

	$htaccess = <<<HT
<IfModule mod_rewrite.c>
	RewriteEngine On

	RewriteCond %{REQUEST_URI} ^(.*)$
	RewriteCond %{REQUEST_FILENAME} !-f
	RewriteRule .* index.php/%1 [QSA]
</IfModule>
HT;

	$index = <<<INDEX
<?php
/////////////////////////////////////////////////////////
// PHPirelite Front Controller
/////////////////////////////////////////////////////////

// Name of the folder containing the PHPirelite framework
\$framework_dirname = '{$libname}';

// TODO Implement
// Where to pull routed URL paths from. Used with Router.
// Set to "pathinfo" to use PATH_INFO or any other string to use that URL param
// \$url_source = 'pathinfo';

/////// DO NOT MODIFY AFTER THIS LINE /////////

//// Set project constants

// Shorthand for OS-aware directory separator
define('DS', DIRECTORY_SEPARATOR);

// The full path to the app directory (this directory)
define('APP_DIR', dirname(dirname(__FILE__)));

// The name of the app directory
define('APP_NAME', basename(APP_DIR));

// Full path to the base directory that stores the framework folder, and all the app and module folders
define('BASE_DIR', dirname(APP_DIR));

// Full path to the framework folder
define('LIB_DIR', BASE_DIR . DS . \$framework_dirname);

// PHPire it up!!! ;-)
require_once(LIB_DIR . DS . 'lib' . DS . 'bootstrap.php');
INDEX;

	$config = <<<CONFIG
<?php
	
Loader::load('{$app_class}.php');

App::set_app('{$app_class}');
CONFIG;

	$app = <<<APP
<?php

class {$app_class} extends App {
	protected function main() {
		echo "New App '{$app_class}' successfully created!";
	}
}
APP;


	$app_path = BASE_DIR . DS . $name;
	$web_path = $app_path . DS . 'web';
	$lib_path = $app_path . DS . 'lib';

	if(is_dir($app_path)) {
		writeln("[ABORT] An app named {$name} already exists at " . dirname($app_path));
		exit(2);
	}

	mkdir($app_path, 0755);
	mkdir($web_path, 0755);
	mkdir($lib_path, 0755);
	writeln("[INFO] Created base app folder structure");
	file_put_contents($web_path . DS . '.htaccess', $htaccess);
	file_put_contents($web_path . DS . 'index.php', $index);
	writeln("[INFO] Created app web files");
	file_put_contents($lib_path . DS . 'config.php', $config);
	file_put_contents($lib_path . DS . $app_class . '.php', $app);
	writeln("[INFO] Created app lib files");
	writeln("[SUCCESS] New app '{$name}' created successfully. Point your webroot to {$web_path}");
	exit;
}

function new_module() {}

