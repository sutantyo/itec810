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



class AdminController extends Zend_Controller_Action {

	/**
	 * Initialises the Administrative Controller
	 *
	 * @return void
	 * @author Ben Evans
	 */
    public function init(){
        $this->_auth = Zend_Auth::getInstance();

		if( $this->_auth->hasIdentity() ) {
			$identity = Zend_Auth::getInstance()->getIdentity();
			if( !isset($identity->username) ) {
				// Don't know how you got here... But you're not authenticated
				$this->_helper->redirector("login", "auth");	//Must Log in before accessing anything
			}

			$this->view->username = $identity->username;

			// Determine what sidebars this person has access to
			// (Determined at this point by defined groups)
			$auth_model = Model_Auth_General::getAuthModel();
			if( $auth_model->userInGroup( $identity->username, QUIZ_ADMINISTRATORS ) ) {
				$this->view->is_admin = true;
			}else{
				$this->view->is_admin = false;
			}


		}else{
			$this->_helper->redirector("login", "auth");	//Must Log in before accessing anything
		}

		$this->view->baseUrl = Zend_Controller_Front::getInstance()->getBaseUrl();



		if( !$this->view->is_admin ) {
			throw new Exception("Unauthorised.", 3005);
		}

    }


	/**
	 * Essentially just give a list of quizzes to edit
	 */
	public function manageAction() {
		My_Logger::log("hello world");
		 $this->view->quizzes = Model_Quiz_Quiz::getAll();
		 $this->view->loadtime = Model_Quiz_Quiz::getZeProfiler();

	}


	/**
	 * Adds or Edits a Quiz
	 *
	 * @return void
	 * @author Ben Evans
	 */
	public function addeditAction() {

		// The Form
		$form = new Form_AddQuiz();
		$this->view->form = $form;


		// Editing? Or new Quiz?
		$editing = null;
		$editing = $this->_getParam("id");
		if( !is_null( $editing ) ) {
			$editing = Model_Quiz_Quiz::fromID( $editing );
			$this->view->editing = $editing;

			// Populate Form
			$id = new Zend_Form_Element_Hidden('id');
			$id->setValue( $editing->getID() );
			$form->addElement($id);

			$form->getElement("name")->setValue($editing->getName());
			$form->getElement("permissions")->setValue($editing->getPermissions_group());
			$form->getElement("opendate")->setValue(date("Y-m-d",$editing->getOpen_date()));
			$form->getElement("closedate")->setValue(date("Y-m-d",$editing->getClose_date()));
			$form->getElement("attempts")->setValue($editing->getMax_attempts());
			$form->getElement("percentage")->setValue($editing->getPercentage_pass());
		}


		// Submitting?
		if( $this->getRequest()->isPost() ) {

			$formData = $_POST;
			//My_Logger::log(var_export($formData, true));
			if (!$form->isValid($_POST)) {
				// Failed validation; redisplay form
				$this->view->form = $form;
				return;
			}else{

				if( is_null($editing) ) {
					// New Quiz
					$vQuiz = Model_Quiz_Quiz::fromScratch($formData['name']
					    ,$formData['permissions']
					    ,$formData['opendate'],$formData['closedate']
					    ,$formData['attempts'],$formData['percentage']);
				}else{
					// Editing Quiz
					$editing->setQuiz_name($formData['name']);
					$editing->setPermissions_group($formData['permissions']);
					$editing->setOpen_date(strtotime($formData['opendate']));
					$editing->setClose_date(strtotime($formData['closedate']));
					$editing->setMax_attempts($formData['attempts']);
					$editing->setPercentage_pass($formData['percentage']);
				}

				// Redirect to the Manage Quiz Pages
				$this->_helper->redirector("manage", "admin");
			}
		}
	}


	/**
	 * Deletes a quiz
	 * Expects parameter [id]
	 *
	 * @return void
	 * @author Ben Evans
	 */
	public function deletequizAction() {

		$quiz_id = $this->_getParam("id");

		if( isset($quiz_id) ) {
			$vQuiz = Model_Quiz_Quiz::fromID($quiz_id);
			$vQuiz->remove();
		}
		// Redirect to the Manage Quiz Pages
		$this->_helper->redirector("manage", "admin");
	}

  /**
    * Delete a sequence
    *
    * To delete a sequence, first we go through the table sequence_quiz to remove
    * all entries of that sequence id, then delete the actual sequence from the
    * table sequence.
    *
    * @return void
    * @author Daniel Sutantyo
    */
  public function deletesequenceAction(){
    $sequence_id = $this->_getParam("id");

    if( isset($sequence_id) ){
      $vSequence = Model_Quiz_Sequence::load($sequence_id);
      if (!$vSequence){
        throw new Exception("Sequence does not exist");
      }
      $vSequence->remove($sequence_id);
    }
    // Redirect to the Manage Quiz Pages
    $this->_helper->redirector("sequences", "admin");
  }



