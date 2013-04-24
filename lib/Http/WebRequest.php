<?php

class WebRequest extends Base {
	protected static $initialized = false;

	protected $readonly = false;

	protected static $session_request;
	
	protected static $allowed_verbs = array('GET', 'POST', 'PUT', 'DELETE', 'PATCH', 'HEAD');
	
	protected static $header_content_type_map = array(
		'text/html' => 'html',
		'application/xhtml+xml' => 'html',
		'application/xml' => 'xml',
		'text/xml' => 'xml',
		'application/json' => 'json',
		'text/javascript' => 'jsonp',
		'application/javascript' =>'jsonp',
		'application/ecmascript' =>'jsonp',
		'application/x-ecmascript' =>'jsonp',
		'text/plain' => 'txt',
		'application/x-www-form-urlencoded' => 'form',
		'multipart/form-data' => 'form',
		'*/*' => 'html',
	);
	
	protected $wants;

	protected $content_type;
	
	protected $verb;

	protected $url;

	// $_GET params
	protected $params = array();

	// Pieces of the URL path
	protected $segments = array();

	// Request headers
	protected $headers = array();

	// Request body. Array, String, or null
	protected $body;
	
	public function __construct($verb = 'GET', $url = null, array $headers = array(), $body = null, $readonly = false) {
		parent::__construct();
		
		$this->verb = $verb;
		
		$this->headers = $headers;
		
		// Store the whole URL
		$this->url = $url;
		
		// Parse out URL Params
		if(count($_GET) > 0) {
			foreach ($_GET as $key => $value) {
				$this->params[$key] = $value;
			}
		}

		// Parse out URL path segments
		$this->segments = explode('/', parse_url(substr($url, 1), PHP_URL_PATH));

		$this->body = $body;
		
		$this->readonly = !!$readonly;
	}
	
	// Called to initialize the session request
	public static function init() {
		// Prevent multiple firings
		if(self::$initialized) {
			return;
		}
		
		// Parse out the request headers from $_SERVER
		$headers = self::parse_headers_from_php();

		// Determine which HTTP verb we're handling
		$verb = strtoupper($_SERVER['REQUEST_METHOD']);
		// Support for overriding the verb for older clients via 'X-Http-Method-Override' header or '_method' URL param or body param
		if($verb === 'POST') {
			if(array_key_exists('X-Http-Method-Override', $headers)) {
				$verb = strtoupper($headers['X-Http-Method-Override']);
			}
			else if (array_key_exists('_method', $_REQUEST)) {
				$verb = strtoupper($_REQUEST['_method']);
			}
		}

		// If we've gotten a verb we don't support, default to GET
		// TODO Maybe throw a Bad Request or whichever is the proper status code for unsupported response instead?
		if(!in_array($verb, self::$allowed_verbs)) {
			$verb = 'GET';
		}
		
		// Parse request URL
		$url = $_SERVER['REQUEST_URI'];

		$body = null;

		// Parse request body which should only exist for certain types of requests
		if(!in_array($verb, array('GET', 'HEAD'))) {
			if(array_key_exists('Content-Type', $headers)) {
				$content_type_header = array_shift(explode(';', $headers['Content-Type']));
				$content_type = (array_key_exists($content_type_header, self::$header_content_type_map) ? self::$header_content_type_map[$content_type_header] : 'txt');
			}
		
			if(isset($content_type)) {
				switch ($content_type) {
					case 'json':
						 $body = self::parse_json_body();
						 break;
								 
					case 'form':
						$body = self::parse_form_body();
						break;
			
					default:
						$body = file_get_contents('php://input');
						break;
				}
			}
		}
		
		self::$session_request = new self($verb, $url, $headers, $body, true);
	}
	
	public static function session() {
		return self::$session_request;
	}
	
	// Returns request header if set
	public function get_header($name = null) {
		if(is_string($name) && array_key_exists($name, $this->headers)) {
			return $this->headers[$name];
		}
		else if(is_null($name)) {
			return $this->headers;
		}

		return null;
	}
	
	// Returns the HTTP Verb for the current request
	public function get_verb() {
		return $this->verb;
	}

	// Returns the full URL for the request
	public function get_url($absolute=false) {
		return $this->url;
	}

	// Convenience methods for checking the request verb
	public function is_get() {
		return $this->verb === 'GET';
	}
	
	public function is_post() {
		return $this->verb === 'POST';
	}
	
	public function is_put() {
		return $this->verb === 'PUT';
	}
	
	public function is_delete() {
		return $this->verb === 'DELETE';
	}
	
