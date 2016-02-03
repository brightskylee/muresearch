<?php

class ResearchQuery{

	private $key;
	private $type;
	private $searchQuery;
	private $mc;

	public function __construct(string $type, string $queryStr){

		$this->mc = new Memcached();
		$this->mc->addServer("localhost", 11211);

		$this->type = $type;
		$this->searchQuery = $queryStr;
		$this->key = sha1($type.$queryStr);
	}

	public function getResult($call_back){
		
		/* If memcached already has the key */
		$record = $this->mc->get($this->key);
		if(!empty($record)){
			return $this->mc->get($this->key);
		}
		
		/* If memcached does NOT have the key. Store/return it */

		$result = call_back($this->key);
		$this->mc->set($this->key, $result, time()+300);
		return $result;
	}
}

?>
				
