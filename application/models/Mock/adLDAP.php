<?php
/**
 * Simple mock configured to provide hardcoded responses
 * @author ivan
 *
 */
class Model_Mock_adLDAP extends adLDAP 
{
    //protected $mockUser;
    
    //override
    function authenticate($username, $password, $preventRebind = false) {
    	return in_array($username, array_keys(Mock_adLDAP_User::$userDb) );
        //return true;
    }
    
    public function connect(){
        $this->userClass = new Mock_adLDAP_User();
        $this->groupClass = new Mock_adLDAP_Group();
        return true;
    }

}


class Mock_adLDAP_User{
    //public function groups(){
    public function groups($username, $recursive = NULL, $isGUID = false){
        //admin
        if ( in_array($username, array('foo', 'admin')) )
            return array(QUIZ_ADMINISTRATORS/*, 'comp115-students'*/); //may need to add the quiz.permission_group value

        return array('vagos', 'research-students', 'farreros', 'comp115-students'); //last group determines what quizzes from the db will be presented to the student
        //blah
        //return [];
    }
    
    public function info($username, $fields = NULL, $isGUID = false){
        if(array_key_exists($username, self::userDb)){
            return self::$userDb[$username];
        }
        return false;
    }
    
    /**
     * Static user database for local development and testing
     */
    static public $userDb = 
	    array(
	    'admin' => array(
	        array('givenname' => array('Site'), 'sn' => array('Admin'))
	    ),
	    'foo' => array(
	        array('givenname' => array('Other'), 'sn' => array('Admin'))
	    ),
	    
	    'hugo' => array(
	        array('givenname' => array('Hugo'), 'sn' => array('McPato'))
	    ),
	    
	    'paco' => array(
	        array('givenname' => array('Paco'), 'sn' => array('Reagan'))
	    ),
	    
	    'luis' => array(
	        array('givenname' => array('Luis'), 'sn' => array('Rico'))
	    ),
	    		
		'ivan' => array(
			array('givenname' => array('Ivan'), 'sn' => array('Rodriguez'))
		),
    
    );
}

class Mock_adLDAP_Group{
    public function members($group, $recursive = NULL){
        //return ['hugo', 'paco', 'luis'];
        return array('hugo', 'paco', 'luis', 'ivan');
    }
}