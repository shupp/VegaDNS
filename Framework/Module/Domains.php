<?php


/**
 * Framework_Module_Domains 
 * 
 * @uses        VegaDNS_Auth_ACL
 * @package     VegaDNS
 * @subpackage  Module
 * @copyright   2007 Bill Shupp
 * @author      Bill Shupp <hostmaster@shupp.org> 
 * @license     GPL 2.0  {@link http://www.gnu.org/licenses/gpl.txt}
 */

/**
 * Framework_Module_Domains 
 * 
 * List domains
 * 
 * @uses        VegaDNS_Auth_ACL
 * @package     VegaDNS
 * @subpackage  Module
 * @copyright   2007 Bill Shupp
 * @author      Bill Shupp <hostmaster@shupp.org> 
 * @license     GPL 2.0  {@link http://www.gnu.org/licenses/gpl.txt}
 */
class Framework_Module_Domains extends VegaDNS_Auth_ACL
{

    /**
     * __default 
     * 
     * Call listDomains()
     * 
     * @access public
     * @return void
     */
    public function __default()
    {
        return $this->listDomains();
    }

    /**
     * listDomains 
     * 
     * List domains for a given group
     * 
     * @access public
     * @return void
     */
    public function listDomains()
    {
        // Setup some urls based on permissions
        if ($this->permissions->getBit($this->permissions->getPerms($this->user->data), 'domain_create')) {
            $this->setData('new_domain_url', './?module=Domains&amp;class=add');
        }

        if ($this->permissions->getBit($this->permissions->getPerms($this->user->data), 'domain_edit')) {
            $this->setData('edit_domain_url_base', "./?module=Domains&amp;event=edit");
        }

        // Get search string if it exists
        if (!empty($_REQUEST['search'])) {
            $this->setData('search', $_REQUEST['search']);
            $this->setData('searchtexttag', " matching \""  . $_REQUEST['search'] . "\"");
        }
    
        // Get scope of domain list, if it exists
        if (isset($_REQUEST['recursive'])) {
            $this->setData('recursive', ' checked');
        }
    
        // Get scope of domain list, if it exists
        if (!empty($_REQUEST['scope'])) {
            $this->setData('scope', $_REQUEST['scope']);
        }

        $groups = $this->user->returnGroup($this->group_id, NULL);
        // Count domains
        $countResult = $this->vdns->countDomains($groups);
        $countRow = $countResult->FetchRow();
        VegaDNS_Pager::paginate($this, $countRow['COUNT(*)']);

        $this->setData('sortway', VegaDNS_Sort::getRequestSortWay());
        $this->setData('sortfield', VegaDNS_Sort::getSortfield('domains'));
        $result = $this->vdns->getDomains(
                        $this->start,
                        $this->limit,
                        $groups,
                        NULL,
                        $this->sortfield, $this->sortfield);

        // sort
        $sort_array['Domain'] = 'domain';
        $sort_array['Status'] = 'status';
        $sort_array['Group'] = 'a.group_id';
        $this->setSortLinks($sort_array, 'Domains'); 
    
        if ($this->total == 0) {
            return;
        }
        // Actually list domains
        for ($domain_count = 0; !$result->EOF && ($row = $result->FetchRow()); $domain_count++) {
            $out_array[$domain_count]['domain'] = $row['domain'];
            $out_array[$domain_count]['edit_url'] = "./?&amp;module=Records&amp;domain_id=".$row['domain_id'];
            $out_array[$domain_count]['status'] = $row['status'];
            $out_array[$domain_count]['group_name'] = $row['name'];
            if ($this->permissions->getBit($this->permissions->getPerms($this->user->data), 'domain_delete')) {
                $out_array[$domain_count]['delete_url'] = "./?&amp;module=Domains&amp;class=delete&amp;domain_id={$row['domain_id']}";
            }
            if ($this->permissions->getBit($this->permissions->getPerms($this->user->data), 'domain_delegate')) {
                $out_array[$domain_count]['change_owner_url'] = "./?&amp;module=Domains&amp;event=delegate&amp;domain_id=".$row['domain_id']."&amp;domain=".$row['domain'];
            }
            if ($row['status'] == 'active') {
                if ($this->permissions->getBit($this->permissions->getPerms($this->user->data), 'domain_edit')) {
                    $out_array[$domain_count]['deactivate_url'] = "./?&amp;module=Domains&amp;event=deactivate&amp;domain_id=".$row['domain_id']."&amp;domain=".$row['domain'];
                }
            } else if ($row['status'] == 'inactive') {
                if ($this->permissions->isSeniorAdmin($this->user->data)) {
                    $out_array[$domain_count]['activate_url'] = "./?&amp;module=Domains&amp;event=activate&amp;domain_id=".$row['domain_id']."&amp;domain=".$row['domain'];
                }
            }
        }
    
        if (isset($out_array)) {
            $this->setData('out_array', $out_array);
        }
        $this->tplFile = 'Domains.tpl';
    }
}
?>
