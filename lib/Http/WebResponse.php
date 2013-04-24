<?php

class WebResponse extends Base {
	protected static $statuses = array(
		200 => "OK",
		201 => "Created",
		202 => "Accepted",
		203 => "Non-Authoritative Information", // (since HTTP/1.1)
		204 => "No Content",
		205 => "Reset Content",
		206 => "Partial Content",
		300 => "Multiple Choices",
		301 => "Moved Permanently",
		302 => "Found",
		303 => "See Other", // (since HTTP/1.1)
		304 => "Not Modified",
		305 => "Use Proxy", // (since HTTP/1.1)
		306 => "Switch Proxy",
		307 => "Temporary Redirect", // (since HTTP/1.1)
		308 => "Permanent Redirect", // (approved as experimental RFC)[12]",
		400 => "Bad Request",
		401 => "Unauthorized",
		402 => "Payment Required",
		403 => "Forbidden",
		404 => "Not Found",
		405 => "Method Not Allowed",
		406 => "Not Acceptable",
		407 => "Proxy Authentication Required",
		408 => "Request Timeout",
		409 => "Conflict",
		410 => "Gone",
		411 => "Length Required",
		412 => "Precondition Failed",
		413 => "Request Entity Too Large",
		414 => "Request-URI Too Long",
		415 => "Unsupported Media Type",
		416 => "Requested Range Not Satisfiable",
		417 => "Expectation Failed",
		418 => "I'm a teapot", // (RFC 2324)
		426 => "Upgrade Required", // (RFC 2817)
		428 => "Precondition Required", // (RFC 6585)
		429 => "Too Many Requests", // (RFC 6585)
		431 => "Request Header Fields Too Large", // (RFC 6585)
		444 => "No Response", // (Nginx)
		449 => "Retry With", // (Microsoft)
		450 => "Blocked by Windows Parental Controls", // (Microsoft)
		451 => "Unavailable For Legal Reasons", // (Internet draft)
		494 => "Request Header Too Large", // (Nginx)
		495 => "Cert Error", // (Nginx)
		496 => "No Cert", // (Nginx)
		497 => "HTTP to HTTPS", // (Nginx)
		499 => "Client Closed Request", // (Nginx)
		500 => "Internal Server Error",
		501 => "Not Implemented",
		502 => "Bad Gateway",
		503 => "Service Unavailable",
		504 => "Gateway Timeout",
		505 => "HTTP Version Not Supported",
		506 => "Variant Also Negotiates", // (RFC 2295)
		509 => "Bandwidth Limit Exceeded", // (Apache bw/limited extension)
		510 => "Not Extended", // (RFC 2774)
		511 => "Network Authentication Required", // (RFC 6585)
		598 => "Network read timeout error", // (Unknown)
		599 => "Network connect timeout error", // (Unknown)
	);

	// Request headers
	protected $headers = array();

	// Request body. Array, String, or null
	protected $body = '';

	protected $status = 200;
	
	protected $http_ver = '1.1';
	
	protected $charset = 'utf-8';
	
	protected $send_body = true;
	
	public function __construct($status = 200, array $headers = array(), $body = '') {
		parent::__construct();
		
		$this->status = $status;
		$this->headers = $headers;
		$this->body = $body;
	}
	
	public function send() {
		// Set status code
		header($this->get_status());
		
		// Set other headers
		foreach($this->headers as $name => $value) {
			header("{$name}: {$value}");
		}
		
		if($this->send_body) {
			echo $this->get_body();
		}
		exit;
	}
	
	public function get_body() {
		return $this->body;
	}
	
	public function set_body($data = '', $append = true) {
		if(!!$append) {
			$this->body .= $data;
		} else {
			$this->body = $data;
		}
	}
	
	public function get_header($key = null) {
		if(is_null($key)) {
			return $this->headers;
		}
		
		return get_array_value($this->headers, $key);
	}
	
	public function set_header($key, $value = null) {
		if(is_string($key)) {
			$key = array($key => $value);
		}
		
		foreach($key as $name => $value) {
			$this->headers[$name] = $value;
		}
	}
	
	public function get_status() {
		$status_label = get_array_value(self::$statuses, $this->status);
		
		if(!$status_label) {
			// Log error
		}
		
		return "HTTP/{$this->http_ver} {$this->status} {$status_label}";
	}
	
	public function set_status($code = 200) {
		$this->status = $code;

		// 3xx doesn't usually send a response body, just the headers
		if($status > 299 && $status < 400) {
			$this->send_body(false);
		}
	}
	
	public function send_body($flag) {
		$this->send_body = !!$flag;
	}
}