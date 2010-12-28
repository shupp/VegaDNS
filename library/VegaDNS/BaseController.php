<?php

class VegaDNS_BaseController extends Zend_Controller_Action
{
    public function init()
    {
        $bootstrap = $this->getInvokeArg('bootstrap');
        $this->view->version = $bootstrap->getOption('version');
    }
}
