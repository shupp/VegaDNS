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





// FUNCTIONS

function authenticate_user($email, $password) {
    global $timeout;

    $pdo = VDB::singleton();

    // Garbage collection for sessions
    $oldsessions = time()-$timeout;
    $pdo->query("delete from active_sessions where time < $oldsessions");
    $params = array(':email' => strtolower($email));
    $q = "select Email from accounts where
        Email=:email and
        Password='".md5($password)."' and
        Status='active' LIMIT 1";
    $stmt = $pdo->prepare($q);
    $stmt->execute($params) or die(print_r($stmt->errorInfo()));
    $resultarray = $stmt->fetch();
    if($resultarray['Email'] != "") {
        // Kill any other sessions by this user
        $q = "delete from active_sessions where email=:email";
        $stmt = $pdo->prepare($q);
        $stmt->execute($params) or die("error logging in");
        $q = "insert into active_sessions (
            sid,
            email,
            time)

            VALUES (
            '".session_id()."',
            :email,
            '".time()."')";
        $stmt = $pdo->prepare($q);
        $stmt->execute($params) or die(print_r($stmt->errorInfo()));
        return 'TRUE';
    } else {
        return 'FALSE';
    }
}

function verify_session() {
    global $timeout;

    $pdo = VDB::singleton();

    $querystring = "select email from active_sessions where
        sid='".session_id()."' and
        time > '".(time() - $timeout)."' LIMIT 1";
    $stmt = $pdo->query($querystring);
    $resultarray = $stmt->fetch();
    if($resultarray['email'] != "") {
        return $resultarray['email'];
    } else {
        return "";
    }
}

function get_cid($email) {

    $pdo = VDB::singleton();

    $cid_result = $pdo->query("select cid from accounts where Email='$email'")
        or die(print_r($pdo->errorInfo()));
    if($cid_result->rowCount() == 0) {
        return NULL;
    } else {
        $row = $cid_result->fetch();
        $cid = $row[0];
        return $cid;
    }

}

function get_owner_name($id) {

    $pdo = VDB::singleton();

    $result = $pdo->query("select First_Name, Last_Name from accounts where cid='$id'")
        or die(print_r($pdo->errorInfo()));
    if($result->rowCount() > 0) {
        $row = $result->fetch();
        return $row['First_Name']." ".$row['Last_Name'];
    } else {
        return 'none';
    }

}

function get_groupowner_name($gid) {

    $pdo = VDB::singleton();

    $result = $pdo->query("select First_Name, Last_Name from accounts where cid='$gid'")
        or die(print_r($pdo->errorInfo()));
    if($result->rowCount() > 0) {
        $row = $result->fetch();
        return $row['First_Name']." ".$row['Last_Name'];
    } else {
        return 'none';
    }

}

function get_groupowner_email($gid) {

    $pdo = VDB::singleton();

    $result = $pdo->query("select Email from accounts where cid='$gid'")
        or die(print_r($pdo->errorInfo()));
    if($result->rowCount() > 0) {
        $row = $result->fetch();
        return $row['Email'];
    } else {
        return 'none';
    }

}

function check_email_format($address) {

    $result = preg_match('/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*$/',
        strtolower($address));
    return $result;

}

function check_domain_name_format($name) {

    // Hack to allow for DOMAIN substitutions in default records
    $name = preg_replace('/DOMAIN/', 'test.com', $name);

    if(preg_match('/\.\./', $name)) {
        return FALSE;
    } else {
        $result = preg_match('/^[\*\.a-z0-9-\/]+\.[a-z0-9-]+[\.]{0,1}$/i', strtolower($name));
        return $result;
    }

}

function check_first_use() {

    $pdo = VDB::singleton();

    $result = $pdo->query("show tables") or die(print_r($pdo->errorInfo()));
    if($result->rowCount() == 0) return 1;
    return 0;

}

