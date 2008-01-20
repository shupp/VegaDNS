<?php

/**
 * Framework_Module_Records 
 * 
 * @uses        Vegadns_Common
 * @package     VegaDNS
 * @copyright   2007 Bill Shupp
 * @author      Bill Shupp <hostmaster@shupp.org> 
 * @license     GPL 2.0  {@link http://www.gnu.org/licenses/gpl.txt}
 */


/**
 * Framework_Module_Records 
 * 
 * Base records module.
 * 
 * @uses        Vegadns_Common
 * @package     VegaDNS
 * @copyright   2007 Bill Shupp
 * @author      Bill Shupp <hostmaster@shupp.org> 
 * @license     GPL 2.0  {@link http://www.gnu.org/licenses/gpl.txt}
 */
class Framework_Module_Records extends Vegadns_Common
{

    /**
     * __construct 
     * 
     * Do initial security checks, throw exception if necessary
     * 
     * @access public
     * @return void
     * @throws Framework_Exception
     */
    public function __construct()
    {
        parent::__construct();
        if (empty($_REQUEST['domain_id'])) {
            throw new Framework_Exception("Error: no domain_id supplied");
        }
        $domInfo = $this->vdns->getDomainInfo($_REQUEST['domain_id']);
        if (is_null($domInfo)) {
            throw new Framework_Exception("Error: domain does not exist");
        }
        if (is_null($this->user->isMyGroup($domInfo['group_id']))) {
            throw new Framework_Exception("Error: domain does not belong to you");
        }
        $this->setData('domInfo', $domInfo);
    }
    
    /**
     * __default 
     * 
     * @access public
     * @return function listRecords()
     */
    public function __default()
    { 
        return $this->listRecords();
    }
    
    /**
     * listRecords 
     * 
     * List records for a domain
     * 
     * @access public
     * @return void
     */
    public function listRecords()
    {
        // Let's store some permission lookups
        $can_create = $this->user->getBit($this->user->getPerms(), 'record_create');
        $can_edit   = $this->user->getBit($this->user->getPerms(), 'record_edit');
        $can_delete = $this->user->getBit($this->user->getPerms(), 'record_delete');

        // sort
        $this->setData('sortway', $this->getRequestSortway());
        $this->setData('sortfield',  $this->getSortfield('records'));
    
        $countResult = $this->vdns->countRecords($this->domInfo['domain_id']);
        $countRow = $countResult->FetchRow();
        VegaDNS_Pager::paginate($this, $countRow['COUNT(*)']);

        $result = $this->vdns->getRecords($this->start, $this->limit, $this->domInfo['domain_id'], NULL, $this->sortfield, $this->sortway);

        // sort
        $sort_array['Name']     = 'host';
        $sort_array['Type']     = 'type';
        $sort_array['Address']  = 'val';
        $sort_array['Distance'] = 'distance';
        $sort_array['Weight']   = 'weight';
        $sort_array['Port']     = 'port';
        $sort_array['TTL']      = 'ttl';
    
        $this->setSortLinks($sort_array, 'Records');
    
        // Build records data structure
        for ($counter = 0, $records = array(); !$result->EOF && ($row = $result->FetchRow()); $counter++) {
            if ($this->start < $this->limit && empty($searchstring)) {
                // Get SOA
                if (!isset($soa) && $row['type'] == 'S') {
                    $soa = $row; 
                    continue;
                }
            }
            $records[$counter]['record_id'] = $row['record_id'];
            $records[$counter]['host'] = $row['host'];
            $records[$counter]['type'] = $row['type'];
            $records[$counter]['val'] = $row['val'];
            $records[$counter]['distance'] = $row['distance'];
    	    $records[$counter]['weight'] = $row['weight'];
    	    $records[$counter]['port'] = $row['port'];	    
            $records[$counter]['ttl'] = $row['ttl'];
        }
    
        // Parse SOA
        if ($this->start < $this->limit && empty($searchstring)) {
            if (isset($soa)) {
                $soa_array = $this->parseSoa($soa);
            } else {
                $tldemail = "hostmaster." . $this->domInfo['domain'];
                while((list($num,$array) = each($records)) && !isset($tldhost)) {
                    if ($array['type'] == 'N') $soa_array['tldhost'] = $array['host']; 
                }
                $soa_array = $this->vdns->defaultSOA;
            }
            if ($can_edit) {
                $soa_array['edit_soa_url'] = "./?module=Records&amp;class=editSoa&amp;domain_id={$this->domInfo['domain_id']}";
            }
            $this->setData('soa', $soa_array);
        }
        if ($can_create) {
            $this->setData('add_record_url', "./?module=Records&amp;class=add&amp;domain_id={$this->domInfo['domain_id']}");
        }
        $this->setData('view_log_url', "./?module=Log&amp;domain_id=".$this->domInfo['domain_id']);
    
        $records_array = array();
        $counter = 0;
        foreach ($records as $key => $array) {
            $type = $this->vdns->types[$array['type']];
            if ($type != 'SOA') {
                $records_array[$counter]['host'] = $array['host'];
                $records_array[$counter]['type'] = $type;
                $records_array[$counter]['val'] = $array['val'];
                if ($type == 'MX' || $type == 'SRV') {
                    $records_array[$counter]['distance'] = $array['distance'];
                } else {
                    $records_array[$counter]['distance'] = 'n/a';
                }
    	    if ($type == 'SRV') {
                    $records_array[$counter]['weight'] = $array['weight'];
                } else {
                    $records_array[$counter]['weight'] = 'n/a';
                }
    	    if ($type == 'SRV') {
                    $records_array[$counter]['port'] = $array['port'];
                } else {
                    $records_array[$counter]['port'] = 'n/a';
                }
    
                $records_array[$counter]['ttl'] = $array['ttl'];
                if ($can_delete) {
                    $records_array[$counter]['delete_url'] = "./?module=Records&class=delete&record_id={$array['record_id']}&domain_id={$this->domInfo['domain_id']}";
                }
                if ($can_edit) {
                    $records_array[$counter]['edit_url'] = "./?module=Records&class=edit&record_id={$array['record_id']}&domain_id=$this->domInfo['domain']}";
                }
            }
            $counter++;
        }
        $this->setData('records_array', $records_array);
        $this->tplFile = 'Records.tpl';
        return;
    }
}
?>
