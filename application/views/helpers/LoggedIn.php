<?php

class Zend_View_Helper_LoggedIn extends Zend_View_Helper_Abstract
{
    public function loggedIn()
    {
        if ($this->view->user instanceof Model_User) {
            return true;
        }
        return false;
    }
}
