<?php

abstract class VegaDNS_Common extends Framework_Auth_User
{


    /**
     * vdns 
     * 
     * Instance of the VegaDNS Object.  This object actually modifies the DNS records
     * in the database.
     * 
     * @var mixed
     * @access protected
     */
    protected $vdns = null;

    public function __construct()
    {
        parent::__construct();
        $this->vdns = new VegaDNS;
        $this->setData('module', $this->name);
        $this->setGroupID();
        $this->setData('email', $this->user->email);
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
        
    public function getSortField($mode)
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

    public function setGroupID($id = NULL)
    {
        // Which ID are we talking about?
        if (is_null($id)) {
            $id = (isset($_REQUEST['group_id'])) ? $_REQUEST['group_id'] : NULL;
        }

        // Do we have rights?
        if (!is_null($id)) {
            if ($this->user->isSeniorAdmin()) {
                if ($this->user->returnGroup($_REQUEST['group_id'], NULL) == NULL) {
                    $this->setData('message', "Error: requested group_id does not exist");
                    $id = $this->user->group_id;
                } else {
                    if ($this->user->isMyGroup($id) == NULL) {
                        $this->setData('message', "Error: you do not have permission to access resources for the requested group_id");
                        $id = $this->user->group_id;
                    }
                }
            }
        } else {
            $id = $this->user->group_id;
        }
        $this->session->__set('group_id', $id);
    
        // Set it in session
        $group_name_array = $this->user->returnGroup($id, NULL);
        $this->setData('group_name', $group_name_array['name']);
        $this->setData('group_id', $id);
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
            $out .= "<li><img src='images/home.png' border='0' alt='{$g['name']}' /> <a href=\"./?module=Groups&amp;group_id={$g['group_id']}\">" . $this->curMenuOpt($g['group_id'], 'Groups', $g['name']) . "</a></li>\n";
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
                $out .= "<li {$class}><img src='images/group.gif' border='0' alt='{$val['name']}' /> <a href=\"./?module=Groups&amp;group_id={$val['group_id']}\">" . $this->curMenuOpt($g['group_id'], 'Groups', $val['name']) . "</a>\n";
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

    protected function parseSoa($soa)
    {
        $email_soa = explode(":", $soa['host']);
        $array['tldemail'] = $email_soa[0];
        $array['tldhost'] = $email_soa[1];

        $ttls_soa = explode(":", $soa['val']);
        // ttl
        if(!isset($soa['ttl']) || $soa['ttl']  == "") {
            $array['ttl'] = 86400;
        } else {
            $array['ttl'] = $soa['ttl'];
        }
        // refresh
        if($ttls_soa[0] == "") {
            $array['refresh'] = 16384;
        } else {
            $array['refresh'] = $ttls_soa[0];
        }
        // retry
        if($ttls_soa[1] == "") {
            $array['retry'] = 2048;
        } else {
            $array['retry'] = $ttls_soa[1];
        }
        // expiration
        if($ttls_soa[2] == "") {
            $array['expire'] = 1048576;
        } else {
            $array['expire'] = $ttls_soa[2];
        }
        // min
        if($ttls_soa[3] == "") {
            $array['minimum'] = 2560;
        } else {
            $array['minimum'] = $ttls_soa[3];
        }
        return $array;
    }

    protected function setSortLinks($array, $module)
    {
        while(list($key,$val) = each($array)) {
            $newsortway = $this->getSortway($this->sortfield, $val, $this->sortway);
            if ($module == 'Records') {
                $prefix = "./?module=Records&amp;domain_id={$this->domInfo['domain_id']}";
            } else {
                $prefix = "./?module=Domains&amp;group_id={$this->session->group_id}";
            }
            $url = $prefix . "&amp;sortway=$newsortway&amp;sortfield=$val";
            $string = "<a href='$url'>$key</a>";
            if ($this->sortfield == $val) {
                $string .= "&nbsp;<img border='0' alt='{$this->sortway}' src='images/{$this->sortway}.png' />";
            }
            $this->setData($key, $string);
        }
    }
}
?>
