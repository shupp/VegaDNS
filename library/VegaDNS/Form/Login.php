<?php

class VegaDNS_Form_Login extends Zend_Form
{
    public function init()
    {
        $this->setAction('/login/process');
        $this->setMethod('POST');

        $email = $this->createElement('text', 'email');
        $email->setRequired(true)
              ->setLabel('Email address')
              ->addValidator('NotEmpty')
              ->addValidator('EmailAddress')
              ->addFilter('StringToLower');
        $this->addElement($email);

        $password = $this->createElement('password', 'password');
        $password->setRequired(true)
              ->setLabel('Password')
              ->addValidator('stringLength', false, array(4, 30));
        $this->addElement($password);

        $submit = $this->createElement('submit', 'submit');
        $submit->setLabel('login');
        $this->addElement($submit);
    }
}