function get_dom_id($domain) {

    $pdo = VDB::singleton();
    $q = "select domain_id from domains where domain='$domain'";
    $result = $pdo->query($q) or die(print_r($pdo->errorInfo()));

    if($result->rowCount() == 0) {
        return -1;
    } else {
        $row = $result->fetch();
        return $row[0];
    }

}

function dns_log($domain_id,$entry) {

    global $user_info;
    $pdo = VDB::singleton();
    $name = $user_info['First_Name']." ".$user_info['Last_Name'];
    $params = array(
        ':email'     => $user_info['Email'],
        ':name'      => $name,
        ':domain_id' => $domain_id,
        ':entry'     => $entry
    );
    $q = "insert into log (cid,Email,Name,domain_id,entry,time) values(
        ".$user_info['cid'].",
        :email,
        :name,
        :domain_id,
        :entry,
        ".time().")";
    $stmt = $pdo->prepare($q);
    $stmt->execute($params) or die(print_r($stmt->errorInfo()));

}

function get_type($type) {

    if($type == 'S') return 'SOA';
    if($type == 'N') return 'NS';
    if($type == 'A') return 'A';
    if($type == '3') return 'AAAA';
    if($type == '6') return 'AAAA+PTR';
    if($type == 'M') return 'MX';
    if($type == 'P') return 'PTR';
    if($type == 'T') return 'TXT';
    if($type == 'C') return 'CNAME';
    if($type == 'V') return 'SRV';
    if($type == 'F') return 'SPF';

}

function set_type($type) {

    if($type == 'SOA') return 'S';
    if($type == 'NS') return 'N';
    if($type == 'A') return 'A';
    if($type == 'AAAA') return '3';
    if($type == 'AAAA+PTR') return '6';
    if($type == 'MX') return 'M';
    if($type == 'PTR') return 'P';
    if($type == 'TXT') return 'T';
    if($type == 'CNAME') return 'C';
    if($type == 'SRV') return 'V';
    if($type == 'SPF') return 'F';

}

