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
 * Copyright 2003-2015, Bill Shupp
 * see COPYING for details
 *
 */

if(!preg_match('/.*\/index.php$/', $_SERVER['PHP_SELF'])) {
    header("Location:../index.php");
    exit;
}



if(!isset($_POST['build_config'])) {

    // Include standard functions
    require('src/functions.php');

    // Set temporary smarty dirs for building
    $server_tmp = sys_get_temp_dir();
    $smarty->compile_dir = "$server_tmp/templates_c";
    $smarty->config_dir = "$server_tmp/configs";
    $smarty->cache_dir = "$server_tmp/cache";
    mkdir($smarty->compile_dir);
    mkdir($smarty->config_dir);
    mkdir($smarty->cache_dir);
    $smarty->clear_all_cache();
    $smarty->clear_compiled_tpl();
    $smarty->assign('build_config', true);
    $smarty->display('header.tpl');
    $smarty->display('build_config.tpl');
    $smarty->display('footer.tpl');
    exit;
}
else {

    // Set the config file name
    $config_file_name = 'src/config.php';

    // Get the source config
    $template = file_get_contents('src/config-source.txt');

    // set all checkbox values to false if they're not in the input array
    if (!$_POST['v6_support']) {
        $_POST['v6_support'] = 'false';
    }
    if (!$_POST['get_data_trusted_only']) {
        $_POST['get_data_trusted_only'] = 'false';
    }
    if (!$_POST['mysql_sessions']) {
        $_POST['mysql_sessions'] = 'false';
    }

    // Loop through the inputs str_replace all the variables in the config template with input data from form
    foreach ($_POST as $key => $value) {
        // fix checkbox values == 'on' where we need true
        if ($value == 'on')
        {
            $value = 'true';
        }
        $template = str_replace('||' . $key . '||', $value, $template);
    }

    //delete the config file if it exists, so we can re-create it
    if (file_exists($config_file_name)) {
        unlink($config_file_name);
    }

    //create the config file
    $file_handle = fopen($config_file_name, 'a') or die('Cannot open file ' . $config_file_name . '. Please check file
    and folder permissions for the apache user or use the manual copy / paste method..');

    // write template data to the new file
    if (fwrite($file_handle, $template)) {
        $set_msg='Config file created successfully, please continue with the <a href="INSTALL">INSTALL</a> instructions.';
    } else {
        echo 'Failed to save config file, please try again or use the manual copy / paste method.';
    }
}
