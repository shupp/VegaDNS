<?php
/**
 * Framework_User_VegaDNS 
 * 
 * PHP Version 5
 * 
 * @category  DNS
 * @package   VegaDNS
 * @uses      Framework_User
 * @author    Bill Shupp <hostmaster@shupp.org> 
 * @copyright 2008 Bill Shupp
 * @license   GPL 2.0  {@link http://www.gnu.org/licenses/gpl.txt}
 * @link      http://www.vegadns.org
 */

/**
 * Framework_User_VegaDNS 
 * 
 * VegaDNS User class
 * 
 * @category  DNS
 * @package   VegaDNS
 * @uses      Framework_User
 * @author    Bill Shupp <hostmaster@shupp.org> 
 * @copyright 2008 Bill Shupp
 * @license   GPL 2.0  {@link http://www.gnu.org/licenses/gpl.txt}
 * @link      http://www.vegadns.org
 */
class Framework_User_VegaDNS extends Framework_User
{
    /**
     * groups 
     * 
     * An array of the logged in users groups
     * 
     * @var mixed
     * @access public
     */
    public $groups = null;
    /**
     * permFlagValues 
     * 
     * Permission bit flag values
     * 
     * @var array
     * @access public
     */
    public $permFlagValues = array(
            'inherit_group_perms'   => 0x01,
            'account_edit'          => 0x02,
            'account_create'        => 0x04,
            'account_delete'        => 0x08,
            'group_edit'            => 0x010,
            'group_create'          => 0x020,
            'group_delete'          => 0x040,
            'domain_edit'           => 0x080,
            'domain_create'         => 0x0100,
            'domain_delegate'       => 0x0200,
            'domain_delete'         => 0x0400,
            'record_edit'           => 0x0800,
            'record_create'         => 0x01000,
            'record_delete'         => 0x02000,
            'record_delegate'       => 0x04000,
            'default_record_edit'   => 0x08000,
            'default_record_create' => 0x010000,
            'default_record_delete' => 0x020000,
            'rrtype_allow_n'        => 0x040000,
            'rrtype_allow_a'        => 0x080000,
            'rrtype_allow_3'        => 0x0100000,
            'rrtype_allow_6'        => 0x0200000,
            'rrtype_allow_m'        => 0x0400000,
            'rrtype_allow_p'        => 0x0800000,
            'rrtype_allow_t'        => 0x01000000,
            'rrtype_allow_v'        => 0x02000000,
            'rrtype_allow_all'      => 0x04000000
            );

    /**
     * seniorPerms 
     * 
     * All permissions but inherit
     * 
     * @var float
     * @access private
     */
    private $seniorPerms = 134217726;
    /**
     * defaultPerms 
     * All but account/group create/delete/edit
     * 
     * @var float
     * @access private
     */
    private $defaultPerms = 134217615;

    /**
     * authenticate 
     * 
     * Authenticate using email/password
     * 
     * @param mixed $email    email address
     * @param mixed $password passwod
     * 
     * @access public
     * @return void
     */
    public function authenticate($email, $password)
    {
        $sql = "SELECT user_id FROM `accounts` 
                WHERE email=" . $this->db->Quote($email) . " 
                AND password = MD5(" . $this->db->Quote($password) . ")";
        try {
            $result = $this->db->Execute($sql);
        } catch (Exception $e) {
            throw new Framework_Exception($e->getMessage());
        }
        if ($result->RecordCount() == 0) {
            return false;
        }
        $this->data = $result->FetchRow();
        $session    = Framework_Session::singleton();
        $field      = (string)Framework::$site->config->user->userField;
        $value      = $this->data[$field];

        $session->{$field} = $value;
        return true;
    }

    /**
     * isSeniorAdmin 
     * 
     * @param mixed $data user data
     * 
     * @access public
     * @return bool true on success, false on failure
     */
    public function isSeniorAdmin($data = null)
    {
        if (is_null($data)) {
            $data = $this->data;
        }
        if ($data['account_type'] == 'senior_admin') {
            return true;
        }
        return false;
    }

    /**
     * myGroupID 
     * 
     * @access public
     * @return int logged in user's group id
     */
    public function myGroupID()
    {
        return $this->data['group_id'];
    }

