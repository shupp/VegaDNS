<?php

abstract class VegaDNS_Common extends Framework_Auth_User
{

    public function __construct()
    {
        parent::__construct();
        $this->setData('module', $this->name);
        $this->setGroupID();
    }

    public function getRequestSortWay()
    {
        if (!isset($_REQUEST['sortway'])) {
            $sortway = "asc";
        } else if ( $_REQUEST['sortway'] == 'desc') {
            $sortway = 'desc';
        } else {
            $sortway = 'asc';
        }
        return $sortway;
    }
        
    function getSortField($mode)
    {
        if ($mode == 'records') {
            $default_field = 'type';
        } else if ($mode == 'domains') {
            $default_field = 'status';
        }

        if (!isset($_REQUEST['sortfield'])) {
            $sortfield = $default_field;
        } else {
            $sortfield = $_REQUEST['sortfield'];
        }

        return $sortfield;
    }

    public function getSortWay($sortfield, $val, $sortway)
    {
        if ($sortfield == $val) {
            if ($sortway == 'asc') {
                return 'desc';
            } else {
                return 'asc';
            }
        } else {
            return 'asc';
        }
    }

////////
    public function setGroupID()
    {
        if (isset($_REQUEST['group_id'])) {
            if ($this->user->isSeniorAdmin()) {
                if ($this->user->returnGroup($_REQUEST['group_id'], NULL) == NULL) {
                    $this->setData('message', "Error: requested group_id does not exist");
                    $this->session->__set('group_id', $this->user->myGroupID());
                }
            } else {
                // Check if this is their group
                if ($this->user->isMyGroup($_REQUEST['group_id']) == NULL) {
                    $this->setData('message', 'Error: you do not have permission to access resources for the requested group_id');
                    $this->session->__set('group_id', $this->user->myGroupID());
                }
            }
        } else if (!$this->session->group_id) {
            $this->session->__set('group_id', $this->user->myGroupID());
        }
        $this->setData('group_id', $this->session->group_id);
        $group_id = $this->session->group_id;
        $group_name_array = $this->user->returnGroup($group_id,NULL);
        $this->setData('group_name', $group_name_array['name']);
        $this->setData('group_id', $group_id);
        print_r($this->user->getMenuTree($this->user->groups,1));exit;
        $this->setData('menurows', $this->user->getMenuTree($this->user->groups,1));
    }
////////

}
?>
