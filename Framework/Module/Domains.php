<?php


class Framework_Module_Domains extends VegaDNS_Common
{

    public function __constructor()
    {
        if ($this->user->getBit('domain_create')) {
            $smarty->assign('new_domain_url', $base_url."&module=Domains&domain_mode=add");
        }

        if ($this->user->getBit('domain_edit')) {
            $smarty->assign('edit_domain_url_base', $base_url."&module=Domains&domain_mode=edit_domain");
        }
    }

    public function __default()
    {
        // Get search string if it exists
        if (isset($_REQUEST['search']) && $_REQUEST['search'] != "") {
            $tempstring = ereg_replace("[*]", "%", $_REQUEST['search']);
            $tempstring = ereg_replace("[ ]", "%", $tempstring);
            $searchstring = "and domain like ".$db->Quote('%'.$tempstring.'%')."";
    
            $smarty->assign('search', $_REQUEST['search']);
            $smarty->assign('searchtexttag', " matching \"".$_REQUEST['search']."\""
    );
            $search = $_REQUEST['search'];
        } else {
            $searchstring = "";
            $search = "";
        }
    
        // Get scope of domain list, if it exists
        if (isset($_REQUEST['recursive'])) {
            $groupquery = $my->returnSubgroupsQuery($my->returnGroup($group, NULL), NULL);
            $smarty->assign('recursive', ' checked');
        } else {
            $groupquery = " a.group_id='$group'";
        }
    
        // Get scope of domain list, if it exists
        if (isset($_REQUEST['scope']) && $_REQUEST['scope'] != "") {
            $searchstring = "";
            $search = "";
            $scope = $_REQUEST['scope'];
            $this->setData('scope', $_REQUEST['scope']);
    
            if ($scope != "num") {
                    $sq = " and domain regexp \"^[$scope" . strtoupper($scope) . "]\"";
            } else {
                    $sq = "and domain regexp \"^[0-9]\"";
            }
        } else {
            $sq = "";
        }
    
        // Show domain list
    
        $q = "SELECT a.*, b.group_id, b.name 
                FROM domains a 
                LEFT JOIN groups b ON a.group_id = b.group_id 
                WHERE ($groupquery) $searchstring $sq ";
    
        // sort
        $sortway = $this->getRequestSortWay();
        $sortfield = $this->getSortfield('domains');
    
        $q .= "order by $sortfield $sortway ".( ($sortfield == "status") ? ", domain" : "" )."";
    
        $result = $this->db->Execute($q) or die($db->ErrorMsg()."q: $q");
        $totalitems = $result->RecordCount();
    
        // sort
        $sort_array['Domain'] = 'domain';
        $sort_array['Status'] = 'status';
        $sort_array['Group'] = 'group_id';
    
        $sortbaseurl = "$base_url&module=Domains&page=".( ((isset($_REQUEST['page']) && $_REQUEST['page'] == 'all')) ? "all" : $page);
    
        while (list($key,$val) = each($sort_array)) {
            $newsortway = $this->getSortWay($sortfield, $val, $sortway);
            $url = "<a href='$sortbaseurl&sortway=$newsortway&sortfield=$val'>".ereg_replace('_', ' ', $key)."</a>";
            if ($sortfield == $val) $url .= "&nbsp;<img border=0 alt='$sortway' src=images/$sortway.png>";
            $this->data[$key] = $url;
        }
    
        if ($totalitems > 0) {
    
    
            $domain_count = 0;
            // Actually list domains
            while (++$domain_count && !$result->EOF && ($row = $result->FetchRow())
                && ($domain_count <= $last_item)) {
    
                if ($domain_count < $first_item) continue;
    
                $out_array[$domain_count]['domain'] = $row['domain'];
                $out_array[$domain_count]['edit_url'] = "$base_url&mode=records&domain=".$row['domain'];
                $out_array[$domain_count]['status'] = $row['status'];
                $out_array[$domain_count]['group_name'] = get_groupowner_name($row['group_id']);
                if ($user_info['account_type'] == 'senior_admin' || $user_info['account_type'] == 'group_admin') {
                    $out_array[$domain_count]['change_owner_url'] = "$base_url&module=Domains&domain_mode=change_owner&domain_id=".$row['domain_id']."&domain=".$row['domain'];
                }
                if ($row['status'] == 'inactive') {
                    if ($user_info['account_type'] == 'senior_admin') {
                        $out_array[$domain_count]['activate_url'] = "$base_url&module=Domains&domain_mode=activate_domain&domain_id=".$row['domain_id']."&domain=".$row['domain'];
                    }
                } else if ($row['status'] == 'active') {
                    if ($user_info['account_type'] == 'senior_admin') {
                        $out_array[$domain_count]['deactivate_url'] = "$base_url&module=Domains&domain_mode=deactivate_domain&domain_id=".$row['domain_id']."&domain=".$row['domain'];
                    }
                }
                $out_array[$domain_count]['delete_url'] = "$base_url&module=Domains&domain_mode=delete&domain_id=".$row['domain_id']."&domain=".$row['domain'];
            }
        }
    
        $this->data['all_url'] = "$base_url&module=Domains&page=all&sortfield=$sortfield&sortway=$sortway&search=".urlencode($search);
        $this->data['first_item'] = $first_item;
        $this->data['last_item'] = $last_item;
        $this->data['totalitems'] = $totalitems;
        $this->data['totalpages'] = $totalpages;
        if (isset($out_array)) {
            $smarty->assign('out_array', $out_array);
        }
    
    }
}
?>
