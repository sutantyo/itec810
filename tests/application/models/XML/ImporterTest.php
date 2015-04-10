<?php

class ImporterTest extends ControllerTestCase
{
    
    function testImport(){
        $this->clearAll();
        $this->clearTemp();
        $importer = $this->createXmlImporter();
        //$this->assertEquals(1, $importer->getTotalQuestions());
        $importer->processFiles();
        
        //Create the quiz
        $qz = $this->createQuiz("Some Quiz", 'comp115-students');
        
        //Add some tested concept
        $this->addTestedConcept($qz, 'Concept_0', 3);
        
        //now student logs in
        //$this->dispatch('/shell/attempt?quiz='.$qz->getID());
        
    }
    
    
    
}