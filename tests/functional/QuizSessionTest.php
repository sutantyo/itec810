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

        return;
        
        //Start quiz
        //first login
        $this->getRequest()->setMethod('POST')
            ->setParams(array('rqz-username'=>'hugo',
                              'rqz-password'=>'123456'
                        ));
        $this->dispatch('/auth/login');
        $this->resetRequest()->resetResponse();
        
        $url = 'shell/attempt?quiz=' . $qz->getID();
        My_Logger::log(__METHOD__ . " ****************** $url");
        $this->dispatch($url);
        $this->resetRequest()->resetResponse();
    }
    
}