	/**
	 * Displays the Concepts for a Given Quiz
	 * Expects the quiz id as parameter [id]
	 *
	 * @return void
	 * @author Ben Evans
	 */
	public function showconceptsAction() {

		//Grab the Quiz
		$quiz_id = $this->_getParam("id");
		if( !isset($quiz_id) ) {
			throw new Exception("No Quiz Identifier Passed", 3000);
		}

		$quiz = Model_Quiz_Quiz::fromID($quiz_id);
		$this->view->quiz = $quiz;
		$this->view->concepts = Model_Quiz_Concept::getAll();
	}


	/**
	 * Allows Adding and Editing a TestedConcept to a quiz
	 * Expects [quiz_id] as a parameter if no [tested_concept] (id) is passed
	 */
	public function addconceptAction() {
		$form = new Form_AddQuizConcept();
		$quiz_id = $this->_getParam("quiz_id"); //part of the url
		$tested_concept = $this->_getParam("tested_concept");

		if( is_numeric($tested_concept) ) {
			$tested_concept_ob = Model_Quiz_TestedConcept::fromID( intval($tested_concept) );
			if( is_null($tested_concept_ob) ) { throw new Exception("Invalid Tested Concept Identifier"); }
			$form->getElement("submit")->setLabel("Edit Tested Concept");
			$this->view->action_text = "Edit";
			$form->populateFromConcept($tested_concept_ob);

		}elseif( is_numeric($quiz_id) ) {
			$quiz_ob = Model_Quiz_Quiz::fromID( intval($quiz_id) );
			if( is_null($quiz_ob) ) { throw new Exception("Invalid Quiz Identifier"); }
			$this->view->action_text = "Add";
		}else{
			throw new Exception("No quiz identifier or tested concept identifier passed");
		}


		if( $this->getRequest()->isPost() ) {
			$formdata = $this->getRequest()->getPost();
			My_Logger::log(var_export($formdata, true));
			if( $form->isValid($formdata) ) {

				// Either update the existing tested concept or add a new one
				if( isset($tested_concept_ob) ) {
					$vConcept = Model_Quiz_Concept::fromID( $formdata['concept_id'] );
					$tested_concept_ob->updateConcept($vConcept);
					$tested_concept_ob->updateLowerDifficulty( $formdata['difficulty_from'] );
					$tested_concept_ob->updateHigherDifficulty( $formdata['difficulty_to'] );
					$tested_concept_ob->updateNumberTested( $formdata['number_of_questions'] );
					$params = array( 'id' => $tested_concept_ob->getQuiz()->getID() );
				}else{
					$vConcept = Model_Quiz_Concept::fromID( $formdata['concept_id'] );
					$vTestedConcept = Model_Quiz_TestedConcept::fromScratch($formdata['difficulty_from']
					    , $formdata['difficulty_to'], $formdata['number_of_questions']
					    , $vConcept, $quiz_ob);
					$params = array('id' => $quiz_ob->getID());
				}

				$this->_helper->redirector("showconcepts", "admin", null, $params);

			}else{
				$form->populate($formdata);
			}
		}

		$this->view->form = $form;
	}



	/**
	 * Deletes a Concept
	 * Expects parameter [concept_id] to be passed
	 *
	 * @return void
	 * @author Ben Evans
	 */
	public function deleteconceptAction() {
		$concept_id = $this->_getParam("concept_id");
		if( !isset($concept_id) ) {
			throw new Exception("Count not delete concept. No identifier passed", 3000);
		}

		$vTestedConcept = Model_Quiz_TestedConcept::fromID( $concept_id );
		if($vTestedConcept == null)
			throw new Exception("ID passed did not correspond to a valid TestedConcept");

		$vQuiz = $vTestedConcept->getQuiz(); //For the return page

		$vTestedConcept->remove();

		//Redirect to the concept page
		$params = array('id' => $vQuiz->getID() );
		$this->_helper->redirector("showconcepts", "admin", null, $params);

	}


	/**
	 * Shows a list of Quizzes, the total number of
	 * attempts, and the date they are due (Summary Screen)
	 * @author Ben Evans
	 */
	public function resultsoverviewAction() {
		// Get all Quizzes
		$quizzes = Model_Quiz_Quiz::getAll(true);

		// Reverse Order
		$quizzes = array_reverse( $quizzes );

		$this->view->quizzes = $quizzes;
	}

