<?php

/**
 * index.php
 *
 * An basic controller for Framework.
 *
 * @author Joe Stump <joe@joestump.net>
 * @author Bill Shupp <hostmaster@shupp.org>
 * @filesource
 */

ini_set('error_reporting', E_ALL & ~E_NOTICE);

if(!isset($_GET['module'])) {
    header("Location: ./?module=Login");
    exit;
}

define('FRAMEWORK_BASE_PATH',dirname(__FILE__) . '/..');
$ta_include_path = FRAMEWORK_BASE_PATH;
// If you are running a local PEAR install, uncomment the next line
// and edit it accordingly

$ta_include_path .= PATH_SEPARATOR . '/Users/shupp/pear/lib';

ini_set('include_path', $ta_include_path . PATH_SEPARATOR . ini_get('include_path'));

try {
    require_once 'Framework.php';

    $controller = 'Web';
    if (isset($_GET['Controller'])) {
        $controller = $_GET['Controller'];
    }
    $result = Framework::start('Default', $controller);
    if (PEAR::isError($result)) {
        switch ($result->getCode()) {
        case FRAMEWORK_ERROR_AUTH:
            header('Location: ./?module=Login');
            break;
        default:
            // If a PEAR error is returned usually something catastrophic 
            // happend like an event returning a PEAR_Error or throwing an 
            // exception of some sort.
            die($result->getMessage());
        }
    }

    // Run shutdown functions and stop the Framework
    Framework::stop();
} catch (Framework_Exception $error) {
    echo $error->getMessage();
}

?>

