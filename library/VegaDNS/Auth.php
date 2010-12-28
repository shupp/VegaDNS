<?php

class VegaDNS_Auth implements Zend_Auth_Adapter_Interface
{
    protected $_email    = null;
    protected $_password = null;

    public function __construct($email, $password)
    {
        $this->_email    = $email;
        $this->_password = $password;
        $this->_user     = null;
    }

    public function authenticate()
    {
        if (!strlen($this->_email) || !strlen($this->_password)) {
            return new Zend_Auth_Result(
                Zend_Auth_Result::FAILURE_CREDENTIAL_INVALID,
                null,
                array('Invalid username or password')
            );
        }

        try {
            $user = new Model_User(
                array(
                    'Email'    => $this->_email,
                    'Password' => md5($this->_password)
                )
            );
        } catch (Deneb_Exception_NotFound $e) {
            return new Zend_Auth_Result(
                Zend_Auth_Result::IDENTITY_NOT_FOUND
            );
        }

        $this->_user = $user;

        return new Zend_Auth_Result(
            Zend_Auth_Result::SUCCESS,
            $user->cid
        );
    }

    public function getUser()
    {
        return $this->_user;
    }
}
