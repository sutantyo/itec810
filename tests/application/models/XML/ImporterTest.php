<?php

class ImporterTest extends ControllerTestCase
{
    
    function testImport(){
        $this->clearAll();
        $importer = $this->getInstance();
        $this->assertEquals(1, $importer->getTotalQuestions());
        $importer->processFiles();
    }
    
    function getInstance(){
        $obj = new Model_XML_Importer(APPLICATION_PATH . '/../tests/fixtures');
        $obj->delegate = function ($msg){
            My_Logger::log($msg);
        };
        return $obj;
    }
    
}