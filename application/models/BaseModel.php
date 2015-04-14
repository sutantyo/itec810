<?php
/**
 * @author Ivan Rodriguez
 *
 */
abstract class Model_BaseModel {
	
	protected $id;
	protected $columnList;
	
	//abstract public function getTable();
	
	public function fromData($param) {
		/*if (!is_array($param))
			return;*/
		foreach ($param as $key => $value) {
			if(in_array($key, $this->columnList))
				$this->$key = $value;
		}
	}
	
	public function toArray() {
		//return get_object_vars($this);
		$res = array();
		foreach ($this->columnList as $key){
			$res[$key] = $this->$key;
		}
		return $res;
	}
	
	
	
	public function __set($name, $value) {
		//My_Logger::log(__METHOD__ .": name:$name, value: $value");
		$mutator = 'set' . ucfirst($name);
		if (method_exists($this, $mutator) && is_callable(array($this, $mutator)))
			return $this->$mutator($value);
		if (property_exists($this, $name))
			return $this->$name = $value;
		throw new \Exception('This property (' . $name . ') does not exist in ' . __CLASS__);
	}
	
	public function __get($name) {
		//My_Logger::log(__METHOD__ .": $name");
		$accessor = 'get' . ucfirst($name);
		if (method_exists($this, $accessor) && is_callable(array($this, $accessor)))
			return $this->$accessor();
		if (property_exists($this, $name))
			return $this->$name;
		throw new \Exception('This property (' . $name . ') does not exist in ' . __CLASS__);
	}
	
	function isNew(){
		return is_null($this->id);
	}
	
	function save(){
		if($this->isNew()){
			$this->insert();
			//$this->inserted_id = $obj->id;
		}else{
			$this->update();
			//$this->edited_id = $obj->id;
		}
		return $this->id;
	}
	
	public function insert() {
		$db = Zend_Registry::get('db');
		try {
			$db->insert(static::getTable(), $this->getInsertArray() );
			$this->id = $db->lastInsertId();
			return $this->id;
		} catch (Exception $e) {
			//	echo $exc->getMessage();
			throw $e;
		}
	}
	
	function getInsertArray(){
		return $this->toArray();
	}
	
	public function update() {
		$db = Zend_Registry::get('db');
		try {
			$db->update(static::getTable(), $this->getUpdateArray(), array('id=?' => $this->id));
		} catch (Exception $e) {
			//echo $exc->getMessage();
			throw $e;
		}
	}
	
	function getUpdateArray(){
		return $this->toArray();
	}
	
}