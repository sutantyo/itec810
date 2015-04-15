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
	
	function getAvailableQuizzes(){
		$db = Zend_Registry::get('db');
		$sql = "SELECT
		q.quiz_id AS id,
		q.quiz_name AS `name`
		FROM
		quiz q
		INNER JOIN sequence s ON q.permissions_group = s.permissions_group
		AND q.quiz_id NOT IN (SELECT quiz_id FROM sequence_quiz sq WHERE sq.sequence_id=?)
				";
		
		return $db->fetchAll($sql, $this->id);
	}
	
	function getQuizzes(){
		$db = Zend_Registry::get('db');
		$sql = "SELECT
quiz.quiz_id AS id,
quiz.quiz_name AS `name`,
sequence_quiz.position
FROM
quiz
INNER JOIN sequence_quiz ON sequence_quiz.quiz_id = quiz.quiz_id
WHERE
sequence_quiz.sequence_id = ?
ORDER BY
sequence_quiz.position ASC";
		return $db->fetchAll($sql, $this->id);
	}
	
	function addQuiz(Model_Quiz_Quiz $quiz){
		$db = Zend_Registry::get('db');
		$current = $this->getQuizzes();
		try {
			$db->insert("sequence_quiz", array(
					'quiz_id' => $quiz->getID(),
					'sequence_id' => $this->id,
					'position' => count($current)+1
			));
			return true;
		} catch (Exception $e) {
			My_Logger::log($e->getMessage());
			return false;
		}
		
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

