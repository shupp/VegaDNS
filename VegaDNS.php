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
    public function getDomains($start, $limit, $groups, $countOnly = NULL, $sortField = NULL, $order = NULL) {
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
    public function countDomains($groups) {
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

    private function _getGroupIDs($item, $key) {
        if ($key == 'group_id') {
            $this->_groupIDs[$item] = $item;
        }
    }

    /**
     * _getSearchQuery 
     * 
     * figure ou the search part o the getDomains query based on $_REQUEST
     * 
     * @param mixed $type 
     * @access private
     * @return string
     */
    private function _getSearchQuery($type)
    {
        if (empty($_REQUEST['search'])) {
            return NULL;
        }

        $tempstring = preg_replace("/[*]/", "%", $_REQUEST['search']);
        $tempstring = preg_replace("/[ ]/", "%", $tempstring);
        return "and $type like ".$this->db->Quote('%'.$tempstring.'%');
    }
   
    /**
     * _getScopeQuery 
     * 
     * get scpe part of getDomains query
     * 
     * @access private
     * @return string
     */
    private function _getScopeQuery() {
   
        // Get scope of domain list, if it exists
        if (empty($_REQUEST['scope'])) {
            return "";
        }
        $scope = $_REQUEST['scope'];
        if ($scope != "num") {
            return " and domain regexp \"^[$scope" . strtoupper($scope) . "]\"";
        } else {
            return "and domain regexp \"^[0-9]\"";
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
        if($result->RecordCount() < 0) {
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

}

?>
