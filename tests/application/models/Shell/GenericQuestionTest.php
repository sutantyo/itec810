<?php

class GenericQuestionTest extends ControllerTestCase{
    
    protected $path;
    
    function setUp(){
        parent::setUp();
        $this->path = APPLICATION_PATH . "/../tests/fixtures";
    }
    
    function testGenerate(){
        $this->clearAll();
        $this->clearTemp();
        $importer = $this->createXmlImporter($this->path);
        $importer->parseFile('q0.xml');
        
        //Create the quiz
        $qz = $this->createQuiz("Some Quiz", $this->permissions_group);
        
        //Add tested concept to quiz
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
        
        
        
        $this->assertEquals('System.out.print("Hello World!");', trim($mQuestion->getProblemNoHiddenLines()));
        
        //$this->clearTemp();
        $this->assertEquals('Hello World!', $mQuestion->getCorrectOutput());
        
        //Model_Shell_Debug::getInstance()->saveToDisk();
    }
    
    //To run a single test do
    //     phpunit --filter testStatic  application\models\Shell\GenericQuestionTest.php
    function testStaticValue(){
        $this->clearAll();
        $this->clearTemp();
        $importer = $this->createXmlImporter($this->path);
        $importer->parseFile('q1.xml');
        
        //Create the quiz
        $qz = $this->createQuiz("Some Quiz", 'comp115-students');
        
        //Add tested concept to quiz
        $this->addTestedConcept($qz, 'Concept_1', 3);
        
        My_Logger::clearLog();
        $this->clearMysqlLog();
        
        My_Logger::log('**** BEGIN ******');
        
        // Get the Question XML
        $file = $this->config->xml->import_path . "/q1.xml";
        $mQuestion = new Model_Shell_GenericQuestion($file );
        
        $this->assertEquals('output', $mQuestion->getFriendlyType());
        $this->assertTrue(in_array('Concept_1', $mQuestion->getConcepts()));
        $this->assertEquals('1', $mQuestion->getDifficulty());
        
        
        
        //Here comes the compilation. After this single call, all the artifacts are produced
        $this->assertEquals('Static substitution', $mQuestion->getInstructions());
        
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
        //let's do our own parsing
        $expected = str_replace('`s1`', 29, (string)$xml->problem);
        $this->assertEquals(trim($expected), trim($mQuestion->getProblem())); //this will be a problem, with the substitution already expanded
        
        
        
        //$this->assertEquals('System.out.print(29);', $mQuestion->getProblemNoHiddenLines());
        
        //$this->clearTemp();
        $this->assertEquals('29', $mQuestion->getCorrectOutput());
    }
    
    function testRandom(){
    	$this->clearAll();
    	$this->clearTemp();
    	$importer = $this->createXmlImporter($this->path);
    	$file = 'q2.xml';
    	$concept = 'Concept_2';
    	$importer->parseFile($file);
    
    	//Create the quiz
    	$qz = $this->createQuiz("Some Quiz", 'comp115-students');
    
    	//Add tested concept to quiz
    	$this->addTestedConcept($qz, $concept, 3);
    
    	My_Logger::clearLog();
    	$this->clearMysqlLog();
    
    	My_Logger::log('**** BEGIN ******');
    
    	// Get the Question XML
    	$file = $this->config->xml->import_path . "/" . $file;
    	$mQuestion = new Model_Shell_GenericQuestion($file );
    
    	$this->assertEquals('output', $mQuestion->getFriendlyType());
    	$this->assertTrue(in_array($concept, $mQuestion->getConcepts()));
    	$this->assertEquals('1', $mQuestion->getDifficulty());
    
    
    
    	//Here comes the compilation. After this single call, all the artifacts are produced
    	$this->assertEquals('Random substitution', $mQuestion->getInstructions());
    
    	//return;
    
    	//for fun let's ourselves load the xml
    	$xml = simplexml_load_file($file);
    	My_Logger::log( __METHOD__. ": simpleXml ". print_r($xml, true));
    	My_Logger::log( __METHOD__. ": substitution ". print_r($xml->substitutions->substitution[0], true));
    	My_Logger::log( __METHOD__. ": substitution ". (string)$xml->substitutions->substitution[0]->attributes()->{'val'});
    	My_Logger::log( __METHOD__. ": substitution ". (string)$xml->substitutions->substitution[0]);
    
    	My_Logger::log( __METHOD__. "problem:" . (string)$xml->problem);
    	My_Logger::log( __METHOD__. "actual:" . $mQuestion->getProblem());
    
    	//$this->clearTemp();
    	$this->assertTrue(in_array($mQuestion->getCorrectOutput(), range(3,5)));
    }
    
    
    function testRandset(){
    	$this->clearAll();
    	$this->clearTemp();
    	
    	$filename = 'randset.xml';
    	$filepath = $this->config->xml->import_path . "/" . $filename;
    	$xml = simplexml_load_file($filepath);
    	$concept = (string)$xml->concepts->concept; //from source
    	
    	$importer = $this->createXmlImporter($this->path);
    	
    	$importer->parseFile($filename);
    
    	//Create the quiz
    	$qz = $this->createQuiz("Some Quiz", 'comp115-students');
    
    	//Add tested concept to quiz
    	$this->addTestedConcept($qz, $concept, 3);
    
    	My_Logger::clearLog();
    	$this->clearMysqlLog();
    
    	My_Logger::log('**** BEGIN ******');
    
    	// Get the Question XML
    	$mQuestion = new Model_Shell_GenericQuestion($filepath );
    	
    	
    
    	$this->assertEquals('output', $mQuestion->getFriendlyType());
    	$this->assertTrue(in_array($concept, $mQuestion->getConcepts()));
    	$this->assertEquals('1', $mQuestion->getDifficulty());
    
    
    
    	//Here comes the compilation. After this single call, all the artifacts are produced
    	$this->assertEquals(trim((string)$xml->instructions), $mQuestion->getInstructions());
    	
    	//return;
    
    	//for fun let's ourselves load the xml
    	
    	My_Logger::log( __METHOD__. ": simpleXml ". print_r($xml, true));
    	My_Logger::log( __METHOD__. ": substitution ". print_r($xml->substitutions->substitution[0], true));
    	My_Logger::log( __METHOD__. ": substitution ". (string)$xml->substitutions->substitution[0]->attributes()->{'val'});
    	My_Logger::log( __METHOD__. ": substitution ". (string)$xml->substitutions->substitution[0]);
    
    	My_Logger::log( __METHOD__. "problem:" . (string)$xml->problem);
    	My_Logger::log( __METHOD__. "actual:" . $mQuestion->getProblem());
    
    	$this->assertEquals('foo', $mQuestion->getCorrectOutput());
    }
    
