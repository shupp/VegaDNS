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





// Make sure this is a group_admin or a senior_admin

if($user_info['Account_Type'] == 'user') {
    set_msg_err("Error: you do not have permission to edit default resource records");
    $smarty->display('header.tpl');
    $smarty->display('footer.tpl');
    exit;
}

// Set cancel message if necessary
if(isset($_REQUEST['record_mode']) && $_REQUEST['record_mode'] == 'delete_cancelled') {
    set_msg("Delete Cancelled");
}

if(!isset($_REQUEST['record_mode'])) {

    // Get records list
    $sa_q = "select * from default_records where default_type='system'";
    $ga_q = "select * from default_records where group_owner_id='".$user_info['cid']."'";
    if($user_info['Account_Type'] == 'senior_admin') {
        $stmt = $pdo->query($sa_q) or die(print_r($pdo->errorInfo()));
    } else {
        $stmt = $pdo->query($ga_q) or die(print_r($pdo->errorInfo()));
        if($stmt->rowCount() == 0)
            $stmt = $pdo->query($sa_q) or die(print_r($pdo->errorInfo()));
    }

    // Build records data structure
    $counter = 0;
    while ($row = $stmt->fetch()) {
        $records[$counter]['record_id'] = $row['record_id'];
        $records[$counter]['host'] = $row['host'];
        $records[$counter]['type'] = $row['type'];
        $records[$counter]['val'] = $row['val'];
        $records[$counter]['distance'] = $row['distance'];
        $records[$counter]['weight'] = $row['weight'];
        $records[$counter]['port'] = $row['port'];
        $records[$counter]['ttl'] = $row['ttl'];
        $counter++;
    }

    // Get SOA
    while((list($num,$array) = each($records)) && !isset($soa)) {
        if($array['type'] == 'S') $soa = $array;
    }
    reset($records);

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
    }

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
                $out_array[$counter]['port'] = $array['port'];
            } else {
                $out_array[$counter]['weight'] = 'n/a';
                $out_array[$counter]['port'] = 'n/a';
            }

            $out_array[$counter]['ttl'] = $array['ttl'];
            $out_array[$counter]['delete_url'] = "$base_url&mode=default_records&record_mode=delete&record_id=".$array['record_id'];
            $counter++;
        }
    }

    $smarty->assign('edit_soa_url', "$base_url&mode=default_records&record_mode=edit_soa");
    $smarty->assign('add_record_url', "$base_url&mode=default_records&record_mode=add_record");
    $smarty->assign('soa_array', $soa_array);
    if(isset($out_array))
        $smarty->assign('out_array', $out_array);

    $smarty->display('header.tpl');
    $smarty->display('list_default_records.tpl');
    $smarty->display('footer.tpl');
    exit;

} else if($_REQUEST['record_mode'] == 'add_record') {

    $smarty->display('header.tpl');
    require('src/add_record_form.php');
    $smarty->display('footer.tpl');
    exit;

} else if($_REQUEST['record_mode'] == 'add_record_now') {

    // verify record to be added
    $result = verify_record($_REQUEST['name'],$_REQUEST['type'],$_REQUEST['address'],$_REQUEST['distance'],$_REQUEST['weight'], $_REQUEST['port'],$_REQUEST['ttl']);
    if($result != 'OK') {
        set_msg_err(htmlentities($result, ENT_QUOTES));
        $smarty->display('header.tpl');
        require('src/add_record_form.php');
        $smarty->display('footer.tpl');
        exit;
    } else {


        // Get default_type
        if($user_info['Account_Type'] == 'senior_admin') {
            $default_type = 'system';
        } else {
            $default_type = 'group';
        }

        // Add domain to 'name'
        if(!preg_match('/^.*\.(DOMAIN)$/i', $_REQUEST['name']) && !preg_match('/^(DOMAIN)$/i', $_REQUEST['name'])
            && $_REQUEST['type'] != 'PTR') {
            if(strlen($_REQUEST['name']) > 0) {
                $name = $_REQUEST['name'].".DOMAIN";
            } else {
                $name = 'DOMAIN';
            }
        } else {
            $name = $_REQUEST['name'];
        }

        $params = array();
        if($_REQUEST['type'] == 'A') {
            $params[':address'] = $_REQUEST['address'];
            $q = "insert into default_records
            (group_owner_id,host,type,val,ttl,default_type) values(
            '".$user_info['cid']."',
            '$name',
            '".set_type($_REQUEST['type'])."',
            :address,
            '".$_REQUEST['ttl']."',
            '$default_type')";
        } else if($_REQUEST['type'] == 'AAAA') {
            $address = uncompress_ipv6($_REQUEST['address']);
            $params[':address'] = $address;
            $q = "insert into default_records
            (group_owner_id,host,type,val,ttl,default_type) values(
            '".$user_info['cid']."',
            '$name',
            '".set_type($_REQUEST['type'])."',
            :address,
            '".$_REQUEST['ttl']."',
            '$default_type')";
        } else if($_REQUEST['type'] == 'MX') {
            if(!preg_match('/\..+$/', $_REQUEST['address'])) {
                $mxaddress = $_REQUEST['address'].".DOMAIN";
            } else {
                $mxaddress = $_REQUEST['address'];
            }
            $params[':mxaddress'] = $mxaddress;
            $params[':distance']  = $_REQUEST['distance'];
            $q = "insert into default_records
            (group_owner_id,host,type,val,distance,ttl,default_type) values(
            '".$user_info['cid']."',
            '$name',
            '".set_type($_REQUEST['type'])."',
            :mxaddress,
            :distance,
            '".$_REQUEST['ttl']."',
            '$default_type')";
        } else if($_REQUEST['type'] == 'NS') {
            $params[':address'] = $_REQUEST['address'];
            $q = "insert into default_records
            (group_owner_id,host,type,val,ttl,default_type) values(
            '".$user_info['cid']."',
            '$name',
            '".set_type($_REQUEST['type'])."',
            :address,
            '".$_REQUEST['ttl']."',
            '$default_type')";
        } else if($_REQUEST['type'] == 'CNAME') {
            $params[':address'] = $_REQUEST['address'];
            $q = "insert into default_records
            (group_owner_id,host,type,val,ttl,default_type) values(
            '".$user_info['cid']."',
            '$name',
            '".set_type($_REQUEST['type'])."',
            :address,
            '".$_REQUEST['ttl']."',
            '$default_type')";
        } else if($_REQUEST['type'] == 'TXT') {
            $params[':address'] = $_REQUEST['address'];
            $q = "insert into default_records
            (group_owner_id,host,type,val,ttl,default_type) values(
            '".$user_info['cid']."',
            '$name',
            '".set_type($_REQUEST['type'])."',
            :address,
            '".$_REQUEST['ttl']."',
            '$default_type')";
        } else if ($_REQUEST['type'] == 'SRV') {
            if(!preg_match('/\..+$/', $_REQUEST['address'])) {
                $srvaddress = $_REQUEST['address'].".DOMAIN";
            } else {
                $srvaddress = $_REQUEST['address'];
            }

            $params[':srvaddress'] = $srvaddress;
            $params[':distance']   = $_REQUEST['distance'];
            $params[':weight']     = $_REQUEST['weight'];
            $params[':port']       = $_REQUEST['port'];
            $q = "insert into default_records
                (group_owner_id,host,type,val,distance,weight,port,ttl,default_type) values (
                '".$user_info['cid']."',
                '$name',
                '".set_type($_REQUEST['type'])."',
                :srvaddress,
                :distance,
                :weight,
                :port,
                '".$_REQUEST['ttl']."',
                '$default_type')";
        } else if($_REQUEST['type'] == 'SPF') {
            $params[':address'] = $_REQUEST['address'];
            $q = "insert into default_records
            (group_owner_id,host,type,val,ttl,default_type) values(
            '".$user_info['cid']."',
            '$name',
            '".set_type($_REQUEST['type'])."',
            :address,
            '".$_REQUEST['ttl']."',
            '$default_type')";
        }
        $stmt = $pdo->prepare($q);
        $stmt->execute($params) or die(print_r($stmt->errorInfo()));
        set_msg("Record added successfully!");
        header("Location: $base_url&mode=default_records");
        exit;
    }

} else if($_REQUEST['record_mode'] == 'delete') {

    // Get record info
    $q = "select * from default_records where record_id='".$_REQUEST['record_id']."' limit 1";
    $stmt = $pdo->query($q) or die(print_r($pdo->errorInfo()));
    $row = $stmt->fetch();

    $smarty->assign('type', get_type($row['type']));
    $smarty->assign('host', $row['host']);
    $smarty->assign('cancel_url', "$base_url&mode=default_records&record_mode=delete_cancelled");
    $smarty->assign('delete_url', "$base_url&mode=default_records&record_mode=delete_now&record_id=".$row['record_id']);
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

    if($user_info['Account_Type'] == 'senior_admin') {
        $q = "delete from default_records where record_id='".$_REQUEST['record_id']."'";
    } else {
        $q = "delete from default_records where record_id='".$_REQUEST['record_id']."' and group_owner_id='".$user_info['cid']."'";
    }
    $pdo->query($q) or die(print_r($pdo->errorInfo()));
    set_msg("Record deleted successfully");
    header("Location: $base_url&mode=default_records");
    exit;

} if($_REQUEST['record_mode'] == 'edit_soa') {

    // Get Current SOA information

    // Get records list
    $sa_q = "select * from default_records where default_type='system' and type='S'";
    $ga_q = "select * from default_records where group_owner_id='".$user_info['cid']."' and type='S'";
    if($user_info['Account_Type'] == 'senior_admin') {
        $stmt = $pdo->query($sa_q) or die(print_r($pdo->errorInfo()));
    } else {
        $stmt = $pdo->query($ga_q) or die(print_r($pdo->errorInfo()));
        if($stmt->rowCount() == 0)
            $stmt = $pdo->query($sa_q) or die(print_r($pdo->errorInfo()));
    }

    $row = $stmt->fetch();
    $soa = parse_soa($row);

    // Edit SOA Menu
    $smarty->display('header.tpl');
    require('src/edit_default_soa_form.php');
    $smarty->display('footer.tpl');
    exit;

} if($_REQUEST['record_mode'] == 'edit_soa_now') {


    // Check email
    if(!isset($_REQUEST['contactaddr']) || $_REQUEST['contactaddr'] == "") {
        set_msg_err("Error: missing contact address");
        $smarty->display('header.tpl');
        require('src/edit_default_soa_form.php');
        $smarty->display('footer.tpl');
        exit;
    }

    // See if this group_admin has an existing soa
    if($user_info['Account_Type'] == 'group_admin') {
        $default_type = 'group';
        $q = "select record_id from default_records where type='S' and group_owner_id='".$user_info['cid']."' limit 1";
        $stmt = $pdo->query($q) or die(print_r($pdo->errorInfo()));
        if($stmt->rowCount() == 0) {
            $new_soa = 1;
        } else {
            $new_soa = 0;
            $row = $stmt->fetch();
            $id = $row[0];
        }
    } else {
        $default_type = 'system';
        $q = "select record_id from default_records where type='S' and default_type='system' limit 1";
        $stmt = $pdo->query($q) or die(print_r($pdo->errorInfo()));
        $row = $stmt->fetch();
        $id = $row[0];
        $new_soa = 0;
    }
    // Build array from $_REQUEST
    $array['host'] = $_REQUEST['contactaddr'].':'.$_REQUEST['primary_name_server'];
    $array['val'] = $_REQUEST['refresh'].':'.$_REQUEST['retry'].':'.$_REQUEST['expire'].':'.$_REQUEST['minimum'];

    $return = parse_soa($array);

    // Build host and val fields
    $host = $return['tldemail'].':'.$return['tldhost'];
    $val = $return['refresh'].':'.$return['retry'].':'.$return['expire'].':'.$return['minimum'];

    // Update table
    $params = array();
    if($new_soa == 1) {
        $params[':host'] = $host;
        $params[':val']  = $val;
        $params[':ttl']  = $_REQUEST['ttl'];
        $q = "insert into default_records values(
            '',
            ".$user_info['cid'].",
            :host,
            'S',
            :val,
            0,,,
            :ttl,
            'group')";
    } else {
        $params[':host'] = $host;
        $params[':val']  = $val;
        $params[':ttl']  = $_REQUEST['ttl'];
        $params[':default_type'] = $default_type;
        $q = "replace into default_records set
            record_id='$id',
            host=:host,
            type='S',
            val=:val,
            ttl=:ttl,
            default_type=:default_type,
            group_owner_id='".$user_info['cid']."'";
    }
    $stmt = $pdo->prepare($q);
    $stmt->execute($params) or die(print_r($stmt->errorInfo()));

    // Display domain
    set_msg("Default SOA record updated successfully");
    header("Location: $base_url&mode=default_records");
    exit;

} else {

    die("Error: illegal records_mode");

}

?>
