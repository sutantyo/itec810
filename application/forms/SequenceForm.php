<?php
/**
 * 
 * @author Ivan Rodriguez
 *
 */
class Form_SequenceForm extends Zend_Form
{

	
    public function init()
    {
        $this->setName('addquiz');

		$validatorPositive = new Zend_Validate_GreaterThan(0);
		$validatorLessthan = new Zend_Validate_LessThan(101);


        $name = new Zend_Form_Element_Text('name');
        $name->setLabel('Quiz Name')
			->setRequired(true)
			->addFilter('StripTags')
			->addFilter('StringTrim')
			->addErrorMessage("Please Enter a Valid Sequence Name")
			->setAttrib('placeholder', 'eg. Test sequence')
			->addValidator('NotEmpty');



		$permissions = new Zend_Form_Element_Text('permissions_group');
        $permissions->setLabel('Permissions Group')
			->setRequired(true)
			->addFilter('StripTags')
			->addFilter('StringTrim')
			->setAttrib('placeholder', 'eg. comp115-students')
			->addValidator('NotEmpty');

        $submit = new Zend_Form_Element_Submit('submit');
        //$submit->setAttrib('value', 'Submit');
        $submit->setAttrib('id', 'submitbutton');


		$this->addElements(array( $name, $permissions, $submit));


    }
}