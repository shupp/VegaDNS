<?php

/**
 * VegaDNS 
 * 
 * @uses Framework_Object_Web
 * @package VegaDNS
 * @copyright 2007 Bill Shupp
 * @author Bill Shupp <hostmaster@shupp.org> 
 * @license GPL 2.0  {@link http://www.gnu.org/licenses/gpl.txt}
 */

/**
 * VegaDNS 
 * 
 * This class contains all the low level record manipulation.
 * 
 * @uses Framework_Object_Web
 * @package VegaDNS
 * @copyright 2007 Bill Shupp
 * @author Bill Shupp <hostmaster@shupp.org> 
 * @license GPL 2.0  {@link http://www.gnu.org/licenses/gpl.txt}
 */
class VegaDNS extends Framework_Object_Web
{

    /**
     * _groupIDs 
     * 
     * Temporary storage for walking the groups array
     * 
     * @var mixed
     * @access private
     */
    private $_groupIDs = null;

    /**
     * defaultSOA 
     * 
     * @var array
     * @access public
     */
    public $defaultSOA = array(
                'serial'    => 'default',
                'refresh'   => 16384,
                'retry'     => 2048,
                'expire'    => 1048576,
                'minimum'   => 2560,
                'ttl'       => 86400);

    /**
     * types 
     * 
     * 
     * 
     * @var array
     * @access public
     */
    public $types = array(
                'S' => 'SOA',
                'S' => 'NS',
                'A' => 'A',
                '3' => 'AAAA',
                '6' => 'AAAA+PTR',
                'M' => 'MX',
                'P' => 'PTR',
                'T' => 'TXT',
                'C' => 'CNAME',
                'V' => 'SRV');

    /**
     * domainExists 
     * 
     * Check if a domain exists in the databse.
     * 
     * @param string $domain 
     * @access public
     * @throws Framework_Exception
     * @return TRUE on success, FALSE on failure
     */
    public function domainExists($domain)
    {
        $q = "SELECT * FROM domains WHERE domain=" . $this->db->Quote($domain) . " LIMIT 1";
        try {
            $result = $this->db->Execute($q);
        } catch (Exception $e) {
            throw new Framework_Exception($e->getMessage());
        }
        if ($result->RecordCount() > 0) {
            return TRUE;
        }
        return FALSE;
    }

    /**
     * getDomainInfo 
     * 
     * Return domain info
     * 
     * @param mixed $id 
     * @access public
     * @return ADODB results if exists, NULL if not
     */
    public function getDomainInfo($id)
    {
        $q = "SELECT * FROM domains WHERE domain_id=" . $this->db->Quote($id) . " LIMIT 1";
        try {
            $result = $this->db->Execute($q);
        } catch (Exception $e) {
            throw new Framework_Exception($e->getMessage());
        }
        if ($result->RecordCount() == 0) {
            return NULL;
        }
        return $result->FetchRow();
    }

    /**
     * getDomains 
     * 
     * Return an ADODBLite result object of domains.  Optionally return only the 
     * count of domains matching the query.
     * 
     * @ see function countDomains
     * @param mixed $start - offset
     * @param mixed $limit - limit
     * @param mixed $groups array of subgroups, or single groupID
     * @param mixed $countOnly return a count of matching domains
     * @param mixed $sortField what field to sort by
     * @param mixed $order asc or desc
     * @access public
     * @return ADODBLite result object
     * @throws Framework_Exception on failure
     */
    public function getDomains($start, $limit, $groups, $countOnly = NULL, $sortField = NULL, $order = NULL)
    {
        $groupquery = $this->_returnSubgroupsQuery($groups);
        $scope = $this->_getScopeQuery();
        $searchstring = is_null($scope) ? $this->_getSearchQuery('domain') : "";
        // print_r($groupquery);exit;

        if (!is_null($countOnly)) {
            $q = "SELECT COUNT(*)
                FROM domains a 
                LEFT JOIN groups b ON a.group_id = b.group_id 
                WHERE ($groupquery) $searchstring $scope";
            try {
                $result = $this->db->Execute($q);
            } catch (Exception $e) {
                throw new Framework_Exception($e->getMessage());
            }
        } else {
            $q = "SELECT a.*, b.group_id, b.name 
                FROM domains a 
                LEFT JOIN groups b ON a.group_id = b.group_id 
                WHERE ($groupquery) $searchstring $sq ";
            $q .= "ORDER BY $sortField $sortWay ".( ($sortField == "status") ? ", domain" : "" );

            try {
                $result = $this->db->SelectLimit($q, $limit, $start);
            } catch (Exception $e) {
                throw new Framework_Exception($e->getMessage());
            }
        }
        return $result;
    }

