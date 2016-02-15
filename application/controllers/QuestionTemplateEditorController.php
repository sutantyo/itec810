<?php
/**
 *  Randomised Programming Quiz System - A quiz system that develops random programming questions from defined templates
 *  Copyright (C) 2010-2013 Ben Evans <ben@nebev.net>
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
class QuestionTemplateEditorController extends Zend_Controller_Action {

	/**
	 * Initialization
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
    }

    public function indexAction(){

    	$this->view->title = "Welcome to the Randomised Quiz System (RQS)";
    	$this->view->headTitle("Welcome");

    	}

	/**
	 * The main editor
	 * @author Ivan Rodriguez
	 */
	public function editorAction() {
		if( !$this->view->is_admin ) {
			throw new Exception("Access Denied");
		}

		$this->_helper->layout->disableLayout();


		/* Get the appropriate files and show them in a nice little combobox */
		$config = new Zend_Config_Ini(APPLICATION_PATH . "/configs/application.ini", APPLICATION_ENV);
		$xml_path = $config->xml->import_path;
    //My_Logger::log("in QuestionTemplateController::editorAction()");
    //My_Logger::log("the xml path is " . $xml_path);
		$this->view->available_files = array(""=>'Select ...') +  $this->getAvailableFiles($xml_path);
		$this->view->selected_xml = $selected_xml = $this->_getParam("q");
		if ( $selected_xml ) {
			$question = Model_Shell_QuestionTemplate::load($xml_path . "/" . $selected_xml .".xml");
		}else{
			$question = new Model_Shell_QuestionTemplate();
		}

		$this->view->question = $question;

		$this->view->fontSizes =  $this->getFontSizeOptions();
		$this->view->substitutions = json_encode($question->getSubstitutions());

		//added by Ivan. Force for now, comment out in release
		//Model_Shell_Debug::getInstance()->saveToDisk();
	}

	protected function getFontSizeOptions(){
		$values = array(10, 11, 12, 14, 16, 18, 20, 24);
		$res = array();
		foreach ( $values as $size){
			$res[$size . "px"] = $size . 'px';
		}
		return $res;
	}

	function ajaxAction(){

		$ajax = new Ajax_TemplateEditorProcessor();
		$res = $ajax->process($this->getRequest()->getPost());
		$this->_helper->json($res);
	}

	protected function getAvailableFiles($xml_path){
		/* Get the appropriate files and show them in a nice little combobox */
		$res = array();
		if ($handle = opendir($xml_path)) {
			while (false !== ($file = readdir($handle))) {
				if(strtolower(substr($file,-3))=="xml"){
					$res[substr($file,0,-4)] = $file;
				}

			}
			closedir($handle);
		}
    sort($res);
		return $res;
	}








}
