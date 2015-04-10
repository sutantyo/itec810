<?php

class GenericQuestionTest extends ControllerTestCase{
    
    function testGenerate(){
        $this->clearAll();
        $this->clearTemp();
        $importer = $this->createXmlImporter();
        //$this->assertEquals(1, $importer->getTotalQuestions());
        //$importer->processFiles();
        $importer->parseFile('q0.xml');
        
        //Create the quiz
        $qz = $this->createQuiz("Some Quiz", 'comp115-students');
        
        //Add some tested concept
        $this->addTestedConcept($qz, 'Concept_0', 3);
        
        My_Logger::clearLog();
        $this->clearMysqlLog();
        
        My_Logger::log('**** BEGIN ******');
        
        // Get the Question XML
        $file = $this->config->xml->import_path . "/q0.xml";
        //Apparently this creates nothing. Actual compilation occurrs when specific setters are called on view code
        $mQuestion = new Model_Shell_GenericQuestion($file ); 
        //My_Logger::log( __METHOD__ . ' **********');
        
        $this->assertEquals('output', $mQuestion->getFriendlyType());
        $this->assertTrue(in_array('Concept_0', $mQuestion->getConcepts()));
        $this->assertEquals('1', $mQuestion->getDifficulty());
        
        
        
        //Here comes the compilation. After this single call, all the artifacts are produced
        $this->assertEquals('What is printed on the screen?', $mQuestion->getInstructions());
        
        //return;
        
        //for fun let's ourselves load the xml
        $xml = simplexml_load_file($file);
        My_Logger::log( __METHOD__. ": simpleXml ". print_r($xml, true));
        My_Logger::log( __METHOD__. ": substitution ". print_r($xml->substitutions->substitution[0], true));
        My_Logger::log( __METHOD__. ": substitution ". (string)$xml->substitutions->substitution[0]->attributes()->{'val'});
        My_Logger::log( __METHOD__. ": substitution ". (string)$xml->substitutions->substitution[0]);
        
        //My_Logger::log( __METHOD__. "problem:" . (string)$xml->problem);
        //My_Logger::log( __METHOD__. "actual:" . $mQuestion->getProblem());
        
        //$this->assertEquals((string)$xml->problem, $mQuestion->getProblem()); //apparently some CR is introduced, so we have to trim
        $this->assertEquals(trim((string)$xml->problem), trim($mQuestion->getProblem()));
        
        
        
        $this->assertEquals('System.out.print("Hello World!");', $mQuestion->getProblemNoHiddenLines());
        
        //$this->clearTemp();
        $this->assertEquals('Hello World!', $mQuestion->getCorrectOutput());
        
        //Model_Shell_Debug::getInstance()->saveToDisk();
    }
    
}
