<?php

class SequenceTest extends ControllerTestCase
{
    protected $path;
    
    function setUp(){
    	parent::setUp();
    	$this->path = APPLICATION_PATH . "/../tests/fixtures/quiz1";
    	$this->getFrontController()->setParam('xml_path', $this->path); //Override for our tests
    }
    
    function testEditor(){
    	$this->clearAll();
    	$this->clearTemp();
    	
    	$importer = $this->createXmlImporter($this->path);
    	$importer->processFiles();
    	
    	//Create the quiz
    	$quizA = $this->createQuiz('Quiz A', $this->permissions_group);
    	$this->addTestedConcept($quizA, 'T1', 1);
    	//Another quiz
    	$quizB = $this->createQuiz('Quiz B', $this->permissions_group);
    	$this->addTestedConcept($quizB, 'T1', 1);
    	
    	$seq = $this->createSequence('Test Sequence', $this->permissions_group);
    	
    	$this->assertEquals(2, count($seq->getAvailableQuizzes()));
    	$this->assertEquals(0, count($seq->getQuizzes()));
    	
    	//Now add quiz to sequence
    	$this->assertTrue($seq->addQuiz($quizA));
    	$this->assertEquals(1, count($seq->getAvailableQuizzes()));
    	$this->assertEquals(1, count($seq->getQuizzes()));
    	
    	//should fail
    	$this->assertFalse($seq->addQuiz($quizA));
    	
    	//Add a new onw
    	$this->assertTrue($seq->addQuiz($quizB));
    	$this->assertEquals(0, count($seq->getAvailableQuizzes()));
    	$this->assertEquals(2, count($seq->getQuizzes()));
    }
    
    function testDelete(){
    	//todo deletion only works if no quizzes
    	//quizzes should be left intact, just delete the sequence_quiz columns
    }
    
    
    
    
}