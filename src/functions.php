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





// FUNCTIONS

function authenticate_user($email, $password) {
    global $timeout;

    // Garbage collection for sessions
    $oldsessions = time()-$timeout;
    mysql_query("delete from active_sessions where time < $oldsessions");
    $result = mysql_query("select Email from accounts where 
        Email='".mysql_real_escape_string(strtolower($email))."' and 
        Password='".md5($password)."' and
        Status='active' LIMIT 1") or die(mysql_error());
    $resultarray = mysql_fetch_array($result);
    if($resultarray['Email'] != "") {
        // Kill any other sessions by this user
        mysql_query("delete from active_sessions where email='".
            mysql_real_escape_string($email)."'") or die("error logging in");
        mysql_query("insert into active_sessions (
            sid, 
            email, 
            time) 

            VALUES (
            '".session_id()."', 
            '".mysql_real_escape_string($email)."', 
            '".time()."')");
        return 'TRUE';
    } else {
        return 'FALSE';
    }
}

function verify_session() {
    global $timeout;
    $querystring = "select email from active_sessions where 
        sid='".session_id()."' and 
        time > '".(time() - $timeout)."' LIMIT 1";
    $result = mysql_query($querystring);
    $resultarray = mysql_fetch_array($result);
    if($resultarray['email'] != "") {
        return $resultarray['email'];
    } else {
        return "";
    }
}

function get_cid($email) {

    $cid_result = mysql_query("select cid from accounts where Email='$email'")
        or die(mysql_error());
    if(mysql_num_rows($cid_result) == 0) {
        return NULL;
    } else {
        $cid = mysql_result($cid_result,0);
        return $cid;
    }

}

function get_owner_name($id) {

    $result = mysql_query("select First_Name, Last_Name from accounts where cid='$id'")
        or die(mysql_error());
    if(mysql_num_rows($result) > 0) {
        $row = mysql_fetch_array($result);
        return $row['First_Name']." ".$row['Last_Name'];
    } else {
        return 'none';
    }

}

function get_groupowner_name($gid) {

    $result = mysql_query("select First_Name, Last_Name from accounts where cid='$gid'")
        or die(mysql_error());
    if(mysql_num_rows($result) > 0) {
        $row = mysql_fetch_array($result);
        return $row['First_Name']." ".$row['Last_Name'];
    } else {
        return 'none';
    }

}

function get_groupowner_email($gid) {

    $result = mysql_query("select Email from accounts where cid='$gid'")
        or die(mysql_error());
    if(mysql_num_rows($result) > 0) {
        $row = mysql_fetch_array($result);
        return $row['Email'];
    } else {
        return 'none';
    }

}

function check_email_format($address) {

    $result = ereg("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*$", 
        strtolower($address));
    return $result;

}

function check_domain_name_format($name) {

    // Hack to allow for DOMAIN substitutions in default records
    $name = ereg_replace('DOMAIN', 'test.com', $name);

    if(ereg('\.\.', $name)) {
        return FALSE;
    } else {
        $result = eregi("^[\*\.a-z0-9-]+\.[a-z0-9-]+$", strtolower($name));
        return $result;
    }

}

function check_first_use() {

    $result = mysql_query("show tables") or die(mysql_error());
    if(mysql_num_rows($result) == 0) return 1;
    return 0;

}

function get_dom_id($domain) {

    $q = "select domain_id from domains where domain='$domain'";
    $result = mysql_query($q) or die(mysql_error());

    if(mysql_num_rows($result) == 0) {
        return -1;
    } else {
        return mysql_result($result,0);
    }

}

function dns_log($domain_id,$entry) {

    global $user_info;
    $name = $user_info['First_Name']." ".$user_info['Last_Name'];
    $q = "insert into log (cid,Email,Name,domain_id,entry,time) values(
        ".$user_info['cid'].",
        '".mysql_escape_string($user_info['Email'])."',
        '".mysql_escape_string($name)."',
        '".mysql_escape_string($domain_id)."',
        '".mysql_escape_string($entry)."',
        ".time().")";
    mysql_query($q) or die(mysql_error());

}

function get_type($type) {

    if($type == 'S') return 'SOA';
    if($type == 'N') return 'NS';
    if($type == 'A') return 'A';
    if($type == 'M') return 'MX';
    if($type == 'P') return 'PTR';
    if($type == 'T') return 'TXT';
    if($type == 'C') return 'CNAME';

}