    /**
     * getAccountInfo 
     * 
     * Get current account settings
     * 
     * @param mixed $userID user id to get account info for
     * 
     * @access public
     * @return mixed account info array  on success, null on failure
     */
    public function getAccountInfo($userID)
    {
        $sql = "SELECT a.*, b.perm_value AS user_perms, c.perm_value AS group_perms
                FROM " . (string)Framework::$site->config->user->userTable . " a 
                LEFT JOIN user_permissions b ON a.user_id = b.user_id
                LEFT JOIN group_permissions c on a.group_id = c.group_id
                WHERE a." . (string)Framework::$site->config->user->userField 
                . " = " . $this->db->Quote($userID);

        try {
            $result = $this->db->Execute($sql);
        } catch (Exception $e) {
            throw new Framework_Exception($e->getMessage());
        }
        
        if ($result->RecordCount() == 0) {
            return null;
        }
        return $result->FetchRow();
    }

    /**
     * getSubGroups 
     * 
     * Get sub groups of a given group
     * 
     * @param int $id id of parent group
     * 
     * @access public
     * @return mixed sub group array on success, null on failure
     */
    public function getSubGroups($id)
    {
        $q = "SELECT group_id from GROUPS 
                WHERE group_id != ".$this->db->Quote($id)." 
                AND parent_group_id = ".$this->db->Quote($id);

        $result = $this->db->Execute($q) or die($this->db->ErrorMsg());
        if ($result->RecordCount() == 0) {
            return null;
        } else {
            $count = 0;
            while (!$result->EOF) {
                $row[$count] = $result->FetchRow();
                $count++;
            }
            return $row;
        }
    }

    /**
     * returnGroup 
     * 
     * Return group
     * 
     * @param mixed $id group id
     * @param mixed $g  parent, defauls to null
     * 
     * @access public
     * @return void
     */
    public function returnGroup($id, $g = null)
    {
        if ($g == null) {
            $g = $this->groups;
        }
        if ($g['group_id'] == $id) {
            return $g;
        }
        if (!isset($g['subgroups'])) {
            $array = null;
        } else {
            while (list($key,$val) = each($g['subgroups'])) {
                $temp = $this->returnGroup($id, $val);
                if ($temp['group_id'] == $id) {
                    $array = $temp;
                    break;
                } else {
                    $array = null;
                }
            }
        }
        return $array;
    }

    /**
     * getAllSubgroups 
     * 
     * Get all subgroups of $id
     * 
     * @param mixed $id id of parent group
     * 
     * @access public
     * @return mixed array of subgroups on success, null on failure
     */
    public function getAllSubgroups($id)
    {
        // Get Top
        $q = "SELECT * FROM groups WHERE group_id=".$this->db->Quote($id)." LIMIT 1";

        $result = $this->db->Execute($q) or die($this->db->ErrorMsg());
        if ($result->RecordCount() == 0) {
            return null;
        } else {
            $top = $result->FetchRow();
        }
        // Get subgroups
        $subs = $this->getSubGroups($top['group_id']);
        if ($subs == null) {
            return $top;
        } else {
            $count = 0;
            while (list($key,$val) = each($subs)) {
                $top['subgroups'][$count] = $this->getAllSubgroups($val['group_id']);
                $count++;
            }
            return $top;
        }
        
    }

    /**
     * isMyGroup 
     * 
     * @param mixed $g     group id
     * @param mixed $array array of groups to check against.
     * Defaults to null
     * 
     * @access public
     * @return void
     */
    public function isMyGroup($g, $array = null)
    {
        if (($temp = $this->returnGroup($g, $array)) == null) {
            return null;
        } else {
            return $temp;
        }
    }

    public function isMyAccount($id)
    {

        // Fetch group_id
        if (($g = $this->userID_to_GroupID($id)) == null) {
            return false;
        } else if (($temp = $this->returnGroup($g)) == null) {
            return false;
        } else {
            return true;
        }
    }

    public function returnUserPermissions($id)
    {
        $q = "select * from user_permissions where user_id=".$this->db->Quote($id);
        $result = $this->db->Execute($q) or die($this->db->ErrorMsg());
        if ($result->RecordCount() == 0) {
            return 'INHERIT';
        }
        $perms = $result->FetchRow();
        if ($perms['inherit_group_perms'] == 1) {
            return 'INHERIT';
        } else {
            return $perms;
        }
        
    }

    public function returnGroupParentID($id)
    {
        $q = "select parent_group_id from groups where group_id=".$this->db->Quote($id);
        $result = $this->db->Execute($q) or die($this->db->ErrorMsg());
        if ($result->RecordCount() == 0) return null;
        $row = $result->FetchRow();
        return $row['parend_group_id'];
    }

