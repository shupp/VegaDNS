<?php


class Framework_Module_Domains extends VegaDNS_Common
{

    public function __construct()
    {
        parent::__construct();
        if ($this->user->getBit($this->user->getPerms(), 'domain_create')) {
            $this->setData('new_domain_url', './?module=Domains&event=add');
        }

        if ($this->user->getBit($this->user->getPerms(), 'domain_edit')) {
            $this->setData('edit_domain_url_base', "./?module=Domains&event=edit");
        }
    }

    public function __default()
    {
        return $this->listDomains();
    }

    public function listDomains()
    {
        // Get search string if it exists
        if (isset($_REQUEST['search']) && $_REQUEST['search'] != "") {
            $tempstring = preg_replace("/[*]/", "%", $_REQUEST['search']);
            $tempstring = preg_replace("/[ ]/", "%", $tempstring);
            $searchstring = "and domain like ".$this->db->Quote('%'.$tempstring.'%')."";
    
            $this->setData('search', $_REQUEST['search']);
            $this->setData('searchtexttag', " matching \"".$_REQUEST['search']."\""
    );
            $search = $_REQUEST['search'];
        } else {
            $searchstring = "";
            $search = "";
        }
    
        // Get scope of domain list, if it exists
        if (isset($_REQUEST['recursive'])) {
            $groupquery = $this->user->returnSubgroupsQuery($this->user->returnGroup($this->group_id, NULL), NULL);
            $this->setData('recursive', ' checked');
        } else {
            $groupquery = " a.group_id='{$this->group_id}'";
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
    
        try {
            $result = $this->db->Execute($q);
        } catch (Exception $e) {
            throw new Framework_Exception($e->getMessage());
        }
        $totalitems = $result->RecordCount();
        $this->setData('total', $totalitems);
    
        // sort
        $sort_array['Domain'] = 'domain';
        $sort_array['Status'] = 'status';
        $sort_array['Group'] = 'group_id';
    
        $sortbaseurl = "./?&module=Domains&page=".( ((isset($_REQUEST['page']) && $_REQUEST['page'] == 'all')) ? "all" : $page);
    
        while (list($key,$val) = each($sort_array)) {
            $newsortway = $this->getSortWay($sortfield, $val, $sortway);
            $url = "<a href='$sortbaseurl&sortway=$newsortway&sortfield=$val'>" . preg_replace('/_/', ' ', $key)."</a>";
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
                $out_array[$domain_count]['edit_url'] = "./?&mode=records&domain=".$row['domain'];
                $out_array[$domain_count]['status'] = $row['status'];
                $out_array[$domain_count]['group_name'] = get_groupowner_name($row['group_id']);
                if ($user_info['account_type'] == 'senior_admin' || $user_info['account_type'] == 'group_admin') {
                    $out_array[$domain_count]['change_owner_url'] = "./?&module=Domains&domain_mode=change_owner&domain_id=".$row['domain_id']."&domain=".$row['domain'];
                }
                if ($row['status'] == 'inactive') {
                    if ($user_info['account_type'] == 'senior_admin') {
                        $out_array[$domain_count]['activate_url'] = "./?&module=Domains&domain_mode=activate_domain&domain_id=".$row['domain_id']."&domain=".$row['domain'];
                    }
                } else if ($row['status'] == 'active') {
                    if ($user_info['account_type'] == 'senior_admin') {
                        $out_array[$domain_count]['deactivate_url'] = "./?&module=Domains&domain_mode=deactivate_domain&domain_id=".$row['domain_id']."&domain=".$row['domain'];
                    }
                }
                $out_array[$domain_count]['delete_url'] = "./?&module=Domains&domain_mode=delete&domain_id=".$row['domain_id']."&domain=".$row['domain'];
            }
        }
    
        $this->setData('all_url', "./?&module=Domains&page=all&sortfield=$sortfield&sortway=$sortway&search=".urlencode($search));
        $this->setData('first_item', $first_item);
        $this->setData('last_item', $last_item);
        $this->setData('totalitems', $totalitems);
        $this->setData('totalpages', $totalpages);
        if (isset($out_array)) {
            $this->setData('out_array', $out_array);
        }
    }

    public function add()
    {
        if (!$this->user->getBit($this->user->getPerms(), 'domain_create')) {
            $this->setData("Error: you do not have enough privileges to create domains.");
            return $this->listDomains();
        }
        $form = $this->addForm();
        $this->setData('form', $form->toHtml());
        // $this->pageTemplateFile = 'thickbox.tpl';
        $this->tplFile = 'add.tpl';
    }

    protected function addForm()
    {
        $form = new HTML_QuickForm('formLogin', 'post', './?module=Domains&event=addNow&modal=true', '', 'class="thickbox"');

        $form->addElement('header', 'MyHeader', _('Add Domain'));
        $form->addElement('text', 'domain', _('Domain Name'));
        $form->addElement('submit', 'submit', _('Add'));

        $form->registerRule('secondLevel', 'regex', '/.*\..*/');
        $form->registerRule('validChars', 'regex', '/^[\.a-z0-9-]+$/i');
        $form->addRule('domain', _('Please enter a domain name'), 'required', null, 'client');
        $form->addRule('domain', _('Domain must be at least a second level domain'), 'secondLevel', null, 'client');
        $form->addRule('domain', _('Invalid characters in domain name'), 'validChars', null, 'client');

        $form->applyFilter('domain', 'strtolower');

        return $form;
    }

    public function addNow()
    {
        if (!$this->user->getBit($this->user->getPerms(), 'domain_create')) {
            $this->setData("Error: you do not have enough privileges to create domains.");
            return $this->listDomains();
        }

        $form = $this->addForm();
        if (!$form->validate()) {
            return $this->add();
        }
    
        $domain = strtolower($_REQUEST['domain']);
    
        // Make sure the domain does not already exist.
        $q = "SELECT * FROM domains WHERE domain=" . $this->db->Quote($domain) . " LIMIT 1";
        try {
            $result = $this->db->Execute($q);
        } catch (Exception $e) {
            throw new Framework_Exception($e->getMessage());
        }
        if ($result->RecordCount() > 0) {
            $this->setData('message', "Error: domain $domain already exists");
            return $this->add();
        }

        $domain_status = 'inactive';
        if ($this->user->isSeniorAdmin()) {
            $domain_status = 'active';
        }

        $domain_id = $this->addDomainRecord($domain, $domain_status);
        $this->addDefaultRecords($domain, $domain_id);
    
        // email the support address if an inactive domain is added
        $body = "$domain_status domain \"$domain\" added by {$this->session->email}\n\n";
        $body .= "\n\nThanks,\n\n";
        $body .= "VegaDNS";
    
        $supportemail = (string)Framework::$site->config->supportEmail;
        $supportname = (string)Framework::$site->config->supportName;
        mail($supportemail,
            "New $domain_status Domain Created",
            $body,
            "Return-path: $supportemail\r\nFrom: \"$supportname\" <$supportemail>");
    
        $this->setData('message', "Domain $domain added successfully!");
        // $this->setData('continueUrl', "./?module=Records&domain=".urlencode($domain));
        // $this->pageTemplateFile = 'thickbox.tpl';
        $this->tplFile = 'addSuccess.tpl';
        header("Location: ./?module=Records&domain=".urlencode($domain));
        return;
    }

    private function addDomainRecord($domain, $domain_status)
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

    private function addDefaultRecords($domain, $id)
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
        $this->log->log($q);
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

}
?>
