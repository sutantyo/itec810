<?php

/**
 * Factor out code from AdminController::rebuildxmlAction
 * @author Ivan Rodriguez
 *
 */
class Model_XML_Importer {
	protected $path;
	public $delegate;

	function __construct($path) {
		$this->path = $path;
		$this->delegate = function ($msg){};
	}

	function getTotalQuestions() {
		// Get the amount of files in the Questions Dir
		$counter = 0;
		if ($handle = opendir ( $this->path )) {
			while ( false !== ($file = readdir ( $handle )) ) {
				if (strtolower ( substr ( $file, - 3 ) ) == "xml") {
					$counter = $counter + 1;
				}
			}
			closedir ( $handle );
		}
		return $counter;
	}

	function processFiles() {
	    $path = $this->path;
	    
		
	    $handle = opendir ( $path );
	    if(!$handle) return ;
		
	    
	    
	    // Process the XML Files
		while ( false !== ($file = readdir ( $handle )) ) {
			if (strtolower ( substr ( $file, - 3 ) ) == "xml") {
				$this->parseFile($file);
			}
		}
		closedir ( $handle );
		
		Model_Quiz_GeneratedQuestion::removePregeneratedQuestions (); // Remove all Pre-Generated questions, so as to not cause any conficts
		
	}
	
	function parseFile($basename){
	    $notify = $this->delegate;
	    $notify("Parsing file: $basename <br/>\nConcepts: ");
	    
	    // Begin Processing
	    $vQuestion = new Model_Shell_GenericQuestion ( $this->path . '/' .$basename );
	    
	    // Add to questionBase (if not already there)
	    $vQuestionBase = Model_Quiz_QuestionBase::fromXml ( $basename );
	    if ($vQuestionBase == null) {
	    	$vQuestionBase = Model_Quiz_QuestionBase::fromScratch ( $basename, $vQuestion->getDifficulty()
	    	    , $vQuestion->getEstimatedTime()
	    	    , $vQuestion->getFriendlyType()
	    	    , strtotime ( "today" ) );
	    }
	    
	    // Now look at the concepts
	    $vConcepts = $vQuestion->getConcepts ();
	    foreach ( $vConcepts as $vConcept ) {
	    	// Make sure this concept exists in the database
	    	$vConceptObj = Model_Quiz_Concept::fromID ( $vConcept );
	    	if ($vConceptObj == null) {
	    		// Doesn't exist... we should make a record
	    		$vConceptObj = Model_Quiz_Concept::fromScratch ( $vConcept );
	    	}
	    	$notify( $vConcept . "; ");
	    		
	    	// Now we need to make sure that this question has this concept associated with it
	    	$vQuestionBase->addConcept ( $vConceptObj );
	    }
	    
	    // Update the questionBase's Difficulty & EstimatedTime (these are the things most likely to change)
	    $vQuestionBase->setDifficulty ( $vQuestion->getDifficulty () );
	    $vQuestionBase->setEstimated_time ( $vQuestion->getEstimatedTime () );
	    
	    $notify( "<br/>Difficulty: " . $vQuestion->getDifficulty ());
	    $notify("<br/>Estimated time to complete: " . $vQuestion->getEstimatedTime ());
	    $notify("<br/><br/>\n");
	}
	
}