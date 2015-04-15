<?php
/**
 * 
 * @author Ivan Rodriguez Asqui
 *
 */
class Ajax_SequenceEditorProcessor {
	
	
	function process($data){
		try {
			$this->doProcess($data);
			$res = array('result'=>'ok', 'msg'=> 'All changes saved.');
		} catch (Exception $e) {
			$res = array('result'=>'error', 'message'=>$e->getMessage());
		}
		
		return $res;
	}
	
	protected function doProcess($data){
		My_Logger::log(__METHOD__ . ': ' . var_export($data, true));
		$seq = Model_Quiz_Sequence::load($data['id']);
		if(!$seq){
			throw new Exception('Invalid sequence');
		}
		
		$db = Zend_Registry::get('db');
		$db->beginTransaction();
		
		try {
			//remove previous
			$db->delete('sequence_quiz', array('sequence_id=?'=> $seq->id));
			
			$items = $this->get($data['items'], array());
			$cnt = 1;
			foreach($items as $quiz_id){
				$db->insert('sequence_quiz', array(
						'sequence_id' => $seq->id,
						'quiz_id' => $quiz_id,
						'position' => $cnt
				));
				$cnt++;
			}
			$db->commit();
		} catch (Exception $e) {
			$db->rollBack();
			throw $e;
		}
		
		
		
	}
	
	function get(&$var, $default=null) {
	    return isset($var) ? $var : $default;
	}
	
	//function getParam
}
