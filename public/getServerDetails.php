<?php

$acceptedIps = array("95.97.95.106","95.97.95.110","178.238.100.50","81.204.12.180");

if(!in_array($_SERVER["REMOTE_ADDR"],$acceptedIps)) exit;


header('Cache-Control: no-cache, must-revalidate');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Content-type: application/json');

echo json_encode(ServerDetails::getInstance()->getData());

class ServerDetails {
	private $spaceNames = array( 'B', 'KB', 'MB', 'GB', 'TB', 'PT', 'EB', 'ZB', 'YB' );
	private $serverLoad;
	private $freeSpace;
	private $what;

	public static function getInstance(){
		return new self();
	}

	private function __construct(){
		
	}

	public function getData(){
		return new ServerDetailsModel($this->getServerLoad(),$this->getFreeSpace());
	}

	private function getServerLoad(){
		$load = sys_getloadavg();
		return array("1" => $load[0],"5" => $load[1], "15" => $load[2]);
	}

	private function getFreeSpace($dir = "/"){
		$bytes = disk_free_space($dir);
        $base = 1024;
    	$class = min((int)log($bytes , $base) , count($this->spaceNames) - 1);
    	$space = sprintf('%1.2f' , $bytes / pow($base,$class));
    	$MB 	= ceil($bytes/ (1024*1024));
    	return array("space" => sprintf('%s%s', $space,$this->spaceNames[$class]), "MB" => $MB);
	}
}

class ServerDetailsModel {
	public $serverLoad;
	public $freeSpace;
	public $date;

	public function __construct($load = null,$freeSpace = null){
		$this->date = date("Y-m-d H:i:s");
		$this->serverLoad = $load;
		$this->freeSpace = $freeSpace;
	}
}