function validate_ip($ip) {

    $return = TRUE;
    $tmp = explode(".", $ip);
    if(count($tmp) < 4) {
        $return = FALSE;
    } else {
        foreach($tmp AS $sub) {
            if($return != FALSE) {
                if(!preg_match('/^([0-9])/i', $sub)) {
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

function validate_ipv6($ip) {
    // Singleton
    static $class = null;
    if ($class === null) {
        $class = new Net_IPv6;
    }
    return $class->checkIPv6($ip);
}

function uncompress_ipv6($ip) {
    // Singleton
    static $class = null;
    if ($class === null) {
        $class = new Net_IPv6;
    }
    return $class->uncompress($ip, true);
}

function ipv6_to_octal($ip) {
    $ip = uncompress_ipv6($ip);
    $out = '';
    foreach (explode(':', $ip) as $part) {
        $oneAndTwo    = $part[0] . $part[1];
        $threeAndFour = $part[2] . $part[3];
        $out .= '\\' . str_pad(base_convert($oneAndTwo, 16, 8), 3, '0', STR_PAD_LEFT);
        $out .= '\\' . str_pad(base_convert($threeAndFour, 16, 8), 3, '0', STR_PAD_LEFT);
    }
    return $out;
}

function ipv6_to_ptr_record($ip, $domain, $ttl) {
    $ip    = uncompress_ipv6($ip);
    $parts = array_reverse(explode(':', $ip));

    $characters = array();
    foreach ($parts as $part) {
        for ($i = 3; $i > -1; $i--) {
            $characters[] = $part[$i];
        }
    }

    return '^' . implode('.', $characters) . '.ip6.arpa:' . $domain . ':' . $ttl . "\n";
}

function verify_record($name,$type,$address,$distance,$weight,$port,$ttl) {

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

    // verify AAAA record
    if($type == '3') {
        if(validate_ipv6($address) == FALSE) {
            return "\"$address\" is not a valid AAAA record address";
        }
        if(check_domain_name_format($name) == FALSE) {
            return "\"$name\" is not a valid AAAA record name";
        }
    }

    // verify AAAA+PTR record
    if($type == '6') {
        if(validate_ipv6($address) == FALSE) {
            return "\"$address\" is not a valid AAAA+PTR record address";
        }
        if(check_domain_name_format($name) == FALSE) {
            return "\"$name\" is not a valid AAAA+PTR record name";
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
        if(!preg_match('/^([0-9])+$/i', $distance))
            return "\"$distance\" is not a valid MX distance";
    }

    // verify PTR
    if($type == 'P') {
       if(!preg_match('/^.*\.in-addr.arpa\.*$/i', $name))
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

    // verify SRV record
    if ($type == 'V')  {

    if (!preg_match('/^_.*\._.*$/i',$name))
        return"SRV \"$name\" should be in the format _service._protocol";

    if (($distance > 65535) || !preg_match('/^([0-9])+$/i', $distance))
        return "SRV distance must be a numeric value between 0 and 65535";

    if (($weight > 65535) || !preg_match('/^([0-9])+$/i', $weight))
        return "SRV weight must be a numeric value between 0 and 65535";

    if (($port > 65535) || !preg_match('/^([0-9])+$/i', $port) )
        return "SRV port must be a numeric value between 0 and 65535";
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
    // serial
    if(!isset($ttls_soa[4]) || $ttls_soa[4] == "") {
        $array['serial'] = '';
    } else {
        $array['serial'] = $ttls_soa[4];
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
    $pdo = VDB::singleton();
    $q = "select * from accounts where cid=$id";
    $result = $pdo->query("select * from accounts where cid=$id")
        or die(print_r($pdo->errorInfo()));
    return $result->fetch();
}


// DOPRY: begin generic  encoding functions
function encode_rdata_octets($value) {
    // DOPRY: Big Endian 16 bit MSB LSB encoding for decimal values to rdata octets(tinydns-data)

    // DOPRY: pack into 16 bit big endian, just in case.
    $data = pack("n",$value);

    // DOPRY: unpack bytes
    $bytes = unpack("Cmsb/Clsb", $data);

    // DOPRY: add the backslashes and pad string to length
    $octets = "\\".str_pad(decoct($bytes['msb']),3,0, STR_PAD_LEFT).
             "\\".str_pad(decoct($bytes['lsb']),3,0, STR_PAD_LEFT);

    return $octets;
}

function encode_rdata_qname($hostname) {
    // DOPRY: QNAME(RFC 1035 section 4.1.2) encoding for url to octets(tinydns-data)

    // DOPRY: split the hostname by . (need length of each element)
    $qnameparts = preg_split('/\./',$hostname);

    // DOPRY: write length octet, then characters... ( I think djbdbs handles converting them to oct... doesn't seem RFC compliant
    //but produces identical output to Rob Mayoff's SRV generator...);

    $qname = '';
    foreach ($qnameparts as $part)  $qname .= "\\".str_pad(decoct(strlen($part)),3,0,STR_PAD_LEFT)."".$part;

    // DOPRY: add term octet for QNAME
    $qname .= "\\000";
    return $qname;
}
// DOPRY: end generic record  encoding functions

// DOPRY: begin generic record decoding functions
function decode_rdata_octets($octets) {
    $octs = preg_split('/[\\]/',$octets);
    $data = pack("CC",octdec($octs[1]),octdec($octs[2]));
    $value = unpack("ndec",$data);
    return $value['dec'];

}

function decode_rdata_qname($qname) {
    $hostname = '';
    $pos = 0;

    //use len -4 to offet for terminating character
    $len = strlen($qname)-4;
    while ($pos < $len-4) {

        //position + 1 ot offset for backslash
        $element_length = substr($qname,$pos+1,3);
        $element_length = octdec($element_length);

        // move position past the length identifier
        $pos += 4;

        // get substr
         $hostname .= substr($qname,$pos,$element_length).".";

        //move position to end of element.
        $pos += $element_length;

    }
    return $hostname;
}

// DOPRY: generic rdata encoding function for tinydns-data
// format is a string indicating value types octets(c) or qname(q)
//  ex) for SRV records $format='cccq';

function encode_rdata($format, $values) {
    $rdata = '';
    $len = strlen($format);
    if ($len != count($values))  die("encode_rdata: value count mismatch in format");
    for ($i = 0; $i < $len; $i++) {
        $format_code  =  substr($format,$i,1);
        switch ($format_code) {
            case 'c' : $rdata .= encode_rdata_octets($values[$i]); break;
            case 'q' : $rdata .= encode_rdata_qname($values[$i]); break;
            default: die("encode_rdata: invalid format code: '$format_code'. 'c' or 'q' only");
        }
    }
    return $rdata;
}

function decode_rdata($format, $value) {
    $rdata = array();
    $pos = 0;
    $len = strlen($format);
    for ($i = 0; $i < $len; $i++) {
        $format_code  =  substr($format,$i,1);
        switch ($format_code) {
            case 'c' :
                $octets = substr($value,$pos,8);
                $rdata[$i] =  decode_rdata_octets($octets);
                $pos += 8;
                break;
            case 'q' :
                if (!preg_match('/.+000/',$value,$qname,0,$pos)) die("decode_rdata: couldn't match qname at format position ".($i+1)."\n");
                print $qname[0]."\n";
                $rdata[$i] .= decode_rdata_qname($qname[0]);
                $pos += strlen($qname[0]);
                break;

            default:
                die("decode_rdata: invalid format code: '$format_code'. 'c' or 'q' only");
        }
    }
    return $rdata;
}


function build_data_line($row,$domain) {
    global $use_ipv6;

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
    } else if($row['type'] == 'F') {
        $val_str = str_replace(":",'\072', $row['val']);
        $s = ":".$row['host'].":99:\\".str_pad(decoct(strlen($row['val'])),3,0,STR_PAD_LEFT)."".$val_str.":".$row['ttl']."\n";
    } else if($row['type'] == 'C') {
        $s = "C".$row['host'].":".$row['val'].":".$row['ttl']."\n";
    } else if($row['type'] == 'S') {
        $soa = parse_soa($row);
        $s = "Z".$domain.":".$soa['tldhost'].":".$soa['tldemail'].":".$soa['serial'].":".$soa['refresh'].":".$soa['retry'].":".$soa['expire'].":".$soa['minimum'].":".$soa['ttl']."\n";
    } else if($row['type'] == 'V') {
        $s = ":".$row['host'].":33:".encode_rdata('cccq',array($row['distance'],$row['weight'],$row['port'],preg_replace('/\.$/', '', $row['val']))).":".$row['ttl']."\n";
    } else if(($row['type'] == '3' || $row['type'] == '6') && $use_ipv6) {
        $s = ":".$row['host'].":28:".ipv6_to_octal($row['val']).":".$row['ttl']."\n";
        if($row['type'] == '6') {
            $s .= ipv6_to_ptr_record($row['val'], $row['host'], $row['ttl']);
        }
    } else {
        $s = "\n";
    }

    return $s;

}


function parse_dataline($line) {

    // Strip first char
    $stripped = preg_replace('/^./', '', $line);
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
        $out_array['val'] = $array[4].":".$array[5].":".$array[6].":".$array[7].":".$array[3];
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
        if($array[1] == '99') {
            // Is a SPF record
            $out_array['host'] = $array[0];
            $out_array['type'] = 'F';
            $out_array['val'] = $array[2];
            $out_array['distance'] = '';
            $out_array['ttl'] = $array[3];
        }
        if($array[1] == '33') {
           // DOPRY:
           $out_array['host'] = $array[0];
           $out_array['type'] = 'V';

           // decode the rdata octets
           $srv_rdata = decode_rdata('cccq',$array[2]);
           $out_array['val'] = $srv_rdata[3];
           $out_array['distance'] = $srv_rdata[0];
           $out_array['weight'] = $srv_rdata[1];
           $out_array['port'] = $srv_rdata[2];
           // back to your regularly scheduled programming.

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
