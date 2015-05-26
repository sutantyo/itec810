<?php

class QuestionTemplateTest extends ControllerTestCase{
    
    protected $path;
    
    function setUp(){
        parent::setUp();
        $this->path = APPLICATION_PATH . "/../tests/fixtures/editor";
    }
   
    
    function testNew(){
    	$obj = Model_Shell_QuestionTemplate::load($this->path . "/foo.xml" );
    	
    	$this->assertEquals('', $obj->getType());
    	$this->assertEquals('', $obj->getInstructions());
    	$this->assertEquals('', $obj->getEstimatedTime());
    	$this->assertEquals(array(), $obj->getConcepts());
    	$this->assertEquals('', $obj->getDifficulty());
    	$this->assertEquals('', $obj->getInstructions());
    	$this->assertEquals('', $obj->getProblem());
    	$this->assertEquals(array(), $obj->getSubstitutions());
    }
    
    function testOld(){
    	//$this->clearAll();
    	//$this->clearTemp();
    	 
    	$filename = 'old.xml';
    	$filepath = $this->path . "/" . $filename;
    	$xml = simplexml_load_file($filepath);
    	$concept = (string)$xml->concepts->concept; //from source
    	 
    	//$importer = $this->createXmlImporter($this->path);
    	//$importer->parseFile($filename);
    
    	$obj = Model_Shell_QuestionTemplate::load($filepath);
    
    	$this->assertEquals(trim((string)$xml->instructions), trim($obj->getInstructions()));
    	
    	$this->assertEquals(2, count($obj->getSubstitutions()));
    
    	//Here comes the compilation. After this single call, all the artifacts are produced
    	//ini_set("display_errors", 0);
    	//$this->assertEquals(trim((string)$xml->instructions), $mQuestion->getInstructions());
    	//ini_set("display_errors", 0);
    	
    }
    
}
