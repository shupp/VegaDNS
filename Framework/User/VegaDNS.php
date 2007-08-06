<?php


/**
 * Framework_User_VegaDNS 
 * 
 * @package VegaDNS
 * @copyright 2007 Bill Shupp
 * @author Bill Shupp <hostmaster@shupp.org> 
 * @license GPL 2.0  {@link http://www.gnu.org/licenses/gpl.txt}
 */
class Framework_User_VegaDNS extends Framework_User {

    /**
     * groups 
     * 
     * An array of the logged in users groups
     * 
     * @var mixed
     * @access public
     */
    public $groups = null;
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
    private $seniorPerms = 134217726; // All but inherit_group_permissions
    /**
     * defaultPerms 
     * 
     * @var float
     * @access private
     */
    private $defaultPerms = 134217615; // All but account/group create/delete/edit

    public function authenticate($email, $password)
    {
        $sql = "SELECT user_id FROM `accounts` WHERE email=" . $this->db->Quote($email) . " AND password = MD5(" . $this->db->Quote($password) . ")";
        try {
            $result = $this->db->Execute($sql);
        } catch (Exception $e) {
            throw new Framework_Exception($e->getMessage());
        }
        if ($result->RecordCount() == 0) {
            return false;
        }
        $this->data = $result->FetchRow();
        $session = & Framework_Session::singleton();
        $session->__set((string)Framework::$site->config->user->userField, 
                $this->data[(string)Framework::$site->config->user->userField]);
        return true;
    }

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

    public function myGroupID()
    {
        return $this->data['group_id'];
    }

    public function myEmail()
    {
        return $this->data['email'];
    }

