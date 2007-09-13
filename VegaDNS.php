<?php

/**
 * VegaDNS 
 * 
 * @uses Framework_Object_DB
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
 * @uses Framework_Object_DB
 * @package VegaDNS
 * @copyright 2007 Bill Shupp
 * @author Bill Shupp <hostmaster@shupp.org> 
 * @license GPL 2.0  {@link http://www.gnu.org/licenses/gpl.txt}
 */
class VegaDNS extends Framework_Object_DB
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
        $groupquery = $this->getGroupQuery($groupID, $groupArray);
        $scope = $this->getScopeQuery();
        $searchstring = is_null($scope) ? $this->getSearchQuery('domain') : "";

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

    private function returnSubgroupsQuery($g,$string)
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
                $temp .= $this->returnSubgroupsQuery($val, $temp);
            }
        }
        return $string.$temp;
    }

    private function getSearchQuery($type)
    {
        if (empty($_REQUEST['search'])) {
            return NULL;
        }

        $tempstring = preg_replace("/[*]/", "%", $_REQUEST['search']);
        $tempstring = preg_replace("/[ ]/", "%", $tempstring);
        return "and $type like ".$this->db->Quote('%'.$tempstring.'%');
    }
   
    private function getGroupQuery($groupID, $groupArray = NULL)
    {
        // Get scope of domain list, if it exists
        if (!is_null($groupArray)) {
            return $this->returnSubgroupsQuery($groupArray, NULL);
        } else {
            return " a.group_id='$groupID'";
        }
    }

    private function getScopeQuery() {
   
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
}

?>
