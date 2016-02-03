<?php
include_once "databaseConfig.php";

class Sphinx{
	
	private $db;
	private $port;
	private $configFilePath;
	private $loggingPidFilePath;
	private $indexDataFilePath;
	private $table = 'sphinxPortManager';
	
    public function __construct(){
		
		$this->db = new mysqli(HOSTNAME, USERNAME, PASSWD, DATABASE);
		if($this->db->connect_error){
			die('Connect Error ('. $this->db->connect_errno. '):'. $this->db->connect_error);
		}
		
		while(true){
			$port = rand(20000, 30000);
			if($r = $this->db->query("INSERT INTO ".$this->table." VALUES(".$port.")")){
				$this->port = $port;
				break;
			}
			
		}
	}
	
	public function __destruct(){
		$this->db->close();
	}
	
	
	public function initiateFiles(){
		
		$this->configFilePath = getcwd() . "/sphinxConfigFiles/".$this->port.".conf";
		$this->loggingPidFilePath = getcwd()."/sphinxLoggingFiles/".$this->port;
		$this->indexDataFilePath = getcwd()."/sphinxIndexDataFiles/".$this->port;
		
		if(!fopen($this->configFilePath, "a")){
			die("Config file generate failed");
		}

		if(!mkdir($this->indexDataFilePath, 0777)){
			die("Cannot mkdir index data file folder");
		}
		
		if(!mkdir($this->loggingPidFilePath, 0777)){
			die("Cannot mkdir logging pid file folder");
		}
	}
	
	public function putMOSpace(){
		
		$content = "\nsource mospace
					{
					type			= mysql
					sql_host		= localhost
					sql_user		= root
					sql_pass		= 
					sql_db			= muresearch2
					sql_port		= 3306

					sql_query		= \\
						SELECT id, title, creator,subject,description,date,type,identifier,language,relation,publisher,contributor,rights,source \\
						FROM MOSpaceAll
			
					sql_attr_timestamp	= date
					sql_field_string = title
					sql_field_string = creator
					sql_field_string = subject
					sql_field_string = description
					sql_field_string = type
					sql_field_string = identifier
					sql_field_string = language
					sql_field_string = relation
					sql_attr_string = publisher
					sql_field_string = contributor
					sql_field_string = rights
					sql_field_string = source
				}

				index mospace
				{
					source			= mospace
					path			= ".$this->indexDataFilePath."/mospace
				}\n";
		
		$content = str_replace("\r", "", $content);
		if($fp = fopen($this->configFilePath, "a")){
			if(fprintf($fp, $content) == 0){
				die("Cannot write to config file");
			}
		}
		else{
			die("Cannot open file for writing");
		}
	}
	
	public function putPubmed(){
		
		$content = "\nindex pubmed
					{
				type			= rt
				rt_mem_limit		= 1024M
				path = ". $this->indexDataFilePath."/pubmed
				rt_field = title
				rt_field = authors
				rt_field = abstract
				rt_field = keywords
				rt_field = date
				rt_field = affiliations
				
				rt_attr_uint = pubid
				rt_attr_string = authors
				rt_attr_string = title
				rt_attr_string = abstract
				rt_attr_string = date
				rt_attr_string = url
				rt_attr_string = keywords
				rt_attr_string = affiliations
			}\n";
			
		$content = str_replace("\r", "", $content);
		if($fp = fopen($this->configFilePath, "a")){
			if(fprintf($fp, $content) == 0){
				die("Cannot write to config file");
			}
		}
		else{
			die("Cannot open file for writing");
		}
	}
	
	public function putIEEE(){
	
		$content = "\nindex ieee
					{
        			type = rt
        			rt_mem_limit = 1024M
        			path = ". $this->indexDataFilePath."/IEEE
    				rt_field = title
    				rt_field = authors
    				rt_field = pubtitle
    				rt_field = pubtype
    				rt_field = abstract
    				rt_attr_string = title
        			rt_attr_string = authors
        			rt_attr_string = pubtitle
    				rt_attr_string = pubtype
        			rt_attr_uint   = volume
    				rt_attr_uint   = issue
    				rt_attr_string = abstract
					rt_attr_string = affiliation
        			rt_attr_string = issn
        			rt_attr_string = mdurl
        			rt_attr_string = pdf   
       				}\n";
       				
       	$content = str_replace("\r", "", $content);
		if($fp = fopen($this->configFilePath, "a")){
			if(fprintf($fp, $content) == 0){
				die("Cannot write to config file");
			}
		}
		else{
			die("Cannot open file for writing");
		}
	}
	
	public function putGoogleScholar(){
			
		$content = "\nindex scholar
					{
					type			= rt
					rt_mem_limit		= 1024M

					path			= ".$this->indexDataFilePath."/googleScholar

    				rt_field = title
    				rt_field = year
					rt_field = url
    				rt_field = num_citations
    				rt_field = abstract;                                
	
    				rt_attr_uint = label
    				rt_attr_string = title
     				rt_attr_string = url
	
					rt_attr_string = year
    				rt_attr_string = num_citations                                       
					rt_attr_string= abstract
					}\n";
       				
       	$content = str_replace("\r", "", $content);
		if($fp = fopen($this->configFilePath, "a")){
			if(fprintf($fp, $content) == 0){
				die("Cannot write to config file");
			}
		}
		else{
			die("Cannot open file for writing");
		}
	}
	
