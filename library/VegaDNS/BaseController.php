<?php

class VegaDNS_BaseController extends Zend_Controller_Action
{
    protected $_user = null;
    protected $_log = null;

    public function init()
    {
        $bootstrap           = $this->getInvokeArg('bootstrap');
        $this->view->version = $bootstrap->getOption('version');
        $this->_setLoggedInUser();
    }

    protected function _setLoggedInUser()
    {
        $auth = Zend_Auth::getInstance();
        if (!$auth->hasIdentity()) {
            return;
        }

        $cid = $auth->getIdentity();
        try {
            $user = new Model_User(array('cid' => $cid));
        } catch (Deneb_Exception_NotFound $e) {
            $auth->clearIdentity();
            return;
        }

        $this->_user      = $user;
        $this->view->user = $user;
    }

    public function getUser()
    {
        return $this->_user;
    }

    public function requireLoggedInUser()
    {
        if ($this->_user instanceof Model_User) {
            return true;
        }
        $this->_helper->_redirector('index', 'login');
    }

    public function requireLoggedOutUser()
    {
        if ($this->_user instanceof Model_User) {
            $this->_helper->_redirector('index', 'index');
        }
        return true;
    }

    public function requireSeniorAdminUser()
    {
        if ($this->_user instanceof Model_User) {
            if ($this->_user->isSeniorAdmin()) {
                return true;
            }
            throw new Zend_Controller_Action_Exception('Insufficient Privileges', 401);
        } else {
            throw new Zend_Controller_Action_Exception('Login required', 401);
        }
    }

    public function requireGroupAdminUser()
    {
        if ($this->_user instanceof Model_User) {
            if ($this->_user->isSuperAdmin() || $this->_user->isGrouAdmin()) {
                return true;
            }
            throw new Zend_Controller_Action_Exception('Insufficient Privileges', 401);
        } else {
            throw new Zend_Controller_Action_Exception('Login required', 401);
        }
    }

    public function getLog()
    {
        if ($this->_log == null) {
            $this->_log = $this->getInvokeArg('bootstrap')->getResource('Log');
        }

        return $this->_log;
    }
}