    /**
     * countDomains 
     * 
     * Shortcut for getDomains $countOnly = 1
     * 
     * @param mixed $groups
     * @access public
     * @return getDomain()
     */
    public function countDomains($groups)
    {
        return $this->getDomains(NULL, NULL, $groups, 1);
    }

    /**
     * _returnSubgroupsQuery 
     * 
     * get the subgroup part of the "where"  query
     * 
     * @see function getDomains
     * @param mixed $g 
     * @param mixed $string 
     * @access private
     * @return string
     */
    private function _returnSubgroupsQuery($groups)
    {
        $this->_groupIDs = array();
        array_walk_recursive($groups, array($this, '_getGroupIDs'));
        sort($this->_groupIDs);

        for ($count = 0; count($this->_groupIDs) > $count; $count++) {
            if ($count == 0) {
                $string = " a.group_id='{$this->_groupIDs[$count]}' ";
            } else {
                $string .= " or a.group_id='{$this->_groupIDs[$count]}'";
            }
        }
        return $string;
    }

    private function _getGroupIDs($item, $key)
    {
        if ($key == 'group_id') {
            $this->_groupIDs[$item] = $item;
        }
    }

    /**
     * _getSearchQuery 
     * 
     * figure out the search part o the getDomains/getRecords query based on $_REQUEST
     * 
     * @param mixed $type 'domain' or 'host'
     * @access private
     * @return string
     */
    private function _getSearchQuery($type)
    {
        if (empty($_REQUEST['search'])) {
            return;
        }

        $tempstring = preg_replace("/[*]/", "%", $_REQUEST['search']);
        $tempstring = preg_replace("/[ ]/", "%", $tempstring);
        
        if ($type = 'domain') {
            return " and domain like ".$this->db->Quote('%'.$tempstring.'%');
        } else if ($type == 'host') {
            return " and host like " . $this->db->Quote('%'.$tempstring.'%')." and type != 'S' ";
        }
    }
   
    /**
     * _getScopeQuery 
     * 
     * get scpe part of getDomains query
     * 
     * @access private
     * @return string
     */
    private function _getScopeQuery()
    {
        if (empty($_REQUEST['scope'])) {
            return "";
        }
        $scope = $_REQUEST['scope'];
        if ($scope != "num") {
            return " and domain regexp \"^[$scope" . strtoupper($scope) . "]\"";
        } else {
            return " and domain regexp \"^[0-9]\"";
        }
        return "";
    }

    /**
     * addDomainRecord 
     * 
     * Add initial domain record
     * 
     * @param mixed $domain 
     * @param mixed $domain_status 
     * @access public
     * @return void
     */
    public function addDomainRecord($domain, $domain_status)
    {
        $q = "INSERT INTO domains (domain,group_id,status)
            values(".$this->db->Quote($domain).",
            '{$this->session->group_id}',
            '$domain_status')";
        try {
            $result = $this->db->Execute($q);
        } catch (Exception $e) {
            throw new Framework_Exception($e->getMessage());
        }
   
        // Get new domain id, or die
        $id = $this->getDomainID($domain);
        if ($id == NULL) {
            throw new Framework_Exception("Error getting domain id");
        }
        $this->user->dnsLog($id,"added domain $domain with status $domain_status");
        return $id;
    }

