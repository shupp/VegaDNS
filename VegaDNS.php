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
     * domainExists 
     * 
     * Check if a domain exists in the databse.
     * 
     * @param string $domain 
     * @access public
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

    public function getDomains($start, $limit, $groupID, $groupArray = NULL, $countOnly = NULL, $sortField = NULL, $order = NULL) {
        $groupquery = $this->_getGroupQuery($groupID, $groupArray);
        $scope = $this->_getScopeQuery();
        $searchstring = is_null($scope) ? $this->_getSearchQuery('domain') : "";

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

    public function countDomains($groupID, $groupArray = NULL) {
        return $this->getDomains(NULL, NULL, $groupID, $groupArray, 1);
    }

    private function _returnSubgroupsQuery($g,$string)
    {
        if ($string == NULL) {
            $string = " a.group_id='".$g['group_id']."'";
        } else {
            $string .= " or a.group_id='".$g['group_id']."'";
        }

        if (!isset($g['subgroups'])) {
            return $string;
        } else {
            $temp = " ";
            while (list($key,$val) = each($g['subgroups'])) {
                $temp .= $this->_returnSubgroupsQuery($val, $temp);
            }
        }
        return $string.$temp;
    }

    private function _getSearchQuery($type)
    {
        if (empty($_REQUEST['search'])) {
            return NULL;
        }

        $tempstring = preg_replace("/[*]/", "%", $_REQUEST['search']);
        $tempstring = preg_replace("/[ ]/", "%", $tempstring);
        return "and $type like ".$this->db->Quote('%'.$tempstring.'%');
    }
   
    private function _getGroupQuery($groupID, $groupArray = NULL)
    {
        // Get scope of domain list, if it exists
        if (!is_null($groupArray)) {
            return $this->_returnSubgroupsQuery($groupArray, NULL);
        } else {
            return " a.group_id='$groupID'";
        }
    }

    private function _getScopeQuery() {
   
        // Get scope of domain list, if it exists
        if (empty($_REQUEST['scope'])) {
            return NULL;
        }
        $scope = $_REQUEST['scope'];
        if ($scope != "num") {
            return " and domain regexp \"^[$scope" . strtoupper($scope) . "]\"";
        } else {
            return "and domain regexp \"^[0-9]\"";
        }
        return NULL;
    }

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

}

?>
