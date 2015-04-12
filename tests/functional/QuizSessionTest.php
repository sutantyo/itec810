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
    
    function testSession(){
        $this->clearAll();
        $this->clearTemp();
        
        $importer = $this->createXmlImporter($this->path);
        $importer->processFiles();
        
        //Create the quiz
        $qz = $this->createQuiz('Quiz with 2 concepts', $this->permissions_group);
        
        $this->addTestedConcept($qz, 'T1', 1);
        $this->addTestedConcept($qz, 'T2', 1);
        
        
        $this->getFrontController()->setParam('xml_path', $this->path); //Override for our tests

        //return;
        
        
        //Login
        $this->getRequest()->setMethod('POST')
            ->setParams(array('rqz-username'=>'hugo',
                              'rqz-password'=>'123456'
                        ));
        $this->dispatch('/auth/login');
        
        $quiz_id = $qz->getID();
        
        $url = 'shell/attempt?quiz=' . $quiz_id;
        
        // 1. Start quiz
        My_Logger::log(__METHOD__ . " >>>>> 1. Start test at url: $url");
        $this->resetRequest()->resetResponse();
        $this->dispatch($url);
        
        
        $this->assertRows(1, 'quiz_attempt');
        $this->assertRows(1, 'generated_questions');
        $this->assertRows(1, 'question_attempt');
        //verify question view
        $this->assertXpathCount('//input[@name="quiz"][@value='. $quiz_id.']', 1);
        $this->assertXpathCount('//input[@name="marking"][@value=1]', 1);
        $this->assertXpathCount('//textarea[@name="ans"]', 1);
        
        $attempt_id = $this->db->fetchOne("SELECT attempt_id FROM question_attempt");

        // 2. Send correct answer
        My_Logger::log(__METHOD__ . " >>>>> 2. Send correct answer");
        $this->resetRequest()->resetResponse();
        $this->setPost(array(
            'quiz' => $quiz_id,
            'marking'=> '1',
            'ans' => 'foo'
        ));
        $this->dispatch($url);
        
        $this->assertRows(1, 'question_attempt', "initial_result=1 AND attempt_id=$attempt_id");
        //return;
        
        $this->assertXpathCount('//input[@name="quiz"][@value='. $quiz_id.']', 1);
        $this->assertNotXpath('//textarea'); //question result view
        
        //3. Continue, new quesion
        My_Logger::log(__METHOD__ . " >>>>> 3. Continue, new quesion");
        $this->resetRequest()->resetResponse();
        $this->setPost(array(
        		'quiz' => $quiz_id,
        ));
        $this->dispatch($url);
        $this->assertRows(1, 'quiz_attempt');
        $this->assertRows(2, 'generated_questions');
        $this->assertRows(2, 'question_attempt');
        //verify question view
        $this->assertXpathCount('//input[@name="quiz"][@value='. $quiz_id.']', 1);
        $this->assertXpathCount('//input[@name="marking"][@value=1]', 1);
        $this->assertXpathCount('//textarea[@name="ans"]', 1);
        
        //Send answer
        
        
        
    }
    
    function setPost($data){
        $this->getRequest()->setMethod('POST')->setPost($data);
    }
    
}