    /**
     * addDefaultRecords 
     * 
     * Add default domain records
     * 
     * @param mixed $domain 
     * @param mixed $id 
     * @access public
     * @return void
     * @throws Framework_Exception
     */
    public function addDefaultRecords($domain, $id)
    {
        // Try for group's records
        $q = "SELECT * FROM default_records WHERE default_type='group' and group_id='{$this->session->group_id}'";
        try {
            $result = $this->db->Execute($q);
        } catch (Exception $e) {
            throw new Framework_Exception($e->getMessage());
        }
        if ($result->RecordCount() == 0) {
            // If there aren't any, get system default records 
            $q = "SELECT * FROM default_records WHERE default_type='system'";
            try {
                $result = $this->db->Execute($q);
            } catch (Exception $e) {
                throw new Framework_Exception($e->getMessage());
            }
        }
   
        if ($result->RecordCount() == 0) {
            // If these don't exist, bail!
            throw new Framework_Exception("Error: you have not yet setup default records");
        }
   
        // Build arrays
        $counter = 0;
        while (!$result->EOF && $row = $result->FetchRow()) {
            if ($row['type'] == 'S' && !isset($soa_array)) {
                $soa_array = $row;
            } else {
                $records_array[$counter] = $row;
                $counter++;
            }
        }
   
        // Add SOA record
        $host = preg_replace("/DOMAIN/", $domain, $soa_array['host']);
        $val = preg_replace("/DOMAIN/", $domain, $soa_array['val']);
        $q = "INSERT INTO records (domain_id,host,type,val,ttl)
                VALUES('$id',
                ".$this->db->Quote($host).",
                'S',
                '$val',
                '".$soa_array['ttl']."')";
        // $this->log->log($q);
        try {
            $result = $this->db->Execute($q);
        } catch (Exception $e) {
            throw new Framework_Exception($e->getMessage());
        }
        $this->user->dnsLog($id, "added soa");

        // Add default records
        if (isset($records_array) && is_array($records_array)) {
            while (list($key,$row) = each($records_array)) {
                $host = ereg_replace("DOMAIN", $domain, $row['host']);
                $val = ereg_replace("DOMAIN", $domain, $row['val']);
                $q = "INSERT INTO records (domain_id,host,type,val,distance,ttl)
                    VALUES ('$id',
                    " . $this->db->Quote($host) . ",
                    '".$row['type']."',
                    '$val',
                    '".$row['distance']."',
                    '".$row['ttl']."')";
                $this->log->log($q);
                try {
                    $result = $this->db->Execute($q);
                } catch (Exception $e) {
                    throw new Framework_Exception($e->getMessage());
                }
                $this->user->dnsLog($id, "added ".$row['type']." $host with value $val");
            }
        }
    }

    /**
     * getDomainID 
     * 
     * @param mixed $domain 
     * @access protected
     * @throws Framework_Exception
     * @return void
     */
    protected function getDomainID($domain)
    {
        $q = "SELECT domain_id FROM domains WHERE domain=" . $this->db->Quote($domain);
        try {
            $result = $this->db->Execute($q);
        } catch (Exception $e) {
            throw new Framework_Exception($e->getMessage());
        }
        if ($result->RecordCount() < 0) {
            return NULL;
        }
        $row = $result->FetchRow();
        return $row['domain_id'];
    }

    /**
     * deleteDomain 
     * 
     * Delete a domain
     * 
     * @param mixed $id 
     * @access public
     * @return void
     */
    public function deleteDomain($id) {
        $q = "DELETE FROM domains WHERE domain_id=" . $this->db->Quote($id);
        try {
            $result = $this->db->Execute($q);
        } catch (Exception $e) {
            throw new Framework_Exception($e->getMessage());
        }
        $q = "DELETE FROM records WHERE domain_id=" . $this->db->Quote($id);
        try {
            $result = $this->db->Execute($q);
        } catch (Exception $e) {
            throw new Framework_Exception($e->getMessage());
        }
    }


    /**
     * getRecords 
     * 
     * Get records for a domain
     * 
     * @param mixed $start 
     * @param mixed $limit 
     * @param mixed $domain_id 
     * @param mixed $countOnly 
     * @param mixed $sortField 
     * @param mixed $order 
     * @access public
     * @return ADODB result set
     * @throws Framework_Exception
     */
    public function getRecords($start, $limit, $domain_id, $countOnly = NULL, $sortField = NULL, $order = NULL) {

        $searchstring = is_null($scope) ? $this->_getSearchQuery('host') : "";

        if (!is_null($countOnly)) {
            $q = "SELECT COUNT(*) FROM records
                WHERE domain_id = " . $this->db->Quote($domain_id) . $searchstring;
            try {
                $result = $this->db->Execute($q);
            } catch (Exception $e) {
                throw new Framework_Exception($e->getMessage());
            }
        } else {
            $q = "SELECT * FROM records 
                WHERE domain_id = " . $this->db->Quote($domain_id) . $searchstring;
            $q .= "ORDER BY $sortField $sortWay ".( ($sortField == "type") ? ", host" : "" );
            try {
                $result = $this->db->SelectLimit($q, $limit, $start);
            } catch (Exception $e) {
                throw new Framework_Exception($e->getMessage());
            }
        }
        return $result;
    }

