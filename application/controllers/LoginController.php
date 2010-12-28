<?php

class LoginController extends VegaDNS_BaseController
{
    public function init()
    {
        parent::init();
        $this->requireLoggedOutUser();
    }

    public function indexAction()
    {
        $this->view->form = new VegaDNS_Form_Login();
    }

    public function processAction()
    {
        $request = $this->getRequest();
        $this->view->form = new VegaDNS_Form_Login();
        if (!$this->view->form->isValid($request->getParams())) {
            $this->render('index');
        }

        $values  = $this->view->form->getValues();
        $auth    = Zend_Auth::getInstance();
        $adapter = new VegaDNS_Auth($values['email'], $values['password']);
        $result  = $auth->authenticate($adapter);
        if (!$result->isValid()) {
            $this->view->form->addError('Invalid login');
            $this->render('index');
        }

        $this->_helper->redirector('index', 'index');
    }
}
