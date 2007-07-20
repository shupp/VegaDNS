<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Framework
 *
 * @author      Joe Stump <joe@joestump.net>
 * @copyright   (c) 2005 2006 Joseph C. Stump. All rights reserved. 
 * @license     http://www.opensource.org/licenses/bsd-license.php
 * @package     Framework
 * @filesource
 */

if (!function_exists('__autoload')) {
    /**
     * __autoload
     *
     * Autoload is ran by PHP when it can't find a class it is trying to load.
     * By naming our classes intelligently we should be able to load most 
     * classes dynamically.
     *
     * @author      Joe Stump <joe@joestump.net>
     * @param       string      $class Class name we're trying to load
     * @return      void
     * @package     Framework
     */
    function __autoload($class)
    {
        $file = str_replace('_','/',$class.'.php');     
        include_once($file);
    }
}

define('FRAMEWORK_ERROR_MODULE_INIT', 2);
define('FRAMEWORK_ERROR_MODULE_EVENT', 4);
define('FRAMEWORK_ERROR_MODULE_STATUS', 128);
define('FRAMEWORK_ERROR_AUTH', 8);
define('FRAMEWORK_ERROR_PRESENTER', 16);
define('FRAMEWORK_ERROR_REQUEST', 32);
define('FRAMEWORK_ERROR_SITE', 64);

/**
 * Framework
 *
 * This is the base controller of the framework. It handles incoming requests
 * and loads the appropriate modules, presenters, etc. 
 *
 * @author Joe Stump <joe@joestump.net>
 * @package Framework
 */
abstract class Framework
{
    /**
     * $module
     *
     * @access public
     * @var object $module Instance of Framework_Module being loaded/ran
     * @static
     */
    static public $module = null;

    /**
     * $site
     *
     * @access public
     * @var object $site Instance of Framework_Site being loaded/ran
     * @static
     */
    static public $site = null;

    /**
     * $request
     *
     * @access public
     * @var object $request Instance of Framework_Request 
     * @static
     */
    static public $request = null;

    /**
     * $db
     *
     * @access public
     * @var object $db Instance of PEAR DB 
     * @static
     * @link http://pear.php.net/package/DB
     */
    static public $db = null;

    /**
     * $log
     *
     * @access public
     * @var object $log Instance of PEAR Log
     * @static
     * @link http://pear.php.net/package/Log
     */
    static public $log = null;

    /**
     * $controller
     * 
     * @access      private
     * @var         object      $controller
     * @see         Framework_Controller, Framework_Controller_Common
     */
    static private $controller = null;

    /**
     * start
     *
     * @access public
     * @return mixed PEAR_Error on failure, true on success
     */
    static public function start($site = 'Default', $controller = 'Web')
    {
        self::$site = Framework_Site::factory($site);
        if (PEAR::isError(self::$site)) {
            return PEAR::raiseError(self::$site->getMessage(), 
                                    FRAMEWORK_ERROR_SITE);
        }

        $result = self::$site->prepare();
        if (PEAR::isError($result)) {
            return PEAR::raiseError($result->getMessage(), 
                                    FRAMEWORK_ERROR_SITE);
        }

        self::$controller = Framework_Controller::factory($controller);
        if (PEAR::isError(self::$controller)) {
            return self::$controller;
        }

        try {
            self::$request = Framework_Request::factory(self::$controller->requester);
        } catch (Framework_Exception $error) {
            return PEAR::raiseError($error->getMessage(), 
                                    FRAMEWORK_ERROR_REQUEST);
        }

        self::$module = & self::$controller->module();
        if (PEAR::isError(self::$module)) {
            return PEAR::raiseError(self::$module->getMessage(), 
                                    FRAMEWORK_ERROR_MODULE_INIT);
        }

        if (!in_array($controller, self::$module->controllers)) {
            return PEAR::raiseError('Invalid controller requested', 
                                    FRAMEWORK_MODULE_ERROR_INVALID_CONTROLLER);
        }

        $result = self::$controller->authenticate();
        if (PEAR::isError($result)) {
            return PEAR::raiseError($result->getMessage(), 
                                    FRAMEWORK_ERROR_AUTH);
        }

        $result = self::$controller->start();
        if (PEAR::isError($result)) {
            return PEAR::raiseError($result->getMessage(), 
                                    FRAMEWORK_ERROR_MODULE_EVENT);
        }

        return self::$controller->display();
    }

    /**
     * stop
     * 
     * @access public   
     * @return mixed True on success, PEAR_Error on failure
     */
    static public function stop()
    {
        $result = self::$controller->stop();
        if (PEAR::isError($result)) {
            return $result;
        }
        
        $result = self::$site->stop();
        if (PEAR::isError($result)) {
            return $result;
        }

        if (self::$log instanceof Log) {
            self::$log->close();
        }

        return true; 
    }
}

?>
