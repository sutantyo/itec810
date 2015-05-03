<?php

class EditorControllerTest extends ControllerTestCase
{
	protected $code = <<<'code'
	 class ExampleProgram {											//HIDE
			public static void main(String[] `s2`){				//HIDE
				System.out.print("foo");
				if ( x < 0)
				    trhow new Exception();

			}//HIDE
		}//HIDE
code;
    
    function testIndex(){
        $this->dispatch('/');
        
        
        
        $this->assertController('index');
        $this->assertAction('index');
        $this->assertResponseCode(302); //redirect to auth/login
    }
    
}