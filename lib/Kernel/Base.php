<?php

class Base {
	public $class;
	
	public function __construct() {
		$this->class = get_class($this);
	}
	
	public static function dump() {}
}