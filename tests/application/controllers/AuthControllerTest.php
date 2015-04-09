<?php

class AuthControllerTest extends ControllerTestCase
{
    
    function testLogin(){
        $this->dispatch('/auth/login');
    
        //echo $this->getResponse()->getBody();
    
        $this->assertController('auth');
        $this->assertAction('login');
        $this->assertResponseCode(200);
        
        $this->assertXpathCount('//input[@name="rqz-username"]', 1);
        $this->assertXpathContentContains('//h1', 'Randomised Quiz System');
        
    }
    
    function testLoginSubmit(){
        
        $this->getRequest()->setMethod('POST')
            ->setParams(array('rqz-username'=>'foo',
                              'rqz-password'=>'secret'
                        ));
        
        $this->assertTrue($this->getRequest()->isPost());
        
        $this->dispatch('/auth/login');
        
        //echo $this->getResponse()->getBody();
        
        $this->assertController('auth');
        $this->assertAction('login');
        $this->assertResponseCode(302);
        
        My_Logger::log('Hello!');
    }
    
    
}