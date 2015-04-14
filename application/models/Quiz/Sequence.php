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

	function getTable(){
		return 'sequence';
	}
	
	function getInsertArray(){
		$res = $this->toArray();
		unset($res['created_at']);
		return $res;
	}
	
	static public function getAll(){
		$sql = "SELECT * FROM sequence ORDER BY id DESC";
		$db = Zend_Registry::get('db');
		return $db->fetchAll($sql);
	}
	
}

