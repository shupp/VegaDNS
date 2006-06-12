<?php


/*
 * 
 * VegaDNS - DNS Administration Tool for use with djbdns
 * 
 * CREDITS:
 * Written by Bill Shupp
 * <bill@merchbox.com>
 * 
 * LICENSE:
 * This software is distributed under the GNU General Public License
 * Copyright 2003-2006, MerchBox.Com
 * see COPYING for details
 * 
 */ 

if(!ereg(".*/index.php$", $_SERVER['PHP_SELF'])) {
    header("Location:../index.php");
    exit;
}





// Make sure the domain was given
if(!isset($_REQUEST['domain'])) {
    set_msg_err("Error: no domain given");
    $smarty->display('header.tpl');
    $smarty->display('footer.tpl');
    exit;
}

$domain = $_REQUEST['domain'];
$smarty->assign('domain', $domain);

// Get domain information
$q = "select * from domains where domain='$domain'";
$result = $db->Execute($q) or die($db->ErrorMsg());
if($result->RecordCount() == 0) {
    set_msg_err("Error: domain $domain does not exist");
    $smarty->display('header.tpl');
    $smarty->display('footer.tpl');
    exit;
}

$dom_row = $result->FetchRow();

// Make sure this user has permission to view/edit this domain

if($user_info['account_type'] != 'senior_admin') {
    if($my->isMyGroup($dom_row['group_id']) == NULL) {
        set_msg_err("Error: you do not have permission to view resource records for domain $domain");
        $smarty->display('header.tpl');
        $smarty->display('footer.tpl');
        exit;
    }
}

