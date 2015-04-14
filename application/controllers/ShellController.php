<?php
/**
 *  Randomised Programming Quiz System - A quiz system that develops random programming questions from defined templates
 *  Copyright (C) 2010-2012 Ben Evans <ben@nebev.net>
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 **/
class ShellController extends Zend_Controller_Action {

	public function init() {
		$this->_auth = Zend_Auth::getInstance();
		
		if ($this->_auth->hasIdentity()) {
			$identity = Zend_Auth::getInstance()->getIdentity();
			if (!isset($identity->username)) {
				// Don't know how you got here... But you're not authenticated
				$this->_helper->redirector("login", "auth"); // Must Log in before accessing anything
			}
			
			$this->view->username = $identity->username;
			
			// Determine what sidebars this person has access to
			// (Determined at this point by defined groups)
			$auth_model = Model_Auth_General::getAuthModel();
			if ($auth_model->userInGroup($identity->username, QUIZ_ADMINISTRATORS)) {
				$this->view->is_admin = true;
			}
			else {
				$this->view->is_admin = false;
			}
		}
		else {
			$this->_helper->redirector("login", "auth"); // Must Log in before accessing anything
		}
	}

	/**
	 * This action is the quiz shell.
	 * It ensures permissions, creates new attempts etc etc.
	 *
	 * @author Ben Evans
	 */
	public function attemptAction() {
		Model_Shell_Debug::getInstance()->log("User Entered the Attempt Action");
		
		$identity = Zend_Auth::getInstance()->getIdentity();
		$username = $identity->username;
		$auth_model = Model_Auth_General::getAuthModel();
		
		/* Before we do anything, test to make sure we've passed a VALID QUIZ which WE ARE ENTITLED to sit. */
		$quiz = $this->findQuiz($this->_getParam("quiz"));
		
		$finished = false;
		$marking = false;
		$now = strtotime("now");
		
		// Permissions
		$is_open = $quiz->getOpen_date() <= $now;
		if ($auth_model->userInGroup($username, $quiz->getPermissions_group()) && $is_open ) {
			
			// Have we run out of attempts?
			$quizAttempts = Model_Quiz_QuizAttempt::getAllFromUser($username, $quiz);
			if (sizeof($quizAttempts) >= $quiz->getMax_attempts()) {
			    
				// It is possible that we're on our last attempt, and that it's "in progress"...check
				$quizAttempt = $this->findQuizAttemptInProgress($quizAttempts);
				if (!$quizAttempt) {
					throw new Exception("You've exceeded your maximum attempts for this quiz. Cannot continue");
				}
			}
		}
		else {
			if (!$this->view->is_admin) {
				throw new Exception("Insufficient Permissions to take this quiz / Quiz not open yet");
			}
			
			$quizAttempts = Model_Quiz_QuizAttempt::getAllFromUser($username, $quiz);
		}
		
		/* Ok. We're allowed to TAKE the quiz. Are we resuming, or starting a new one? */
		$quizAttempt = $this->findQuizAttemptInProgress($quizAttempts);
		
		if ($quizAttempt == null) {
			$quizAttempt = Model_Quiz_QuizAttempt::fromScratch($now, $quiz, $username);
		}
		
		
		$total_questions = $quiz->getTotalQuestions();
		
		/* We have our quizAttempt ready to go. Now we look to see if we're resuming a question or not */
		$questionAttempt = $quizAttempt->getLastIncompleteQuestion();
		if (is_object($questionAttempt) && !$questionAttempt->isValid()) {
			$questionAttempt->destroy(); // Remove the Question attempt (Database was reinitialised or something)
			$questionAttempt = null;
		}
		
		if ($questionAttempt != null) {
			
			/* Are we getting an ANSWER for this question? */
			//if (array_key_exists("marking", $_POST) && $_POST['marking'] == "1") {
			if(1 == $this->getRequest()->getPost('marking')){
				/* Mark it */
				$marking = true;
				
			}
			
			My_Logger::log("Marking is $marking");
			/* If we reach here, the page has probably been refreshed. We just re-display the last question */
		}
		else {
			/* Have we finished this quiz? */
			if ($quizAttempt->getQuestionAttemptCount() >= $total_questions) {
				
				// Close this attempt and display a result later on down the page
				$quizAttempt->setDate_finished($now);
				
				// Calculate and store the final score
				$quizAttempt->setTotal_score($quizAttempt->getTotal_score());
				$finished = true;
			}
			else {
				/* Make a QuestionAttempt */
				$questionAttempt = $this->makeQuestionAttempt($quizAttempt, $now);
			}
			
		}
		
		// Pass all relevant information to the view
		$this->view->quiz = $quiz;
		$this->view->question_attempt = $questionAttempt;
		$this->view->finished = $finished;
		$this->view->marking = $marking;
		$this->view->mQuizAttempt = $quizAttempt;
		$this->view->vTotalQuestions = $total_questions;
	}
	
	protected function findQuiz($quiz_id){
	    if (is_null($quiz_id)) {
	    	throw new Exception("No quiz was passed. Cannot continue.");
	    }
	    
	    $quiz = Model_Quiz_Quiz::fromID($quiz_id);
	    if ($quiz == null) {
	    	throw new Exception("Quiz ID passed was invalid. Cannot continue.");
	    }
	    
	    return $quiz;
	}
	
	protected function findQuizAttemptInProgress($quizAttempts){
	    if (is_array($quizAttempts)) {
	    	foreach ( $quizAttempts as $qa ) {
	    		if ($qa->getDate_finished() == null) {
	    			return $qa;
	    		}
	    	}
	    }
	}
	
	protected function makeQuestionAttempt($quizAttempt, $now){
	    $questionBase = Model_Shell_QuestionChooser::select_next_question($quizAttempt, true);
	    
	    $path = $this->getFrontController()->getParam('xml_path'); // This way we can set it externally from our tests. If not set, called code will just use the configuration value
	    
	    /* Make a GeneratedQuestion */
	    $cnt = 0; // Make sure we don't get any fluke no-text answers
	    while ( $cnt < 3 ) {
	    		
	    	Model_Shell_Debug::getInstance()->log("vQuestionBase: " . isset($questionBase));
	    	Model_Shell_Debug::getInstance()->log("Generating... from " . $questionBase->getXml());
	    		
	    	$vGen = Model_Quiz_GeneratedQuestion::fromQuestionBase($questionBase, $path);
	    		
	    	if ($vGen->getCorrect_answer() != "" && $vGen->getCorrect_answer() != "\r\n") {
	    		break;
	    	}
	    	else {
	    		$vGen->remove();
	    	}
	    	$cnt++;
	    }
	    
	    if ($vGen->getCorrect_answer() == "" || $vGen->getCorrect_answer() == "\r\n") {
	    	throw new Exception("Error. While generating a question for you, blank answers appeared > 3 times. This should never happen. Either try to refresh this page, or consult your lecturer...");
	    }
	    
	    /* Make a QuestionAttempt */
	    return Model_Quiz_QuestionAttempt::fromScratch($questionBase, $now, $now, $quizAttempt, $vGen);
	}

	public function imagegenAction() {
		if ($_GET['gid'] == null)
			die();
		else {
			$gc = Model_Quiz_GeneratedQuestion::fromID($_GET['gid']);
			if ($gc == null) {
				die();
			}
		}
		
		// TODO: Some more auth here...
		$image_generator = new Model_Image_Generator();
		
		$image_generator->makeImage($gc->getQuestion_data());
		die();
	}
}







