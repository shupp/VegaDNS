<?php

class Model_User extends Deneb_Object_Common
{
    protected $_table = 'accounts';
    protected $_object = 'user';
    protected $_primaryKey = 'cid';

    public function isSeniorAdmin()
    {
        return $this->Account_Type == 'senior_admin';
    }

    public function isGroupAdmin()
    {
        return $this->Account_Type == 'group_admin';
    }
}
