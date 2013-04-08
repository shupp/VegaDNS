<?php


/*
 *
 * VegaDNS - DNS Administration Tool for use with djbdns
 *
 * CREDITS:
 * Written by Bill Shupp
 * <hostmaster@shupp.org>
 *
 * LICENSE:
 * This software is distributed under the GNU General Public License
 * Copyright 2003-2013, Bill Shupp
 * see COPYING for details
 *
 */

if(!preg_match('/.*\/index.php$/', $_SERVER['PHP_SELF'])) {
    header("Location:../index.php");
    exit;
}





// Make sure the domain was given
if(!isset($_REQUEST['domain'])) {
    set_msg("Error: no domain given");
    $smarty->display('header.tpl');
    $smarty->display('footer.tpl');
    exit;
}

$domain = $_REQUEST['domain'];
$smarty->assign('domain', $domain);

// Get domain information
$params = array(':domain' => $domain);
$q = "select * from domains where domain=:domain";
$stmt = $pdo->prepare($q);
$stmt->execute($params) or die(print_r($stmt->errorInfo()));
if($stmt->rowCount() == 0) {
    set_msg_err("Error: domain $domain does not exist");
    $smarty->display('header.tpl');
    $smarty->display('footer.tpl');
    exit;
}

$dom_row = $stmt->fetch();

// Make sure this user has permission to view/edit this domain

if($user_info['Account_Type'] != 'senior_admin') {
    if($user_info['Account_Type'] == 'group_admin') {
        if(($dom_row['group_owner_id'] != $user_info['cid']) &&
            ($dom_row['owner_id'] != $user_info['cid'])) {
            set_msg_err("Error: you do not have permission to "
                ."view resource records for domain $domain");
            $smarty->display('header.tpl');
            $smarty->display('footer.tpl');
            exit;
        }
    } else if($user_info['Account_Type'] == 'user') {
        if($dom_row['owner_id'] != $user_info['cid']) {
            set_msg_err("Error: you do not have permission to ".
                "view resource records for domain $domain");
            $smarty->display('header.tpl');
            $smarty->display('footer.tpl');
            exit;
        }
    }
}

