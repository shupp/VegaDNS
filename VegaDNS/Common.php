<?php

abstract class VegaDNS_Common extends Framework_Auth_User
{

    public function __construct()
    {
        parent::__construct();
        $this->setData('module', $this->name);
        $this->setGroupID();
        $this->setData('email', $this->user->myEmail());
        $this->setData('limit', (int)Framework::$site->config->maxPerPage);
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
            $out .= "<li><img src='images/home.png' border='0'alt='{$g['name']}' /> <a href=\"./?module=Groups&amp;group_id={$g['group_id']}\">" . $this->curMenuOpt($g['group_id'], 'Groups', $g['name']) . "</a></li>\n";
        } else {
            $out .= "<ul>\n";
        }

        $out .= "<li><img src='images/newfolder.png' border='0' alt='Domains' /> <a href=\"./?module=Domains$groupstring\">" . $this->curMenuOpt($g['group_id'], 'Domains') . "</a></li>\n";
        $out .= "<li><img src='images/user_folder.png' border='0' alt='Users' /> <a href=\"./?module=Users$groupstring\">" . $this->curMenuOpt($g['group_id'], 'Users') . "</a></li>\n";
        $out .= "<li><img src='images/newfolder.png' border='0' alt='Log' /> <a href=\"./?module=Log$groupstring\">" . $this->curMenuOpt($g['group_id'], 'Log') . "</a></li>\n";
        if (isset($g['subgroups'])) {
            while (list($key, $val) = each($g['subgroups'])) {
                $class = '';
                if ($this->user->isMyGroup($this->session->group_id, $val)) {
                    $class = 'class="open"';
                }
                $out .= "<li {$class}><img src='images/group.gif' border='0'alt='{$val['name']}' /> <a href=\"./?module=Groups&amp;group_id={$val['group_id']}\">" . $this->curMenuOpt($g['group_id'], 'Groups', $val['name']) . "</a>\n";
                $out .= $this->getMenuTree($val);
                $out .= "</li>\n";
            }
        }
        $out .= "</ul>\n";
        return $out;
    }

    private function curMenuOpt($g, $t, $s = NULL)
    {
        if (is_null($s)) {
            $s = $t;
        }
        if ($g != $this->session->group_id || $t != $this->name) {
            return $s;
        }
        return "<span class='curMenuOpt'>$s</span>";
    }

    protected function getDomainID($domain)
    {
        $q = "SELECT domain_id FROM domains WHERE domain=" . $this->db->Quote($domain);
        $result = $this->db->Execute($q);
        if($result->RecordCount() < 0) {
            return NULL;
        }
        $row = $result->FetchRow();
        return $row['domain_id'];
    }
}
?>