	public function is_patch() {
		return $this->verb === 'PATCH';
	}
	
	public function is_head() {
		return $this->verb === 'HEAD';
	}
	
	public function is_ajax() {
		return $this->get_header('X-Requested-With') === 'XMLHttpRequest';
	}
	
	// Determines what data format the client wants based on the Accepts header. Since multiple mime-types can be sent,
	// shorthand is used for the type, i.e. $type = 'json' would check for text/javascript, application/json, etc
	public function wants() {
		if(!$this->wants) {
			if($accept = $this->get_header('Accept')) {
				$accepts_types = explode(',', $accept);
				
				foreach($accepts_types as $mime) {
					$actual_mime = array_shift(explode(';', $mime));
					if(array_key_exists($actual_mime, self::$header_content_type_map)) {
						$this->wants = self::$header_content_type_map[$actual_mime];
						break;
					}
				}
				
				if(!$this->wants) {
					$this->wants = 'html';
				}
			}
		}

		return $this->wants;
	}
	
	public function param($key=null) {
		if(is_string($key) && array_key_exists($key, $this->params)) {
			return $this->params[$key];
		}
		else if(is_null($key)) {
			return $this->params;
		}

		return null;
	}
	
	// Returns the URL segment at (zero-based) index $index. e.g. /foo/bar/baz Request::segment(1) would return "bar"
	public function segment($index=0) {
		if(array_key_exists($index, $this->segments)) {
			return $this->segments[$index];
		}

		return null;
	}
	
	// Convenience method to get the Content-Type header or, alternatively, a simple indicator,
	// e.g. a application/x-www-form-urlencoded Content-Type would return "form" from this method
	public function type() {
		if(!$this->content_type) {
			if($mime = $this->get_header('Content-Type')) {
				$actual_mime = array_shift(explode(';', $mime));
				
				if(array_key_exists($actual_mime, self::$header_content_type_map)) {
					$this->content_type = self::$header_content_type_map[$actual_mime];
				}
			}
		}

		return $this->content_type;
	}
	
	// Returns the value of a top-level key (either strings or numeric indices) from the Request Body, or the whole body,
	// parsed accordingaly, if no key is passed (JSON parsed, XML converted to a DOM object, form data converted, etc).
	// This would intelligently handle different formats of submission, esp where keys are or are not used, for example
	// form or JSON data (supports keys) vs XML or CSV data (no good key support)
	public function body($key=null) {
		if(is_string($key) && is_array($this->body) && array_key_exists($key, $this->body)) {
			return $this->body[$key];
		}
		else if(is_null($key)) {
			return $this->body;
		}

		return null;
	}
	
	// Optionally, we can add convenience methods for other parts of the URL like Domain, User, Password, Port, Protocol, etc
	
	// INTERNAL METHODS //////////////////////

	// Borrowed from SilverStripe's Director::extract_request_headers() method https://github.com/silverstripe/sapphire/blob/3.0/control/Director.php
	protected static function parse_headers_from_php() {
		$headers = array();

		foreach($_SERVER as $key => $value) {
			if(substr($key, 0, 5) == 'HTTP_') {
				$key = substr($key, 5);
				$key = strtolower(str_replace('_', ' ', $key));
				$key = str_replace(' ', '-', ucwords($key));
				$headers[$key] = $value;
			}
		}

		if(isset($_SERVER['CONTENT_TYPE'])) $headers['Content-Type'] = $_SERVER['CONTENT_TYPE'];
		if(isset($_SERVER['CONTENT_LENGTH'])) $headers['Content-Length'] = $_SERVER['CONTENT_LENGTH'];

		return $headers;
	}
	
	protected static function parse_json_body() {
		// Manually parse request body
		if(
			($reqbody = trim(file_get_contents('php://input'))) &&
			strlen($reqbody) > 0
		) {
			$body = json_decode($reqbody, true);
		}
		// Return nuthin'
		else {
			$body = null;
		}
		
		return $body;
	}

	protected static function parse_form_body() {
		// Use $_POST if available since it's already parsed out
		if(isset($_POST) && count($_POST) > 0) {
			$body = array();
			foreach ($_POST as $key => $value) {
				$body[$key] = $value;
			}
		}
		// Manually parse request body
		else if(
			($reqbody = trim(file_get_contents('php://input'))) &&
			strlen($reqbody) > 0
		) {
				parse_str($reqbody, $body);
		}
		// Return nuthin'
		else {
			$body = null;
		}
		
		return $body;
	}
}
