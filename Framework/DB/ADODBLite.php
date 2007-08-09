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

require_once 'adodb_lite/adodb-exceptions.inc.php';
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
        try {
            Framework::$db = ADONewConnection($dsn);
        } catch (Exception $e) {
            throw new Framework_Exception($e->getMessage());
        }
        $GLOBALS['ADODB_FETCH_MODE'] = ADODB_FETCH_ASSOC;
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
        if ($db->IsConnected()) {
            $db->Close();
        }
    }
}

?>