    // Get current account settings
    function getAccountInfo($userID) {

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
            return NULL;
        }
        return $result->FetchRow();
    }

    function getSubGroups($id) {
        $q = "SELECT group_id from GROUPS WHERE group_id != ".$this->db->Quote($id)." AND parent_group_id = ".$this->db->Quote($id);
        $result = $this->db->Execute($q) or die($this->db->ErrorMsg());
        if ($result->RecordCount() == 0) {
            return NULL;
        } else {
            $count = 0;
            while (!$result->EOF) {
                $row[$count] = $result->FetchRow();
                $count++;
            }
            return $row;
        }
    }

    function returnGroup($id, $g = NULL) {
        if ($g == NULL) {
            $g = $this->groups;
        }
        if ($g['group_id'] == $id) {
            return $g;
        }
        if (!isset($g['subgroups'])) {
            $array = NULL;
        } else {
            while (list($key,$val) = each($g['subgroups'])) {
                $temp = $this->returnGroup($id, $val);
                if ($temp['group_id'] == $id) {
                    $array = $temp;
                    break;
                } else {
                    $array = NULL;
                }
            }
        }
        return $array;
    }

    function getAllSubgroups($id) {
        // Get Top
        $q = "SELECT * FROM groups WHERE group_id=".$this->db->Quote($id)." LIMIT 1";
        $result = $this->db->Execute($q) or die($this->db->ErrorMsg());
        if ($result->RecordCount() == 0) {
            return NULL;
        } else {
            $top = $result->FetchRow();
        }
        // Get subgroups
        $subs = $this->getSubGroups($top['group_id']);
        if ($subs == NULL) {
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

    function isMyGroup($g, $array = NULL) {
        if (($temp = $this->returnGroup($g, $array)) == NULL) {
            return NULL;
        } else {
            return $temp;
        }
    }

    function isMyAccount($id) {

        // Fetch group_id
        if (($g = $this->userID_to_GroupID($id)) == NULL) {
            return FALSE;
        } else if (($temp = $this->returnGroup($g)) == NULL) {
            return FALSE;
        } else {
            return TRUE;
        }
    }

    function returnUserPermissions($id) {
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

    function returnGroupParentID($id) {
        $q = "select parent_group_id from groups where group_id=".$this->db->Quote($id);
        $result = $this->db->Execute($q) or die($this->db->ErrorMsg());
        if ($result->RecordCount() == 0) return NULL;
        $row = $result->FetchRow();
        return $row['parend_group_id'];
    }

    function returnGroupPermissions($id) {
        $q = "select * from group_permissions where group_id=".$this->db->Quote($id);
        $result = $this->db->Execute($q) or die($this->db->ErrorMsg());
        if ($result->RecordCount() == 0) return NULL;
        $perms = $result->FetchRow();
        if ($perms['inherit_group_perms'] == 1) {
            // Find the parent permissions
            $inherit = TRUE;
            while ($inherit != FALSE) {
                // Get parent ID
                $parent = $this->returnParentGroupID($id);
                $q = "select * from group_permissions where group_id=".$this->db->Quote($parent);
                $result = $this->db->Execute($q) or die($this->db->ErrorMsg());
                if ($result->RecordCount() == 0) return NULL;
                $perms = $result->FetchRow();
                if ($perms['inherit_group_perms'] == 1) {
                    $id = $parent;
                    continue;
                } else {
                    $inherit = FALSE;
                }
            }
        }
        return $perms;
    }

    function returnSubgroupsQuery($g,$string) {

        if ($string == NULL) {
            $string = " group_id='".$g['group_id']."'";
        } else {
            $string .= " or group_id='".$g['group_id']."'";
        }

        if (!isset($g['subgroups'])) {
            return $string;
        } else {
            $temp = " ";
            while (list($key,$val) = each($g['subgroups'])) {
                $temp .= $this->returnSubgroupsQuery($val, $temp);
            }
        }
        return $string.$temp;
    }

    function canCreateSubGroups() {
        if ($this->account['permissions']['group_create'] == 1) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    function canCreateDomains() {
        if ($this->account['permissions']['domain_create'] == 1) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    function canEditDomains() {
        if ($this->account['permissions']['domain_edit'] == 1) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    function canDeleteDomains() {
        if ($this->account['permissions']['domain_delete'] == 1) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    function canCreateRecord() {
        if ($this->account['permissions']['record_create'] == 1) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    function canDeleteRecord() {
        if ($this->account['permissions']['record_delete'] == 1) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    function canEditRecord() {
        if ($this->account['permissions']['record_edit'] == 1) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    function canDelegateRecord() {
        if ($this->account['permissions']['record_delegate'] == 1) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    function canCreateDefaultRecords() {
        if ($this->account['permissions']['default_record_create'] == 1) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    function canEditDefaultRecords() {
        if ($this->account['permissions']['default_record_edit'] == 1) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    function canDeleteDefaultRecords() {
        if ($this->account['permissions']['default_record_delete'] == 1) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    function canEditUser($id) {
        if ($this->account['permissions']['account_edit'] == 1) {
            if ($this->isMyAccount($id)) {
                return TRUE;
            } else {
                return FALSE;
            }
        } else {
            return FALSE;
        }
    }

    function canEditSelf() {
        if ($this->account['permissions']['self_edit'] == 1) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    function userID_to_GroupID($user_id) {
        $q = "select group_id from accounts where user_id=".$this->db->Quote($user_id);
        $result = $this->db->Execute($q) or die($this->db->ErrorMsg());
        if ($result->RecordCount() == 0) {
            return NULL;
        }

        $row = $result->FetchRow();
        return $row['group_id'];
    }

    function canEditSubGroups() {
        if ($this->account['permissions']['group_edit'] == 1) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    function canDeleteGroup($g) {
        if ($this->account['permissions']['group_delete'] == 1) {
            if ($g == NULL) {
                return TRUE;
            } else if ($this->isMyGroup($g)) {
                return TRUE;
            } else {
                return FALSE;
            }
        } else {
            return FALSE;
        }
    }

    function canEditGroup($g) {
        if ($this->account['permissions']['group_edit'] == 1) {
            if ($g == NULL) {
                return TRUE;
            } else if ($this->isMyGroup($g)) {
                return TRUE;
            } else {
                return FALSE;
            }
        } else {
            return FALSE;
        }
    }


    function canCreateUsers($id,$g) {

        // Senior Admins can do anything
        if ($this->account['account_type'] == 'senior_admin') return TRUE;

        // See if it's the logged in user
        if ($id == NULL) {
            if ($this->account['permissions']['account_create'] == 1) {
                return TRUE;
            } else {
                return FALSE;
            }
        }

        // Else look up the permissions

        $perms = $this->returnUserPermissions($id);
        if ($perms == "INHERIT") {
            // GET GROUP PERMS
            $perms = $this->returnGroupPermissions($g);
        } else if ($perms == NULL) {
            return FALSE;
        } else if ($perms['group_create'] == 1) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    function canDeleteUsers($id,$g) {

        // Senior Admins can do anything
        if ($this->account['account_type'] == 'senior_admin') return TRUE;

        // See if it's the logged in user
        if ($id == NULL) {
            if ($this->account['permissions']['account_delete'] == 1) {
                return TRUE;
            } else {
                return FALSE;
            }
        }

        // Else look up the permissions

        $perms = $this->returnUserPermissions($id);
        if ($perms == "INHERIT") {
            // GET GROUP PERMS
            $perms = $this->returnGroupPermissions($g);
        } else if ($perms == NULL) {
            return FALSE;
        } else if ($perms['group_create'] == 1) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    function canDeleteUser($id) {

        if ($this->canDeleteUsers(NULL,NULL) == FALSE) {
            return FALSE;
        } else {
            $q = "select group_id from accounts where user_id='$id'";
            $result = $this->db->Execute($q) or die($this->db->ErrorMsg());
            if ($result->RecordCount() == 0) return NULL;
            $row = $result->FetchRow();
            if ($this->isMyGroup($row['group_id']) != NULL) {
                return TRUE;
            } else {
                return FALSE;
            }
        }
    }

    function returnGroupID($name) {
        $q = "select group_id from groups where name=".$this->db->Quote($name);
        $result = $this->db->Execute($q) or die($this->db->ErrorMsg());
        if ($result->RecordCount() == 0) return NULL;
        $row = $result->FetchRow();
        return $row['group_id'];
    }

    function returnUserID($email) {
        $q = "select user_id from accounts where email=".$this->db->Quote($email);
        $result = $this->db->Execute($q) or die($this->db->ErrorMsg());
        if ($result->RecordCount() == 0) return NULL;
        $row = $result->FetchRow();
        return $row['user_id'];
    }


    function returnCreateGroupPermQuery($name) {
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
        if (($id = $this->returnGroupID($name)) == NULL) return NULL;


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

    function returnEditGroupPermQuery($id) {

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

    function returnEditAccountPermQuery($id,$inherit) {
        // If we are inheriting, just set that
        if ($inherit != NULL) {
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

    function returnCreateUserPermQuery($email) {
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
        if (($id = $this->returnUserID($email)) == NULL) return NULL;


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
        if ($result == NULL) {
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
        if ($account['user_perms'] == NULL || 
            $this->getBit($this->data['user_perms'], 'inherit_group_permissions')) {
            if($account['group_perms'] == NULL) {
                return $this->defaultPerms;
            }
            return $account['group_perms'];
        }
        return $account['user_perms'];
    }

};
?>
