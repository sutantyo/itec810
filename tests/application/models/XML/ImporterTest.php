<?php

class ImporterTest extends ControllerTestCase
{
    
    function testImport(){
        $this->clearAll();
        $importer = $this->getInstance();
        //$this->assertEquals(1, $importer->getTotalQuestions());
        $importer->processFiles();
        
        //Create the quiz
        $qz = $this->createQuiz("Some Quiz", 'comp115-students');
        
        //Add some tested concept
        $this->addTestedConcept($qz, 'Concept_0', 3);
        
        //now student logs in
        $this->dispatch('/shell/attempt?quiz='.$qz->getID());
        
    }
    
    function getInstance(){
        $obj = new Model_XML_Importer(APPLICATION_PATH . '/../tests/fixtures');
        $obj->delegate = function ($msg){
            My_Logger::log($msg);
        };
        return $obj;
    }
    
}