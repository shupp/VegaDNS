<?php


/**
 * Framework_Module_Records_Delete 
 * 
 * @uses        Framework_Module_Records
 * @package     VegaDNS
 * @subpackage  Module
 * @copyright   2007 Bill Shupp
 * @author      Bill Shupp <hostmaster@shupp.org> 
 * @license     GPL 2.0  {@link http://www.gnu.org/licenses/gpl.txt}
 */

/**
 * Framework_Module_Records_Delete 
 * 
 * Delete Records
 * 
 * @uses        Framework_Module_Records
 * @package     VegaDNS
 * @subpackage  Module
 * @copyright   2007 Bill Shupp
 * @author      Bill Shupp <hostmaster@shupp.org> 
 * @license     GPL 2.0  {@link http://www.gnu.org/licenses/gpl.txt}
 */
class Framework_Module_Records_Delete extends Framework_Module_Records
{

    public function __construct()
    {
        parent::__construct();
        if (!$this->user->getBit($this->user->getPerms(), 'record_delete')) {
            $this->setData('message', 'Error: you do not have enough privileges to delete records.');
            return $this->listRecords();
        }
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
            return $this->listRecords();
        }
        $this->setData('delete_url', "./?module=Records&amp;class=delete&amp;event=deleteNow&amp;record_id={$this->domInfo['record_id']}");
        $this->setData('cancel_url', "./?module=Records&amp;class=delete&amp;event=cancel&amp;domain_id={$this->domInfo['domain_id']}");
        $this->tplFile = 'delete.tpl';
    }

    /**
     * cancel 
     * 
     * @access public
     * @return function listRecords()
     */
    public function cancel()
    {
        $this->setData('message', "Record deletion canceled");
        return $this->listRecords();
    }

    /**
     * deleteNow 
     * 
     * Actually delete the record
     * 
     * @access public
     * @return void
     */
    public function deleteNow()
    {
        // If 'message' is set, then we have a problem
        if (!is_null($this->message)) {
            return $this->listRecords();
        }
        $this->vdns->deleteRecord($this->domInfo['record_id']);
        $this->setData('message', "Record deleted successfully");
        return $this->listRecords();
    }
}
?>