    public function returnGroupPermissions($id)
    {
        $q = "select * from group_permissions where group_id=".$this->db->Quote($id);
        $result = $this->db->Execute($q) or die($this->db->ErrorMsg());
        if ($result->RecordCount() == 0) return null;
        $perms = $result->FetchRow();
        if ($perms['inherit_group_perms'] == 1) {
            // Find the parent permissions
            $inherit = true;
            while ($inherit != false) {
                // Get parent ID
                $parent = $this->returnParentGroupID($id);
                $q = "select * from group_permissions where group_id=".$this->db->Quote($parent);
                $result = $this->db->Execute($q) or die($this->db->ErrorMsg());
                if ($result->RecordCount() == 0) return null;
                $perms = $result->FetchRow();
                if ($perms['inherit_group_perms'] == 1) {
                    $id = $parent;
                    continue;
                } else {
                    $inherit = false;
                }
            }
        }
        return $perms;
    }

    public function canCreateSubGroups()
    {
        if ($this->account['permissions']['group_create'] == 1) {
            return true;
        } else {
            return false;
        }
    }

    public function canCreateDomains()
    {
        if ($this->account['permissions']['domain_create'] == 1) {
            return true;
        } else {
            return false;
        }
    }

    public function canEditDomains()
    {
        if ($this->account['permissions']['domain_edit'] == 1) {
            return true;
        } else {
            return false;
        }
    }

    public function canDeleteDomains()
    {
        if ($this->account['permissions']['domain_delete'] == 1) {
            return true;
        } else {
            return false;
        }
    }

    public function canCreateRecord()
    {
        if ($this->account['permissions']['record_create'] == 1) {
            return true;
        } else {
            return false;
        }
    }

    public function canDeleteRecord()
    {
        if ($this->account['permissions']['record_delete'] == 1) {
            return true;
        } else {
            return false;
        }
    }

    public function canEditRecord()
    {
        if ($this->account['permissions']['record_edit'] == 1) {
            return true;
        } else {
            return false;
        }
    }

    public function canDelegateRecord()
    {
        if ($this->account['permissions']['record_delegate'] == 1) {
            return true;
        } else {
            return false;
        }
    }

    public function canCreateDefaultRecords()
    {
        if ($this->account['permissions']['default_record_create'] == 1) {
            return true;
        } else {
            return false;
        }
    }

    public function canEditDefaultRecords()
    {
        if ($this->account['permissions']['default_record_edit'] == 1) {
            return true;
        } else {
            return false;
        }
    }

    public function canDeleteDefaultRecords()
    {
        if ($this->account['permissions']['default_record_delete'] == 1) {
            return true;
        } else {
            return false;
        }
    }

