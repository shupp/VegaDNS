<?php

/**
 * Framework_Module_Domains_Delete 
 * 
 * PHP Version 5
 * 
 * @category   DNS
 * @package    VegaDNS
 * @subpackage Module
 * @uses       Framework_Module_Domains
 * @author     "Bill Shupp" <hostmaster@shupp.org> 
 * @copyright  2007 Bill Shupp
 * @license    GPL 2.0  {@link http://www.gnu.org/licenses/gpl.txt}
 * @link       http://www.vegadns.org
 */

/**
 * Framework_Module_Domains_Delete 
 * 
 * Delete Domains
 * 
 * @category   DNS
 * @package    VegaDNS
 * @subpackage Module
 * @uses       Framework_Module_Domains
 * @author     "Bill Shupp" <hostmaster@shupp.org> 
 * @copyright  2007 Bill Shupp
 * @license    GPL 2.0  {@link http://www.gnu.org/licenses/gpl.txt}
 * @link       http://www.vegadns.org
 */
class Framework_Module_Domains_Delete extends Framework_Module_Domains
{

    /**
     * init 
     * 
     * Centralize some security and setup tasks
     * 
     * @access protected
     * @return void
     */
    protected function init()
    {
        $domInfo = $this->vdns->getDomainInfo($_REQUEST['domain_id']);
        if (is_null($domInfo)) {
            $this->setData('message', 'Error: domain does not exist');
        }
        if (is_null($this->user->isMyGroup($domInfo['group_id']))) {
            $this->setData('message', "Error: domain does not belong to you");
        }
        $this->setData('domInfo', $domInfo);
    }

    /**
     * __default 
     * 
     * Display delete form
     * 
     * @access public
     * @return void
     */
    public function __default()
    {
        $this->init();
        // If 'message' is set, then we have a problem
        if (!is_null($this->message)) {
            return $this->listDomains();
        }
        $this->setData('domain', $this->domInfo['domain']);
        $dUrl = 'module=Domains&amp;class=delete&amp;event=deleteNow&amp;'
            . 'domain_id=' . $this->domInfo['domain_id'];
        $cUrl = 'module=Domains&class=delete&event=cancel';
        $this->setData('delete_url', './?' . $dUrl);
        $this->setData('cancel_url', './?' . $cUrl);
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
        $this->init();
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