if(!isset($_REQUEST['record_mode']) || $_REQUEST['record_mode'] == 'delete_cancelled') {

    if(isset($_REQUEST['record_mode']) && $_REQUEST['record_mode'] == 'delete_cancelled') {
        set_msg('Delete Cancelled');
    }

    // Get search string if it exists
    if(isset($_REQUEST['search']) && $_REQUEST['search'] != "") {
        $searchstring = ereg_replace("[*]", "%", $_REQUEST['search']);
        $searchstring = ereg_replace("[ ]", "%", $searchstring);
        $searchstring = "host like ".$db->Quote('%'.$_REQUEST['search'].'%')." and type != 'S' and ";
        $smarty->assign('search', $_REQUEST['search']);
        $smarty->assign('searchtexttag', " matching \"".$_REQUEST['search']."\"");
        $search = $_REQUEST['search'];
    } else {
        $searchstring = "";
        $search = "";
    }

    // sort
    $sortway = get_request_sortway();
    $sortfield = get_sortfield('records');

    // Get records list
    $q = "select * from records where $searchstring domain_id = '".
        $dom_row['domain_id']."' order by $sortfield $sortway".(($sortfield=='type') ? ", host" : "")."";
    $result = $db->Execute($q) or die($db->ErrorMsg());
    $totalitems = $result->RecordCount();

    // See if the search failed to match
    if($totalitems == 0 && $searchstring != "") {
        set_msg_err("Error: no records matching \"".$_REQUEST['search']."\"");
        $smarty->display('header.tpl');
        $smarty->display('footer.tpl');
        exit;
    }
    // If no search, make sure there are records to display
    if($totalitems == 0 && $searchstring == "") {
        set_msg_err("Error: no resource records for domain $domain");
        $smarty->display('header.tpl');
        $smarty->display('footer.tpl');
        exit;
    }

    // Pagination
    require_once('src/pagination.php');

    // Should we display the soa stuff?
    if(($page == 1 || $page == "all") && $searchstring == "") {
        $display_soa = 1;
    } else {
        $display_soa = 0;
    }

    // sort
    $sort_array['Name'] = 'host';
    $sort_array['Type'] = 'type';
    $sort_array['Address'] = 'val';
    $sort_array['Distance'] = 'distance';
    $sort_array['Weight'] = 'weight';
    $sort_array['Port'] = 'port';
    $sort_array['TTL'] = 'ttl';

    $sortbaseurl = "$base_url&mode=records&domain=$domain&page=".( ((isset($_REQUEST['page']) && $_REQUEST['page'] == 'all')) ? "all" : $page);

    while(list($key,$val) = each($sort_array)) {
        $newsortway = get_sortway($sortfield, $val, $sortway);
        $url = "<a href='$sortbaseurl&sortway=$newsortway&sortfield=$val'>$key</a>";
        if($sortfield == $val) $url .= "&nbsp;<img border=0 alt='$sortway' src=images/$sortway.png>";
        $smarty->assign($key, $url);
    }

    // Build records data structure
    $counter = 0;
    while (++$counter && !$result->EOF && $row = $result->FetchRow()) {
        // Get SOA
        if(!isset($soa) && $row['type'] == 'S') $soa = $row; 

        if($counter < $first_item) continue;
        if($counter <= $last_item) {
            $records[$counter]['record_id'] = $row['record_id'];
            $records[$counter]['host'] = $row['host'];
            $records[$counter]['type'] = $row['type'];
            $records[$counter]['val'] = $row['val'];
            $records[$counter]['distance'] = $row['distance'];
	        $records[$counter]['weight'] = $row['weight'];
	        $records[$counter]['port'] = $row['port'];	    
            $records[$counter]['ttl'] = $row['ttl'];
        }
    }


    if($display_soa) {
        // Parse SOA
        if(isset($soa)) {
            $soa_array = parse_soa($soa);
        } else {
            $tldemail = "hostmaster.$domain";
            while((list($num,$array) = each($records)) && !isset($tldhost)) {
                if($array['type'] == 'N') $soa_array['tldhost'] = $array['host']; 
            }
            $soa_array['serial'] = "default";
            $soa_array['refresh'] = 16384;
            $soa_array['retry'] = 2048;
            $soa_array['expire'] = 1048576;
            $soa_array['minimum'] = 2560;
            $soa_array['ttl'] = 86400;
        }
        $smarty->assign('tldemail', $soa_array['tldemail']);
        $smarty->assign('tldhost', $soa_array['tldhost']);
        if($my->canEditRecord())
            $smarty->assign('edit_soa_url', "$base_url&mode=records&record_mode=edit_soa&domain=$domain");
        $smarty->assign('refresh', $soa_array['refresh']);
        $smarty->assign('retry', $soa_array['retry']);
        $smarty->assign('expire', $soa_array['expire']);
        $smarty->assign('minimum', $soa_array['minimum']);
        $smarty->assign('ttl', $soa_array['ttl']);
    }

    $smarty->assign('display_soa', $display_soa);
    if($my->canCreateRecord()) {
        $smarty->assign('add_record_url', "$base_url&mode=records&record_mode=add_record&domain=$domain");
    }
    $smarty->assign('view_log_url', "$base_url&mode=log&domain_id=".$dom_row['domain_id']);

    $smarty->assign('all_url', "$base_url&mode=records&domain=$domain&page=all&sortfield=$sortfield&sortway=$sortway&search=".urlencode($search));
    $smarty->assign('first_item', $first_item);
    $smarty->assign('last_item', $last_item);
    $smarty->assign('totalitems', $totalitems);
    $smarty->assign('totalpages', $totalpages);
    $smarty->assign('page', $page);

    $counter = 0;
    while(list($key,$array) = each($records)) {
        $type = get_type($array['type']);
        if($type != 'SOA') {
            $out_array[$counter]['host'] = $array['host'];
            $out_array[$counter]['type'] = $type;
            $out_array[$counter]['val'] = $array['val'];
            if($type == 'MX' || $type == 'SRV') {
                $out_array[$counter]['distance'] = $array['distance'];
            } else {
                $out_array[$counter]['distance'] = 'n/a';
            }
	    if($type == 'SRV') {
                $out_array[$counter]['weight'] = $array['weight'];
            } else {
                $out_array[$counter]['weight'] = 'n/a';
            }
	    if($type == 'SRV') {
                $out_array[$counter]['port'] = $array['port'];
            } else {
                $out_array[$counter]['port'] = 'n/a';
            }

            $out_array[$counter]['ttl'] = $array['ttl'];
            if($my->canDeleteRecord()) {
                $out_array[$counter]['delete_url'] = "$base_url&mode=records&record_mode=delete&record_id=".$array['record_id']."&domain=".urlencode($domain);
            }
            if($my->canEditRecord()) {
                $out_array[$counter]['edit_url'] = "$base_url&mode=records&record_mode=edit_record&record_id=".$array['record_id']."&domain=".urlencode($domain);
            }
            $counter++;
        }
    }

    if(isset($out_array)) $smarty->assign('out_array', $out_array);
    $smarty->display('header.tpl');
    $smarty->display('list_records.tpl');
    $smarty->display('footer.tpl');
    exit;

} else if($_REQUEST['record_mode'] == 'add_record') {

    // Check permissions
    if(!$my->canCreateRecord()) {
        set_msg_err("Error: You do not have privileges to create a resource record");
        $smarty->display('header.tpl');
        $smarty->display('footer.tpl');
        exit;
    }

    $smarty->display('header.tpl');
    require('src/add_record_form.php');
    $smarty->display('footer.tpl');
    exit;

} else if($_REQUEST['record_mode'] == 'add_record_now') {

    // Check permissions
    if(!$my->canCreateRecord()) {
        set_msg_err("Error: You do not have privileges to create a resource record");
        $smarty->display('header.tpl');
        $smarty->display('footer.tpl');
        exit;
    }

    // Add domain to 'name'
    if($_REQUEST['type'] != 'PTR' && 
        !eregi("^.*\.($domain)\.*$", $_REQUEST['name']) && 
        !eregi("^($domain)\.*$", $_REQUEST['name'])) {

        if(strlen($_REQUEST['name']) > 0) {
            $name = $_REQUEST['name'].".$domain";
        } else {
            $name = $domain;
        }
    } else {
        $name = $_REQUEST['name'];
    }

    // verify record to be added
    $result = verify_record($name,$_REQUEST['type'],$_REQUEST['address'],$_REQUEST['distance'],$_REQUEST['weight'], $_REQUEST['port'], $_REQUEST['ttl']);
    if($result != 'OK') {
        set_msg_err($result);
        $smarty->display('header.tpl');
        require('src/add_record_form.php');
        $smarty->display('footer.tpl');
        exit;
    } else {



        // add record to db

        if($_REQUEST['type'] == 'A') {
            $q = "insert into records 
            (domain_id,host,type,val,ttl) values(
            '".get_dom_id($domain)."',
            '$name',
            '".set_type($_REQUEST['type'])."',
            ".$db->Quote($_REQUEST['address']).",
            '".$_REQUEST['ttl']."')";
        } else if($use_ipv6 == 'TRUE' && $_REQUEST['type'] == 'AAAA') {
	    $ipv6 = new Net_IPv6;
	    $address = uncompress_ipv6($_REQUEST['address']);
            $q = "insert into records
            (domain_id,host,type,val,ttl) values(
            '".get_dom_id($domain)."',
            '$name',
            '".set_type($_REQUEST['type'])."',
            ".$db->Quote($address).",
            '".$_REQUEST['ttl']."')";
        } else if($use_ipv6 == 'TRUE' && $_REQUEST['type'] == 'AAAA+PTR') {
            $ipv6 = new Net_IPv6;
            $address = uncompress_ipv6($_REQUEST['address']);
            $q = "insert into records
            (domain_id,host,type,val,ttl) values(
            '".get_dom_id($domain)."',
            '$name',
            '".set_type($_REQUEST['type'])."',
            ".$db->Quote($address).",
            '".$_REQUEST['ttl']."')";
        } else if($_REQUEST['type'] == 'MX') {
            if(!ereg("\..+$", $_REQUEST['address'])) {
                $mxaddress = $_REQUEST['address'].".".$domain;
            } else {
                $mxaddress = $_REQUEST['address'];
            }
            $q = "insert into records 
            (domain_id,host,type,val,distance,ttl) values(
            '".get_dom_id($domain)."',
            '$name',
            '".set_type($_REQUEST['type'])."',
            ".$db->Quote($mxaddress).",
            ".$db->Quote($_REQUEST['distance']).",
            '".$_REQUEST['ttl']."')";
        } if($_REQUEST['type'] == 'NS') {
            $q = "insert into records 
            (domain_id,host,type,val,ttl) values(
            '".get_dom_id($domain)."',
            '$name',
            '".set_type($_REQUEST['type'])."',
            ".$db->Quote($_REQUEST['address']).",
            '".$_REQUEST['ttl']."')";
        } if($_REQUEST['type'] == 'PTR') {
            $q = "insert into records 
            (domain_id,host,type,val,ttl) values(
            '".get_dom_id($domain)."',
            '$name',
            '".set_type($_REQUEST['type'])."',
            ".$db->Quote($_REQUEST['address']).",
            '".$_REQUEST['ttl']."')";
        } if($_REQUEST['type'] == 'TXT') {
            $q = "insert into records 
            (domain_id,host,type,val,ttl) values(
            '".get_dom_id($domain)."',
            '$name',
            '".set_type($_REQUEST['type'])."',
            ".$db->Quote($_REQUEST['address']).",
            '".$_REQUEST['ttl']."')";
        } if($_REQUEST['type'] == 'CNAME') {
            $q = "insert into records 
            (domain_id,host,type,val,ttl) values(
            '".get_dom_id($domain)."',
            '$name',
            '".set_type($_REQUEST['type'])."',
            ".$db->Quote($_REQUEST['address']).",
            '".$_REQUEST['ttl']."')";
        } if($_REQUEST['type'] == 'SRV') {
	    if(!ereg("\..+$", $_REQUEST['address'])) {
                $srvaddress = $_REQUEST['address'].".".$domain;
            } else {
                $srvaddress = $_REQUEST['address'];
            }
	    $q = "insert into records
	    (domain_id,host,type,val,distance,weight,port,ttl) values(
	    '".get_dom_id($domain)."',
            '$name',
            '".set_type($_REQUEST['type'])."',
            ".$db->Quote($srvaddress).",
            ".$db->Quote($_REQUEST['distance']).",
	        ".$db->Quote($_REQUEST['weight']).",
	        ".$db->Quote($_REQUEST['port']).",
            '".$_REQUEST['ttl']."')";
 
        }
        $db->Execute($q) or die($db->ErrorMsg());
        set_msg("Record added successfully!");
        header("Location: $base_url&mode=records&domain=".urlencode($domain));
        exit;
    }

} else if($_REQUEST['record_mode'] == 'delete') {

    // Check permissions
    if(!$my->canDeleteRecord()) {
        set_msg_err("Error: You do not have privileges to delete a resource record");
        $smarty->display('header.tpl');
        $smarty->display('footer.tpl');
        exit;
    }

    // Get record info
    $q = "select * from records where record_id='".$_REQUEST['record_id']."' limit 1";
    $result = $db->Execute($q) or die($db->ErrorMsg());
    $row = $result->FetchRow();

    $smarty->assign('type', get_type($row['type']));
    $smarty->assign('host', $row['host']);
    $smarty->assign('cancel_url', "$base_url&mode=records&domain=".urlencode($domain)."&record_mode=delete_cancelled");
    $smarty->assign('delete_url', "$base_url&mode=records&record_mode=delete_now&record_id=".$row['record_id']."&domain=".urlencode($domain));
    $smarty->display('header.tpl');
    $smarty->display('delete_record_confirm.tpl');
    $smarty->display('footer.tpl');
    exit;

} else if($_REQUEST['record_mode'] == 'delete_now') {

    // Check permissions
    if(!$my->canDeleteRecord()) {
        set_msg_err("Error: You do not have privileges to delete a resource record");
        $smarty->display('header.tpl');
        $smarty->display('footer.tpl');
        exit;
    }

    // make sure the record_id was given
    if(!isset($_REQUEST['record_id'])) {
        set_msg_err("Error: no record_id");
        $smarty->display('header.tpl');
        $smarty->display('footer.tpl');
        exit;
    }

    $q = "delete from records where record_id='".$_REQUEST['record_id']."'";
    $db->Execute($q) or die($db->ErrorMsg());
    set_msg("Record deleted successfully");
    header("Location: $base_url&mode=records&domain=".urlencode($domain));
    exit;

} if($_REQUEST['record_mode'] == 'edit_soa') {


    // Permissions check
    if(!$my->canEditRecord()) {
        set_err_msg("Error: you do not have enough privileges to edit a resource record");
        $smarty->display('header.tpl');
        $smarty->display('footer.tpl');
        exit;
    }

    // Get Current SOA information

    $q = "select * from records where type='S' and domain_id='".
        get_dom_id($domain)."' limit 1";
    $result = $db->Execute($q) or die($db->ErrorMsg());
    $row = $result->FetchRow();

    $soa = parse_soa($row);

    // Edit SOA Menu
    $smarty->display('header.tpl');
    require('src/edit_soa_form.php');
    $smarty->display('footer.tpl');
    exit;

} if($_REQUEST['record_mode'] == 'edit_soa_now') {

    // Permissions check
    if(!$my->canEditRecord()) {
        set_err_msg("Error: you do not have enough privileges to edit a resource record");
        $smarty->display('header.tpl');
        $smarty->display('footer.tpl');
        exit;
    }

    if(!eregi("^[^.].*\..*", ereg_replace("\.$", "", $_REQUEST['primary_name_server']))) {
        set_msg_err("Error: primary name server ".$_REQUEST['primary_name_server']." does not appear to be a valid second level or or more domain");
        $smarty->display('header.tpl');
        require('src/edit_soa_form.php');
        $smarty->display('footer.tpl');
        exit;
    }

    // Check email
    if($_REQUEST['contactaddr'] == "") {
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
    $db->Execute($q) or die($db->ErrorMsg());

    // Display domain
    set_msg("SOA record updated successfully");
    header("Location: $base_url&mode=records&domain=".urlencode($domain));
    exit;
    
} else if($_REQUEST['record_mode'] == 'view_log') {

    $smarty->display('header.tpl');
    require('src/view_log.php');
    $smarty->display('footer.tpl');
    exit;

} if($_REQUEST['record_mode'] == 'edit_record') {

    // Permissions check
    if(!$my->canEditRecord()) {
        set_err_msg("Error: you do not have enough privileges to edit a resource record");
        $smarty->display('header.tpl');
        $smarty->display('footer.tpl');
        exit;
    }

    // Make sure record_id was given
    if($_REQUEST['record_id'] == "") {
        set_msg_err("Error: missing record_id");
        $smarty->display('header.tpl');
        require('src/list_records.php');
        $smarty->display('footer.tpl');
        exit;
    }

    // Get Current RR information

    $q = "select * from records where record_id='".$_REQUEST['record_id']."' and domain_id='".
        get_dom_id($domain)."' and type!='S' limit 1";
    $result = $db->Execute($q) or die($db->ErrorMsg());
    $row = $result->FetchRow();


    // Set values for template
    $smarty->assign('record_id', $_REQUEST['record_id']);
    $smarty->assign('name', $row['host']);
    $smarty->assign('address', $row['val']);
    $smarty->assign('type', get_type($row['type']));
    $smarty->assign('distance', $row['distance']);
    $smarty->assign('weight', $row['weight']);
    $smarty->assign('port', $row['port']);
    $smarty->assign('ttl', $row['ttl']);

    // Edit Record Menu
    $smarty->display('header.tpl');
    $smarty->display('edit_record.tpl');
    $smarty->display('footer.tpl');
    exit;

} else if($_REQUEST['record_mode'] == 'edit_record_now') {

    // Permissions check
    if(!$my->canEditRecord()) {
        set_err_msg("Error: you do not have enough privileges to edit a resource record");
        $smarty->display('header.tpl');
        $smarty->display('footer.tpl');
        exit;
    }

    // Add domain to 'name'
    if($_REQUEST['type'] != 'PTR' &&
        !eregi("^.*\.($domain)\.*$", $_REQUEST['name']) && 
        !eregi("^($domain)\.*$", $_REQUEST['name'])) {

        if(strlen($_REQUEST['name']) > 0) {
            $name = $_REQUEST['name'].".$domain";
        } else {
            $name = $domain;
        }
    } else {
        $name = $_REQUEST['name'];
    }

    // verify record to be added
    $result = verify_record($name,$_REQUEST['type'],$_REQUEST['address'],$_REQUEST['distance'], $_REQUEST['weight'], $_REQUEST['port'], $_REQUEST['ttl']);
    if($result != 'OK') {

        // Set values
        $q = "select * from records where 
            record_id='".$_REQUEST['record_id']."' and domain_id='".        
            get_dom_id($domain)."' and type!='S' limit 1";    
        $result = $db->Execute($q) or die($db->ErrorMsg());
        $row = $result->FetchRow();

        $smarty->assign('record_id', $_REQUEST['record_id']);
        $smarty->assign('name', $row['host']);
        $smarty->assign('address', $row['val']);
        $smarty->assign('type', get_type($row['type']));
        $smarty->assign('distance', $row['distance']);
	    $smarty->assign('weight', $row['weight']);
    	$smarty->assign('port', $row['port']);
        $smarty->assign('ttl', $row['ttl']);
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

        $db->Execute($q) or die($db->ErrorMsg());
        set_msg("Record updated successfully!");
        header("Location: $base_url&mode=records&domain=".urlencode($domain));
        exit;
    }

} else {

    // Illegal records_mode
    set_err_msg("Error: illegal records_mode");
    $smarty->display('header.tpl');
    $smarty->display('footer.tpl');
    exit;

}

?>
