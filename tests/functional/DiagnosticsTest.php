<?php
/**
 * Functional class to reproduce the live diagnostic test
 * @author ivan
 *
 */
class DiagnosticsTest extends ControllerTestCase{
	
    protected $path;
    
    function setUp(){
    	parent::setUp();
    	$this->path = APPLICATION_PATH . "/../tests/fixtures/diagnostic";
    }
    
    function testImport(){
        $this->clearAll();
        $this->clearTemp();
        
        $importer = $this->createXmlImporter($this->path);
        $importer->processFiles();
        
        //Create the quiz
        $qz = $this->createQuiz('Diagnostic DEV', $this->permissions_group);
        //Add tested concepts
        foreach(range(1,10) as $n){
            $this->addTestedConcept($qz, '_Q'. str_pad($n, 2, '0', STR_PAD_LEFT), 1	);
        }
        	
    }
    
}