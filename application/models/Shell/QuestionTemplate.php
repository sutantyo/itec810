<?php
/**
 * Question Template data transfer object for the editor
 * @author ivan
 *
 */
class Model_Shell_QuestionTemplate{

		private $file_name;
		private $file_Contents;
		private $substitutions = array();
		private $problem;
		private $valid=false;

		private $xml=false;

		/**
		 * Create a new Question from a passed XML File
		 * @param string $vFileName
		 * @throws Exception
		 */
		public function __construct(){

		}

		static function load($file_name){
			//My_Logger::log(__METHOD__ . " loading:" . $file_name);
			$obj = new self();
			if(!file_exists($file_name))
				return $obj;
			$file_contents = Model_XML_Parser::xml2array($file_name);
			if( !is_array($file_contents) || sizeof($file_contents) == 0 ) {
				return $obj;
			}

			$obj->xml = simplexml_load_file($file_name);

			//My_Logger::log(__METHOD__ . " contents:" . print_r($file_contents, true));
			//My_Logger::log(__METHOD__ . " xml:" . print_r($obj->xml, true));

			$obj->file_Contents = $file_contents;
			$obj->file_name = $file_name;
			$obj->substitutions = array();
			$obj->valid = true;
			//print_r($obj->mFileContents);
			return $obj;
		}

		public function isValid(){
			return $this->valid;
		}

		private function get(&$var, $default=null) {
			return isset($var) ? $var : $default;
		}

		public function getConcepts(){
			$res = array();
			foreach( $this->get( $this->file_Contents['question']['concepts'], array() ) as $i){
				//Check to see if $i is an array first
				if(is_array($i)){
					foreach($i as $j){
						$res[] = $j;
					}
				}else
					$res[] = $i;
			}
			return $res;
		}

		public function getType(){
			return $this->get($this->file_Contents['question_attr']['type']);
		}

		public function getEstimatedTime(){
			return $this->get($this->file_Contents['question']['estimated_time']);
		}


		public function getDifficulty(){
			return $this->get($this->file_Contents['question']['difficulty']);
		}

		public function getInstructions(){
			return $this->get($this->file_Contents['question']['instructions']);
		}


		public function getProblem(){
			return $this->get($this->file_Contents['question']['problem']);

		}

		public function getSubstitutions(){
			//Return them in a format suitable for iteration
			//return $this->substitutions;
			$res = array();
			$subs = $this->get($this->xml->substitutions, new SimpleXMLElement('<foo/>'));
			foreach ($subs->children() as $s ){
				//My_Logger::log(__METHOD__ . " s: " . print_r($s, true) );
				$res[] = array(
						'name'=> (string)$s->attributes()->val, 'value'=> (string)$s
				);
			}
			//My_Logger::log(__METHOD__ . " res: " . print_r($res, true) );
			return $res;
		}






	}
