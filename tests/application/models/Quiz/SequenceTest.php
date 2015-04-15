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
    	
    	//Add a new one
    	$this->assertTrue($seq->addQuiz($quizB));
    	$this->assertEquals(0, count($seq->getAvailableQuizzes()));
    	$this->assertEquals(2, count($seq->getQuizzes()));

    	//reset
    	$this->db->query("TRUNCATE TABLE sequence_quiz");
    	$this->assertEquals(2, count($seq->getAvailableQuizzes()));
    	$this->assertEquals(0, count($seq->getQuizzes()));
    	
    	//add multiple
    	$data = array (
		  'id' => $seq->id,
		  'items' => array (
		    0 => '31',
		    1 => '32',
		  ),
		);
    	
    	$ajax = new Ajax_SequenceEditorProcessor();
    	$res = $ajax->process($data);
    	$this->assertEquals('ok', $res['result']);
    	$this->assertEquals(0, count($seq->getAvailableQuizzes()));
    	$this->assertEquals(2, count($seq->getQuizzes()));
    	
    	
    	//add none
    	$data = array (
    			'id' => '520',
    	);
    	
    	$ajax = new Ajax_SequenceEditorProcessor();
    	$res = $ajax->process($data);
    	$this->assertEquals('ok', $res['result']);
    	$this->assertEquals(2, count($seq->getAvailableQuizzes()));
    	$this->assertEquals(0, count($seq->getQuizzes()));
    	
    	
    }
    
    function testDelete(){
    	//todo deletion only works if no quizzes
    	//quizzes should be left intact, just delete the sequence_quiz columns
    }
    
    
    
    
}