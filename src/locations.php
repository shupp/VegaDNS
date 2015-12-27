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

if (!preg_match('/.*\/index.php$/', $_SERVER['PHP_SELF'])) {
    header("Location:../index.php");
    exit;
}

if (!isset($_REQUEST['location_mode']) || $_REQUEST['location_mode'] == 'delete_cancelled') {


    // Display cancel message if necessary
    if (isset($_REQUEST['location_mode']) && $_REQUEST['location_mode'] == 'delete_cancelled') {
        set_msg("Delete Cancelled");
    }

    // Get search string if it exists
    $params = array();
    if (isset($_REQUEST['search']) && $_REQUEST['search'] != "") {
        $tempstring = preg_replace('/[*]/', '%', $_REQUEST['search']);
        $tempstring = preg_replace('/[ ]/', '%', $tempstring);
        $params[':search'] = '%' . $tempstring . '%';
        $searchstring = "location like :search and ";

        $smarty->assign('search', $_REQUEST['search']);
        $smarty->assign('searchtexttag', " matching \"" . $_REQUEST['search'] . "\"");
        $search = $_REQUEST['search'];
    } else {
        $searchstring = "";
        $search = "";
    }

    if (isset($_REQUEST['scope']) && $_REQUEST['scope'] != "") {
        $searchstring = "";
        $search = "";
        $scope = $_REQUEST['scope'];
        $smarty->assign('scope', $_REQUEST['scope']);

        if ($user_info['Account_Type'] == 'senior_admin') {
            $aux = "where";
        } else {
            $aux = "and";
        }

        if ($scope != "num") {
            $sq = "$aux location regexp \"^[$scope" . strtoupper($scope) . "]\"";
        } else {
            $sq = "$aux location regexp \"^[0-9]\"";
        }
    } else {
        $sq = "";
    }

// Show domain list

    if ($user_info['Account_Type'] == 'senior_admin')
        $q = "select * from locations $searchstring $sq ";


// sort
    if (!isset($_REQUEST['sortway'])) {
        $sortway = 'asc';
    } else if ($_REQUEST['sortway'] == 'desc') {
        $sortway = 'desc';
    } else {
        $sortway = 'asc';
    }


    if (!isset($_REQUEST['sortfield'])) {
        $sortfield = 'status';
    } else {
        $sortfield = $_REQUEST['sortfield'];
    }

    // Get locations list
    $q .= " order by $sortfield $sortway" . ( ($sortfield == "status") ? ", location" : "" ) . "";
    $stmt = $pdo->prepare($q);
    $stmt->execute($params) or die(print_r($stmt->errorInfo()));
    $totallocations = $stmt->rowCount();

    // See if the search failed to match
    if ($totallocations == 0 && $searchstring != "") {
        set_msg_err("Error: no locations matching \"" . $_REQUEST['search'] . "\"");
        $smarty->display('header.tpl');
        $smarty->display('footer.tpl');
        exit;
    }
    // If no search, make sure there are locations to display
    if ($totallocations == 0 && $searchstring == "") {
        set_msg_err("Error: no resource locations");
        $smarty->display('header.tpl');
        $smarty->display('footer.tpl');
        exit;
    }

    // Pagination
    if (isset($_REQUEST['page'])) {
        if ($_REQUEST['page'] == 'all') {
            $page = 1;
            $first_location = 1;
            $last_location = $totallocations;
            $totalpages = 1;
        } else {
            $page = $_REQUEST['page'];
            $first_location = ($page * $per_page) - $per_page + 1;
            if ($first_location == 0)
                $first_location++;
            $last_location = ($first_location + $per_page - 1);
            $totalpages = ceil(number_format($totallocations / $per_page, 10));
        }
    } else {
        $page = 1;
        $first_location = 1;
        $last_location = ($first_location + $per_page - 1);
        $totalpages = ceil(number_format($totallocations / $per_page, 10));
    }
    if ($last_location > $totallocations)
        $last_location = $totallocations;
    if ($page > 1) {
        $smarty->assign('previous_url', "$base_url&mode=locations&page=" . ($page - 1) . "&sortfield=$sortfield&sortway=$sortway&search=" . urlencode($search));
    }
    if ($page < $totalpages) {
        $smarty->assign('next_url', "$base_url&mode=locations&page=" . ($page + 1) . "&sortfield=$sortfield&sortway=$sortway&search=" . urlencode($search));
    }
    if ($page > 1 || (isset($_REQUEST['page']) && $_REQUEST['page'] == 'all')) {
        $smarty->assign('first_url', "$base_url&mode=locations&page=1&sortfield=$sortfield&sortway=$sortway&search=" . urlencode($search));
    }
    if ($page < $totalpages) {
        $smarty->assign('last_url', "$base_url&mode=locations&page=$totalpages&sortfield=$sortfield&sortway=$sortway&search=" . urlencode($search));
    }

    // Should we display the soa stuff?
    if (($page == 1 || $page == "all") && $searchstring == "") {
        $display_soa = 1;
    } else {
        $display_soa = 0;
    }

    // sort
    $sort_array['Location'] = 'location';
    $sort_array['Prefix'] = 'prefix';
    $sort_array['Status'] = 'status';

    $sortbaseurl = "$base_url&mode=locations&page=" . ( ((isset($_REQUEST['page']) && $_REQUEST['page'] == 'all')) ? "all" : $page);

    while (list($key, $val) = each($sort_array)) {
        $newsortway = get_sortway($sortfield, $val, $sortway);
        $url = "<a href='$sortbaseurl&sortway=$newsortway&sortfield=$val'>$key</a>";
        if ($sortfield == $val)
            $url .= "&nbsp;<img border=0 alt='$sortway' src=images/$sortway.png>";
        $smarty->assign($key, $url);
    }

    // Build locations data structure
    $counter = 0;
    while (++$counter && $row = $stmt->fetch()) {
        // Get SOA

        if ($counter < $first_location)
            continue;
        if ($counter <= $last_location) {
            $locations[$counter]['location_id'] = $row['location_id'];
            $locations[$counter]['location'] = $row['location'];
            $locations[$counter]['prefix'] = $row['prefix'];
            $locations[$counter]['status'] = $row['status'];
        }
    }

    $counter = 0;
    while (list($key, $array) = each($locations)) {
        $out_array[$counter]['location'] = $array['location'];
        $out_array[$counter]['prefix'] = $array['prefix'];
        $out_array[$counter]['status'] = $array['status'];
        $out_array[$counter]['delete_url'] = "$base_url&mode=locations&location_mode=delete&location_id=" . $array['location_id'];
        $out_array[$counter]['edit_url'] = "$base_url&mode=locations&location_mode=edit_location&location_id=" . $array['location_id'];
        if ($array['status'] == 'inactive') {
            if ($user_info['Account_Type'] == 'senior_admin') {
                $out_array[$counter]['activate_url'] = "$base_url&mode=locations&location_mode=activate_location&location_id=" . $array['location_id'] . "&location=" . $array['location'];
            }
        } else if ($array['status'] == 'active') {
            if ($user_info['Account_Type'] == 'senior_admin') {
                $out_array[$counter]['deactivate_url'] = "$base_url&mode=locations&location_mode=deactivate_location&location_id=" . $array['location_id'] . "&location=" . $array['location'];
            }
        }
        $counter++;
    }

    $smarty->assign('add_location_url', "$base_url&mode=locations&location_mode=add_location");
    $smarty->assign('view_log_url', "$base_url&mode=locations&location_mode=view_log");

    $smarty->assign('all_url', "$base_url&mode=locations&page=all&sortfield=$sortfield&sortway=$sortway&search=" . urlencode($search));
    $smarty->assign('first_location', $first_location);
    $smarty->assign('last_location', $last_location);
    $smarty->assign('totallocations', $totallocations);
    $smarty->assign('totalpages', $totalpages);
    $smarty->assign('page', $page);

    if (isset($out_array))
        $smarty->assign('out_array', $out_array);
    $smarty->display('header.tpl');
    $smarty->display('list_locations.tpl');
    $smarty->display('footer.tpl');
    exit;
} else if ($_REQUEST['location_mode'] == 'add_location') {

    $smarty->display('header.tpl');
    require('src/add_location_form.php');
    $smarty->display('footer.tpl');
    exit;
} else if ($_REQUEST['location_mode'] == 'add_location_now') {

    // Add domain to 'name'
    // add location to db

    $params = array();
    $q = "insert into locations (location,prefix) values('" . $_REQUEST['location'] . "','" . $_REQUEST['prefix'] . "')";
    $stmt = $pdo->prepare($q);
    $stmt->execute($params) or die(print_r($stmt->errorInfo()));
    set_msg("Location added successfully!");
    header("Location: $base_url&mode=locations");
    exit;
} else if ($_REQUEST['location_mode'] == 'delete') {

    // Get location info
    $q = "select * from locations where location_id='" . $_REQUEST['location_id'] . "' limit 1";
    $stmt = $pdo->query($q) or die(print_r($pdo->errorInfo()));
    $row = $stmt->fetch();

    $smarty->assign('location', $row['location']);
    $smarty->assign('cancel_url', "$base_url&mode=locations&location_mode=delete_cancelled");
    $smarty->assign('delete_url', "$base_url&mode=locations&location_mode=delete_now&location_id=" . $row['location_id'] . "&domain=" . urlencode($domain));
    $smarty->display('header.tpl');
    $smarty->display('delete_location_confirm.tpl');
    $smarty->display('footer.tpl');
    exit;
} else if ($_REQUEST['location_mode'] == 'delete_now') {

    // make sure the location_id was given
    if (!isset($_REQUEST['location_id'])) {
        set_msg_err("Error: no location_id");
        $smarty->display('header.tpl');
        $smarty->display('footer.tpl');
        exit;
    }

    $q = "delete from locations where location_id='" . $_REQUEST['location_id'] . "'";
    $pdo->query($q) or die(print_r($pdo->errorInfo()));
    set_msg("Location deleted successfully");
    header("Location: $base_url&mode=locations");
    exit;
} if ($_REQUEST['location_mode'] == 'edit_location') {

    // Make sure location_id was given
    if ($_REQUEST['location_id'] == "") {
        set_msg_err("Error: missing location_id");
        $smarty->display('header.tpl');
        require('src/list_locations.tpl');
        $smarty->display('footer.tpl');
        exit;
    }

    // Get Current RR information

    $q = "select * from locations where location_id='" . $_REQUEST['location_id'] . "' limit 1";
    $stmt = $pdo->query($q) or die(print_r($pdo->errorInfo()));
    $row = $stmt->fetch();


    // Set values for template
    $smarty->assign('location_id', $_REQUEST['location_id']);
    $smarty->assign('location', $row['location']);
    $smarty->assign('prefix', $row['prefix']);

    // Edit Record Menu
    $smarty->display('header.tpl');
    $smarty->display('edit_location.tpl');
    $smarty->display('footer.tpl');
    exit;
} else if ($_REQUEST['location_mode'] == 'edit_location_now') {
    $q = "update locations set " .
            "location='" . $_REQUEST['location'] . "'," .
            "prefix='" . $_REQUEST['prefix'] . "'" .
            "where location_id='" . $_REQUEST['location_id'] . "'";

    $pdo->query($q) or die(print_r($pdo->errorInfo()));
    set_msg("Location updated successfully!");
    header("Location: $base_url&mode=locations");
    exit;
} else if ($_REQUEST['location_mode'] == 'activate_location') {

    // Make sure a domain_id was given
    if (!isset($_REQUEST['location_id'])) {
        set_msg_err("Error: no location_id given");
        $smarty->display('header.tpl');
        $smarty->display('footer.tpl');
        exit;
    }

    // Make sure this is a senior_admin  // PERMISSIONS?
    if ($user_info['Account_Type'] != 'senior_admin') {
        set_msg_err("Error: you do not have privileges to change a location status");
        $smarty->display('header.tpl');
        $smarty->display('footer.tpl');
        exit;
    }

    $q = "update locations set status='active' where location_id=" . $_REQUEST['location_id'] . "";
    $pdo->query($q) or die(print_r($pdo->errorInfo()));
    dns_log($_REQUEST['location_id'], "Changed status to ACTIVE");
    set_msg("Location activated successfully");
    header("Location: $base_url&mode=locations");
    exit;
} else if ($_REQUEST['location_mode'] == 'deactivate_location') {

    // Make sure a domain_id was given
    if (!isset($_REQUEST['location_id'])) {
        set_msg_err("Error: no location_id given");
        $smarty->display('header.tpl');
        $smarty->display('footer.tpl');
        exit;
    }

    // Make sure this is a senior_admin  // PERMISSIONS?
    if ($user_info['Account_Type'] != 'senior_admin') {
        set_msg_err("Error: you do not have privileges to change a location status");
        $smarty->display('header.tpl');
        $smarty->display('footer.tpl');
        exit;
    }

    $q = "update locations set status='inactive' where location_id=" . $_REQUEST['location_id'] . "";
    $pdo->query($q) or die(print_r($pdo->errorInfo()));

    dns_log($_REQUEST['location_id'], "Changed status to INACTIVE");

    set_msg("Location de-activated successfully");
    header("Location: $base_url&mode=locations");
    exit;
}