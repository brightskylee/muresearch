<?php

class URLParser{

	protected $context;
	protected $url;

	public function __construct(){
		$argv = func_get_args();
		switch( func_num_args() ){
			case 1:
				self::__construct_without_context($argv[0]);
				break;
			case 2:
				self::__construct_with_context($argv[0]. $argv[1]);
				break;
		}
	}

	public function __construct_without_context($url){
		$this->url = $url;
		$this->context = null;
	}

	public function __construct_with_context($url, $context){
		$this->url = $url;
		$this->context = $context;
	}
	
	public function XMLToArray(){
		if($this->context == null){
			$raw_xml = file_get_contents($this->url);
		}
		else{
			$raw_xml = file_get_contents($this->url, $this->context);
		}

		if($raw_xml == FALSE){
			trigger_error("file get contents returned false", E_USER_ERROR);
			return null;
		}

		$ret_array = simplexml_load_string($raw_xml, null, LIBXML_NOCDATA);
		$ret_array = json_decode(json_encode($ret_array), TRUE);

		return $ret_array;
	}
}
	
	