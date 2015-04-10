<?php
require_once 'Zend/Test/PHPUnit/ControllerTestCase.php';

class ControllerTestCase extends Zend_Test_PHPUnit_ControllerTestCase
{
    /**
     * @var Zend_Application
    */
    protected $application;
    protected $db;
    
    public function setUp() {
        $this->bootstrap = array($this, 'appBootstrap');
        parent::setUp();
        
        //safety check
        $config = new Zend_Config_Ini(APPLICATION_PATH . "/configs/application.ini", APPLICATION_ENV);
        //My_Logger::log(var_export($config->toArray(), true));
        if($config->resources->db->params->dbname!='quiz_db_test'){
        	throw new Exception("Incorrect database!!");
        
        }
        
        $this->db = Zend_Registry::get("db");
        
        
    }
    
    public function appBootstrap() {
        $this->application = new Zend_Application(APPLICATION_ENV, APPLICATION_PATH . '/configs/application.ini');
        $this->application->bootstrap();
    }
    
    //Utilities
    function clearAll(){
        file_put_contents("C:\\wamp\\logs\\genquery.log", '');
        My_Logger::clearLog();
        
        $this->db->query("SET FOREIGN_KEY_CHECKS=0;");
        $tables = array(
            100 =>'concepts',
            123 => 'question_base',
            333=> 'question_concepts',
            31 => 'quiz',
            480 => 'concepts_tested',
            
            //when student logs in, his attempts are registered
            1021 => 'quiz_attempt', //it will register this quiz attempt
            2081 => 'generated_questions', //then it will generate a question
            3225 => 'question_attempt', //and register that particular question
            //on error, sets question_attempt.initial_result to 0
            //on error, sets question_attempt.secondary_result to 0 and question_attempt.time_finished to current time
            //if continue, generated a new question
            //apparently repeats until the number of questions equals  concepts_tested.nb_question
        );
        
        foreach ($tables as $inc => $table){
            $this->db->query("TRUNCATE TABLE $table;");
            $this->db->query("ALTER TABLE $table AUTO_INCREMENT = $inc;");
        }
        
    }
    
    function createQuiz($name, $permissions, $starts=false, $ends=false, $attempts=50, $percentage=100){
        return Model_Quiz_Quiz::fromScratch($name
        		, $permissions
        		, $starts ?: date('Y-m-d', strtotime("-1 month"))
            	, $ends ?: date('Y-m-d', strtotime("+5 month"))
        		, $attempts
            	, $percentage
            );
    }
    
    function addTestedConcept($quiz_ob, $concept_id, $nb_questions, $from=1, $to=1){
        $vConcept = Model_Quiz_Concept::fromID( $concept_id );
        return Model_Quiz_TestedConcept::fromScratch($from, 
            $to, 
            $nb_questions, 
            $vConcept, 
            $quiz_ob);
    }
    
}
