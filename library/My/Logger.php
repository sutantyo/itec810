<?php
class My_Logger{
  
  static protected $local;
  
  static public function log($msg){ 
    /*if (!self::isLocal()){
      return;
    }*/
    $filename = __DIR__. "/debuglog.txt";
    // open file
    $fd = fopen($filename, "a");
    // append date/time to message
    $str = "[" . date("Y/m/d H:i:s") . "] " . $msg;  
    // write string
    fwrite($fd, $str . "\n");
    // close file
    fclose($fd);
  }
  
  static public function isLocal() {
    if (!isset(self::$local)){
      self::$local = strpos(dirname(__FILE__), 'sites') !== false;
    }
    return self::$local;
  }
  
  static public function clearLog($logThis=false,$place='')
  {
  	if(!self::isLocal()){
  		return;
  	}
  
    $filename = dirname(__FILE__). '/debuglog.txt';
    
  	$fd = fopen($filename, 'w');
  	ftruncate($fd,0);
  	fflush($fd);
  	fclose($fd);
  	//file_put_contents($filename, '');
  	if($logThis) self::log(__METHOD__ . ": $place Log cleared");
  }
  
  
  static public function getArrayParam($paramName, $array, $default=false){
  	if(is_array($array)){
  		if(isset($array[$paramName])){
  			return $array[$paramName];
  		}
  	}
  	return $default;
  }
  
}