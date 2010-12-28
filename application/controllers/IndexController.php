<?php

class IndexController extends VegaDNS_BaseController
{

    public function init()
    {
        parent::init();
        $this->requireLoggedInUser();
    }

    public function indexAction()
    {
        // action body
    }


}

