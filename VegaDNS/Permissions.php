<?php
/**
 * VegaDNS_Permissions 
 * 
 * PHP Version 5.1+
 * 
 * @category  DNS
 * @package   VegaDNS
 * @uses      Framework_Object_DB
 * @author    Bill Shupp <hostmaster@shupp.org> 
 * @copyright 2008 Bill Shupp
 * @license   GPL 2.0  {@link http://www.gnu.org/licenses/gpl.txt}
 * @link      http://vegadns.org
 */


/**
 * VegaDNS_Permissions 
 * 
 * Permissions related items
 * 
 * @category  DNS
 * @package   VegaDNS
 * @uses      Framework_Object_DB
 * @author    Bill Shupp <hostmaster@shupp.org> 
 * @copyright 2008 Bill Shupp
 * @license   GPL 2.0  {@link http://www.gnu.org/licenses/gpl.txt}
 * @link      http://vegadns.org
 */
class VegaDNS_Permissions extends Framework_Object_DB
{

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
     * @access protected
     */
    protected $seniorPerms = 134217726;
    /**
     * defaultPerms 
     * All but account/group create/delete/edit
     * 
     * @var float
     * @access protected
     */
    protected $defaultPerms = 134217615;

    /**
     * hasAccess 
     * 
     * Get current permissions and check
     * to see if $bit is granted
     * 
     * @param mixed $bit bit flag of requested permission
     * 
     * @access public
     * @return bool result of getBit()
     */
    public function hasAccess($bit)
    {
        return $this->getBit($this->getPerms(), (string)$bit);
    }

    /**
     * returnGroupPermissions 
     * 
     * Return Group Permissions
     * 
     * @param int $id id of group
     * 
     * @access public
     * @return int    permissions number
     */
    public function returnGroupPermissions($id)
    {
        $q      = 'SELECT * FROM group_permissions WHERE group_id=?';
        $stmt   = $this->db->Prepare($q);
        $result = $this->db->Execute($stmt, array($id));
        if ($result->RecordCount() == 0) {
            return null;
        }
        $perms = $result->FetchRow();
        if ($perms['inherit_group_perms'] == 0) {
            return $perms;
        }
        // Find the parent permissions
        $inherit = true;
        while ($inherit != false) {
            // Get parent ID
            $parent = $this->returnParentGroupID($id);
            $q      = 'SELECT * FROM `group_permissions`
                           WHERE group_id=?';
            $stmt   = $this->db->Prepare($q);
            $result = $this->db->Execute($stmt, array($parent));
            if ($result->RecordCount() == 0) return null;
            $perms = $result->FetchRow();
            if ($perms['inherit_group_perms'] == 1) {
                $id = $parent;
                continue;
            } else {
                $inherit = false;
            }
        }
        return $perms;
    }

    /**
     * getBit 
     * 
     * Get bit value
     * 
     * @param mixed $bitmap permissions number
     * @param mixed $bit    which bit to check
     * 
     * @access public
     * @return bool $bit
     */
    public function getBit($bitmap, $bit)
    {
        if (!isset($this->permFlagValues[$bit])) {
            $msg = "Error - unknown bit value specified: $bit";
            throw new Framework_Exception($msg);
        }
        $bitValue = $this->permFlagValues[$bit];
        return ($bitmap&$bitValue) ? true : false;
    }

    /**
     * setBit 
     * 
     * Set bit flag.
     * 
     * @param int    &$bitmap permissions number
     * @param string $bit     permission bit by name
     * @param bool   $value   on or off
     * 
     * @access public
     * @return void
     * @throws Framework_Exception if $bit is unknown
     * @see getBit()
     */
    public function setBit(&$bitmap, $bit, $value)
    {
        if (!isset($this->permFlagValues[$bit])) {
            $msg = "Unknown GID Bit value specified. $bit";
            throw new Framework_Exception($msg);
        }
        if (!is_bool($value)) {
            $msg = 'Non-boolean value specified: ' . var_dump($value);
            throw new Framework_Exception($msg);
        }
        $bitValue = $this->permFlagValues[$bit];
        $value    = ($value == true) ? $bitValue : 0;
        $bitmap   = (int)$value|(~(int)$bitValue&(int)$bitmap);
    }

    /**
     * getPerms 
     * 
     * return permissions of user
     * 
     * @param array $account user account array, defaults to null
     * 
     * @access public
     * @return int    permissions number
     */
    public function getPerms($account)
    {
        if ($account['account_type'] == 'senior_admin') {
            return $this->seniorPerms;
        }
        if ($account['user_perms'] == null ||
            $this->getBit($account['user_perms'],
                'inherit_group_permissions')) {
            if ($account['group_perms'] == null) {
                return $this->defaultPerms;
            }
            return $account['group_perms'];
        }
        return $account['user_perms'];
    }

    /**
     * isSeniorAdmin 
     * 
     * @param mixed $data user data
     * 
     * @access public
     * @return bool true on success, false on failure
     */
    public function isSeniorAdmin($data)
    {
        return ($data['account_type'] == 'senior_admin');
    }

    /**
     * singleton 
     * 
     * This will be used in several places, so let's just have one instance.
     * 
     * @static
     * @access public
     * @return object instance of VegaDNS_Permissions
     */
    static public function singleton()
    {
        static $instance = null;
        if (!($instance instanceof VegaDNS_Permissions)) {
            $instance = new VegaDNS_Permissions;
        }
        return $instance;
    }
}
?>
