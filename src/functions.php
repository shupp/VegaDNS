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





// FUNCTIONS

function pre($array) {
    echo "<pre>\n";
    print_r($array);
    echo "</pre>\n";
    exit;
}

function verify_session() {
    global $timeout, $db, $smarty;
    $q = "select email,time from active_sessions where 
        sid='".session_id()."' LIMIT 1";
    $result = $db->Execute($q) or die($db->ErrorMsg());
    $row = $result->FetchRow();
    if($row['email'] != "") {
        if($row['time'] > (time() - $timeout)) {
            $smarty->assign('logged_in', 1);
            return $row['email'];
        } else {
            return 'TIMEOUT';
        }
    } else {
        return '';
    }
}

function get_user_id($email) {

    global $db;
    $user_id_result = $db->Execute("select user_id from accounts where email='$email'")
        or die($db->ErrorMsg());
    if($user_id_result->RecordCount() == 0) {
        return NULL;
    } else {
        $row = $user_id_result->FetchRow();
        return $row['user_id'];
    }

}

function get_owner_name($id) {

    global $db;
    $q = "select first_name, last_name from accounts where user_id='$id'";
    $result = $db->Execute($q) or die($db->ErrorMsg());
    if($result->RecordCount() > 0) {
        $row = $result->FetchRow();
        return $row['first_name']." ".$row['last_name'];
    } else {
        return 'none';
    }

}

function get_groupowner_name($group_id) {

    global $db;
    $q = "select first_name, last_name from accounts where user_id='$group_id'";
    $result = $db->Execute($q) or die($db->ErrorMsg());
    if($result->RecordCount() > 0) {
        $row = $result->FetchRow();
        return $row['first_name']." ".$row['last_name'];
    } else {
        return 'none';
    }

}

