<?php


/**
 * Framework_Module_Domains_Delete 
 * 
 * @uses        Framework_Module_Domains
 * @package     VegaDNS
 * @subpackage  Module
 * @copyright   2007 Bill Shupp
 * @author      Bill Shupp <hostmaster@shupp.org> 
 * @license     GPL 2.0  {@link http://www.gnu.org/licenses/gpl.txt}
 */

/**
 * Framework_Module_Domains_Delete 
 * 
 * Delete Domains
 * 
 * @uses        Framework_Module_Domains
 * @package     VegaDNS
 * @subpackage  Module
 * @copyright   2007 Bill Shupp
 * @author      Bill Shupp <hostmaster@shupp.org> 
 * @license     GPL 2.0  {@link http://www.gnu.org/licenses/gpl.txt}
 */
class Framework_Module_Domains_Delete extends Framework_Module_Domains
{

    public function __construct()
    {
        parent::__construct();
        if (!$this->user->getBit($this->user->getPerms(), 'domain_delete')) {
            $this->setData('message', 'Error: you do not have enough privileges to delete domains.');
            return $this->listDomains();
        }
        $domInfo = $this->vdns->getDomainInfo($_REQUEST['domain_id']);
        if (is_null($domInfo)) {
            $this->setData('message', 'Error: domain does not exist');
            return $this->listDomains();
        }
        if (is_null($this->user->isMyGroup($domInfo['group_id']))) {
            $this->setData('message', "Error: domain does not belong to you");
            return $this->listDomains();
        }
        $this->setData('domInfo', $domInfo);
    }

    /**
     * __default 
     * 
     * run delete()
     * 
     * @access public
     * @return void
     */
    public function __default()
    {
        return $this->delete();
    }

    /**
     * delete 
     * 
     * Display delete form
     * 
     * @access public
     * @return void
     */
    public function delete()
    {
        // If 'message' is set, then we have a problem
        if (!is_null($this->message)) {
            return $this->listDomains();
        }
        $this->setData('domain', $this->domInfo['domain']);
        $this->setData('delete_url', "./?module=Domains&amp;class=delete&amp;event=deleteNow&amp;domain_id={$this->domInfo['domain_id']}");
        $this->setData('cancel_url', "./?module=Domains&amp;class=delete&amp;event=cancel");
        $this->tplFile = 'delete.tpl';
    }

    /**
     * cancel 
     * 
     * @access public
     * @return function listDomains()
     */
    public function cancel()
    {
        $this->setData('message', "Domain deletion canceled");
        return $this->listDomains();
    }

    /**
     * deleteNow 
     * 
     * Actually delete the domain
     * 
     * @access public
     * @return void
     */
    public function deleteNow()
    {
        // If 'message' is set, then we have a problem
        if (!is_null($this->message)) {
            return $this->listDomains();
        }
        $this->vdns->deleteDomain($this->domInfo['domain_id']);
        $this->setData('message', "Domain deleted successfully");
        return $this->listDomains();
    }
}
?>
