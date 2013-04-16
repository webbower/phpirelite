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

$debug = isset($opts['d']);

if($debug) {
	echo "\nOpts\n";
	print_r($opts);

	echo "\nARGV\n";
	print_r($argv);
}

if(isset($opts['h']) || isset($opts['help'])) {
	usage();
}

$script = $argv[0];
$action = $argv[1];
$subaction = $argv[2];
$target = $argv[3];

if($action === 'app') {
	switch($subaction) {
		case 'new':
			new_app($target);
			break;
	
		default:
			
			break;
	}
}

function usage() {
	echo <<<USAGE

phpire <action> <subaction> ...

App:
phpire app new <name>

Creates a new app skeleton

USAGE;
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
		echo "[ABORT] An app named {$name} already exists at " . dirname($app_path);
		exit(2);
	}

	mkdir($app_path, 0755);
	mkdir($web_path, 0755);
	mkdir($lib_path, 0755);
	echo "[INFO] Created base app folder structure\n";
	file_put_contents($web_path . DS . '.htaccess', $htaccess);
	file_put_contents($web_path . DS . 'index.php', $index);
	echo "[INFO] Created app web files\n";
	file_put_contents($lib_path . DS . 'config.php', $config);
	file_put_contents($lib_path . DS . $app_class . '.php', $app);
	echo "[INFO] Created app lib files\n";
	echo "[SUCCESS] New app '{$name}' created successfully. Point your webroot to {$web_path}";
	exit;
}

function new_module() {}