function get_groupowner_email($group_id) {

    global $db;
    $result = $db->Execute("select email from accounts where user_id='$group_id'")
        or die($db->ErrorMsg());
    if($result->RecordCount() > 0) {
        $row = $result->FetchRow();
        return $row['email'];
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

    global $db;
    $result = $db->Execute("show tables") or die($db->ErrorMsg());
    if($result->RecordCount() == 0) return 1;
    return 0;

}

function get_dom_id($domain) {

    global $db;
    $q = "select domain_id from domains where domain='$domain'";
    $result = $db->Execute($q) or die($db->ErrorMsg());

    if($result->RecordCount() == 0) {
        return -1;
    } else {
        $row = $result->FetchRow();
        return $row['domain_id'];
    }

}

function dns_log($domain_id,$entry) {

    global $user_info, $db;
    $name = $user_info['first_name']." ".$user_info['last_name'];
    $q = "insert into log (user_id,group_id,email,Name,domain_id,entry,time) 
    values(
        ".$user_info['user_id'].",
        ".$_SESSION['group'].",
        ".$db->Quote($user_info['email']).",
        ".$db->Quote($name).",
        ".$db->Quote($domain_id).",
        ".$db->Quote($entry).",
        ".time().")";
    $db->Execute($q) or die($db->ErrorMsg());

}

function get_type($type) {

    global $use_ipv6;

    if($type == 'S') return 'SOA';
    if($type == 'N') return 'NS';
    if($type == 'A') return 'A';
    if($use_ipv6 == 'TRUE') {
        if($type == '3') return 'AAAA';
        if($type == '6') return 'AAAA+PTR';
    }
    if($type == 'M') return 'MX';
    if($type == 'P') return 'PTR';
    if($type == 'T') return 'TXT';
    if($type == 'C') return 'CNAME';
    if($type == 'V') return 'SRV';

}

function set_type($type) {

    global $use_ipv6;

    if($type == 'SOA') return 'S';
    if($type == 'NS') return 'N';
    if($type == 'A') return 'A';
    if($use_ipv6 == 'TRUE') {
        if($type == 'AAAA') return '3';
        if($type == 'AAAA+PTR') return '6';
    }
    if($type == 'MX') return 'M';
    if($type == 'PTR') return 'P';
    if($type == 'TXT') return 'T';
    if($type == 'CNAME') return 'C';
    if($type == 'SRV') return 'V';

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

if($use_ipv6 == 'TRUE') {
    function validate_ipv6($ip) {
          $class = new Net_IPv6;
          return $class->checkIPv6($ip);
    }

    function uncompress_ipv6($ip) {
            $class = new Net_IPv6;
            $ip = $class->uncompress($ip);
            $splitip = explode(":", $ip);
            for ($i=0, $max=sizeOf($splitip); $i<$max; $i++) {
                    $chunk =& $splitip[$i];
                    $length = strlen($chunk);
                    if ($length<4) {
                            $filler="";
                            for ($i=0; $i<(4-$length); $i++) $filler .= "0";
                            $chunk = $filler.$chunk;
                    }
            }
            $ip = implode(":", $splitip);
            return $ip;
    }
}


function verify_record($name,$type,$address,$distance,$weight,$port,$ttl) {

    global $use_ipv6;

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

    if($use_ipv6 == 'TRUE') {
        // verify AAAA record
        if($type == 'AAAA') {
            if(validate_ipv6ip($address) == FALSE) {
                return "\"$address\" is not a valid AAAA record address";
            }
            if(check_domain_name_format($name) == FALSE) {
                return "\"$name\" is not a valid AAAA record name";
            }
        }

        // verify AAAA+PTR record
        if($type == 'AAAA+PTR') {
            if(validate_ipv6ip($address) == FALSE) {
                return "\"$address\" is not a valid AAAA+PTR record address";
            }
            if(check_domain_name_format($name) == FALSE) {
                return "\"$name\" is not a valid AAAA+PTR record name";
            }
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
       if(!eregi("^.*\.in-addr\.arpa\.*$", $name))
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

	if (!eregi("^_.*\._.*$",$name))
		return"SRV \"$name\" should be in the format _service._protocol";	
	
	if (($distance > 65535) || !eregi("^([0-9])+$", $distance)) 
                return "SRV distance must be a numeric value between 0 and 65535";

	if (($weight > 65535) || !eregi("^([0-9])+$", $weight))
                return "SRV weight must be a numeric value between 0 and 65535";
        
	if (($port > 65535) || !eregi("^([0-9])+$", $port) ) 
                return "SRV port must be a numeric value between 0 and 65535";

    }

    // make sure a TTL was given
    if($ttl == "") return "no TTL given";

    return 'OK';

}

function parse_soa($soa) {

    $email_soa = explode(":", $soa['host']);
    $array['tldemail'] = $email_soa[0];
    $array['tldhost'] = $email_soa[1];

    $ttls_soa = explode(":", $soa['val']);
    // ttl
    if(!isset($soa['ttl']) || $soa['ttl']  == "") {
        $array['ttl'] = 86400;
    } else {
        $array['ttl'] = $soa['ttl'];
    }
    // refresh
    if($ttls_soa[0] == "") {
        $array['refresh'] = 16384;
    } else {
        $array['refresh'] = $ttls_soa[0];
    }
    // retry
    if($ttls_soa[1] == "") {
        $array['retry'] = 2048;
    } else {
        $array['retry'] = $ttls_soa[1];
    }
    // expiration
    if($ttls_soa[2] == "") {
        $array['expire'] = 1048576;
    } else {
        $array['expire'] = $ttls_soa[2];
    }
    // min
    if($ttls_soa[3] == "") {
        $array['minimum'] = 2560;
    } else {
        $array['minimum'] = $ttls_soa[3];
    }

    return $array;

}

function get_account_info($id) {

    global $db;
    $q = "select * from accounts where user_id=$id";
    $result = $db->Execute("select * from accounts where user_id=$id")
        or die($db->ErrorMsg());
    return $result->FetchRow();
}

/*
// DOPRY: old version of function
// DOPRY: making encode_rdata_octet and  encode_qname so it will be easier to incorporate new record types on same code.
function encode_srv_rdata($distance,$weight,$port,$target) {
	// priotity weight   port     qname
	// MSB LSB, MSB LSB, MSB LSB, LABEL_SEQUENCE

	$rdata = '';

        //pack data into 16 bit big-endian format just in case.
        $data = pack("nnn",$distance,$weight,$port);

        //get decimal value of individual bytes
        $bytes = unpack('C*',$data);

        //convert byte to oct pad to three characters and append to record
        foreach($bytes as $byte) $rdata .= "\\".str_pad(decoct($byte),3,0, STR_PAD_LEFT);

        //split the target into proper parts to become a proper QNAME see RFC1035 4.1.2
        $qnameparts = split('\.',$target);

        //write length octet, then characters... ( I think djbdbs handles converting them to octet... doesn't seem RFC compliant
        //but produces identical output to Rob Mayoff's SRV generator...);
        foreach ($qnameparts as $part)  $rdata .= "\\".str_pad(decoct(strlen($part)),3,0,STR_PAD_LEFT)."".$part;

        //add term octet for QNAME
       $rdata .= "\\000";

        return $rdata;
}
*/

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
        $qnameparts = split('\.',$hostname);

        // DOPRY: write length octet, then characters... ( I think djbdbs handles converting them to oct... doesn't seem RFC compliant
        //but produces identical output to Rob Mayoff's SRV generator...);

        foreach ($qnameparts as $part)  $qname .= "\\".str_pad(decoct(strlen($part)),3,0,STR_PAD_LEFT)."".$part;

        // DOPRY: add term octet for QNAME
        $qname .= "\\000";
        return $qname;
}
// DOPRY: end generic record  encoding functions

// DOPRY: begin generic record decoding functions
function decode_rdata_octets($octets) {
	$octs = split('[\\]',$octets);
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
				
                        default: die("decode_rdata: invalid format code: '$format_code'. 'c' or 'q' only");
                }
        }
        return $rdata;
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
    } else if($row['type'] == 'V') {
        $s = ":".$row['host'].":33:".encode_rdata('cccq',array($row['distance'],$row['weight'],$row['port'],$row['val'])).":".$row['ttl']."\n";
    } else {
        $s = "\n";
    }

    return $s;

}


