<?php

if(!preg_match('/.*\/index.php$/', $_SERVER['PHP_SELF'])) {
    header("Location:../index.php");
    exit;
}

//include('vendor/sysinfo/lib/class.serverInfo.php');
//$info = new serverInfo();

// get domain count
$q = "select * from domains";

if($user_info['Account_Type'] == 'group_admin') {
    $q = "select * from domains where owner_id = '".$user_info['cid']."' or group_owner_id = '".$user_info['cid']."' ";
} else if($user_info['Account_Type'] == 'user') {
    $q = "select * from domains where owner_id = '".$user_info['cid']."'";
}
$stmt = $pdo->prepare($q);
$stmt->execute() or die(print_r($stmt->errorInfo()));
$totaldomains = $stmt->rowCount();



$smarty->assign('totaldomains', $totaldomains);
//$smarty->assign('system', (array)$info);
$smarty->assign('dashboardajax', 'true');
$smarty->display('header.tpl');
$smarty->display('dashboard.tpl');
$smarty->display('footer.tpl');