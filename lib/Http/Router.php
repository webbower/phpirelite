<?php

/**
 * Router
 * 
 * Desired syntax:
 * 
 * Router::url('/foo/bar/:param)
 * 	->get('FooController::bar')
 * 	->post('FooController::bar_post')
 * ;
 * 
 */

class Router extends Base {
	protected static $routes = array();

	protected static $group_map = array();
	
	protected static $wildcard_group;
	
	protected static $use_grouping = false;
	
	protected static $initialized = false;

	// TODO Necessary?
	public static function init() {
		if(self::$initialized) {
			Logger::warn('Router::init() was already called.');
			return;
		}
		
		self::$initialized = true;
	}
	
	public static function enable_grouping() {
		self::$use_grouping = true;
		self::$wildcard_group = new RouteGroup('*');
	}
	
	public static function match_trailing_slash() {
		Route::match_trailing_slash();
	}
	
	public static function url($template, $add_to_group=true) {
		$route = new Route($template);
		
		if(self::$use_grouping) {
			// Opt-out of determining which group or a group cannot be parsed out, grab wildcard
			if(!$add_to_group || !($group_label = RouteGroup::parse_group($template))) {
				$group = self::$wildcard_group;
			}
			// If the group already exists, grab that
			else if(isset(self::$group_map[$group_label])) {
				$group = self::$group_map[$group_label];
			}
			// Create a new group
			else {
				$group = new RouteGroup($group_label);
				self::$group_map[$group_label] = $group;
				array_push(self::$routes, $group);
			}
			
			$group->push($route);
		} else {
			array_push(self::$routes, $route);
		}

		return $route;
	}
	
	public static function find_route($url) {
		if(self::$use_grouping) {
			// Add the wildcard group to the end
			array_push(self::$routes, self::$wildcard_group);
			
			// Loop over groups
			foreach(self::$routes as $group) {
				if($group->test($url)) {
					break;
				}
			}
			
			return $group->find_route($url);
		} else {
			foreach(self::$routes as $route) {
				if($route->test($url)) {
					return $route;
				}
			}
		}
		
		return null;
	}
	
	public static function dump($log=true) {
		if(DEBUG) {
			parent::dump($log);
		
			echo '<h2>Router</h2>';
			if(self::$use_grouping) {
				echo '<h3>Groups</h3>';
				echo '<pre style="text-align:left;background-color:#FFF;font-size:12px;">';
				print_r(self::$group_map);
				print_r(self::$wildcard_group);
				echo '</pre>';
			}

			echo '<h3>Routes</h3>';
			echo '<pre style="text-align:left;background-color:#FFF;font-size:12px;">';
			print_r(self::$routes);
			echo '</pre>';
		}
	}
}

class RouteGroup extends Base {
	protected static $group_regex = '!^(/(?:[a-zA-Z0-9_-]+)?)!';
	
	protected $routes = array();

	protected $group;

	public function __construct($group) {
		parent::__construct();
		$this->group = $group;
		return $this;
	}
	
	public static function parse_group($template) {
		if(preg_match(self::$group_regex, $template, $matches) > 0) {
			return $matches[1];
		}
		
		return null;
	}
	
	public function push(Route $route) {
		array_push($this->routes, $route);
	}

	public function test($url) {
		preg_match(self::$group_regex, $url, $matches);
		
		return (isset($matches[1]) && $this->group === $matches[1]) || $this->group === '*';
	}
	
	public function find_route($url) {
		foreach($this->routes as $route) {
			if($route->test($url)) {
				return $route;
			}
		}
		
		return null;
	}
}

class Route extends Base {
	protected static $match_trailing_slash = false;

	protected static $param_find_regex = ':[a-z]+';

	protected static $param_repl_regex = '([a-zA-Z0-9_-]+)';

	protected static $splat_find_regex = '\*[a-z]+';

	protected static $splat_repl_regex = '([a-zA-Z0-9_/-]+)';

	protected $route_params = array();

	protected $param_keys = array();

	protected $raw_template;

	protected $parsed_template;

	// Flag if the route doesn't have any placeholders. Will test with a straight
	// string comparison in that case for performance boost.
	protected $static_route = false;

	protected $handlers = array();
	
	public function __construct($template) {
		parent::__construct();
		$this->raw_template = $template;
		$components = self::parse_route_template($template);
		$parsed_template = $components['template'];
		$this->static_route = ($this->raw_template === $parsed_template);
		$this->parsed_template = "!^{$parsed_template}" . (self::$match_trailing_slash && substr($parsed_template, -1) !== '/' ? '/?' : '') . "$!";
		$this->param_keys = $components['params'];

		return $this;
	}

	public static function match_trailing_slash() {
		self::$match_trailing_slash = true;
	}

	public function get($handler) {
		return $this->create_route('GET', $handler);
	}
	
	public function head($handler) {
		return $this->create_route('HEAD', $handler);
	}
	
	public function post($handler) {
		return $this->create_route('POST', $handler);
	}
	
	public function put($handler) {
		return $this->create_route('PUT', $handler);
	}
	
	public function patch($handler) {
		return $this->create_route('PATCH', $handler);
	}
	
	public function delete($handler) {
		return $this->create_route('DELETE', $handler);
	}
	
	public function get_param($key=null) {
		if(is_null($key)) {
			return $this->route_params;
		}
		
		return get_array_value($this->route_params, $key);
	}
	
	public function get_handler($verb) {
		return get_array_value($this->handlers, strtoupper((string) $verb));
	}
	
	protected static function parse_route_template($template) {
		$result = array();
		
		preg_match_all('!' . self::$param_find_regex . '|' . self::$splat_find_regex . '!', $template, $matches, PREG_PATTERN_ORDER);
		
		$result['params'] = $matches[0];
		
		// Replace params
		$parsed_template = preg_replace('!' . self::$param_find_regex . '!', self::$param_repl_regex, $template);

		// Replace splats
		$parsed_template = preg_replace('!' . self::$splat_find_regex . '!', self::$splat_repl_regex, $parsed_template);
		
		$result['template'] = $parsed_template;
		
		return $result;
	}
	
	protected function create_route($verb, $handler) {
		if(isset($this->handlers[$verb])) {
			error_log("[USE LOGGER] Handler already set for {$verb} {$this->raw_template}");
			return;
		}

		$this->handlers[$verb] = $handler;
		return $this;
	}
	
	public function test($url) {
		if($this->static_route) {
			$route_match = $this->raw_template === $url || (self::$match_trailing_slash && ($this->raw_template . '/') === $url);
		} else {
			$route_match = preg_match_all($this->parsed_template, $url, $matches, PREG_SET_ORDER) > 0;
		}
		
		if($route_match) {
			if(isset($matches)) {
				array_shift($matches[0]);
				for($i = 0 ; $i < count($matches[0]) ; $i++) {
					$key = substr($this->param_keys[$i], 1);
					$this->route_params[$key] = $matches[0][$i];
				}
			}
		}
		
		return $route_match;
	}
	
}