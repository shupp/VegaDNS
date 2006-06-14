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
 * Copyright 2003-2005, MerchBox.Com
 * see COPYING for details
 * 
 */ 

if(!ereg(".*/index.php$", $_SERVER['PHP_SELF'])) {
    header("Location:../index.php");
    exit;
}





if(!isset($_REQUEST['domain_mode']) || $_REQUEST['domain_mode'] == 'delete_cancelled') {


    // Display cancel message if necessary
    if(isset($_REQUEST['domain_mode']) && $_REQUEST['domain_mode'] == 'delete_cancelled') {
        set_msg("Delete Cancelled");
    }

    // Get search string if it exists
    if(isset($_REQUEST['search']) && $_REQUEST['search'] != "") {
        $tempstring = ereg_replace("[*]", "%", $_REQUEST['search']);
        $tempstring = ereg_replace("[ ]", "%", $tempstring);
        $searchstring = "domain like '%".mysql_escape_string($tempstring)."%'";
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

    $result = mysql_query($q) or die(mysql_error());
    $totaldomains = mysql_num_rows($result);

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
        $url = "<a href='$sortbaseurl&sortway=$newsortway&sortfield=$val'>".ereg_replace('_', ' ', $key)."</a>";
        if($sortfield == $val) $url .= "&nbsp;<img border=0 alt='$sortway' src=images/$sortway.png>";
        $smarty->assign($key, $url);
    }

    if($totaldomains > 0) {


        $domain_count = 0;
        // Actually list domains
        while(++$domain_count && ($row = mysql_fetch_array($result))
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
    if(!eregi(".*\..*", $domain)) {
        set_msg_err("Error: domain $domain does not appear to be at least a second level domain");
        $smarty->display('header.tpl');
        require('src/new_domain_form.php');
        $smarty->display('footer.tpl');
        exit;
    }
    // make sure it's at least a correct domain name
	if (!eregi("^[\.a-z0-9-]+$",$domain)) {
        set_msg_err("Error: domain $domain does not appear to be a valid domain name");
        $smarty->display('header.tpl');
        require('src/new_domain_form.php');
        $smarty->display('footer.tpl');
        exit;
    }

    $q = "select * from domains where domain='".mysql_escape_string($domain)."'";
    $result = mysql_query($q);
    if(mysql_num_rows($result) > 0) {
    
        set_msg_err("Error: domain $domain already exists");
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
        values('".mysql_escape_string($domain)."',
        '$owner_id',
        '$group_owner_id',
        '$domain_status')";
    $result = mysql_query($q) or die(mysql_error()."<p>query was: $q");

    // Get new domain id, or die
    $id = get_dom_id($domain);
    if($id == -1) die("Error getting domain id");
    dns_log($id,"added domain $domain with status $domain_status");

    // Get default records
    if($user_info['Account_Type'] == 'user') {
        $q = "select * from default_records where default_type='group' and group_owner_id='".$user_info['gid']."'";
        $result = mysql_query($q) or die(mysql_error());
        if(mysql_num_rows($result) == 0) {
            // Get system default records
            $q = "select * from default_records where default_type='system'";
            $result = mysql_query($q) or die(mysql_error());
         }
    } else if($user_info['Account_Type'] == 'group_admin') {
        $q = "select * from default_records where default_type='group' and group_owner_id='".$user_info['cid']."'";
        $result = mysql_query($q) or die(mysql_error());
        if(mysql_num_rows($result) == 0) {
            // Get system default records
            $q = "select * from default_records where default_type='system'";
            $result = mysql_query($q) or die(mysql_error());
        }
    } else if($user_info['Account_Type'] == 'senior_admin') {
        // Get system default records
        $q = "select * from default_records where default_type='system'";
        $result = mysql_query($q) or die(mysql_error());
    }

    if(mysql_num_rows($result) == 0) {
        set_msg_err("Error: you have not yet setup default records");
        header("Location: $base_url");
        exit;
    }

    // Build arrays
    $counter = 0;
    while($row = mysql_fetch_array($result)) {
        if($row['type'] == 'S' && !isset($soa_array)) {
            $soa_array = $row;
        } else {
            $records_array[$counter] = $row;
            $counter++;
        }
    }


    // Add SOA record
    $host = ereg_replace("DOMAIN", $domain, $soa_array['host']);
    $val = ereg_replace("DOMAIN", $domain, $soa_array['val']);
    $q = "insert into records (domain_id,host,type,val,ttl)
            values('$id',
            '".mysql_escape_string($host)."',
            'S',
            '$val',
            '".$soa_array['ttl']."')";
    mysql_query($q) or die(mysql_error());
    dns_log($id, "added soa");
            
    // Add default records

    if(is_array($records_array)) {
        while(list($key,$row) = each($records_array)) {
            $host = ereg_replace("DOMAIN", $domain, $row['host']);
            $val = ereg_replace("DOMAIN", $domain, $row['val']);
            $q = "insert into records (domain_id,host,type,val,distance,ttl)
                values('$id',
                '".mysql_escape_string($host)."',
                '".$row['type']."',
                '$val',
                '".$row['distance']."',
                '".$row['ttl']."')";
            mysql_query($q) or die(mysql_error());
            dns_log($id, "added ".$row['type']." $host with value $val");
        }
    }

    // Email the support address if an inactive domain is added
    $body = "inactive domain \"$domain\" added by ".$user_info['Email']."\n\n";
    $body .= "\n\nThanks,\n\n";
    $body .= "VegaDNS";

    mail(strtolower($_REQUEST['username']),"New Inactive Domain Created",$body
, "Return-path: $supportemail\r\nFrom: \"$supportname\" <$supportemail>");

    set_msg("Domain $domain added successfully!");
    header("Location: $base_url&mode=records&domain=".urlencode($domain));
    exit;
    
} else if($_REQUEST['domain_mode'] == 'delete') {

    // Get domain info
    $q = "select * from domains where domain_id='".$_REQUEST['domain_id']."' limit 1";
    $result = mysql_query($q) or die(mysql_error());

    // Does the domain exist?
    if(mysql_num_rows($result) == 0) {
        set_msg_err("Error: domain ".$_REQUEST['domain']." does not exist");
        $smarty->display('header.tpl');
        $smarty->display('footer.tpl');
        exit;
    }

    $row = mysql_fetch_array($result);

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
    $result = mysql_query($q) or die(mysql_error());

    // Does the domain exist?
    if(mysql_num_rows($result) == 0) {
        set_msg_err("Error: domain ".$_REQUEST['domain']." does not exist");
        $smarty->display('header.tpl');
        $smarty->display('footer.tpl');
        exit;
    }

    $dom_row = mysql_fetch_array($result);

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


    $q = "delete from domains where domain_id='".$_REQUEST['domain_id']."'";
    mysql_query($q) or die(mysql_error());
    $q = "delete from records where domain_id='".$_REQUEST['domain_id']."'";
    mysql_query($q) or die(mysql_error());
    $q = "delete from log where domain_id='".$_REQUEST['domain_id']."'";
    mysql_query($q) or die(mysql_error());
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
    mysql_query($q) or die(mysql_error());
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
    mysql_query($q) or die(mysql_error());

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
    if (isset($_REQUEST['default_soa']) && $_REQUEST['default_soa']=="on")
     $def_soa=mysql_fetch_array(
      mysql_query("SELECT host,val FROM default_records WHERE type='S'"));
    if (isset($_REQUEST['default_ns']) && $_REQUEST['default_ns']=="on") {
     $q=mysql_query("SELECT host,val,distance,ttl FROM default_records WHERE type='N'");
     while ($l = mysql_fetch_array($q))
      $def_ns[]=$l;
    }

    while(list($key,$domain) = each($array)) {
        if(strlen($domain) == 0) continue;

        // Make sure each domain is NOT in the database already 
        if(get_dom_id($domain) != -1) {
            set_msg_err("Error: $domain is already in this database");
            $smarty->display('header.tpl');
            require('src/import_form.php');
            $smarty->display('footer.tpl');
            exit;
        }

        $command = "wget -q -O - '$vegadns_url/axfr_get.php?domain=$domain&hostname=".$_REQUEST['hostname']."' 2>&1";
        $out = shell_exec($command);

        // Make $out an array
        $out_array = explode("\n", $out);
        if(ereg("^#.*$", $out_array[0])) {
            $out_array['domain'] = $domain;
            $domains_array[$counter] = $out_array;
            $counter++;
        } else {
            set_msg_err("Error: could not do axfr-get for $domain:<br>".$out_array[0]);
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
        $q = "insert into domains (domain,status) values('".mysql_escape_string($domain)."', 'active')";
        mysql_query($q) or die(mysql_error());
        $domain_id = get_dom_id($domain);

        while(list($line_key,$value) = each($line)) {
            if($line_key != 'domain' && !ereg("^#", $value)) {
                $result = parse_dataline($value);
                if(is_array($result)) {
		    if ((isset($_REQUEST['default_soa']) && $_REQUEST['default_soa']=="on") && ($result['type']=='S')) {
		     $result['val']=$def_soa['val'];
		     $result['host']=$def_soa['host'];
		    }
		    if ((isset($_REQUEST['default_ns']) && $_REQUEST['default_ns']!="on") || ($result['type']!='N')) {
                     $q = "insert into records 
                         (domain_id,host,type,val,distance,ttl) 
                         values(
                             $domain_id,
                             '".mysql_escape_string(ereg_replace("[\]052", "*", $result['host']))."',
                             '".$result['type']."',
                             '".mysql_escape_string($result['val'])."',
                             '".$result['distance']."',
                             '".$result['ttl']."')";
                     mysql_query($q) or die(mysql_error().$q);
		    }
                }
            }
	}
        if (isset($_REQUEST['default_ns']) && $_REQUEST['default_ns']=="on") {
	 $counter=0;
         while ($ns = $def_ns[$counter]) {
	  $host = ereg_replace("DOMAIN", $domain, $ns['host']);
          $q = "insert into records 
                (domain_id,host,type,val,distance,ttl) 
                values(
                $domain_id,
                '".mysql_escape_string($host)."',
                'N',
                '".mysql_escape_string($ns['val'])."',
                '".$ns['distance']."',
                '".$ns['ttl']."')";
          mysql_query($q) or die(mysql_error().$q);	  
	  $counter++;
	 }
	}
        $log_entry = "imported via axfr from ".$_REQUEST['hostname'];
        dns_log($domain_id,$log_entry);
    }
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
        $result = mysql_query($q) or die(mysql_error());
        if(mysql_num_rows($result) == 0) {
            set_msg_err("Error: ".$_REQUEST['domain']." is not in the database");
            $smarty->display('header.tpl');
            $smarty->display('footer.tpl');
            exit;
        }
        $row = mysql_fetch_array($result);

        // Get owner info
        $q = "select * from accounts where cid='".$row['owner_id']."' limit 1";
        $result = mysql_query($q) or die(mysql_error());
        if(mysql_num_rows($result) > 0) {
            $owner_row = mysql_fetch_array($result);
        } else {
            $owner_row = NULL;
        }

        // Get group owner info
        $q = "select * from accounts where cid='".$row['group_owner_id']."' limit 1";
        $result = mysql_query($q) or die(mysql_error());
        if(mysql_num_rows($result) > 0) {
            $group_owner_row = mysql_fetch_array($result);
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
            $result = mysql_query($q) or die(mysql_error());
            if(mysql_num_rows($result) == 0) {
                set_msg_err("Error: ".$_REQUEST['email_address']." is not in the database");
                $smarty->display('header.tpl');
                $smarty->display('footer.tpl');
                exit;
            }

            if($user_info['Account_Type'] == 'senior_admin' && isset($_REQUEST['group_email_address']) && $_REQUEST['group_email_address'] != '') {
                $group_owner_id = get_cid(strtolower($_REQUEST['group_email_address']));
                $q = "select 'Email' from accounts where cid='$group_owner_id' and Account_Type='group_admin'";
                $result = mysql_query($q) or die(mysql_error());
                if(mysql_num_rows($result) == 0) {
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
            $result = mysql_query($q) or die(mysql_error());

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