if(!isset($_REQUEST['record_mode']) || $_REQUEST['record_mode'] == 'delete_cancelled') {

    // Set cancelled message if necessary
    if(isset($_REQUEST['record_mode']) && $_REQUEST['record_mode'] == 'delete_cancelled') {
        set_msg("Delete Cancelled");
    }

    // Get search string if it exists
    $params = array();
    if(isset($_REQUEST['search']) && $_REQUEST['search'] != "") {
        $tempstring = preg_replace('/[*]/', '%', $_REQUEST['search']);
        $tempstring = preg_replace('/[ ]/', '%', $tempstring);
        $params[':search'] = '%' . $tempstring . '%';
        $searchstring = "host like :search and type != 'S' and ";

        $smarty->assign('search', $_REQUEST['search']);
        $smarty->assign('searchtexttag', " matching \"".$_REQUEST['search']."\"");
        $search = $_REQUEST['search'];
    } else {
        $searchstring = "";
        $search = "";
    }

    // sort
    if (!isset($_REQUEST['sortway'])) {
        $sortway = 'asc';
    } else if ( $_REQUEST['sortway'] == 'desc') {
        $sortway = 'desc';
    } else {
        $sortway = 'asc';
    }

    if (!isset($_REQUEST['sortfield'])) {
        $sortfield = 'type';
    } else {
        $sortfield = $_REQUEST['sortfield'];
    }

    // Get records list
    $q = "select * from records where $searchstring domain_id = '".
        $dom_row['domain_id']."' order by $sortfield $sortway".(($sortfield=='type') ? ", host" : "")."";
    $stmt = $pdo->prepare($q);
    $stmt->execute($params) or die(print_r($stmt->errorInfo()));
    $totalrecords = $stmt->rowCount();

    // See if the search failed to match
    if($totalrecords == 0 && $searchstring != "") {
        set_msg_err("Error: no records matching \"".$_REQUEST['search']."\"");
        $smarty->display('header.tpl');
        $smarty->display('footer.tpl');
        exit;
    }
    // If no search, make sure there are records to display
    if($totalrecords == 0 && $searchstring == "") {
        set_msg_err("Error: no resource records for domain $domain");
        $smarty->display('header.tpl');
        $smarty->display('footer.tpl');
        exit;
    }

    // Pagination
    if(isset($_REQUEST['page'])) {
        if($_REQUEST['page'] == 'all') {
            $page = 1;
            $first_record = 1;
            $last_record = $totalrecords;
            $totalpages = 1;
        } else {
            $page = $_REQUEST['page'];
            $first_record = ($page * $per_page) - $per_page + 1;
            if($first_record == 0) $first_record++;
            $last_record = ($first_record + $per_page - 1);
            $totalpages = ceil(number_format($totalrecords / $per_page, 10));
        }
    } else {
        $page = 1;
        $first_record = 1;
        $last_record = ($first_record + $per_page - 1);
        $totalpages = ceil(number_format($totalrecords / $per_page, 10));
    }
    if($last_record > $totalrecords) $last_record = $totalrecords;
    if($page > 1) {
        $smarty->assign('previous_url', "$base_url&mode=records&domain=$domain&page=".($page - 1)."&sortfield=$sortfield&sortway=$sortway&search=".urlencode($search));
    }
    if($page < $totalpages) {
        $smarty->assign('next_url', "$base_url&mode=records&domain=$domain&page=".($page + 1)."&sortfield=$sortfield&sortway=$sortway&search=".urlencode($search));
    }
    if($page > 1 || (isset($_REQUEST['page']) && $_REQUEST['page'] == 'all')) {
        $smarty->assign('first_url', "$base_url&mode=records&domain=$domain&page=1&sortfield=$sortfield&sortway=$sortway&search=".urlencode($search));
    }
    if($page < $totalpages) {
        $smarty->assign('last_url', "$base_url&mode=records&domain=$domain&page=$totalpages&sortfield=$sortfield&sortway=$sortway&search=".urlencode($search));
    }

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
    while (++$counter && $row = $stmt->fetch()) {
        // Get SOA
        if(!isset($soa) && $row['type'] == 'S') $soa = $row;

        if($counter < $first_record) continue;
        if($counter <= $last_record) {
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
        $smarty->assign('edit_soa_url', "$base_url&mode=records&record_mode=edit_soa&domain=$domain");
        if(strlen($soa_array['serial']) == 0 ) {
            $smarty->assign('serial', '(using djbdns default)');
        } else {
            $smarty->assign('serial', $soa_array['serial']);
        }
        $smarty->assign('refresh', $soa_array['refresh']);
        $smarty->assign('retry', $soa_array['retry']);
        $smarty->assign('expire', $soa_array['expire']);
        $smarty->assign('minimum', $soa_array['minimum']);
        $smarty->assign('ttl', $soa_array['ttl']);
    }

    $smarty->assign('display_soa', $display_soa);
    $smarty->assign('add_record_url', "$base_url&mode=records&record_mode=add_record&domain=$domain");
    $smarty->assign('view_log_url', "$base_url&mode=records&record_mode=view_log&domain=$domain");

    $smarty->assign('all_url', "$base_url&mode=records&domain=$domain&page=all&sortfield=$sortfield&sortway=$sortway&search=".urlencode($search));
    $smarty->assign('first_record', $first_record);
    $smarty->assign('last_record', $last_record);
    $smarty->assign('totalrecords', $totalrecords);
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
            $out_array[$counter]['delete_url'] = "$base_url&mode=records&record_mode=delete&record_id=".$array['record_id']."&domain=".urlencode($domain);
            $out_array[$counter]['edit_url'] = "$base_url&mode=records&record_mode=edit_record&record_id=".$array['record_id']."&domain=".urlencode($domain);
            $counter++;
        }
    }

    if(isset($out_array)) $smarty->assign('out_array', $out_array);
    $smarty->display('header.tpl');
    $smarty->display('list_records.tpl');
    $smarty->display('footer.tpl');
    exit;

} else if($_REQUEST['record_mode'] == 'add_record') {

    $smarty->display('header.tpl');
    require('src/add_record_form.php');
    $smarty->display('footer.tpl');
    exit;

} else if($_REQUEST['record_mode'] == 'add_record_now') {

    // Add domain to 'name'
    if(!preg_match('/^.*\.($domain)\.*$/i', $_REQUEST['name']) && !preg_match('/^($domain)\.*$/i', $_REQUEST['name'])) {
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
        set_msg_err(htmlentities($result, ENT_QUOTES));
        $smarty->display('header.tpl');
        require('src/add_record_form.php');
        $smarty->display('footer.tpl');
        exit;
    } else {



        // add record to db

        $params = array();
        if($_REQUEST['type'] == 'A') {
            $params[':address'] = $_REQUEST['address'];
            $q = "insert into records
            (domain_id,host,type,val,ttl) values(
            '".get_dom_id($domain)."',
            '$name',
            '".set_type($_REQUEST['type'])."',
            :address,
            '".$_REQUEST['ttl']."')";
        } else if($_REQUEST['type'] == 'AAAA') {
            $address = uncompress_ipv6($_REQUEST['address']);
            $params[':address'] = $address;
            $q = "insert into records
            (domain_id,host,type,val,ttl) values(
            '".get_dom_id($domain)."',
            '$name',
            '".set_type($_REQUEST['type'])."',
            :address,
            '".$_REQUEST['ttl']."')";
        } else if($_REQUEST['type'] == 'AAAA+PTR') {
            $address = uncompress_ipv6($_REQUEST['address']);
            $params[':address'] = $address;
            $q = "insert into records
            (domain_id,host,type,val,ttl) values(
            '".get_dom_id($domain)."',
            '$name',
            '".set_type($_REQUEST['type'])."',
            :address,
            '".$_REQUEST['ttl']."')";
        } else if($_REQUEST['type'] == 'MX') {
            if(!preg_match('/\..+$/', $_REQUEST['address'])) {
                $mxaddress = $_REQUEST['address'].".".$domain;
            } else {
                $mxaddress = $_REQUEST['address'];
            }
            $params[':mxaddress'] = $mxaddress;
            $params[':distance']  = $_REQUEST['distance'];
            $q = "insert into records
            (domain_id,host,type,val,distance,ttl) values(
            '".get_dom_id($domain)."',
            '$name',
            '".set_type($_REQUEST['type'])."',
            :mxaddress,
            :distance,
            '".$_REQUEST['ttl']."')";
        }
        if($_REQUEST['type'] == 'NS') {
            $params[':address'] = $_REQUEST['address'];
            $q = "insert into records
            (domain_id,host,type,val,ttl) values(
            '".get_dom_id($domain)."',
            '$name',
            '".set_type($_REQUEST['type'])."',
            :address,
            '".$_REQUEST['ttl']."')";
        }
        if($_REQUEST['type'] == 'PTR') {
            $params[':address'] = $_REQUEST['address'];
            $q = "insert into records
            (domain_id,host,type,val,ttl) values(
            '".get_dom_id($domain)."',
            '$name',
            '".set_type($_REQUEST['type'])."',
            :address,
            '".$_REQUEST['ttl']."')";
        }
        if($_REQUEST['type'] == 'TXT') {
            $params[':address'] = $_REQUEST['address'];
            $q = "insert into records
            (domain_id,host,type,val,ttl) values(
            '".get_dom_id($domain)."',
            '$name',
            '".set_type($_REQUEST['type'])."',
            :address,
            '".$_REQUEST['ttl']."')";
        }
        if($_REQUEST['type'] == 'CNAME') {
            $params[':address'] = $_REQUEST['address'];
            $q = "insert into records
            (domain_id,host,type,val,ttl) values(
            '".get_dom_id($domain)."',
            '$name',
            '".set_type($_REQUEST['type'])."',
            :address,
            '".$_REQUEST['ttl']."')";
        }
        if($_REQUEST['type'] == 'SRV') {
            if(!preg_match('/\..+$/', $_REQUEST['address'])) {
                $srvaddress = $_REQUEST['address'].".".$domain;
            } else {
                $srvaddress = $_REQUEST['address'];
            }
            $params[':srvaddress'] = $srvaddress;
            $params[':distance']   = $_REQUEST['distance'];
            $params[':weight']     = $_REQUEST['weight'];
            $params[':port']       = $_REQUEST['port'];

            $q = "insert into records
            (domain_id,host,type,val,distance,weight,port,ttl) values(
            '".get_dom_id($domain)."',
            '$name',
            '".set_type($_REQUEST['type'])."',
            :srvaddress,
            :distance,
            :weight,
            :port,
            '".$_REQUEST['ttl']."')";

        }
        if($_REQUEST['type'] == 'SPF') {
            $params[':address'] = $_REQUEST['address'];
            $q = "insert into records
            (domain_id,host,type,val,ttl) values(
            '".get_dom_id($domain)."',
            '$name',
            '".set_type($_REQUEST['type'])."',
            :address,
            '".$_REQUEST['ttl']."')";
        }
        $stmt = $pdo->prepare($q);
        $stmt->execute($params) or die(print_r($stmt->errorInfo()));
        set_msg("Record added successfully!");
        header("Location: $base_url&mode=records&domain=".urlencode($domain));
        exit;
    }

} else if($_REQUEST['record_mode'] == 'delete') {

    // Get record info
    $q = "select * from records where record_id='".$_REQUEST['record_id']."' limit 1";
    $stmt = $pdo->query($q) or die(print_r($pdo->errorInfo()));
    $row = $stmt->fetch();

    $smarty->assign('type', get_type($row['type']));
    $smarty->assign('host', $row['host']);
    $smarty->assign('cancel_url', "$base_url&mode=records&domain=".urlencode($domain)."&record_mode=delete_cancelled");
    $smarty->assign('delete_url', "$base_url&mode=records&record_mode=delete_now&record_id=".$row['record_id']."&domain=".urlencode($domain));
    $smarty->display('header.tpl');
    $smarty->display('delete_record_confirm.tpl');
    $smarty->display('footer.tpl');
    exit;

} else if($_REQUEST['record_mode'] == 'delete_now') {

    // make sure the record_id was given
    if(!isset($_REQUEST['record_id'])) {
        set_msg_err("Error: no record_id");
        $smarty->display('header.tpl');
        $smarty->display('footer.tpl');
        exit;
    }

    $q = "delete from records where record_id='".$_REQUEST['record_id']."'";
    $pdo->query($q) or die(print_r($pdo->errorInfo()));
    set_msg("Record deleted successfully");
    header("Location: $base_url&mode=records&domain=".urlencode($domain));
    exit;

} if($_REQUEST['record_mode'] == 'edit_soa') {

    // Get Current SOA information

    $q = "select * from records where type='S' and domain_id='".
        get_dom_id($domain)."' limit 1";
    $stmt = $pdo->query($q) or die(print_r($pdo->errorInfo()));
    $row = $stmt->fetch();

    $soa = parse_soa($row);

    // Edit SOA Menu
    $smarty->display('header.tpl');
    require('src/edit_soa_form.php');
    $smarty->display('footer.tpl');
    exit;

} if($_REQUEST['record_mode'] == 'edit_soa_now') {

    if(!preg_match('/^[^.].*\..*/i', preg_replace('/\.$/', '', $_REQUEST['primary_name_server']))) {
        set_msg_err("Error: primary name server ".$_REQUEST['primary_name_server']." does not appear to be a valid second level or or more domain");
        $smarty->display('header.tpl');
        require('src/edit_soa_form.php');
        $smarty->display('footer.tpl');
        exit;
    }

    // Check email
    if(!isset($_REQUEST['contactaddr']) || $_REQUEST['contactaddr'] == "") {
        set_msg_err("Error: missing email address");
        $smarty->display('header.tpl');
        require('src/edit_soa_form.php');
        $smarty->display('footer.tpl');
        exit;
    }

    // Build array
    $array['host'] = $_REQUEST['contactaddr'].':'.$_REQUEST['primary_name_server'];
    $array['val'] = $_REQUEST['refresh'].':'.$_REQUEST['retry'].':'.$_REQUEST['expire'].':'.$_REQUEST['minimum'].':'.$_REQUEST['serial'];

    $return = parse_soa($array);

    // Update table
    $host = $return['tldemail'].':'.$return['tldhost'];
    $val = $return['refresh'].':'.$return['retry'].':'.$return['expire'].':'.$return['minimum'].':'.$return['serial'];
    $q = "update records set host='$host',
        val='$val',
        ttl='".$_REQUEST['ttl']."'  where type='S' and
        domain_id='".get_dom_id($domain)."'";
    $pdo->query($q) or die(print_r($pdo->errorInfo()));

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
    $stmt = $pdo->query($q) or die(print_r($pdo->errorInfo()));
    $row = $stmt->fetch();


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

    // Add domain to 'name'
    if(!preg_match('/^.*\.($domain)\.*$/i', $_REQUEST['name']) && !preg_match('/^($domain)\.*$/i', $_REQUEST['name'])) {
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
        $q = "select * from records where record_id='".$_REQUEST['record_id']."' and domain_id='".
            get_dom_id($domain)."' and type!='S' limit 1";
        $stmt = $pdo->query($q) or die(print_r($pdo->errorInfo()));
        $row = $stmt->fetch();

        $smarty->assign('record_id', $_REQUEST['record_id']);
        $smarty->assign('name', $row['host']);
        $smarty->assign('address', $row['val']);
        $smarty->assign('type', get_type($row['type']));
        $smarty->assign('distance', $row['distance']);
        $smarty->assign('weight', $row['weight']);
        $smarty->assign('port', $row['port']);
        $smarty->assign('ttl', $row['ttl']);
        set_msg_err(htmlentities($result, ENT_QUOTES));
        $smarty->display('header.tpl');
        $smarty->display('edit_record.tpl');
        $smarty->display('footer.tpl');
        exit;
    } else {

        // Update record

        if ($_REQUEST['type']=='AAAA' || $_REQUEST['type']=='AAAA+PTR') {
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

        $pdo->query($q) or die(print_r($pdo->errorInfo()));
        set_msg("Record updated successfully!");
        header("Location: $base_url&mode=records&domain=".urlencode($domain));
        exit;
    }

} else {

    die("Error: illegal records_mode");

}

?>
