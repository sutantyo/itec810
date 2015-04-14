<?php

/**
 * 
 * @author Ivan Rodriguez
 *
 */
class Model_Quiz_Sequence extends Model_BaseModel
{
	protected $name; 
	protected $created_at; 
	protected $permissions_group; 

	protected $columnList = array('id', 'name', 'created_at', 'permissions_group');

	static function getTable(){
		return 'sequence';
	}
	
	function getInsertArray(){
		$res = $this->toArray();
		unset($res['created_at']);
		return $res;
	}
	
	static public function load($id){
		$db = Zend_Registry::get('db');
		$sql = "SELECT * FROM " . self::getTable() . " WHERE id=" . $db->quote($id);
		$res = $db->fetchRow($sql);
		if(empty($res)) return; 
		
		$obj = new self();
		$obj->fromData($res);
		//var_dump($obj); exit;
		//My_Logger::log(print_r($obj, true));
		return $obj;
	}
	
	static public function getAll(){
		$sql = "SELECT * FROM sequence ORDER BY id DESC";
		$db = Zend_Registry::get('db');
		return $db->fetchAll($sql);
	}
	
}

