<?php
/**
 * VegaDNS_Group 
 * 
 * PHP Version 5
 * 
 * @category  DNS
 * @package   VegaDNS
 * @uses      Framework_Object_DB
 * @author    Bill Shupp <hostmaster@shupp.org> 
 * @copyright 2008 Bill Shupp
 * @license   GPL 2.0  {@link http://www.gnu.org/licenses/gpl.txt}
 * @link      http:/vegadns.org
 */


/**
 * VegaDNS_Group 
 * 
 * Object for accessing VegaDNS Groups
 * 
 * @category  DNS
 * @package   VegaDNS
 * @uses      Framework_Object_DB
 * @author    Bill Shupp <hostmaster@shupp.org> 
 * @copyright 2008 Bill Shupp
 * @license   GPL 2.0  {@link http://www.gnu.org/licenses/gpl.txt}
 * @link      http:/vegadns.org
 */
class VegaDNS_Group extends Framework_Object_DB
{
    /**
     * id 
     * 
     * @var mixed groups.group_id defaults to null
     * @access protected
     */
    protected $id = null;
    /**
     * parentID 
     * 
     * @var mixed groups.parent_group_id defaults to null
     * @access protected
     */
    protected $parentID = null;
    /**
     * name 
     * 
     * @var mixed groups.name defaults to null
     * @access protected
     */
    protected $name = null;
    /**
     * permValue 
     * 
     * @var mixed group_permissions.perm_value defaults to null
     * @access protected
     */
    protected $permValue = null;
    /**
     * subGroups 
     * 
     * @var mixed array of subgroups, defaults to null
     * @access protected
     */
    protected $subGroups = null;

    /**
     * factory 
     * 
     * Factory for creating groups objects
     * 
     * @param mixed $id group id
     * 
     * @static
     * @access public
     * @throws Framework_Exception on invalid group id
     * @return VegaDNS_Group object
     */
    static public function factory($id)
    {
        $group = new VegaDNS_Group;
        if (!$group->getGroup($id)) {
            throw new Framework_Exception('Invalid group');
        }
        return $group;
    }

    /**
     * __get 
     * 
     * Getter.  Could probably be more secure.
     * 
     * @param mixed $val object property name to get
     * 
     * @access public
     * @return mixed  value of $val on success, null if $val is not set
     */
    public function __get($val)
    {
        if (!isset($this->$val)) {
            return null;
        }
        return $this->$val;
    }

    /**
     * getGroup 
     * 
     * Look up group data and set the object properties.
     * 
     * @param mixed $id group id
     * 
     * @access protected
     * @return bool true on success, false on failure
     */
    protected function getGroup($id)
    {
        $q      = 'SELECT g.group_id,g.parent_group_id,g.name,p.perm_value
                    FROM `groups` AS g
                    LEFT JOIN group_permissions AS p ON g.group_id = p.group_id
                    WHERE g.group_id = ?';
        $stmt   = $this->db->Prepare($q);
        $result = $this->db->Execute($stmt, array($id));
        if ($result->RecordCount() == 0) {
            return false;
        }
        $row             = $result->FetchRow();
        $this->id        = $row['group_id'];
        $this->parentID  = $row['parent_group_id'];
        $this->name      = $row['name'];
        $this->permValue = $row['perm_value'];
        $this->subGroups = $this->getSubGroups($this->id);
        return true;
    }

    /**
     * getSubGroups 
     * 
     * Lookup groups whose parent is this id and return an array of 
     * VegaDNS_Group objects for each.  Works recursively.
     * 
     * @param mixed $id group id
     * 
     * @access protected
     * @return mixed null on failure, array of VegaDNS_Objects on success
     */
    protected function getSubGroups($id)
    {
        $q      = 'SELECT group_id from `groups` 
                    WHERE group_id != ?
                    AND parent_group_id = ?';
        $stmt   = $this->db->Prepare($q);
        $result = $this->db->Execute($stmt, array($id, $id));
        if ($result->RecordCount() == 0) {
            return null;
        }
        $array = array();
        while ($row = $result->FetchRow()) {
            $array[] = VegaDNS_Group::factory($row['group_id']);
        }
        return $array;
    }

    /**
     * fetchGroup 
     * 
     * Fetch a group from $this->subGroups
     * 
     * @param int $id id of group
     * 
     * @access public
     * @return Group object on success, null on failure
     */
    public function fetchGroup($id)
    {
        if ($id == $this->id) {
            return $this;
        }
        if (count($this->subGroups)) {
            foreach ($this->subGroups as $val) {
                if ($id == $val->id) {
                    return $val;
                }
                $result = $val->fetchGroup($id);
                if ($result != null) {
                    return $result;
                }
            }
        }
        return null;
    }
}
?>
