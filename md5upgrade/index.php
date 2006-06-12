<?

/*
 *
 * This is the update script for users of VegaDNS 0.7 and lower that want to 
 * upgrade to 0.8 or newer.
 *
 * by Bill Shupp 5/19/2004
 *
 */


ini_set('include_path', ini_get('include_path') . ':../');
require('src/config.php');
require('src/connect.php');

$q = "select * from accounts limit 1";
$result = mysql_query($q) or die(mysql_error());

$len = mysql_field_len($result,3);

if($len == 34) {
    echo "The Password field length is already set to 34, so no upgrade appears to be necessary.";
    exit;
}

if(!isset($_REQUEST['doit'])) {

    echo "This script will update the database structure for VegaDNS versions older than 0.8.  Do you want to proceed?<p><br><br>\n";
    echo "<a href=".$_SERVER['PHP_SELF']."?doit=TRUE>update</a>\n";
    exit;
} else {

    // Update table definition
    $q = "alter table accounts change column Password Password varchar(34);";
    mysql_query($q) or die(mysql_error());

    // Update all passwords
    $q = "select cid,Password from accounts";
    $result = mysql_query($q) or die(mysql_error());
    while($row = mysql_fetch_array($result)) {
        $q = "update accounts set Password='".md5($row['Password'])."' where cid='".$row['cid']."'";
        mysql_query($q) or die(mysql_error());
    }

    echo "Update complete!  You may delete this directory if you like.<p>";
    echo "<a href=../index.php>log in</a>\n";
    exit;
}
?>
