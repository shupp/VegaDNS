<?php

class LogoutController extends VegaDNS_BaseController
{
    public function init()
    {
        parent::init();
        $this->requireLoggedInUser();
    }

    public function indexAction()
    {
        $request = $this->getRequest();
        if ($request->getMethod() != 'POST') {
            $this->getLog()->crit('Non-POST request for logout');
            throw new Zend_Controller_Action_Exception('Invalid request');
        }

        $auth = Zend_Auth::getInstance();
        $auth->clearIdentity();
        $this->_helper->json(array('success' => true));
    }
}
?>
