<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Framework_DB_Interface
 *
 * @author      Bill Shupp <hostmaster@shupp.org>
 * @copyright   Bill Shupp <hostmaster@shupp.org>
 * @package     Framework
 * @subpackage  DB
 * @filesource
 */

/**
 * Framework_DB_Interface
 *
 * All Framework_DB drivers must implement this so they all behave in
 * the same basic manner. 
 *
 * @author      Bill Shupp <hostmaster@shupp.org
 * @package     Framework
 * @subpackage  DB
 */
interface Framework_DB_Interface
{
    /**
     * start 
     * 
     * @param mixed $dsn 
     * @access public
     * @return void
     */
    public function start($dsn);
    /**
     * stop 
     * 
     * @param mixed $db 
     * @access public
     * @return void
     */
    public function stop(&$db);
}

?>
