<?php
/**
 * 
 * @author Ivan Rodriguez Asqui
 *
 */
class Ajax_TemplateEditorProcessor {
	
	
	function process($data){
		My_Logger::log(__METHOD__ . ': ' . var_export($data, true));
		try {
			$res = $this->doProcess($data); 
		} catch (Exception $e) {
			$res = array('result'=>'error', 'msg'=>$e->getMessage());
		}
		
		return $res;
	}
	
	protected function doProcess($data){
		$method = $this->get($data['method']);
		switch($method){
			case 'saveTemplate':
				$res = $this->saveTemplate($data);
				break;
			default:
				throw new Exception("Invalid method {$method}");
		}
		return $res;
		
	}
	
	function saveTemplate($data){
		$expected = array('filename', 'type', 'time', 'concepts', 'instructions', 'problem');
		
		$filename = $this->get($data['filename']);
		if(empty($filename)){
			throw new Exception('Missing Filename');
		}
		
		//build xml
		$xml = new SimpleXMLExtended('<question/>');
		$xml->addAttribute('type', $this->get($data['type']));
		$xml->addChild('estimated_time', $this->get($data['time']));
		$concepts = $xml->addChild('concepts');
		$concepts->addChild('concept', $this->get($data['concepts']));
		$xml->addChild('difficulty', $this->get($data['difficulty']));
		$xml->addChild('instructions', $this->get($data['instructions']));
		
		//$xml->problem = null;
		//$xml->problem->addCData($this->get($data['problem']));
		$xml->addCData('problem', $this->get($data['problem']));
		
		$sc = $xml->addChild('substitutions');
		foreach(range(1, 2) as $i){
			$s = $sc->addCData('substitution', 'return "s'.$i.'";');
			$s->addAttribute('val', 's'.$i);
		}
		
		
		$config = new Zend_Config_Ini(APPLICATION_PATH . "/configs/application.ini", APPLICATION_ENV);
		$path = $config->xml->import_path;
		
		$full_filename = $path . DIRECTORY_SEPARATOR . $filename; 
		
		My_Logger::log("Saving to $full_filename");
		
		$xml->saveXML( $full_filename );
		
		return array('result'=>'success', 'msg'=>"File '$filename' saved correctly");
	}
	
	
	function get(&$var, $default=null) {
	    return isset($var) ? $var : $default;
	}
	
	//function getParam
}

class SimpleXMLExtended extends SimpleXMLElement {
	//public function addCData($cdata_text) {
	public function addCData($name, $value = NULL) {
		
		/*$node = dom_import_simplexml($this);
		$no   = $node->ownerDocument;
		$node->appendChild($no->createCDATASection($cdata_text));*/
		$new_child = $this->addChild($name);
		
		if ($new_child !== NULL) {
			$node = dom_import_simplexml($new_child);
			$no   = $node->ownerDocument;
			$node->appendChild($no->createCDATASection($value));
		}
		
		return $new_child;
	}
}
