<?php

abstract class VegaDNS_Common extends Framework_Auth_User
{

    public function __construct()
    {
        parent::__construct();
        $this->setData('module', $this->name);
        $this->setGroupID();
        $this->setData('email', $this->user->myEmail());
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
            $this->session->__set('group_id', $_REQUEST['group_id']);
        } else if (is_null($this->session->group_id)) {
            $this->session->__set('group_id', $this->user->myGroupID());
        }
        $this->setData('group_id', $this->session->group_id);
        $group_id = $this->session->group_id;
        $group_name_array = $this->user->returnGroup($group_id,NULL);
        $this->setData('group_name', $group_name_array['name']);
        $this->setData('group_id', $group_id);
        $this->setData('menurows', $this->getMenuTree($this->user->groups,1));
    }

    public function getMenuTree($g,$top = NULL)
    {
        $out = '';
        $groupstring = '';
        if (!is_null($g)) {
            $groupstring = "&amp;group_id={$g['group_id']}";
        }
        if (!is_null($top)) {
            $out .= "<ul>\n";
            $out .= "<li><img src='images/home.png' border='0'alt='{$g['name']}' /> {$g['name']}</li>\n";
        } else {
            $out .= "<ul>\n";
        }

        $out .= "<li><img src='images/newfolder.png' border='0' alt='Domains' /> <a href=\"./?module=Domains$groupstring\">Domains</a></li>\n";
        $out .= "<li><img src='images/user_folder.png' border='0' alt='Users' /> <a href=\"./?module=Users$groupstring\">Users</a></li>\n";
        $out .= "<li><img src='images/newfolder.png' border='0' alt='Log' /> <a href=\"./?module=Log$groupstring\">Log</a></li>\n";
        if (isset($g['subgroups'])) {
            while (list($key, $val) = each($g['subgroups'])) {
                $class = '';
                $current = '';
                if ($this->user->isMyGroup($this->session->group_id, $val)) {
                    $class = 'class="open"';
                }
                if ($this->session->group_id == $val['group_id']) {
                    $current = 'id="current"';
                }
                $out .= "<li {$current} {$class}><img src='images/group.gif' border='0'alt='{$val['name']}' /> <a href=\"./?module=Groups&amp;group_id={$val['group_id']}\">{$val['name']}</a>\n";
                $out .= $this->getMenuTree($val);
                $out .= "</li>\n";
            }
        }
        $out .= "</ul>\n";
        return $out;
    }
}
?>
