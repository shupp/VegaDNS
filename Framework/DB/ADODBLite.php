<?php

/**
 * Framework_DB_ADODBLite
 *
 * ADODB Lite Framework Database Driver
 *
 * @author Bill Shupp <hostmaster@shupp.org>
 * @license http://www.opensource.org/licenses/bsd-license.php
 * @package Framework
 * @filesource
 */

require_once 'adodb_lite/adodb.inc.php';

/**
 * Framework_DB_ADODBLite
 *
 * ADODB Lite Framework Database Driver
 *
 * @author Bill Shupp <hostmaster@shupp.org>
 * @package Framework
 */
class Framework_DB_ADODBLite
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
        Framework::$db = ADONewConnection($dsn);
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
        if (is_object($db)) {
            $db->Close();
        }
    }
}

?>
