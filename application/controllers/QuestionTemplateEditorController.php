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
    


	/**
	 * This is the default page that people see
	 * Generally speaking, it shows the welcome page, and some sidebard
	 *
	 * @return void
	 * @author Ben Evans
	 */
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
		
		$available_selects = array();
		/*if ($handle = opendir($xml_path)) {
		    while (false !== ($file = readdir($handle))) {
		        if(strtolower(substr($file,-3))=="xml"){
					$entity = "<option value='".substr($file,0,-4)."'";
						if((array_key_exists("q", $_GET)) && substr($file,0,-4) == $_GET['q']){
							$entity .= " selected='yes' ";
						}
					$entity .= ">$file</option>\n";
					$available_selects[] = $entity;
				}
				
		    }
			closedir($handle);
		}
*/
		$this->view->available_selects = $available_selects;

		// See what Question we're looking at...
		$selected_xml = $this->_getParam("q");
		if( isset($selected_xml) && !is_null($selected_xml) ) {
			
			// Get the Question XML
			try{
				$mQuestion = new Model_Shell_GenericQuestion($xml_path . "/" . $selected_xml .".xml");
				$this->view->question = $mQuestion;
			} catch (Exception $e) {
				//throw $e;
			}
			
			// Just make a new random question, so we get access to functions like Randset
			$temp = new Model_Quiz_GeneratedQuestion();
			
		}
		
		//added by Ivan. Force for now, comment out in release 
		Model_Shell_Debug::getInstance()->saveToDisk();
	}
	
	
	
	
	
	


}// End Class

function sort_by_score(&$a, &$b){
	if($a->getTotal_score()>$b->getTotal_score())
		return -1;
	elseif($a->getTotal_score()==$b->getTotal_score()){
		//Take into account the time taken
		//a less time (better) -> -1
		if(($a->getDate_finished()-$a->getDate_started()) <  ($b->getDate_finished()-$b->getDate_started()) )
			return -1;
		else
			return 1;
	}
	else
		return 1;
}