	/**
	 * This shows the results of an individual quiz,
	 * It works by going through all the People in the Quizzes
	 * primary Active Directory group, and then seeing if their
	 * account has an attempt associated with it.
	 */
	public function resultsquizAction() {
		$quiz_id = $this->_getParam("quiz_id");
		if( !isset($quiz_id) ) {
			throw new Exception("No Quiz Identifier Passed", 3000);
		}
		$quiz = Model_Quiz_Quiz::fromID( $quiz_id );
		if( is_null($quiz) || $quiz === false ) {
			throw new Exception("Invalid Quiz Identifier", 3000);
		}

		// Pass the quiz (for general information)
		$this->view->quiz = $quiz;

		// Start By Populating an array with the Group information
		$results = array();
		$group_members = Model_Auth_ActiveDirectory::getUsersFromGroup( $quiz->getPermissions_group() );
		foreach( $group_members as $gm ) {
			$results[ $gm ] = array();
		}
		unset($group_members);

		// At this point, we have an array with keys being the username
		foreach( $results as $name => &$result ) {

			// Get the User's First and Last Name
			$details = Model_Auth_ActiveDirectory::getUserDetails( $name );
			$result['first_name'] = $details['first_name'];
			$result['last_name'] = $details['last_name'];
			$result['username'] = $name;

			//Get the verdict / best score...
			$vHighest = Model_Quiz_QuizAttempt::getHighestMarkQuiz($name, $quiz); // Will be null if not completed, Model_Quiz_QuizAttempt otherwise

			if( !is_null($vHighest) ) {

				// Get their finish date
				$result['completion_date'] = $vHighest->getDate_finished();

				//Is this 'highest' attempt still in progress?
				if( $vHighest->getDate_finished()==null ) {
					$result['verdict'] = "<span class='orange'>In Progress</span>";
				}else{
					// Completed
					//Did they pass/fail?
					if(($vHighest->getTotal_score() / $quiz->getTotalQuestions())*100 >= $quiz->getPercentage_pass()){
						$result['verdict'] = "<span class='green'>PASS</span>";
					}else{
						$result['verdict'] = "<span class='red'>FAIL</span>";
					}
				}



				// Best Score
				$result['best_score'] = $vHighest->getTotal_score();

				// Attempts
				$result['attempts'] = sizeof(Model_Quiz_QuizAttempt::getAllFromUser($name, $quiz));
			}


		}

		$this->view->results = $results;

	}


	/**
	 * This function rebuilds XML files
	 * In doing so, all pre-generated questions will be removed.
	 */
	public function rebuildxmlAction() {
	    $config = new Zend_Config_Ini(APPLICATION_PATH . "/configs/application.ini", APPLICATION_ENV);
	    $path = $config->xml->import_path;

	    $importer = new Model_XML_Importer($path);
	    $importer->delegate = function ($msg){
	        echo $msg;
	    };
		$process = $this->_getParam("process");
		if ( is_null($process) || $process !== "1" ) {
			$this->view->count = $importer->getTotalQuestions();
		}else{
			$importer->processFiles();
		}
	}


	/**
	 * This function Caches the local Usernames (and First/Last names)
	 *  from the designated Authentication source (eg. LDAP)
	 */
	public function syncusernamesAction() {

		$vGroups = array();
		$vCounter = 0;

		//Get all the quizzes
		$vQuizzes = Model_Quiz_Quiz::getAll();
		foreach($vQuizzes as $vQuiz){
			if(!in_array($vQuiz->getPermissions_group(),$vGroups)){
				$vGroups[] = $vQuiz->getPermissions_group();
			}
		}

		//So we have all groups now in the system
		foreach($vGroups as $vGroup){
			//Get the members of this group
			$vMembers = Model_Auth_ActiveDirectory::getUsersFromGroup( $vGroup );
			if( is_array($vMembers) && sizeof($vMembers) > 0 ) {
				foreach($vMembers as $vMember){
					Model_Auth_ActiveDirectory::updateUser($vMember);
					$vCounter++;
				}
			}
		}

		$this->view->counter = $vCounter;

	}

	// ****************************** Sequence, CRUD in same controller for now

	//Sequences
	public function sequencesAction() {
		$this->view->rows = Model_Quiz_Sequence::getAll();
	}

	public function addeditsequenceAction() {

		// The Form
		$form = new Form_SequenceForm();
		$this->view->form = $form;

		// Editing? Or new?
		$id = $this->_getParam("id");
		if ( !is_null( $id ) ) {
			$obj = Model_Quiz_Sequence::load( $id );
			if (!$obj){
				throw new Exception("Object does not exist");
			}
			$this->view->editing = true;

			// Populate Form
			$el = new Zend_Form_Element_Hidden('id');
			$el->setValue( $obj->id );
			$form->addElement($el);

			$form->getElement("name")->setValue($obj->name);
			$form->getElement("permissions_group")->setValue($obj->permissions_group);
		}

		// Submitting?
		if( $this->getRequest()->isPost() ) {

			//if (!$form->isValid($this->getRequest()->getPost())) {
			if ($form->isValid($this->getRequest()->getPost())) {
				$obj = new Model_Quiz_Sequence();
				$obj->fromData($form->getValues());
				$obj->save();
				$this->_helper->redirector("sequences", "admin");
			}
		}

	}

	function addQuizzesToSequenceAction(){
		$seq = Model_Quiz_Sequence::load( $this->_getParam("id") );
		if(!$seq) throw new Exception("Invalid sequence.");


		$available = array();
		foreach ($seq->getAvailableQuizzes() as $row){
			$available[$row['id']] = $row['name'];
		}

		$current = array();
		foreach ($seq->getQuizzes() as $row){
			$current[$row['id']] = $row['name'];
		}

		$this->view->seq = $seq;
		$this->view->available = $available;
		$this->view->current = $current;

		$this->render('sequence-editor');
	}


	function processSequenceEditorAction(){
		$ajax = new Ajax_SequenceEditorProcessor();
		$data = $ajax->process($this->getRequest()->getPost());
		$this->_helper->json($data);
	}


}
