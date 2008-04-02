<?php
/**
 * Framework_Module_Groups 
 * 
 * PHP Version 5.1.0+
 * 
 * @uses      VegaDNS_Auth_ACL
 * @category  DNS
 * @package   VegaDNS
 * @author    Bill Shupp <hostmaster@shupp.org> 
 * @copyright 2008 Bill Shupp
 * @license   GPL 2.0  {@link http://www.gnu.org/licenses/gpl.txt}
 * @link      http://vegadns.org/
 */

/**
 * Framework_Module_Groups 
 * 
 * VegaDNS Groups Class Module
 * 
 * @uses      VegaDNS_Auth_ACL
 * @category  DNS
 * @package   VegaDNS
 * @author    Bill Shupp <hostmaster@shupp.org> 
 * @copyright 2008 Bill Shupp
 * @license   GPL 2.0  {@link http://www.gnu.org/licenses/gpl.txt}
 * @link      http://vegadns.org/
 */
class Framework_Module_Groups extends VegaDNS_Auth_ACL
{
    /**
     * __default 
     * 
     * Run listGroups();
     * 
     * @return $this->listGroups()
     */
    public function __default()
    {
        return $this->listGroups();
    }

    /**
     * listGroups 
     * 
     * List current group info and the first dimension of sub-groups
     * 
     * @return void
     */
    public function listGroups()
    {
        $group = $this->user->groups->fetchGroup($this->session->group_id);
        $this->setData('curGroup', $group);
        $this->setData('subGroups', $group->subGroups);
    }

}
?>
