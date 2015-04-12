<?php
/**
 * Simple setup to evaluate student flow logic on the quiz system
 * We will automate the steps of a student to complete the test 
 * @author ivan
 *
 */
class QuizSessionTest extends ControllerTestCase{
	
    protected $path;
    
    function setUp(){
    	parent::setUp();
    	$this->path = APPLICATION_PATH . "/../tests/fixtures/quiz1";
    }
    
    function testImport(){
        $this->clearAll();
        $this->clearTemp();
        
        $importer = $this->createXmlImporter($this->path);
        $importer->processFiles();
        
        //Create the quiz
        $qz = $this->createQuiz('Quiz with 2 concepts', $this->permissions_group);
        
        $this->addTestedConcept($qz, 'T1', 1);
        $this->addTestedConcept($qz, 'T2', 1);
        
        //Start quiz
        //$this->assertRows()
        	
    }
    
}