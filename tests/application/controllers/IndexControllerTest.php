<?php

class IndexControllerTest extends ControllerTestCase
{
    
    function testIndex(){
        $this->dispatch('/');
        
        
        
        $this->assertController('index');
        $this->assertAction('index');
        $this->assertResponseCode(302); //redirect to auth/login
    }
    
}