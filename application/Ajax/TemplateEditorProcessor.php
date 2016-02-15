<?php
/**
 *
 * @author Ivan Rodriguez Asqui
 *
 */
class Ajax_TemplateEditorProcessor {

	function process($data){
		My_Logger::log("in Ajax_TemplateEditorProcessor process()");
		//My_Logger::log(__METHOD__ . ': ' . var_export($data, true));
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
				My_Logger::log("in Ajax_TemplateEditorProcessor doProcess(), case saveTemplate");
				$res = $this->saveTemplate($data);
				break;
			case 'qualityTest':
				My_Logger::log("in Ajax_TemplateEditorProcessor doProcess(), case qualityTest");
				$res = $this->qualityTest($data);
				break;
			default:
				throw new Exception("Invalid method {$method}");
		}
		return $res;

	}


	function saveTemplate($data){
		My_Logger::log("in Ajax_TemplateEditorProcessor saveTemplate()");
		$expected = array('filename', 'type', 'time', 'concepts', 'instructions', 'problem');

		$filename = $this->get($data['filename']);
		if(empty($filename)){
			throw new Exception('Missing Filename');
		}

		My_Logger::log("filename is " . $filename);

		foreach ($expected as $field){
			$toCheck = $this->get($data[$field]);
			if (empty($toCheck)){
			//if (empty($this->get($data[$field]))){
				throw new Exception('Missing field: ' . $field);
			}
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
		$subs = $this->get($data['s']);
		if ($subs){
			foreach ($subs as $sd){
				if(empty($sd['name']) || empty($sd['value'])) continue;
				$s = $sc->addCData('substitution', $sd['value']);
				$s->addAttribute('val', $sd['name']);
			}
		}



		$config = new Zend_Config_Ini(APPLICATION_PATH . "/configs/application.ini", APPLICATION_ENV);
		$path = $config->xml->import_path;

		$full_filename = $path . DIRECTORY_SEPARATOR . $filename;

		My_Logger::log("Saving to $full_filename");

		$xml->saveXML( $full_filename );
		chmod($full_filename,0666);
		

		return array('result'=>'success', 'msg'=>"File '$filename' saved correctly");
	}

	function qualityTest($data){
		//Will try to generate n questions, and show a ratio of success/total compilations
		$total = 10;
		$success=$errors=0;

		$config = new Zend_Config_Ini(APPLICATION_PATH . "/configs/application.ini", APPLICATION_ENV);
		$xml_path = $config->xml->import_path;

		$selected_xml = $this->get($data['file']);

		if(empty($selected_xml)){
			throw new Exception("Please save file first.");
		}

		$full_filename = $xml_path . "/" . $selected_xml .".xml";

		My_Logger::log( __METHOD__ . " full_filename: ". $full_filename);
		if (!file_exists($full_filename)){

			throw new Exception("File does not exist.");
		}

		for($i=1; $i<=$total; $i++){
			try{
				$mQuestion = new Model_Shell_GenericQuestion($full_filename);
				$mQuestion->getProblemNoHiddenLines();
				$mQuestion->getCorrectOutput();
				$success++;
			} catch (Exception $e) {
				//throw $e;
				$errors++;
			}
		}

		My_Logger::log("total: $total, error: $errors, success: $success");
		$ratio = round($success/$total * 100, 2);
		return array('result'=>'success', 'title'=>'Compilation results' , 'msg'=> 'Success Ratio:' . $ratio  . '%'  );
	}

	// if the variable $var is set and is not null, return the variable, otherwise return NULL
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
