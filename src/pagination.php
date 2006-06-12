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



// Pagination
if(isset($_REQUEST['page'])) {
    if($_REQUEST['page'] == 'all') {
        $page = 1;
        $first_item = 1;
        $last_item = $totalitems;
        $totalpages = 1;
    } else {
        $page = $_REQUEST['page'];
        $first_item = ($page * $per_page) - $per_page + 1;
        if($first_item == 0) $first_item++;
        $last_item = ($first_item + $per_page - 1);
        $totalpages = ceil(number_format($totalitems / $per_page, 10));
    }
} else {
    $page = 1;
    $first_item = 1;
    $last_item = ($first_item + $per_page - 1);
    $totalpages = ceil(number_format($totalitems / $per_page, 10));
}
if($last_item > $totalitems) $last_item = $totalitems;


// Append "domain=" for records mode only
if($_REQUEST['mode'] == 'records') {
    $pag_base_url = $base_url . "&domain=$domain";
} else {
    $pag_base_url = $base_url;
}

if($page > 1) {
    $smarty->assign('previous_url', "$pag_base_url&mode=".$_REQUEST['mode']."&page=".($page - 1)."&sortfield=$sortfield&sortway=$sortway&search=".urlencode($search));
}
if($page < $totalpages) {
    $smarty->assign('next_url', "$pag_base_url&mode=".$_REQUEST['mode']."&page=".($page + 1)."&sortfield=$sortfield&sortway=$sortway&search=".urlencode($search));
}
if($page > 1 || (isset($_REQUEST['page']) && $_REQUEST['page'] == 'all')) {
    $smarty->assign('first_url', "$pag_base_url&mode=".$_REQUEST['mode']."&page=1&sortfield=$sortfield&sortway=$sortway&search=".urlencode($search));
}
if($page < $totalpages) {
    $smarty->assign('last_url', "$pag_base_url&mode=".$_REQUEST['mode']."&page=$totalpages&sortfield=$sortfield&sortway=$sortway&search=".urlencode($search));
}

