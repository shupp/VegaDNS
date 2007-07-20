<?php

/**
 * Framework_DB
 *
 * Default Framework Database Driver
 *
 * @author Bill Shupp <hostmaster@shupp.org>
 * @license http://www.opensource.org/licenses/bsd-license.php
 * @package Framework
 * @filesource
 */

require_once 'DB.php';

/**
 * Framework_DB
 *
 * Default Framework Database Driver
 *
 * @author Bill Shupp <hostmaster@shupp.org>
 * @package Framework
 */
class Framework_DB 
implements Framework_DB_Interface
{
    /**
     * start 
     * 
     * @param mixed $dsn 
     * @access public
     * @return void
     */
    public function start($dsn)
    {
        Framework::$db = DB::connect($dsn);
        if (!PEAR::isError(Framework::$db)) {
            Framework::$db->setFetchMode(DB_FETCHMODE_ASSOC);
        } else {
            throw new Framework_Exception(Framework::$db);
        }
    }
    /**
     * stop 
     * 
     * @param mixed $db 
     * @access private
     * @return void
     */
    public function stop(&$db)
    {
        if ($db instanceof DB) {
            $db->disconnect();
        }
    }
}

?>