    /**
     * countRecords 
     * 
     * Shortcut for getRecords $countOnly = 1
     * 
     * @param mixed $id
     * @access public
     * @return getDomain()
     */
    public function countRecords($id)
    {
        return $this->getRecords(NULL, NULL, $id, 1);
    }

    /**
     * addRecord 
     * 
     * Adds record to DB.
     * 
     * @param mixed $domainID 
     * @param mixed $values 
     * @access public
     * @throws Framework_Exception on DB error
     * @return TRUE on success, error return of $this->validateRecord() on failure
     */
    public function addRecord($domainID, $values)
    {
        // convert type to single character format
        if (array_key_exists($values['type'], $this->types) === FALSE) {
            return "Error: Invalid record type";
        }

        if (($result = $this->validateRecord($values) !== TRUE )) {
            return $result;
        }
        return TRUE;
    }

    public function validateHostName($name)
    {
        // Hack to allow for DOMAIN substitutions in default records
        $name = preg_replace('/DOMAIN/', 'test.com', $name);

        if (ereg('\.\.', $name)) {
            return FALSE;
        } else {
            $result = preg_match("/^[\*\.a-z0-9-]+\.[a-z0-9-]+$/i", strtolower($name));
            return $result;
        }
    }

    // function verify_record($name,$type,$address,$distance,$weight,$port,$ttl) {
    public function validateRecord($values)
    {
        // verify IP format for A and NS records
        if ($values['type'] == 'A' || $values['type'] == 'NS') {
            if (!Net_IPv4::validateIP($values['address'])) {
                return "Error: Invalid IPv4 address format";
            }
        }

        // Validate hostname format
        if (!$this->validateHostName($values['hostname'])) {
            return "Error: Invalid hostname";
        }

        if ($values['type'] == 'AAAA' || $values['type'] == 'AAAA+PTR') {
            if (!Net_IPv6::checkIPv6($values['address'])) {
                return "Error: Invalid AAAA record format";
            }
        }

        // verify NS record
        if ($values['type'] == 'N' || $values['type'] == 'M' || $values['type'] == 'C') {
            if (Net_IPv4::validateIP($values['address'])) {
                return "Error: this record type can not be an IP address";
            }
        }

        // verify MX record
        if ($values['type'] == 'M') {
            if (!eregi("^([0-9])+$", $distance)) {
                return "Error: Invalid or missing MX distance";
            }
        }

        // verify PTR
        if ($values['type'] == 'P') {
            if (!eregi("^.*\.in-addr\.arpa\.*$", $name)) {
                return "Error: PTR must end in .in-addr.arpa.";
            }
        }

        // verify SRV record
        if ($values['type'] == 'V')  {
	        if (!preg_match("/^_.*\._.*$/i", $name)) {
		        return "Error: SRV \"{$values['address']}\" should be in the format _service._protocol";	
            }
	        if (($values['distance'] > 65535) || !preg_match('/^([0-9])+$/i', $values['distance'])) {
                return 'Error: SRV distance must be a numeric value between 0 and 65535';
            }
	        if (($values['weight'] > 65535) || !preg_match('/^([0-9])+$/i', $values['weight'])) {
                return 'Error: SRV weight must be a numeric value between 0 and 65535';
            }
	        if (($values['port'] > 65535) || !preg_match('/^([0-9])+$/i', $values['port'])) {
                return 'Error: SRV port must be a numeric value between 0 and 65535';
            }
        }

        // make sure a TTL was given
        if (empty($values['ttl'])) {
            return "Error: no TTL given";
        }
        return TRUE;
    }
}
?>