    /**
     * @expectedException CompilerException
     */
    function testCompilerError(){
        $this->clearAll();
        $this->clearTemp();
        
        $filename = 'compiler_error.xml';
        $filepath = $this->config->xml->import_path . "/" . $filename;
        $xml = simplexml_load_file($filepath);
        $concept = (string)$xml->concepts->concept; //from source
        
        $importer = $this->createXmlImporter($this->path);
        
        $importer->parseFile($filename);
        
        //Create the quiz
        $qz = $this->createQuiz("Some Quiz", 'comp115-students');
        
        //Add tested concept to quiz
        $this->addTestedConcept($qz, $concept, 3);
        
        
        My_Logger::log('**** BEGIN ******');
        
        // Get the Question XML
        $mQuestion = new Model_Shell_GenericQuestion($filepath );
        
        
        
        $this->assertEquals('output', $mQuestion->getFriendlyType());
        $this->assertTrue(in_array($concept, $mQuestion->getConcepts()));
        $this->assertEquals('1', $mQuestion->getDifficulty());
        
        //Forces compilation.
        try {
            $mQuestion->getInstructions();
        } catch (Exception $e) {
            
            //read error file
            $str = file_get_contents($e->error_file);
            $this->assertContains('error:', $str); //verify that error file was generated and contains an error string
            
            throw $e;
        }
        
        
    }
    
    
    /**
     * @expectedException EvalException 
     */
    function testEvalError(){
    	$this->clearAll();
    	$this->clearTemp();
    	 
    	$filename = 'evalerror.xml';
    	$filepath = $this->config->xml->import_path . "/" . $filename;
    	$xml = simplexml_load_file($filepath);
    	$concept = (string)$xml->concepts->concept; //from source
    	 
    	$importer = $this->createXmlImporter($this->path);
    	 
    	$importer->parseFile($filename);
    
    	//Create the quiz
    	$qz = $this->createQuiz("Some Quiz", 'comp115-students');
    
    	//Add tested concept to quiz
    	$this->addTestedConcept($qz, $concept, 3);
    
    	My_Logger::clearLog();
    	$this->clearMysqlLog();
    
    	My_Logger::log('**** BEGIN ******');
    
    	// Get the Question XML
    	$mQuestion = new Model_Shell_GenericQuestion($filepath );
    	 
    	 
    
    	$this->assertEquals('output', $mQuestion->getFriendlyType());
    	$this->assertTrue(in_array($concept, $mQuestion->getConcepts()));
    	$this->assertEquals('1', $mQuestion->getDifficulty());
    
    
    
    	//Here comes the compilation. After this single call, all the artifacts are produced
    	$this->assertEquals(trim((string)$xml->instructions), $mQuestion->getInstructions());
    	
    }
    
}