function set_type($type) {

    if($type == 'SOA') return 'S';
    if($type == 'NS') return 'N';
    if($type == 'A') return 'A';
    if($type == 'MX') return 'M';
    if($type == 'PTR') return 'P';
    if($type == 'TXT') return 'T';
    if($type == 'CNAME') return 'C';

}

function validate_ip($ip) {

    $return = TRUE;
    $tmp = explode(".", $ip);
    if(count($tmp) < 4) {
        $return = FALSE;
    } else {
        foreach($tmp AS $sub) {
            if($return != FALSE) {
                if(!eregi("^([0-9])", $sub)) {
                    $return = FALSE;
                } else if($sub > 255){
                    $return = FALSE;
                } else {
                    $return = TRUE;
                }
            }
        }
    }
    return $return;
}

function verify_record($name,$type,$address,$distance,$ttl) {

    // convert type to single character format
    $type = set_type($type);

    // Make sure name was given for non A and MX records
    if($type != 'A' && $type != 'M' && $name == "") 
        return "no Hostname supplied";

    // verify A record
    if($type == 'A') {
        if(validate_ip($address) == FALSE) {
            return "\"$address\" is not a valid A record address";
        }
        if(check_domain_name_format($name) == FALSE) {
            return "\"$name\" is not a valid A record name";
        }
    }

    // verify NS record
    if($type == 'N') {
        if(validate_ip($address) != FALSE) {
            return "\"$address\" should not be an IP address";
        }
        if(check_domain_name_format($name) == FALSE) {
            return "\"$name\" is not a valid NS record name";
        }
    }

    // verify MX record
    if($type == 'M') {
        if(validate_ip($name)) {
            return "MX records can not be an IP address";
        }
        if(check_domain_name_format($name) == FALSE) {
            return "\"$name\" is not a valid MX record name";
        }
        if(!eregi("^([0-9])+$", $distance)) 
            return "\"$distance\" is not a valid MX distance";
    }

    // verify PTR
    if($type == 'P') {
       if(!eregi("^.*\.in-addr.arpa\.*$", $name))
            return "PTR \"$name\" does not end in .in-addr.arpa.";
    }

    // verify CNAME record
    if($type == 'C') {
        if(validate_ip($address)) {
            return "CNAME records can not point to an IP address";
        }
        if(check_domain_name_format($name) == FALSE) {
            return "\"$name\" is not a valid CNAME record name";
        }
    }

    // make sure a TTL was given
    if($ttl == "") return "no TTL given";

    return 'OK';

}

function parse_soa($soa) {

    $email_soa = explode(":", $soa['host']);
    if (isset($email_soa[0])) {
        $array['tldemail'] = $email_soa[0];
    }
    if (isset($email_soa[1])) {
        $array['tldhost'] = $email_soa[1];
    }
    $ttls_soa = explode(":", $soa['val']);
    // ttl
    if(!isset($soa['ttl']) || $soa['ttl'] == "") {
        $array['ttl'] = 86400;
    } else {
        $array['ttl'] = $soa['ttl'];
    }
    // refresh
    if(!isset($ttls_soa[0]) || $ttls_soa[0] == "") {
        $array['refresh'] = 16384;
    } else {
        $array['refresh'] = $ttls_soa[0];
    }
    // retry
    if (!isset($ttls_soa[1]) || $ttls_soa[1] == "") {
        $array['retry'] = 2048;
    } else {
        $array['retry'] = $ttls_soa[1];
    }
    // expiration
    if (!isset($ttls_soa[2]) || $ttls_soa[2] == "") {
        $array['expire'] = 1048576;
    } else {
        $array['expire'] = $ttls_soa[2];
    }
    // min
    if(!isset($ttls_soa[3]) || $ttls_soa[3] == "") {
        $array['minimum'] = 2560;
    } else {
        $array['minimum'] = $ttls_soa[3];
    }

    return $array;

}

function set_edit_id($user_info) {

    if($user_info['Account_Type'] == 'user') {
        return $user_info['cid'];
    } else if($user_info['Account_Type'] == 'group_admin' ||
            $user_info['Account_Type'] == 'senior_admin') {
        return $_REQUEST['cid'];
    }

}

