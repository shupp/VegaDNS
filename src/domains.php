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





if(!isset($_REQUEST['domain_mode']) || $_REQUEST['domain_mode'] == 'delete_cancelled') {


    // Display cancel message if necessary
    if(isset($_REQUEST['domain_mode']) && $_REQUEST['domain_mode'] == 'delete_cancelled') {
        set_msg("Delete Cancelled");
    }

    $params = array();
    // Get search string if it exists
    if(isset($_REQUEST['search']) && $_REQUEST['search'] != "") {
        $tempstring = preg_replace('/[*]/', '%', $_REQUEST['search']);
        $tempstring = preg_replace('/[ ]/', '%', $tempstring);
        $params[':tempstring'] = '%'.$tempstring.'%';
        $searchstring = 'domain like :tempstring';
        // Set appropriate query
        if($user_info['Account_Type'] == 'senior_admin') {
            $searchstring = 'where '.$searchstring;
        } else {
            $searchstring = $searchstring.' and ';
        }
        $smarty->assign('search', $_REQUEST['search']);
        $smarty->assign('searchtexttag', " matching \"".$_REQUEST['search']."\""
);
        $search = $_REQUEST['search'];
    } else {
        $searchstring = "";
        $search = "";
    }

    // Get scope of domain list, if it exists
    if(isset($_REQUEST['scope']) && $_REQUEST['scope'] != "") {
        $searchstring = "";
        $search = "";
        $scope = $_REQUEST['scope'];
        $smarty->assign('scope', $_REQUEST['scope']);

        if($user_info['Account_Type'] == 'senior_admin') {
                $aux = "where";
        } else {
                $aux = "and";
        }

        if($scope != "num") {
                $sq = "$aux domain regexp \"^[$scope" . strtoupper($scope) . "]\"";
        } else {
                $sq = "$aux domain regexp \"^[0-9]\"";
        }
    } else {
        $sq = "";
    }

    // Show domain list

    if($user_info['Account_Type'] == 'senior_admin') {
        $q = "select * from domains $searchstring $sq ";
    } else if($user_info['Account_Type'] == 'group_admin') {
        $q = "select * from domains where $searchstring owner_id = '".$user_info['cid']."' or group_owner_id = '".$user_info['cid']."' $sq ";
    } else if($user_info['Account_Type'] == 'user') {
        $q = "select * from domains where $searchstring owner_id = '".$user_info['cid']."' $sq ";
    }

    // sort
    if (!isset($_REQUEST['sortway'])) {
        $sortway = "asc";
    } else if ( $_REQUEST['sortway'] == 'desc') {
        $sortway = 'desc';
    } else {
        $sortway = 'asc';
    }

    if (!isset($_REQUEST['sortfield'])) {
        $sortfield = 'status';
    } else {
        $sortfield = $_REQUEST['sortfield'];
    }

    $q .= "order by $sortfield $sortway ".( ($sortfield == "status") ? ", domain" : "" )."";

    $stmt = $pdo->prepare($q);
    $stmt->execute($params) or die(print_r($stmt->errorInfo()));
    $totaldomains = $stmt->rowCount();

    // Pagination
    if(isset($_REQUEST['page'])) {
        if($_REQUEST['page'] == 'all') {
            $page = 1;
            $first_domain = 1;
            $last_domain = $totaldomains;
            $totalpages = 1;
        } else {
            $page = $_REQUEST['page'];
            $first_domain = ($page * $per_page) - $per_page + 1;
            if($first_domain == 0) $first_domain++;
            $last_domain = ($first_domain + $per_page - 1);
            $totalpages = ceil(number_format($totaldomains / $per_page, 10));
        }
    } else {
        $page = 1;
        if($totaldomains == 0) {
            $first_domain = 0;
        } else {
            $first_domain = 1;
        }
        $last_domain = ($first_domain + $per_page - 1);
        $totalpages = ceil(number_format($totaldomains / $per_page, 10));
    }
    if($last_domain > $totaldomains) $last_domain = $totaldomains;
    if($page > 1) {
        $smarty->assign('previous_url', "$base_url&mode=domains&page=".($page - 1)."&sortfield=$sortfield&sortway=$sortway&search=".urlencode($search));
    }
    if($page < $totalpages) {
        $smarty->assign('next_url', "$base_url&mode=domains&page=".($page + 1)."&sortfield=$sortfield&sortway=$sortway&search=".urlencode($search));
    }
    if($page > 1 || (isset($_REQUEST['page']) && $_REQUEST['page'] == 'all')) {
        $smarty->assign('first_url', "$base_url&mode=domains&page=1&sortfield=$sortfield&sortway=$sortway&search=".urlencode($search));
    }
    if($page < $totalpages) {
        $smarty->assign('last_url', "$base_url&mode=domains&page=$totalpages&sortfield=$sortfield&sortway=$sortway&search=".urlencode($search));
    }

    // sort
    $sort_array['Domain'] = 'domain';
    $sort_array['Status'] = 'status';
    $sort_array['Owner'] = 'owner_id';
    $sort_array['Group_Owner'] = 'group_owner_id';

    $sortbaseurl = "$base_url&mode=domains&page=".( ((isset($_REQUEST['page']) && $_REQUEST['page'] == 'all')) ? "all" : $page);

    while(list($key,$val) = each($sort_array)) {
        $newsortway = get_sortway($sortfield, $val, $sortway);
        $url = "<a href='$sortbaseurl&sortway=$newsortway&sortfield=$val'>".preg_replace('/_/', ' ', $key)."</a>";
        if($sortfield == $val) $url .= "&nbsp;<img border=0 alt='$sortway' src=images/$sortway.png>";
        $smarty->assign($key, $url);
    }

    if($totaldomains > 0) {


        $domain_count = 0;
        // Actually list domains
        while(++$domain_count && ($row = $stmt->fetch())
            && ($domain_count <= $last_domain)) {

            if($domain_count < $first_domain) continue;

            $out_array[$domain_count]['domain'] = $row['domain'];
            $out_array[$domain_count]['edit_url'] = "$base_url&mode=records&domain=".$row['domain'];
            $out_array[$domain_count]['status'] = $row['status'];
            $out_array[$domain_count]['owner_name'] = get_owner_name($row['owner_id']);
            $out_array[$domain_count]['group_owner_name'] = get_groupowner_name($row['group_owner_id']);
            if($user_info['Account_Type'] == 'senior_admin' || $user_info['Account_Type'] == 'group_admin') {
                $out_array[$domain_count]['change_owner_url'] = "$base_url&mode=domains&domain_mode=change_owner&domain_id=".$row['domain_id']."&domain=".$row['domain'];
            }
            if($row['status'] == 'inactive') {
                if($user_info['Account_Type'] == 'senior_admin') {
                    $out_array[$domain_count]['activate_url'] = "$base_url&mode=domains&domain_mode=activate_domain&domain_id=".$row['domain_id']."&domain=".$row['domain'];
                }
            } else if($row['status'] == 'active') {
                if($user_info['Account_Type'] == 'senior_admin') {
                    $out_array[$domain_count]['deactivate_url'] = "$base_url&mode=domains&domain_mode=deactivate_domain&domain_id=".$row['domain_id']."&domain=".$row['domain'];
                }
            }
            $out_array[$domain_count]['delete_url'] = "$base_url&mode=domains&domain_mode=delete&domain_id=".$row['domain_id']."&domain=".$row['domain'];
        }
    }

    $smarty->assign('all_url', "$base_url&mode=domains&page=all&sortfield=$sortfield&sortway=$sortway&search=".urlencode($search));
    $smarty->assign('first_domain', $first_domain);
    $smarty->assign('last_domain', $last_domain);
    $smarty->assign('totaldomains', $totaldomains);
    $smarty->assign('totalpages', $totalpages);
    if(isset($out_array)) $smarty->assign('out_array', $out_array);

    $smarty->display('header.tpl');
    $smarty->display('list_domains.tpl');
    $smarty->display('footer.tpl');
    exit;

} if($_REQUEST['domain_mode'] == 'add') {

    // New Domain Menu
    $smarty->display('header.tpl');
    require('src/new_domain_form.php');
    $smarty->display('footer.tpl');
    exit;

} else if($_REQUEST['domain_mode'] == 'add_now') {

    $domain = strtolower($_REQUEST['domain']);
    // make sure it's at least a second level domain
    if(!preg_match('/.*\..*/i', $domain)) {
        set_msg_err("Error: domain " . htmlentities($domain, ENT_QUOTES) . " does not appear to be at least a second level domain");
        $smarty->display('header.tpl');
        require('src/new_domain_form.php');
        $smarty->display('footer.tpl');
        exit;
    }
    // make sure it's at least a correct domain name
    if (!preg_match('/^[\.a-z0-9-\/]+$/i',$domain)) {
        set_msg_err("Error: domain " . htmlentities($domain, ENT_QUOTES) . " does not appear to be a valid domain name");
        $smarty->display('header.tpl');
        require('src/new_domain_form.php');
        $smarty->display('footer.tpl');
        exit;
    }

    $params = array(':domain' => $domain);
    $q = 'select * from domains where domain=:domain';
    $stmt = $pdo->prepare($q);
    $stmt->execute($params) or die(print_r($stmt->errorInfo()));
    if($stmt->rowCount() > 0) {

        set_msg_err("Error: domain " . htmlentities($domain, ENT_QUOTES) . " already exists");
        $smarty->display('header.tpl');
        require('src/new_domain_form.php');
        $smarty->display('footer.tpl');
        exit;
    }

    // Set domain status, ids based on account type
    if($user_info['Account_Type'] == 'senior_admin') {
        $domain_status = 'active';
        $owner_id = '';
        $group_owner_id = '';
    } else if($user_info['Account_Type'] == 'group_admin') {
        $domain_status = 'inactive';
        $owner_id = $user_info['cid'];
        $group_owner_id = $user_info['cid'];
    } else {
        $domain_status = 'inactive';
        $owner_id = $user_info['cid'];
        $group_owner_id = $user_info['gid'];
    }

    // Add domain
    $q = "insert into domains (domain,owner_id,group_owner_id,status)
        values(:domain,
        '$owner_id',
        '$group_owner_id',
        '$domain_status')";
    $stmt = $pdo->prepare($q);
    $stmt->execute($params) or die(print_r($stmt->errorInfo()));

    // Get new domain id, or die
    $id = get_dom_id($domain);
    if($id == -1) die("Error getting domain id");
    dns_log($id,"added domain $domain with status $domain_status");

    // Get default records
    if($user_info['Account_Type'] == 'user') {
        $q = "select * from default_records where default_type='group' and group_owner_id='".$user_info['gid']."'";
        $stmt = $pdo->query($q) or die(print_r($pdo->errorInfo()));
        if($stmt->rowCount() == 0) {
            // Get system default records
            $q = "select * from default_records where default_type='system'";
            $stmt = $pdo->query($q) or die(print_r($pdo->errorInfo()));
         }
    } else if($user_info['Account_Type'] == 'group_admin') {
        $q = "select * from default_records where default_type='group' and group_owner_id='".$user_info['cid']."'";
        $stmt = $pdo->query($q) or die(print_r($pdo->errorInfo()));
        if($stmt->rowCount() == 0) {
            // Get system default records
            $q = "select * from default_records where default_type='system'";
            $stmt = $pdo->query($q) or die(print_r($pdo->errorInfo()));
        }
    } else if($user_info['Account_Type'] == 'senior_admin') {
        // Get system default records
        $q = "select * from default_records where default_type='system'";
        $stmt = $pdo->query($q) or die(print_r($pdo->errorInfo()));
    }

    if($stmt->rowCount() == 0) {
        set_msg_err("Error: you have not yet setup default records");
        header("Location: $base_url");
        exit;
    }

    // Build arrays
    $counter = 0;
    while($row = $stmt->fetch()) {
        if($row['type'] == 'S' && !isset($soa_array)) {
            $soa_array = $row;
        } else {
            $records_array[$counter] = $row;
            $counter++;
        }
    }


    // Add SOA record
    $host = preg_replace('/DOMAIN/', $domain, $soa_array['host']);
    $val = preg_replace('/DOMAIN/', $domain, $soa_array['val']);
    $params = array(':host' => $host);
    $q = "insert into records (domain_id,host,type,val,ttl)
            values('$id',
            :host,
            'S',
            '$val',
            '".$soa_array['ttl']."')";
    $stmt = $pdo->prepare($q);
    $stmt->execute($params) or die(print_r($stmt->errorInfo()));
    dns_log($id, "added soa");

    // Add default records

    if(isset($records_array) && is_array($records_array)) {
        while(list($key,$row) = each($records_array)) {
            $host = preg_replace('/DOMAIN/', $domain, $row['host']);
            $val = preg_replace('/DOMAIN/', $domain, $row['val']);
            $params = array(':host' => $host);
            $q = "insert into records (domain_id,host,type,val,distance,ttl)
                values('$id',
                :host,
                '".$row['type']."',
                '$val',
                '".$row['distance']."',
                '".$row['ttl']."')";
            $stmt = $pdo->prepare($q);
            $stmt->execute($params) or die(print_r($stmt->errorInfo()));
            dns_log($id, "added ".$row['type']." $host with value $val");
        }
    }

    // Email the support address if an inactive domain is added
    $body = "inactive domain \"$domain\" added by ".$user_info['Email']."\n\n";
    $body .= "\n\nThanks,\n\n";
    $body .= "VegaDNS";

    mail($email,"New Inactive Domain Created",$body
, "Return-path: $supportemail\r\nFrom: \"$supportname\" <$supportemail>");

    set_msg("Domain $domain added successfully!");
    header("Location: $base_url&mode=records&domain=".urlencode($domain));
    exit;

} else if($_REQUEST['domain_mode'] == 'delete') {

    // Get domain info
    $q = "select * from domains where domain_id='".$_REQUEST['domain_id']."' limit 1";
    $stmt = $pdo->query($q) or die(print_r($pdo->errorInfo()));

    // Does the domain exist?
    if($stmt->rowCount() == 0) {
        set_msg_err("Error: domain ".htmlentities($_REQUEST['domain'], ENT_QUOTES)." does not exist");
        $smarty->display('header.tpl');
        $smarty->display('footer.tpl');
        exit;
    }

    $row = $stmt->fetch();

    $smarty->assign('domain', $_REQUEST['domain']);
    $smarty->assign('cancel_url', "$base_url&mode=domains&domain_mode=delete_cancelled");
    $smarty->assign('delete_url', "$base_url&mode=domains&domain_mode=delete_now&domain_id=".$_REQUEST['domain_id']."&domain=".$_REQUEST['domain']);
    $smarty->display('header.tpl');
    $smarty->display('delete_domain_confirm.tpl');
    $smarty->display('footer.tpl');
    exit;
} else if($_REQUEST['domain_mode'] == 'delete_now') {

    // make sure the domain_id was given
    if(!isset($_REQUEST['domain_id'])) {
        set_msg_err("Error: no domain_id");
        $smarty->display('header.tpl');
        $smarty->display('footer.tpl');
        exit;
    }

    // Make sure the user has rights to delete this domain
    $q = "select * from domains where domain_id='".$_REQUEST['domain_id']."' limit 1";
    $stmt = $pdo->query($q) or die(print_r($pdo->errorInfo()));

    // Does the domain exist?
    if($stmt->rowCount() == 0) {
        set_msg_err("Error: domain ".htmlentities($_REQUEST['domain'], ENT_QUOTES)." does not exist");
        $smarty->display('header.tpl');
        $smarty->display('footer.tpl');
        exit;
    }

    $dom_row = $stmt->fetch();

    // make sure the user has permission to delete this domain
    if($user_info['Account_Type'] != 'senior_admin') {
        if($user_info['Account_Type'] == 'group_admin' && $dom_row['group_owner_id'] != $user_info['cid'] && $dom_row['owner_id'] != $user_info['cid']) {
            set_msg_err("Error: you do not have permission to delete this domain");
            $smarty->display('header.tpl');
            $smarty->display('footer.tpl');
            exit;
        } else if($user_info['Account_Type'] == 'user'
                && $dom_row['owner_id'] != $user_info['cid']) {
            set_msg_err("Error: you do not have permission to delete this domain");
            $smarty->display('header.tpl');
            $smarty->display('footer.tpl');
            exit;
        }
    }
    $domain = $dom_row['domain'];


    $q = "delete from domains where domain_id='".$_REQUEST['domain_id']."'";
    $pdo->query($q) or die(print_r($pdo->errorInfo()));
    $q = "delete from records where domain_id='".$_REQUEST['domain_id']."'";
    $pdo->query($q) or die(print_r($pdo->errorInfo()));
    $q = "delete from log where domain_id='".$_REQUEST['domain_id']."'";
    $pdo->query($q) or die(print_r($pdo->errorInfo()));
    set_msg("Domain $domain deleted successfully");
    header("Location: $base_url&mode=domains");
    exit;

} else if($_REQUEST['domain_mode'] == 'activate_domain') {

    // Make sure a domain_id was given
    if(!isset($_REQUEST['domain_id'])) {
        set_msg_err("Error: no domain_id given");
        $smarty->display('header.tpl');
        $smarty->display('footer.tpl');
        exit;
    }

    // Make sure this is a senior_admin  // PERMISSIONS?
    if($user_info['Account_Type'] != 'senior_admin') {
        set_msg_err("Error: you do not have privileges to change a domain status");
        $smarty->display('header.tpl');
        $smarty->display('footer.tpl');
        exit;
    }

    $q = "update domains set status='active' where domain_id=".$_REQUEST['domain_id']."";
    $pdo->query($q) or die(print_r($pdo->errorInfo()));
    dns_log($_REQUEST['domain_id'], "Changed status to ACTIVE");
    set_msg("Domain activated successfully");
    header("Location: $base_url&mode=domains");
    exit;

} else if($_REQUEST['domain_mode'] == 'deactivate_domain') {

    // Make sure a domain_id was given
    if(!isset($_REQUEST['domain_id'])) {
        set_msg_err("Error: no domain_id given");
        $smarty->display('header.tpl');
        $smarty->display('footer.tpl');
        exit;
    }

    // Make sure this is a senior_admin  // PERMISSIONS?
    if($user_info['Account_Type'] != 'senior_admin') {
        set_msg_err("Error: you do not have privileges to change a domain status");
        $smarty->display('header.tpl');
        $smarty->display('footer.tpl');
        exit;
    }

    $q = "update domains set status='inactive' where domain_id=".$_REQUEST['domain_id']."";
    $pdo->query($q) or die(print_r($pdo->errorInfo()));

    dns_log($_REQUEST['domain_id'], "Changed status to INACTIVE");

    set_msg("Domain de-activated successfully");
    header("Location: $base_url&mode=domains");
    exit;

} else if($_REQUEST['domain_mode'] == 'import_domains') {

    // Make sure this is a senior_admin  // PERMISSIONS?
    if($user_info['Account_Type'] != 'senior_admin') {
        set_msg_err("Error: you do not have privileges to import via AXFR");
        $smarty->display('header.tpl');
        $smarty->display('footer.tpl');
        exit;
    }

    // Show import screen
    $smarty->display('header.tpl');
    require('src/import_form.php');
    $smarty->display('footer.tpl');
    exit;

} else if($_REQUEST['domain_mode'] == 'import_domains_now') {

    // Make sure this is a senior_admin  // PERMISSIONS?
    if($user_info['Account_Type'] != 'senior_admin') {
        set_msg_err("Error: you do not have privileges to import via AXFR");
        $smarty->display('header.tpl');
        $smarty->display('footer.tpl');
        exit;
    }

    // Make sure the host was given
    if(!isset($_REQUEST['hostname']) || $_REQUEST['hostname'] == "") {
        set_msg_err("Error: no hostname given");
        $smarty->display('header.tpl');
        require('src/import_form.php');
        $smarty->display('footer.tpl');
        exit;
    }

    // Make sure that some domains were given
    if(!isset($_REQUEST['domains']) || $_REQUEST['domains'] == "") {
        set_msg_err("Error: no domains were supplied  given");
        $smarty->display('header.tpl');
        require('src/import_form.php');
        $smarty->display('footer.tpl');
        exit;
    }

    // Build domains array
    $array_notunique = explode("\n",$_REQUEST['domains']);
    // clean array
    while(list($key,$domain_untrimmed) = each($array_notunique)) {
        $array_notunique[$key] = trim($domain_untrimmed);
    }
    reset($array_notunique);
    $array = array_unique($array_notunique);

    $counter = 0;
    // default SOA and NS
    if (isset($_REQUEST['default_soa']) && $_REQUEST['default_soa']=='on') {
        $q = "SELECT host,val FROM default_records WHERE type='S'";
        $stmt = $pdo->query($q) or die(print_r($pdo->errorInfo()));
        $def_soa = $stmt->fetch();
    }
    if (isset($_REQUEST['default_ns']) && $_REQUEST['default_ns'] == 'on') {
        $q = "SELECT host,val,distance,ttl FROM default_records WHERE type='N'";
        $stmt = $pdo->query($q) or die(print_r($pdo->errorInfo()));
        if($stmt->rowCount() >= 1) {
            $def_cnt = 0;
            while ($def_row = $stmt->fetch()) {
                $def_ns[$def_cnt] = $def_row;
                $def_cnt++;
            }
        } else {
            $def_ns = '';
        }
    }

    while(list($key,$domain) = each($array)) {
        if(strlen($domain) == 0) continue;

        // Make sure each domain is NOT in the database already
        if(get_dom_id($domain) != -1) {
            set_msg_err("Error: " . htmlentities($domain, ENT_QUOTES) . " is already in this database");
            $smarty->display('header.tpl');
            require('src/import_form.php');
            $smarty->display('footer.tpl');
            exit;
        }

        $command = "wget -q -O - '$vegadns_url/axfr_get.php?domain=$domain&hostname=".$_REQUEST['hostname']."' 2>&1";
        $out = shell_exec($command);

        // Make $out an array
        $out_array = explode("\n", $out);
        if(preg_match('/^#.*$/', $out_array[0])) {
            $out_array['domain'] = $domain;
            $domains_array[$counter] = $out_array;
            $counter++;
        } else {
            set_msg_err("Error: could not do axfr-get for " . htmlentities($domain, ENT_QUOTES) . ":<br>".htmlentities($out_array[0], ENT_QUOTES));
            $smarty->display('header.tpl');
            require('src/import_form.php');
            $smarty->display('footer.tpl');
            exit;
        }
    }


    // ADD TO SQL
    while(list($key,$line) = each($domains_array)) {
        $domain = $line['domain'];
        // add domain first and get the id
        $params = array(':domain' => $domain);
        $q = "insert into domains (domain,status) values(:domain, 'active')";
        $stmt = $pdo->prepare($q);
        $stmt->execute($params) or die(print_r($stmt->errorInfo()));
        $domain_id = get_dom_id($domain);

        $skip_ns = 'FALSE';
        if (isset($_REQUEST['default_ns']) && $_REQUEST['default_ns'] == 'on') {
            $skip_ns = 'TRUE';
            if(is_array($def_ns)) {
                foreach ($def_ns as $ns) {
                    $host = preg_replace('/DOMAIN/', $domain, $ns['host']);
                    $params = array(
                        ':host' => $host,
                        ':val'  => $ns['val']
                    );
                    $q = "insert into records
                        (domain_id,host,type,val,distance,ttl)
                        values(
                        $domain_id,
                        :host,
                        'N',
                        :val,
                        '".$ns['distance']."',
                        '".$ns['ttl']."')";
                    $stmt = $pdo->prepare($q);
                    $stmt->execute($params) or die(print_r($stmt->errorInfo()));
                    $counter++;
                }
            }
        }
        while(list($line_key,$value) = each($line)) {
            if($line_key != 'domain' && !preg_match('/^#/', $value)) {
                $result = parse_dataline($value);
                if(!is_array($result)) continue;
                if ((isset($_REQUEST['default_soa']) && $_REQUEST['default_soa']=="on") && ($result['type']=='S')) {
                    $result['val']=$def_soa['val'];
                    $result['host']=$def_soa['host'];
                }
                // if ((isset($_REQUEST['default_ns']) && $_REQUEST['default_ns']!="on") || ($result['type']!='N')) {
                if ($result['type'] == 'N' && $skip_ns == 'TRUE') continue;

                $params = array(
                    ':host' => preg_replace('/\\052/', '*', $result['host']),
                    ':val'  => $result['val']
                );
                $q = "insert into records
                    (domain_id,host,type,val,distance,ttl)
                    values(
                        $domain_id,
                        :host,
                        '".$result['type']."',
                        :val,
                        '".$result['distance']."',
                        '".$result['ttl']."')";
                $stmt = $pdo->prepare($q);
                $stmt->execute($params) or die(print_r($stmt->errorInfo()));
            }
        }
    }
    $log_entry = "imported via axfr from ".$_REQUEST['hostname'];
    dns_log($domain_id,$log_entry);
    set_msg("Domains added successfully!");
    header("Location: $base_url");
    exit;

} else if($_REQUEST['domain_mode'] == 'change_owner' ||
    $_REQUEST['domain_mode'] == 'change_owner_now') {

    // DO ERROR/PERMISSIONS CHECKING FOR OWNER CHANGES

    // Make sure domain_id was given
    if(!isset($_REQUEST['domain_id'])) {
        set_msg_err("Error: domain_id not supplied");
        $smarty->display('header.tpl');
        $smarty->display('footer.tpl');
        exit;
    }

    // Make sure this is NOT just a user
    if($user_info['Account_Type'] == 'user') {
        set_msg_err("Error: you do not have privileges to to change owner for ".$_REQUEST['domain']);
        $smarty->display('header.tpl');
        $smarty->display('footer.tpl');
        exit;
    } else if($user_info['Account_Type'] == 'group_admin' ||
        $user_info['Account_Type'] == 'senior_admin') {

        // Get domain data, make sure it even exists
        $q = "select * from domains where domain_id='".$_REQUEST['domain_id']."' limit 1";
        $stmt = $pdo->query($q) or die(print_r($pdo->errorInfo()));
        if($stmt->rowCount() == 0) {
            set_msg_err("Error: ".$_REQUEST['domain']." is not in the database");
            $smarty->display('header.tpl');
            $smarty->display('footer.tpl');
            exit;
        }
        $row = $stmt->fetch();

        // Get owner info
        $q = "select * from accounts where cid='".$row['owner_id']."' limit 1";
        $stmt = $pdo->query($q) or die(print_r($pdo->errorInfo()));
        if($stmt->rowCount() > 0) {
            $owner_row = $stmt->fetch();
        } else {
            $owner_row = NULL;
        }

        // Get group owner info
        $q = "select * from accounts where cid='".$row['group_owner_id']."' limit 1";
        $stmt = $pdo->query($q) or die(print_r($pdo->errorInfo()));
        if($stmt->rowCount() > 0) {
            $group_owner_row = $stmt->fetch();
        } else {
            $group_owner_row = NULL;
        }

        // Make sure a group_admin has rights
        if($user_info['Account_Type'] == 'group_admin'
            && $user_info['cid'] != $row['owner_id']) {

            if($row['group_owner_id'] != $user_info['cid']) {
                set_msg_err("Error: You do not have permissions to edit the owner of ".$_REQUEST['domain']);
                $smarty->display('header.tpl');
                $smarty->display('footer.tpl');
                exit;
            }
        }

        if($_REQUEST['domain_mode'] == 'change_owner') {
            $smarty->display('header.tpl');
            require('src/change_owner.php');
            $smarty->display('footer.tpl');
            exit;
        } else if($_REQUEST['domain_mode'] == 'change_owner_now') {
            // CHANGE OWNERSHIP

            // make sure the email addresses are in in the database
            $owner_id = get_cid(strtolower($_REQUEST['email_address']));
            $q = "select 'Email' from accounts where cid='$owner_id'";
            $stmt = $pdo->query($q) or die(print_r($pdo->errorInfo()));
            if($stmt->rowCount() == 0) {
                set_msg_err("Error: ".$_REQUEST['email_address']." is not in the database");
                $smarty->display('header.tpl');
                $smarty->display('footer.tpl');
                exit;
            }

            if($user_info['Account_Type'] == 'senior_admin' && isset($_REQUEST['group_email_address']) && $_REQUEST['group_email_address'] != '') {
                $group_owner_id = get_cid(strtolower($_REQUEST['group_email_address']));
                $q = "select 'Email' from accounts where cid='$group_owner_id' and Account_Type='group_admin'";
                $stmt = $pdo->query($q) or die(print_r($pdo->errorInfo()));
                if($stmt->rowCount() == 0) {
                    set_msg_err("Error: ".$_REQUEST['group_email_address']." is not in the database, or their Account_Type is not 'group_admin'");
                    $smarty->display('header.tpl');
                    require('src/change_owner.php');
                    $smarty->display('footer.tpl');
                    exit;
                }
                $change_group = 1;
            }

            $q = "update domains set owner_id='$owner_id'";
            if($change_group == 1)
            $q .= ", group_owner_id = '$group_owner_id'";
            $q .= " where domain_id = '".$_REQUEST['domain_id']."'";
            $pdo->query($q) or die(print_r($pdo->errorInfo()));

            $log_entry = "changed owner to ".get_owner_name($owner_id);
            if($change_group == 1)
                $log_entry .= " and changed group_owner to ".get_groupowner_name($group_owner_id);
            dns_log($_REQUEST['domain_id'], $log_entry);

            set_msg("Ownership changed successfully");
            header("Location: $base_url&mode=domains");
            exit;

        }
    }

} else if($_REQUEST['domain_mode'] == 'domain_prefs') {

}
?>
