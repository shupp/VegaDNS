<?php
/**
 * VegaDNS_Common 
 * 
 * Common functions for VegaDNS auth modules
 * 
 * PHP Version 5
 * 
 * @category  VegaDNS
 * @package   VegaDNS
 * @uses      Framework_Auth_User
 * @author    Bill Shupp <hostmaster@shupp.org> 
 * @copyright 2008 Bill Shupp
 * @license   GPL 2.0  {@link http://www.gnu.org/licenses/gpl.txt}
 * @link      {link http://vegadns.org}
 * @abstract
 */


/**
 * VegaDNS_Common 
 * 
 * Common functions for VegaDNS auth modules
 * 
 * @category  VegaDNS
 * @package   VegaDNS
 * @uses      Framework_Auth_User
 * @author    Bill Shupp <hostmaster@shupp.org> 
 * @copyright 2008 Bill Shupp
 * @license   GPL 2.0  {@link http://www.gnu.org/licenses/gpl.txt}
 * @link      {link http://vegadns.org}
 * @abstract
 */
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

    /**
     * __construct 
     * 
     * Set a few things
     * 
     * @access public
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->vdns        = new VegaDNS;
        $this->permissions = VegaDNS_Permissions::singleton();
        $this->setData('module', $this->name);
        $this->setGroupID();
        $this->setData('email', $this->user->email);
    }

    /**
     * setGroupID 
     * 
     * @param mixed $id id default null
     * 
     * @access public
     * @return void
     */
    public function setGroupID($id = null)
    {

        // Which ID are we talking about?
        if ($id === null) {
            $id = (isset($_REQUEST['group_id'])) ? $_REQUEST['group_id'] : null;
        }

        // Do we have rights?
        if ($id !== null) {
            $group = $this->user->groups->fetchGroup($id);
            if ($group == null) {
                $this->setData('message',
                    "Error: requested group_id does not exist or is not yours");
                $group = $this->user->groups;
            }
        } else {
            $group = $this->user->groups;
        }
        $this->session->group_id = $group->id;
    
        // Set it in session
        $this->setData('group_name', $group->name);
        $this->setData('group_id', $group->id);
        $this->setData('menurows', $this->getMenuTree($this->user->groups, 1));
    }

    /**
     * getMenuTree 
     * 
     * @param mixed $g   group
     * @param mixed $top whether this is the first group
     * 
     * @access public
     * @return void
     */
    public function getMenuTree($g,$top = null)
    {
        $groupstring = '';
        $out         = '';
        if ($g != null) {
            $groupstring = "&amp;group_id={$g->id}";
        }
        if ($top != null) {
            $out .= "<ul id=\"menu\">\n";
            $out .= "<li><img src='images/home.png' border='0' alt='{$g->name}' /> <a href=\"./?module=Groups&amp;group_id={$g->id}\">" . $this->_curMenuOpt($g->id, 'Groups', $g->name) . "</a></li>\n";
        } else {
            $out .= "<ul>\n";
        }

        $out .= "<li><img src='images/newfolder.png' border='0' alt='Domains' /> <a href=\"./?module=Domains$groupstring\">" . $this->_curMenuOpt($g->id, 'Domains') . "</a></li>\n";
        $out .= "<li><img src='images/user_folder.png' border='0' alt='Users' /> <a href=\"./?module=Users$groupstring\">" . $this->_curMenuOpt($g->id, 'Users') . "</a></li>\n";
        $out .= "<li><img src='images/newfolder.png' border='0' alt='Log' /> <a href=\"./?module=Log$groupstring\">" . $this->_curMenuOpt($g->id, 'Log') . "</a></li>\n";
        if (count($g->subGroups)) {
            foreach ($g->subGroups as $val) {
                $class = '';
                if ($this->session->group_id ==  $val->id) {
                    $class = ' class="open"';
                }
                $out .= "<li{$class}><img src='images/group.gif' border='0' alt='{$val->name}' /> <a href=\"./?module=Groups&amp;group_id={$val->id}\">" . $this->_curMenuOpt($g->id, 'Groups', $val->name) . "</a>\n";
                $out .= $this->getMenuTree($val);
                $out .= "</li>\n";
            }
        }
        $out .= "</ul>\n";
        return $out;
    }

    /**
     * _curMenuOpt 
     * 
     * @param mixed $g group
     * @param mixed $t 
     * @param mixed $s 
     * 
     * @access private
     * @return void
     */
    private function _curMenuOpt($groupID, $type, $s = null)
    {
        if ($s == null) {
            $s = $type;
        }
        if ($groupID != $this->session->group_id || $type != $this->name) {
            return $s;
        }
        return "<span class='curMenuOpt'>$s</span>";
    }

    /**
     * parseSoa 
     * 
     * @param string $soa soa string
     * 
     * @access protected
     * @return void
     */
    protected function parseSoa($soa)
    {
        $email_soa         = explode(":", $soa['host']);
        $array['tldemail'] = $email_soa[0];
        $array['tldhost']  = $email_soa[1];

        $ttls_soa = explode(":", $soa['val']);
        // ttl
        if (!isset($soa['ttl']) || $soa['ttl']  == "") {
            $array['ttl'] = 86400;
        } else {
            $array['ttl'] = $soa['ttl'];
        }
        // refresh
        if ($ttls_soa[0] == "") {
            $array['refresh'] = 16384;
        } else {
            $array['refresh'] = $ttls_soa[0];
        }
        // retry
        if ($ttls_soa[1] == "") {
            $array['retry'] = 2048;
        } else {
            $array['retry'] = $ttls_soa[1];
        }
        // expiration
        if ($ttls_soa[2] == "") {
            $array['expire'] = 1048576;
        } else {
            $array['expire'] = $ttls_soa[2];
        }
        // min
        if ($ttls_soa[3] == "") {
            $array['minimum'] = 2560;
        } else {
            $array['minimum'] = $ttls_soa[3];
        }
        return $array;
    }

    /**
     * setSortLinks 
     * 
     * @param mixed $array  array
     * @param mixed $module module
     * 
     * @access protected
     * @return void
     */
    protected function setSortLinks($array, $module)
    {
        while (list($key,$val) = each($array)) {
            $newsortway = VegaDNS_Sort::getSortway($this->sortfield, $val, $this->sortway);
            if ($module == 'Records') {
                $prefix = "./?module=Records&amp;domain_id={$this->domInfo['domain_id']}";
            } else {
                $prefix = "./?module=Domains&amp;group_id={$this->session->group_id}";
            }
            $url    = $prefix . "&amp;sortway=$newsortway&amp;sortfield=$val";
            $string = "<a href='$url'>$key</a>";
            if ($this->sortfield == $val) {
                $string .= "&nbsp;<img border='0' alt='{$this->sortway}' src='images/{$this->sortway}.png' />";
            }
            $this->setData($key, $string);
        }
    }
}
?>
