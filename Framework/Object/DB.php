<?php

/**
 * Framework_Object_DB
 *
 * @author Joe Stump <joe@joestump.net>
 * @copyright Joe Stump <joe@joestump.net> 
 * @license http://www.opensource.org/licenses/bsd-license.php
 * @package Framework
 * @subpackage Object
 * @filesource
 */

require_once 'DB.php';

/**
 * Framework_Object_DB
 *
 * Extends the base Framework_Object class to include a database connection. If
 * your class requires a database connection you will want to extend from this
 * class. 
 *
 * @author Joe Stump <joe@joestump.net>
 * @package Framework
 * @subpackage Object
 * @see Framework_Object
 */
abstract class Framework_Object_DB extends Framework_Object
{
    /**
     * $db
     *
     * @access protected
     * @var object $db
     * @see DB
     */
    protected $db = null;

    /**
     * driver 
     * 
     * Database Driver class
     * 
     * @static
     * @var mixed
     * @access protected
     */
    static protected $dbDriver = null;

    /**
     * __construct
     *
     * @access public
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->db = & self::createDB();
    }

    /**
     * createDB
     *
     * @access  private
     * @return  reference
     */
    static private function &createDB()
    {
        if (is_null(self::$dbDriver)) {
            $dsn = null;
            $class = 'Framework_DB';
            $file = null;
            if (isset(Framework::$site->config->dbClass)) {
                $class = 'Framework_DB_' . (string)Framework::$site->config->dbClass;
                $file = 'Framework/DB/' . Framework::$site->config->dbClass.'.php';
            }

            if (isset(Framework::$site->config->dsn)) {
                $dsn = (string)Framework::$site->config->dsn;
            } else {
                $class = 'Framework_DB_None';
            }

            if (!is_null($file)) {
                if (!include_once($file)) {
                    return PEAR::raiseError('Could not load class file: '.$file);
                }
            }

            self::$dbDriver = new $class;
        }

        if (is_null(Framework::$db)) {
            self::$dbDriver->start($dsn);
        }

        return Framework::$db;
    }

    /**
     * __sleep
     *
     * @access  public
     * @return  void
     */
    public function __sleep()
    {
        $this->db = null;
    }

    /**
     * __wakeup
     *
     * @access  public
     * @return  void
     */
    public function __wakeup()
    {
        $this->db = & self::createDB();
    }

    /**
     * stopDB
     * 
     * Disconnect DB
     * 
     * @access public
     * @return void
     */
    private function stopDB()
    {
        if (!is_null($this->dbDriver)) {
            $this->dbDriver->stop($this->db);
        }
    }

    /**
     * __destruct
     * 
     * @access public
     * @return void
     */
    public function __destruct()
    {
        $this->stopDB();
        parent::__destruct();
    }
}

?>