	public function putNews(){
			
		$content = "\nindex news
					{
        			type                    = rt
        			rt_mem_limit            = 1024M

        			path                    = ".$this->indexDataFilePath."/news

    				rt_field = title
    				rt_field = pubdate

    				rt_attr_uint = label
    				rt_attr_string = title
        			rt_attr_string = link
        			rt_attr_string = pubdate

					}\n";
       				
       	$content = str_replace("\r", "", $content);
		if($fp = fopen($this->configFilePath, "a")){
			if(fprintf($fp, $content) == 0){
				die("Cannot write to config file");
			}
		}
		else{
			die("Cannot open file for writing");
		}
	}
	
	public function putEvents(){
		
		$content = "\nindex events
					{
        			type                    = rt
        			rt_mem_limit            = 1024M

        			path                    = ".$this->indexDataFilePath."/events

    				rt_field = title
   					rt_field = start
        			rt_field = end
        			rt_field = venue
        			rt_field = descr


   					rt_attr_uint = label
   					rt_attr_string = title
               		rt_attr_string = start
                    rt_attr_string = end
                    rt_attr_string = venue
                    rt_attr_string = link
                    rt_attr_string = descr
   
                    }\n";
       				
       	$content = str_replace("\r", "", $content);
		if($fp = fopen($this->configFilePath, "a")){
			if(fprintf($fp, $content) == 0){
				die("Cannot write to config file");
			}
		}
		else{
			die("Cannot open file for writing");
		}
	}
	
	public function putIndexer(){
    
    	$content = "\nindexer
					{
					mem_limit		= 1024M
					}\n";
       	$content = str_replace("\r", "", $content);
		if($fp = fopen($this->configFilePath, "a")){
			if(fprintf($fp, $content) == 0){
				die("Cannot write to config file");
			}
		}
		else{
			die("Cannot open file for writing");
		}		
	}
	
	public function putSearchd(){
		
		$content = "\nsearchd
					{
						listen			= ".$this->port.":mysql41
						log			= ".$this->loggingPidFilePath."/searchd.log
						query_log		= ".$this->loggingPidFilePath."/query.log
						read_timeout		= 5
						max_children		= 30
						pid_file		= ".$this->loggingPidFilePath."/searchd.pid
						seamless_rotate		= 1
						preopen_indexes		= 1
						unlink_old		= 1
						workers			= threads # for RT to work
						binlog_path		= ".$this->loggingPidFilePath."
					}\n";
		
		$content = str_replace("\r", "", $content);
		if($fp = fopen($this->configFilePath, "a")){
			if(fprintf($fp, $content) == 0){
				die("Cannot write to config file");
			}
		}
		else{
			die("Cannot open file for writing");
		}			
	}
	
	public function startSearchd(){
		shell_exec("indexer --config ".$this->configFilePath. " --all");
		shell_exec("searchd --config ".$this->configFilePath);
	}
	
	public function stopSearchd(){
		shell_exec("searchd --config ".$this->configFilePath." --stop");
	}
	
	public function releasePort(){
		if(!($r = $this->db->query("delete from ".$this->table." where port = ".$this->port))){
			die("release port failed");
		}
	}
	
	private function deleteDirectory($dir) {
		if (!file_exists($dir)) {
			return true;
		}

		if (!is_dir($dir)) {
			return unlink($dir);
		}

		foreach (scandir($dir) as $item) {
			if ($item == '.' || $item == '..') {
				continue;
			}
			
			//echo "deleting ".$dir."/".$item."<br>";
			
			if (!$this->deleteDirectory($dir . "/" . $item)) {
				return false;
			}

		}

		return rmdir($dir);
	}
	
	public function deleteSphinxFiles(){
		$this->deleteDirectory($this->configFilePath); //or trigger_error("Delete config file dir failed", E_USER_ERROR);
		$this->deleteDirectory($this->loggingPidFilePath); //or trigger_error("Delete logging file dir failed", E_USER_ERROR);
		$this->deleteDirectory($this->indexDataFilePath); //or trigger_error("Delete index file dir failed", E_USER_ERROR);
	}

	public function getPort(){
		return $this->port;
	}
}

//$nf = new Sphinx();
//echo $nf->getPort();
//$nf->initiateFiles();
//$nf->putMOSpace();
//$nf->putPubmed();
//$nf->putGoogleScholar();
//$nf->putIEEE();
//$nf->putNews();
//$nf->putEvents();
//$nf->putIndexer();
//$nf->putSearchd();

////$nf->startSearchd();
//sleep(120);
//$nf->stopSearchd();
//$nf->deleteSphinxFiles();
//$nf->releasePort();
//unset($nf);
