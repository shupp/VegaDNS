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
     * @return mixed null on failure, returnGroup() result on success
     */
    public function isMyGroup($g, $array = null)
    {
        if (($temp = $this->returnGroup($g, $array)) == null) {
            return null;
        } else {
            return $temp;
        }
    }

    /**
     * isMyAccount 
     * 
     * Check if $id is owned by the logged in user
     * 
     * @param mixed $id 
     * 
     * @access public
     * @return bool true on success, false on failure
     */
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

    /**
     * returnGroupParentID 
     * 
     * Get parent ID of group
     * 
     * @param int $id 
     * 
     * @access public
     * @return mixed null on failure, int parent group id on success
     */
    public function returnGroupParentID($id)
    {
        $q = "select parent_group_id from groups where group_id=".$this->db->Quote($id);
        $result = $this->db->Execute($q) or die($this->db->ErrorMsg());
        if ($result->RecordCount() == 0) return null;
        $row = $result->FetchRow();
        return $row['parend_group_id'];
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
        $perms = VegaDNS_Permissions::singleton();
        // $this->data['permissions'] = $perms->getPerms();
        $this->groups = $this->getAllSubGroups($this->data['group_id']);
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