function parse_dataline($line) {

    $out_array = NULL;

    // Strip first char
    $stripped = ereg_replace("^.", "", $line);
    $array = explode(":", $stripped);

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
            $out_array['val'] = octal_to_char($array[2]);
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

function octal_to_char($val) {
    // Disregard ETB
    $val = ereg_replace('\\023','',$val);
    // Convert spaces
    $val = ereg_replace('\\040',' ',$val);
    return $val;
}

function get_group_name($id) {

    global $db;

    $q = "select name from groups where group_id='$id'";
    $result = $db->Execute($q) or die($db->ErrorMsg());
    if($result->RecordCount() == 0) {
        return "";
    } else {
        $row = $result->FetchRow();
        return $row['name'];
    }

}

function array_trim($arr, $value) { 
    $newarray = array();
    while(list($key,$val) = each($arr)) {
        if($val != $value) {
            $newarray[$key] = $val;
        }
    }
    return $newarray; 
}

function set_msg_err($error) {
    $_SESSION['message'] = "<span class=\"error\">$error</span>";
}

// Set message from session
function set_msg($msg) {
    $_SESSION['message'] = $msg;
}

// Display message from session
function display_msg() {
    if(isset($_SESSION['message'])) {
        echo stripslashes($_SESSION['message']);
        unset($_SESSION['message']);
    }
}

function getmicrotime() { 
    list($usec, $sec) = explode(" ", microtime()); 
    return ((float)$usec + (float)$sec); 
} 

function display_execution_time() {
    global $start_time;
    echo number_format(getmicrotime() - $start_time, 2);
}

function get_request_sortway() {
    if (!isset($_REQUEST['sortway'])) {
        $sortway = "asc";
    } else if ( $_REQUEST['sortway'] == 'desc') {
        $sortway = 'desc';
    } else {
        $sortway = 'asc';
    }
    return $sortway;
}
        
function get_sortfield($mode) {
    if($mode == 'records') {
        $default_field = 'type';
    } else if($mode == 'domains') {
        $default_field = 'status';
    }

    if (!isset($_REQUEST['sortfield'])) {
        $sortfield = $default_field;
    } else {
        $sortfield = $_REQUEST['sortfield'];
    }

    return $sortfield;
}

// END FUNCTIONS

?>