    public function canEditUser($id)
    {
        if ($this->account['permissions']['account_edit'] == 1) {
            if ($this->isMyAccount($id)) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public function canEditSelf()
    {
        if ($this->account['permissions']['self_edit'] == 1) {
            return true;
        } else {
            return false;
        }
    }

    public function userID_to_GroupID($user_id)
    {
        $q = "select group_id from accounts where user_id=".$this->db->Quote($user_id);
        $result = $this->db->Execute($q) or die($this->db->ErrorMsg());
        if ($result->RecordCount() == 0) {
            return null;
        }

        $row = $result->FetchRow();
        return $row['group_id'];
    }

    public function canEditSubGroups()
    {
        if ($this->account['permissions']['group_edit'] == 1) {
            return true;
        } else {
            return false;
        }
    }

    public function canDeleteGroup($g)
    {
        if ($this->account['permissions']['group_delete'] == 1) {
            if ($g == null) {
                return true;
            } else if ($this->isMyGroup($g)) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public function canEditGroup($g)
    {
        if ($this->account['permissions']['group_edit'] == 1) {
            if ($g == null) {
                return true;
            } else if ($this->isMyGroup($g)) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }


    public function canCreateUsers($id,$g)
    {

        // Senior Admins can do anything
        if ($this->account['account_type'] == 'senior_admin') return true;

        // See if it's the logged in user
        if ($id == null) {
            if ($this->account['permissions']['account_create'] == 1) {
                return true;
            } else {
                return false;
            }
        }

        // Else look up the permissions

        $perms = $this->returnUserPermissions($id);
        if ($perms == "INHERIT") {
            // GET GROUP PERMS
            $perms = $this->returnGroupPermissions($g);
        } else if ($perms == null) {
            return false;
        } else if ($perms['group_create'] == 1) {
            return true;
        } else {
            return false;
        }
    }

    public function canDeleteUsers($id,$g)
    {

        // Senior Admins can do anything
        if ($this->account['account_type'] == 'senior_admin') return true;

        // See if it's the logged in user
        if ($id == null) {
            if ($this->account['permissions']['account_delete'] == 1) {
                return true;
            } else {
                return false;
            }
        }

        // Else look up the permissions

        $perms = $this->returnUserPermissions($id);
        if ($perms == "INHERIT") {
            // GET GROUP PERMS
            $perms = $this->returnGroupPermissions($g);
        } else if ($perms == null) {
            return false;
        } else if ($perms['group_create'] == 1) {
            return true;
        } else {
            return false;
        }
    }

    public function canDeleteUser($id)
    {

        if ($this->canDeleteUsers(null,null) == false) {
            return false;
        } else {
            $q = "select group_id from accounts where user_id='$id'";
            $result = $this->db->Execute($q) or die($this->db->ErrorMsg());
            if ($result->RecordCount() == 0) return null;
            $row = $result->FetchRow();
            if ($this->isMyGroup($row['group_id']) != null) {
                return true;
            } else {
                return false;
            }
        }
    }

    public function returnGroupID($name)
    {
        $q = "select group_id from groups where name=".$this->db->Quote($name);
        $result = $this->db->Execute($q) or die($this->db->ErrorMsg());
        if ($result->RecordCount() == 0) return null;
        $row = $result->FetchRow();
        return $row['group_id'];
    }

    public function returnUserID($email)
    {
        $q = "select user_id from accounts where email=".$this->db->Quote($email);
        $result = $this->db->Execute($q) or die($this->db->ErrorMsg());
        if ($result->RecordCount() == 0) return null;
        $row = $result->FetchRow();
        return $row['user_id'];
    }


    public function returnCreateGroupPermQuery($name)
    {
        // Get permissions key list from senior_perms array
        // Then compare user perms against $_REQUEST elements 

        $u_perms = $this->account['permissions'];
        $perm_array = array();

        while (list($key,$val) = each($this->senior_perms)) {
            if (isset($u_perms[$key]) && $u_perms[$key] == 1) {
                if (isset($_REQUEST[$key])) {
                    $perm_array[$key] = 1;
                } else {
                    $perm_array[$key] = 0;
                }
            }
        }

        // Now that the perm_array is built, let's build the query string
        if (($id = $this->returnGroupID($name)) == null) return null;


        // Build colmns, values
        $col_string = "";
        $val_string = "";
        while (list($key,$val) = each($perm_array)) {
            $col_string .= ",$key";
            $val_string .= ",$val";
        }
        $q = "insert into group_permissions (group_id$col_string) values('$id'$val_string)" ;

        // INHERIT???

        return $q;
    }

    public function returnEditGroupPermQuery($id)
    {

        // Get permissions key list from senior_perms array
        // Then compare user perms against $_REQUEST elements 

        $u_perms = $this->account['permissions'];
        $perm_array = array();

        while (list($key,$val) = each($this->senior_perms)) {
            if (isset($u_perms[$key]) && $u_perms[$key] == 1) {
                if (isset($_REQUEST[$key])) {
                    $perm_array[$key] = 1;
                } else {
                    $perm_array[$key] = 0;
                }
            }
        }

        // Build set string
        $edit_string = "";
        $counter = 0;
        while (list($key,$val) = each($perm_array)) {
            $edit_string .= " $key=".$this->db->Quote($val);
            $counter++;
            if ($counter < count($perm_array)) $edit_string .= ",";
        }
        $q = "update group_permissions set $edit_string where group_id='$id'";

        return $q;
    }

    public function returnEditAccountPermQuery($id,$inherit)
    {
        // If we are inheriting, just set that
        if ($inherit != null) {
            $q = "update user_permissions set inherit_group_perms = 1  where user_id='$id'";
            return $q;
        }

        // Otherwise get permissions key list from default_perms array
        // Then compare user perms against $_REQUEST elements 

        $perm_array = array();

        while (list($key,$val) = each($default_perms)) {
            if (isset($_REQUEST[$key])) {
                $perm_array[$key] = 1;
            } else {
                $perm_array[$key] = 0;
            }
        }

        // Build set string
        $edit_string = " inherit_group_perms = 0, ";
        $counter = 0;
        while (list($key,$val) = each($perm_array)) {
            $edit_string .= " $key=".$this->db->Quote($val);
            $counter++;
            if ($counter < count($perm_array)) $edit_string .= ",";
        }
        $q = "update user_permissions set $edit_string where user_id='$id'";

        return $q;
    }

    public function returnCreateUserPermQuery($email)
    {
        // Get permissions key list from senior_perms array
        // Then compare user perms against $_REQUEST elements 

        $u_perms = $this->account['permissions'];
        $perm_array = array();

        while (list($key,$val) = each($this->senior_perms)) {
            if (isset($u_perms[$key]) && $u_perms[$key] == 1) {
                if (isset($_REQUEST[$key])) {
                    $perm_array[$key] = 1;
                } else {
                    $perm_array[$key] = 0;
                }
            }
        }

        // Now that the perm_array is built, let's build the query string
        if (($id = $this->returnUserID($email)) == null) return null;


        // Build colmns, values
        $col_string = "";
        $val_string = "";
        while (list($key,$val) = each($perm_array)) {
            $col_string .= ",$key";
            $val_string .= ",$val";
        }
        $q = "insert into user_permissions (user_id$col_string) values('$id'$val_string)" ;

        // INHERIT???

        return $q;
    }

    protected function getUserData($userID) 
    {
        if (is_null($userID)) {
            $session = & Framework_Session::singleton();
            $userID = $session->{Framework::$site->config->user->userField};
            if (is_null($userID)) {
                $userID = (string)Framework::$site->config->user->defaultUser;
            } else {
                $userID = $session->{(string)Framework::$site->config->user->userField};
            }
        }

        $result = $this->getAccountInfo($userID);
        if ($result == null) {
            throw new Framework_Exception("Could not look up " . (string)Framework::$site->config->user->userField);
        }
        $this->data = $result;
        $this->data['permissions'] = $this->getPerms();
        $this->groups = $this->getAllSubGroups($this->data['group_id']);
    }

    /**
     * getBit 
     * 
     * Get bit value
     * 
     * @param mixed $bitmap 
     * @param mixed $bit 
     * @access public
     * @return bool $bit
     */
    public function getBit($bitmap, $bit)
    {
        if (!isset($this->permFlagValues[$bit])) {
            throw new Framework_Exception("Error - unknown bit value specified: $bit");
        }
        $bitValue = $this->permFlagValues[$bit];
        return ($bitmap&$bitValue) ? true : false;
    }

    /**
     * setBit 
     * 
     * Set bit flag.
     * 
     * @param mixed $bitmap 
     * @param mixed $bit 
     * @param bool $value 
     * @access public
     * @return void
     * @throws Framework_Exception if $bit is unknown
     * @see getBit()
     */
    public function setBit(&$bitmap, $bit, $value)
    {
        if (!isset($this->permFlagValues[$bit])) {
            throw new Framework_Exception("Unknown GID Bit value specified. $bit");
        }
        if (!is_bool($value)) {
            throw new Framework_Exception('Non-boolean value specified: ' . var_dump($value));
        }
        $bitValue = $this->permFlagValues[$bit];
        $value = ($value == true) ? $bitValue : 0;
        $bitmap = (int)$value|(~(int)$bitValue&(int)$bitmap);
    }

    public function getPerms($account = null)
    {
        if (is_null($account)) {
            $account = $this->data;
        }
        if ($this->data['account_type'] == 'senior_admin') {
            return $this->seniorPerms;
        }
        if ($account['user_perms'] == null || 
            $this->getBit($this->data['user_perms'], 'inherit_group_permissions')) {
            if($account['group_perms'] == null) {
                return $this->defaultPerms;
            }
            return $account['group_perms'];
        }
        return $account['user_perms'];
    }

    public function dnsLog($domain_id, $entry)
    {
        $session = & Framework_Session::singleton();
        $name = $this->data['first_name']." ".$this->data['last_name'];
        $q = "INSERT INTO log (user_id,group_id,email,Name,domain_id,entry,time) 
        values(
            " . $this->data['user_id'] . ",
            " . $session->group_id.",
            " . $this->db->Quote($this->data['email']) . ",
            " . $this->db->Quote($name) . ",
            " . $this->db->Quote($domain_id) . ",
            " . $this->db->Quote($entry) . ",
            " . time().")";

        try {
            $result = $this->db->Execute($q);
        } catch (Exception $e) {;
            throw new Framework_Exception($e->getMessage());
        }
    
    }
};
?>
