<?php


class Framework_Module_Records extends Vegadns_Common
{


    public function __construct()
    {
        parent::__construct();
        if (!isset($_REQUEST['domain_id'])) {
            throw new Framework_Exception("Error: no domain given");
        }
        $q = "SELECT * FROM domains WHERE domain_id=" . $this->db->Quote($_REQUEST['domain_id']);
        try {
            $result = $this->db->Execute($q);
        } catch (Exception $e) {
            throw new Framework_Exception($e->getMessage());
        }
        if ($result->RecordCount() == 0) {
            throw new Framework_Exception("Error: domain does not exist");
        }
        $row = $result->GetRows(1);
        $this->setData('domain', $row[0]);
        
        // Make sure this domain is in the current group (permissions)
        if ($this->domain['group_id'] != $this->session->group_id) {
            $message = "Error: this domain is not in the current group - you do not have privileges to access it";
            throw new Framework_Exception($message);
        }
    }
    
    public function __default()
    { 
        return $this->listRecords();
    }
    
    public function listRecords()
    {
        // Let's store some permission lookups
        $can_create = $this->user->getBit($this->user->getPerms(), 'record_create');
        $can_edit   = $this->user->getBit($this->user->getPerms(), 'record_edit');
        $can_delete = $this->user->getBit($this->user->getPerms(), 'record_delete');

        // Get search string if it exists
        if (isset($_REQUEST['search']) && $_REQUEST['search'] != "") {
            $searchstring = ereg_replace("[*]", "%", $_REQUEST['search']);
            $searchstring = ereg_replace("[ ]", "%", $searchstring);
            $searchstring = "host like ".$this->db->Quote('%'.$_REQUEST['search'].'%')." and type != 'S' and ";
            $this->setData('search', $_REQUEST['search']);
            $this->setData('searchtexttag', " matching \"".$_REQUEST['search']."\"");
            $search = $_REQUEST['search'];
        } else {
            $searchstring = "";
            $search = "";
        }
    
        // sort
        $this->setData('sortway', $this->getRequestSortway());
        $this->setData('sortfield',  $this->getSortfield('records'));
    
        $q = "SELECT COUNT(*) FROM records
                WHERE $searchstring domain_id = '". $this->domain['domain_id']."'";
        try {
            $result = $this->db->Execute($q);
        } catch (Exception $e) {
            throw new Framework_Exception($e->getMessage());
        }
        $count_row = $result->FetchRow();
        $this->paginate($count_row['COUNT(*)']);

        // Get records list
        $q = "SELECT * FROM records 
                WHERE $searchstring domain_id = '". $this->domain['domain_id']."' 
                ORDER BY {$this->sortfield} {$this->sortway}".(($this->sortfield == 'type') ? ", host" : "");

        try {
            $result = $this->db->SelectLimit($q, $this->limit, $this->start);
        } catch (Exception $e) {
            throw new Framework_Exception($e->getMessage());
        }

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
                $tldemail = "hostmaster.$domain";
                while((list($num,$array) = each($records)) && !isset($tldhost)) {
                    if ($array['type'] == 'N') $soa_array['tldhost'] = $array['host']; 
                }
                $soa_array['serial']    = 'default';
                $soa_array['refresh']   = 16384;
                $soa_array['retry']     = 2048;
                $soa_array['expire']    = 1048576;
                $soa_array['minimum']   = 2560;
                $soa_array['ttl']       = 86400;
            }
            if ($can_edit) {
                $soa_array['edit_soa_url'] = "./module=Records&amp;event=editSoa&amp;domain_id={$this->domain['domain_id']}";
            }
            $this->setData('soa', $soa_array);
        }
        if ($can_create) {
            $this->setData('add_record_url', "./module=Records&amp;event=add&amp;domain_id={$this->domain['domain_id']}");
        }
        $this->setData('view_log_url', "./?module=Log&amp;domain_id=".$this->domain['domain_id']);
    
        $records_array = array();
        for ($counter = 0; list($key,$array) = each($records); $counter++) {
            $type = get_type($array['type']);
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
                    $records_array[$counter]['delete_url'] = "$base_url&mode=records&record_mode=delete&record_id=".$array['record_id']."&domain=".urlencode($domain);
                }
                if ($can_edit) {
                    $records_array[$counter]['edit_url'] = "$base_url&mode=records&record_mode=edit_record&record_id=".$array['record_id']."&domain=".urlencode($domain);
                }
                $counter++;
            }
        }
        $this->setData('records_array', $records_array);
    }
    /*
    public function add()
    {
        if (isset($blah)) {
        
            // Check permissions
            if (!$this->user->canCreateRecord()) {
                set_msg_err("Error: You do not have privileges to create a resource record");
                $smarty->display('header.tpl');
                $smarty->display('footer.tpl');
                exit;
            }
        
            $smarty->display('header.tpl');
            require('src/add_record_form.php');
            $smarty->display('footer.tpl');
            exit;
        
        } else if ($_REQUEST['record_mode'] == 'add_record_now') {
        
            // Check permissions
            if (!$this->user->canCreateRecord()) {
                set_msg_err("Error: You do not have privileges to create a resource record");
                $smarty->display('header.tpl');
                $smarty->display('footer.tpl');
                exit;
            }
        
            // Add domain to 'name'
            if ($_REQUEST['type'] != 'PTR' && 
                !eregi("^.*\.($domain)\.*$", $_REQUEST['name']) && 
                !eregi("^($domain)\.*$", $_REQUEST['name'])) {
        
                if (strlen($_REQUEST['name']) > 0) {
                    $name = $_REQUEST['name'].".$domain";
                } else {
                    $name = $domain;
                }
            } else {
                $name = $_REQUEST['name'];
            }
        
            // verify record to be added
            $result = verify_record($name,$_REQUEST['type'],$_REQUEST['address'],$_REQUEST['distance'],$_REQUEST['weight'], $_REQUEST['port'], $_REQUEST['ttl']);
            if ($result != 'OK') {
                set_msg_err($result);
                $smarty->display('header.tpl');
                require('src/add_record_form.php');
                $smarty->display('footer.tpl');
                exit;
            } else {
        
        
        
                // add record to db
        
                if ($_REQUEST['type'] == 'A') {
                    $q = "insert into records 
                    (domain_id,host,type,val,ttl) values(
                    '".get_dom_id($domain)."',
                    '$name',
                    '".set_type($_REQUEST['type'])."',
                    ".$this->db->Quote($_REQUEST['address']).",
                    '".$_REQUEST['ttl']."')";
                } else if ($use_ipv6 == 'TRUE' && $_REQUEST['type'] == 'AAAA') {
        	    $ipv6 = new Net_IPv6;
        	    $address = uncompress_ipv6($_REQUEST['address']);
                    $q = "insert into records
                    (domain_id,host,type,val,ttl) values(
                    '".get_dom_id($domain)."',
                    '$name',
                    '".set_type($_REQUEST['type'])."',
                    ".$this->db->Quote($address).",
                    '".$_REQUEST['ttl']."')";
                } else if ($use_ipv6 == 'TRUE' && $_REQUEST['type'] == 'AAAA+PTR') {
                    $ipv6 = new Net_IPv6;
                    $address = uncompress_ipv6($_REQUEST['address']);
                    $q = "insert into records
                    (domain_id,host,type,val,ttl) values(
                    '".get_dom_id($domain)."',
                    '$name',
                    '".set_type($_REQUEST['type'])."',
                    ".$this->db->Quote($address).",
                    '".$_REQUEST['ttl']."')";
                } else if ($_REQUEST['type'] == 'MX') {
                    if (!ereg("\..+$", $_REQUEST['address'])) {
                        $mxaddress = $_REQUEST['address'].".".$domain;
                    } else {
                        $mxaddress = $_REQUEST['address'];
                    }
                    $q = "insert into records 
                    (domain_id,host,type,val,distance,ttl) values(
                    '".get_dom_id($domain)."',
                    '$name',
                    '".set_type($_REQUEST['type'])."',
                    ".$this->db->Quote($mxaddress).",
                    ".$this->db->Quote($_REQUEST['distance']).",
                    '".$_REQUEST['ttl']."')";
                } if ($_REQUEST['type'] == 'NS') {
                    $q = "insert into records 
                    (domain_id,host,type,val,ttl) values(
                    '".get_dom_id($domain)."',
                    '$name',
                    '".set_type($_REQUEST['type'])."',
                    ".$this->db->Quote($_REQUEST['address']).",
                    '".$_REQUEST['ttl']."')";
                } if ($_REQUEST['type'] == 'PTR') {
                    $q = "insert into records 
                    (domain_id,host,type,val,ttl) values(
                    '".get_dom_id($domain)."',
                    '$name',
                    '".set_type($_REQUEST['type'])."',
                    ".$this->db->Quote($_REQUEST['address']).",
                    '".$_REQUEST['ttl']."')";
                } if ($_REQUEST['type'] == 'TXT') {
                    $q = "insert into records 
                    (domain_id,host,type,val,ttl) values(
                    '".get_dom_id($domain)."',
                    '$name',
                    '".set_type($_REQUEST['type'])."',
                    ".$this->db->Quote($_REQUEST['address']).",
                    '".$_REQUEST['ttl']."')";
                } if ($_REQUEST['type'] == 'CNAME') {
                    $q = "insert into records 
                    (domain_id,host,type,val,ttl) values(
                    '".get_dom_id($domain)."',
                    '$name',
                    '".set_type($_REQUEST['type'])."',
                    ".$this->db->Quote($_REQUEST['address']).",
                    '".$_REQUEST['ttl']."')";
                } if ($_REQUEST['type'] == 'SRV') {
        	    if (!ereg("\..+$", $_REQUEST['address'])) {
                        $srvaddress = $_REQUEST['address'].".".$domain;
                    } else {
                        $srvaddress = $_REQUEST['address'];
                    }
        	    $q = "insert into records
        	    (domain_id,host,type,val,distance,weight,port,ttl) values(
        	    '".get_dom_id($domain)."',
                    '$name',
                    '".set_type($_REQUEST['type'])."',
                    ".$this->db->Quote($srvaddress).",
                    ".$this->db->Quote($_REQUEST['distance']).",
        	        ".$this->db->Quote($_REQUEST['weight']).",
        	        ".$this->db->Quote($_REQUEST['port']).",
                    '".$_REQUEST['ttl']."')";
         
                }
                $this->db->Execute($q) or die($this->db->ErrorMsg());
                set_msg("Record added successfully!");
                header("Location: $base_url&mode=records&domain=".urlencode($domain));
                exit;
            }
        
        } else if ($_REQUEST['record_mode'] == 'delete') {
        
            // Check permissions
            if (!$this->user->canDeleteRecord()) {
                set_msg_err("Error: You do not have privileges to delete a resource record");
                $smarty->display('header.tpl');
                $smarty->display('footer.tpl');
                exit;
            }
        
            // Get record info
            $q = "select * from records where record_id='".$_REQUEST['record_id']."' limit 1";
            $result = $this->db->Execute($q) or die($this->db->ErrorMsg());
            $row = $result->FetchRow();
        
            $this->setData('type', get_type($row['type']));
            $this->setData('host', $row['host']);
            $this->setData('cancel_url', "$base_url&mode=records&domain=".urlencode($domain)."&record_mode=delete_cancelled");
            $this->setData('delete_url', "$base_url&mode=records&record_mode=delete_now&record_id=".$row['record_id']."&domain=".urlencode($domain));
            $smarty->display('header.tpl');
            $smarty->display('delete_record_confirm.tpl');
            $smarty->display('footer.tpl');
            exit;
        
        } else if ($_REQUEST['record_mode'] == 'delete_now') {
        
            // Check permissions
            if (!$this->user->canDeleteRecord()) {
                set_msg_err("Error: You do not have privileges to delete a resource record");
                $smarty->display('header.tpl');
                $smarty->display('footer.tpl');
                exit;
            }
        
            // make sure the record_id was given
            if (!isset($_REQUEST['record_id'])) {
                set_msg_err("Error: no record_id");
                $smarty->display('header.tpl');
                $smarty->display('footer.tpl');
                exit;
            }
        
            $q = "delete from records where record_id='".$_REQUEST['record_id']."'";
            $this->db->Execute($q) or die($this->db->ErrorMsg());
            set_msg("Record deleted successfully");
            header("Location: $base_url&mode=records&domain=".urlencode($domain));
            exit;
        
        } if ($_REQUEST['record_mode'] == 'edit_soa') {
        
        
            // Permissions check
            if (!$this->user->canEditRecord()) {
                set_err_msg("Error: you do not have enough privileges to edit a resource record");
                $smarty->display('header.tpl');
                $smarty->display('footer.tpl');
                exit;
            }
        
            // Get Current SOA information
        
            $q = "select * from records where type='S' and domain_id='".
                get_dom_id($domain)."' limit 1";
            $result = $this->db->Execute($q) or die($this->db->ErrorMsg());
            $row = $result->FetchRow();
        
            $soa = parse_soa($row);
        
            // Edit SOA Menu
            $smarty->display('header.tpl');
            require('src/edit_soa_form.php');
            $smarty->display('footer.tpl');
            exit;
        
        } if ($_REQUEST['record_mode'] == 'edit_soa_now') {
        
            // Permissions check
            if (!$this->user->canEditRecord()) {
                set_err_msg("Error: you do not have enough privileges to edit a resource record");
                $smarty->display('header.tpl');
                $smarty->display('footer.tpl');
                exit;
            }
        
            if (!eregi("^[^.].*\..*", ereg_replace("\.$", "", $_REQUEST['primary_name_server']))) {
                set_msg_err("Error: primary name server ".$_REQUEST['primary_name_server']." does not appear to be a valid second level or or more domain");
                $smarty->display('header.tpl');
                require('src/edit_soa_form.php');
                $smarty->display('footer.tpl');
                exit;
            }
        
            // Check email
            if ($_REQUEST['contactaddr'] == "") {
                set_msg_err("Error: missing email address");
                $smarty->display('header.tpl');
                require('src/edit_soa_form.php');
                $smarty->display('footer.tpl');
                exit;
            }
            
            // Build array
            $array['host'] = $_REQUEST['contactaddr'].':'.$_REQUEST['primary_name_server'];
            $array['val'] = $_REQUEST['refresh'].':'.$_REQUEST['retry'].':'.$_REQUEST['expire'].':'.$_REQUEST['minimum'];
        
            $return = parse_soa($array);
        
            // Update table
            $host = $return['tldemail'].':'.$return['tldhost'];
            $val = $return['refresh'].':'.$return['retry'].':'.$return['expire'].':'.$return['minimum'];
            $q = "update records set host='$host',
                val='$val',
                ttl='".$_REQUEST['ttl']."'  where type='S' and 
                domain_id='".get_dom_id($domain)."'";
            $this->db->Execute($q) or die($this->db->ErrorMsg());
        
            // Display domain
            set_msg("SOA record updated successfully");
            header("Location: $base_url&mode=records&domain=".urlencode($domain));
            exit;
            
        } else if ($_REQUEST['record_mode'] == 'view_log') {
        
            $smarty->display('header.tpl');
            require('src/view_log.php');
            $smarty->display('footer.tpl');
            exit;
        
        } if ($_REQUEST['record_mode'] == 'edit_record') {
        
            // Permissions check
            if (!$this->user->canEditRecord()) {
                set_err_msg("Error: you do not have enough privileges to edit a resource record");
                $smarty->display('header.tpl');
                $smarty->display('footer.tpl');
                exit;
            }
        
            // Make sure record_id was given
            if ($_REQUEST['record_id'] == "") {
                set_msg_err("Error: missing record_id");
                $smarty->display('header.tpl');
                require('src/list_records.php');
                $smarty->display('footer.tpl');
                exit;
            }
        
            // Get Current RR information
        
            $q = "select * from records where record_id='".$_REQUEST['record_id']."' and domain_id='".
                get_dom_id($domain)."' and type!='S' limit 1";
            $result = $this->db->Execute($q) or die($this->db->ErrorMsg());
            $row = $result->FetchRow();
        
        
            // Set values for template
            $this->setData('record_id', $_REQUEST['record_id']);
            $this->setData('name', $row['host']);
            $this->setData('address', $row['val']);
            $this->setData('type', get_type($row['type']));
            $this->setData('distance', $row['distance']);
            $this->setData('weight', $row['weight']);
            $this->setData('port', $row['port']);
            $this->setData('ttl', $row['ttl']);
        
            // Edit Record Menu
            $smarty->display('header.tpl');
            $smarty->display('edit_record.tpl');
            $smarty->display('footer.tpl');
            exit;
        
        } else if ($_REQUEST['record_mode'] == 'edit_record_now') {
        
            // Permissions check
            if (!$this->user->canEditRecord()) {
                set_err_msg("Error: you do not have enough privileges to edit a resource record");
                $smarty->display('header.tpl');
                $smarty->display('footer.tpl');
                exit;
            }
        
            // Add domain to 'name'
            if ($_REQUEST['type'] != 'PTR' &&
                !eregi("^.*\.($domain)\.*$", $_REQUEST['name']) && 
                !eregi("^($domain)\.*$", $_REQUEST['name'])) {
        
                if (strlen($_REQUEST['name']) > 0) {
                    $name = $_REQUEST['name'].".$domain";
                } else {
                    $name = $domain;
                }
            } else {
                $name = $_REQUEST['name'];
            }
        
            // verify record to be added
            $result = verify_record($name,$_REQUEST['type'],$_REQUEST['address'],$_REQUEST['distance'], $_REQUEST['weight'], $_REQUEST['port'], $_REQUEST['ttl']);
            if ($result != 'OK') {
        
                // Set values
                $q = "select * from records where 
                    record_id='".$_REQUEST['record_id']."' and domain_id='".        
                    get_dom_id($domain)."' and type!='S' limit 1";    
                $result = $this->db->Execute($q) or die($this->db->ErrorMsg());
                $row = $result->FetchRow();
        
                $this->setData('record_id', $_REQUEST['record_id']);
                $this->setData('name', $row['host']);
                $this->setData('address', $row['val']);
                $this->setData('type', get_type($row['type']));
                $this->setData('distance', $row['distance']);
        	    $this->setData('weight', $row['weight']);
            	$this->setData('port', $row['port']);
                $this->setData('ttl', $row['ttl']);
                set_msg_err($result);
                $smarty->display('header.tpl');
                $smarty->display('edit_record.tpl');
                $smarty->display('footer.tpl');
                exit;
            } else {
        
                // Update record
        
        	if ($use_ipv6 == 'TRUE' && ($_REQUEST['type']=='AAAA' || $_REQUEST['type']=='AAAA+PTR')) {
        		$address = uncompress_ipv6($_REQUEST['address']);
        	} else {
        		$address = $_REQUEST['address'];
        	}
        
                $q = "update records set ".
                    "host='$name',".
                    "val='".$address."',".
                    "distance='".$_REQUEST['distance']."',".
        	    "weight='".$_REQUEST['weight']."',".
        	    "port='".$_REQUEST['port']."',".
                    "ttl='".$_REQUEST['ttl']."' ".
                    "where record_id='".$_REQUEST['record_id']."' and domain_id='".
                        get_dom_id($domain)."'";
        
                $this->db->Execute($q) or die($this->db->ErrorMsg());
                set_msg("Record updated successfully!");
                header("Location: $base_url&mode=records&domain=".urlencode($domain));
                exit;
            }
        
        }
    }*/

}

?>
