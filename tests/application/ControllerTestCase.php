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
            333=> 'question_concepts'
        );
        foreach ($tables as $inc => $table){
            $this->db->query("TRUNCATE TABLE $table;");
            $this->db->query("ALTER TABLE $table AUTO_INCREMENT = $inc;");
        }
        
    }
    
}
