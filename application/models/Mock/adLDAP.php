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
        return true;
    }
    
    public function connect(){
        $this->userClass = new Mock_adLDAP_User();
        $this->groupClass = new Mock_adLDAP_Group();
        return true;
    }
    /*
    public function user(){
        return $this->mockUser;
    }*/
}

class Mock_adLDAP_User{
    //public function groups(){
    public function groups($username, $recursive = NULL, $isGUID = false){
        //admin
        if ($username == 'foo' || $username == 'admin')
            return array(QUIZ_ADMINISTRATORS/*, 'comp115-students'*/); //may need to add the quiz.permission_group value

        return array('vagos', 'research-students', 'farreros', 'comp115-students'); //last group determines what quizzes from the db will be presented to the student
        //blah
        //return [];
    }
    
    public function info($username, $fields = NULL, $isGUID = false){
        if(array_key_exists($username, $this->userDb)){
            return $this->userDb[$username];
        }
        return false;
    }
    
    protected $userDb = /*[
        'foo' => [
            ['givenname' => ['Ivan'], 'sn' => ['Rodriguez']]
        ],
        
        'hugo' => [
            ['givenname' => ['Hugo'], 'sn' => ['McPato']]
        ],
        
        'paco' => [
            ['givenname' => ['Paco'], 'sn' => ['Reagan']]
        ],
        
        'luis' => [
            ['givenname' => ['Luis'], 'sn' => ['Rico']]
        ],
        
    ];
    */
    array(
    'foo' => array(
        array('givenname' => array('Ivan'), 'sn' => array('Rodriguez'))
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
    
    );
}

class Mock_adLDAP_Group{
    public function members($group, $recursive = NULL){
        //return ['hugo', 'paco', 'luis'];
        return array('hugo', 'paco', 'luis');
    }
}