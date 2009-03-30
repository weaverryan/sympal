<?php

class BasesfSympalUserSigninForm extends sfForm
{
  public function setup()
  {
    parent::setup();

    $this->setWidgets(array(
      'username' => new sfWidgetFormInput(),
      'password' => new sfWidgetFormInput(array('type' => 'password')),
      'remember' => new sfWidgetFormInputCheckbox(),
    ));

    $this->setValidators(array(
      'username' => new sfValidatorString(),
      'password' => new sfValidatorString(),
      'remember' => new sfValidatorBoolean(),
    ));

    $this->validatorSchema->setPostValidator(new sfSympalUserValidator());

    $this->widgetSchema->setNameFormat('signin[%s]');
  }
}