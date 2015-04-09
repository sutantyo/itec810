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
		
	    $notify = $this->delegate;
	    
	    // Process the XML Files
		while ( false !== ($file = readdir ( $handle )) ) {
			if (strtolower ( substr ( $file, - 3 ) ) == "xml") {
				$notify("Parsing file: $file <br/>\nConcepts: ");
				
				// Begin Processing
				$vQuestion = new Model_Shell_GenericQuestion ( $path . '/' .$file );
				
				// Add to questionBase (if not already there)
				$vQuestionBase = Model_Quiz_QuestionBase::fromXml ( $file );
				if ($vQuestionBase == null) {
					$vQuestionBase = Model_Quiz_QuestionBase::fromScratch ( $file, $vQuestion->getDifficulty (), $vQuestion->getEstimatedTime (), $vQuestion->getFriendlyType (), strtotime ( "today" ) );
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
		closedir ( $handle );
		
		Model_Quiz_GeneratedQuestion::removePregeneratedQuestions (); // Remove all Pre-Generated questions, so as to not cause any conficts
		
	}
}