<?php

/**
 * VegaDNS_Auth_ACL
 *
 * PHP Version 5
 *
 * @category   DNS
 * @package    VegaDNS
 * @subpackage Auth
 * @author     "Joe Stump" <joe@joestump.net>
 * @author     "Bill Shupp" <hostmaster@shupp.org>
 * @copyright  2006 Joseph C. Stump. All rights reserved.
 * @copyright  2008 William D. Shupp.  All rights reserved
 * @license    BSD http://www.opensource.org/licenses/bsd-license.php
 * @link       http://www.vegadns.org
 * @filesource
 */

/**
 * VegaDNS_Auth_ACL
 *
 * The VegaDNS_Auth_ACL is based on the Framework_Auth_ACL class.
 * It utilizes the <acl> section located in a site's config.xml.
 * If the module/event pair in config.xml is set, the user must have
 * access to the permission listed (accessLevel).  If the accessLevel 
 * in the config.xml for the given module/event pair being requested 
 * is not granted to the logged in user, then the user is blocked. If 
 * either there is no acl or the variable accessLevel is not present in 
 * the user instance this will return true.
 *
 * This implementation also requires the user to be logged in.
 *
 * @category   DNS
 * @package    VegaDNS
 * @subpackage Auth
 * @author     "Joe Stump" <joe@joestump.net>
 * @author     "Bill Shupp" <hostmaster@shupp.org>
 * @license    BSD http://www.opensource.org/licenses/bsd-license.php
 * @link       http://www.vegadns.org
 */
abstract class VegaDNS_Auth_ACL extends VegaDNS_Common
{
    /**
     * authenticate
     *
     * @access  public
     * @return  boolean
     */
    public function authenticate()
    {
        if (!parent::authenticate()) {
            return false;
        }
        if (!isset(Framework::$site->config->acl)) {
            return true;
        }
        foreach (Framework::$site->config->acl->class as $class) {
            if ($class['name'] == get_class($this)) {
                if ($class['event'] == Framework::$request->event) {
                    if (!(bool)$this->user->hasAccess($class['accessLevel'])) {
                        throw new Framework_Exception(
                            'The user does not have permissions to run this request',
                            FRAMEWORK_ERROR_AUTH_PERMISSIONS
                        );
                    }
                }
            }
        }
        return true;
    }
}

?>
