<?php

class ImporterTest extends ControllerTestCase
{
    protected $path;
    
    function setUp(){
    	parent::setUp();
    	$this->path = APPLICATION_PATH . "/../tests/fixtures/single";
    }
    
    function testOnce(){
        $this->clearAll();
        $this->clearTemp();
        
        $importer = $this->createXmlImporter($this->path);
        $this->assertEquals($this->countFiles($this->path, 'xml'), $importer->getTotalQuestions());
        $importer->processFiles();
        
        //Optionally create simple quiz
        $qz = $this->createQuiz('Simple Quiz', $this->permissions_group);
        $this->addTestedConcept($qz, 'Concept_0', 1);
        
        //Student solves it
        
        //Study what happens when admin imports again
        
    }
    
    //What happens if I import twice?
    function testTwice(){
        $this->clearAll();
        $this->clearTemp();
        
        $importer = $this->createXmlImporter($this->path);
        $this->assertEquals(1, $importer->getTotalQuestions());
        
        /**
         * First import:
         * Creates the a question_base row for each new xml file not registered previously
         * Extracts concept string from xml. Creates a new cocepts row if concept does not exist
         * Looks for concept-question association in question_concepts table, If it does not exist it creates it
         * Then it tries to "remove ALL pre-generated questions that haven't been used in a quiz". Nothing on our tables, though.
         */
        $importer->processFiles();
        
        
        My_Logger::log("******* SECOND IMPORT *******");
        
        /**
         * Second import:
         * Apparently process is idempotent. It didn't create new concepts, question_concepts rows
         * Just tries to update the question_base difficulty and estimated_time. No inserts.
         * Therefore: IDEMPOTENT - SAFE
         */
        $importer->processFiles();
         
    }
    /*
    function test_2_xml_files(){
        
        $this->clearAll();
        $this->clearTemp();
        
        $path = APPLICATION_PATH . "/../tests/fixtures/dual";
        
        $importer = $this->createXmlImporter($path);
        $this->assertEquals($this->countFiles($path, 'xml'), $importer->getTotalQuestions());
        $importer->processFiles();
        
        //Optionally create simple quiz
        $qz = $this->createQuiz('Simple Quiz', $this->permissions_group);
        $this->addTestedConcept($qz, 'Concept_0', 1);
        
        $qz = $this->createQuiz('Other Quiz', $this->permissions_group);
        $this->addTestedConcept($qz, 'Concept_1', 1);
        
    }
    */
    
    
}