function get_account_info($id) {
    $q = "select * from accounts where cid=$id";
    $result = mysql_query("select * from accounts where cid=$id")
        or die(mysql_error());
    return mysql_fetch_array($result);
}

function build_data_line($row,$domain) {

    if($row['type'] == 'A') {
        $s = "+".$row['host'].":".$row['val'].":".$row['ttl']."\n";
    } else if($row['type'] == 'M') {
        $s = "@".$row['host']."::".$row['val'].":".$row['distance'].":".$row['ttl']."\n";
    } else if($row['type'] == 'N') {
        $s = "&".$row['host']."::".$row['val'].":".$row['ttl']."\n";
    } else if($row['type'] == 'P') {
        $s = "^".$row['host'].":".$row['val'].":".$row['ttl']."\n";
    } else if($row['type'] == 'T') {
        $s = "'".$row['host'].":".str_replace(":",'\072', $row['val']).":".$row['ttl']."\n";
    } else if($row['type'] == 'C') {
        $s = "C".$row['host'].":".$row['val'].":".$row['ttl']."\n";
    } else if($row['type'] == 'S') {
        $soa = parse_soa($row);
        $s = "Z".$domain.":".$soa['tldhost'].":".$soa['tldemail']."::".$soa['refresh'].":".$soa['retry'].":".$soa['expire'].":".$soa['minimum'].":".$soa['ttl']."\n";
    } else {
        $s = "\n";
    }

    return $s;

}

function parse_dataline($line) {

    // Strip first char
    $stripped = ereg_replace("^.", "", $line);
    $array = explode(":", $stripped);
    $out_array = '';

    // Format the array according to the type
    if(strncmp('+', $line, 1) == 0) {
        $out_array['host'] = $array[0];
        $out_array['type'] = 'A';
        $out_array['val'] = $array[1];
        $out_array['distance'] = '';
        $out_array['ttl'] = $array[2];
    } else if(strncmp('C', $line, 1) == 0) {
        $out_array['host'] = $array[0];
        $out_array['type'] = 'C';
        $out_array['val'] = $array[1];
        $out_array['distance'] = '';
        $out_array['ttl'] = $array[2];
    } else if(strncmp('@', $line, 1) == 0) {
        $out_array['host'] = $array[0];
        $out_array['type'] = 'M';
        $out_array['val'] = $array[2];
        $out_array['distance'] = $array[3];
        $out_array['ttl'] = $array[4];
    } else if(strncmp('&', $line, 1) == 0) {
        $out_array['host'] = $array[0];
        $out_array['type'] = 'N';
        $out_array['val'] = $array[2];
        $out_array['distance'] = '';
        $out_array['ttl'] = $array[3];
    } else if(strncmp('Z', $line, 1) == 0) {
        $out_array['host'] = $array[2].":".$array[1];
        $out_array['type'] = 'S';
        $out_array['val'] = $array[4].":".$array[5].":".$array[6].":".$array[7];
        $out_array['distance'] = '';
        $out_array['ttl'] = $array[8];
    } else if(strncmp('^', $line, 1) == 0) {
        $out_array['host'] = $array[0];
        $out_array['type'] = 'P';
        $out_array['val'] = $array[1];
        $out_array['distance'] = '';
        $out_array['ttl'] = $array[2];
    } else if(strncmp(':', $line, 1) == 0) {
        // Is a leading colon, check the n field for the record type
        if($array[1] == '16') {
            // Is a TXT record
            $out_array['host'] = $array[0];
            $out_array['type'] = 'T';
            $out_array['val'] = $array[2];
            $out_array['distance'] = '';
            $out_array['ttl'] = $array[3];
        }
    }
    return $out_array;

}

function get_sortway($sortfield, $val, $sortway) {
    if($sortfield == $val) {
        if($sortway == 'asc') {
            return 'desc';
        } else {
            return 'asc';
        }
    } else {
        return 'asc';
    }
}

function set_msg_err($error) {
    $_SESSION['message'] = "<span class=\"error\">$error</span>";
}

function set_msg($msg) {
    $_SESSION['message'] = $msg;
}

// Set message from session
function display_msg() {
    if(isset($_SESSION['message'])) {
        echo stripslashes($_SESSION['message']);
        unset($_SESSION['message']);
    }
}

// END FUNCTIONS

?>
