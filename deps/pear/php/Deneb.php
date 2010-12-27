<?php
/**
 * Deneb
 *
 * @category  Deneb
 * @package   Deneb
 * @author    Bill Shupp <hostmaster@shupp.org>
 * @copyright 2010 Empower Campaigns
 * @link      http://github.com/empower/deneb
 * @license   http://www.opensource.org/licenses/bsd-license.php FreeBSD
 */

/**
 * Required files
 */
require_once 'Deneb/Exception.php';
require_once 'Deneb/Exception/NotFound.php';
require_once 'Deneb/DB/Selector.php';

/**
 * Defines common implementations of methods and properties for
 * {@link Deneb_Object_Common} and {@link Deneb_Collection_Common}
 *
 * @category  Deneb
 * @package   Deneb
 * @author    Bill Shupp <hostmaster@shupp.org>
 * @copyright 2010 Empower Campaigns
 * @link      http://github.com/empower/deneb
 * @license   http://www.opensource.org/licenses/bsd-license.php FreeBSD
 */
abstract class Deneb
{
    /**
     * Instance of Zend_Application, providing access to the config and
     * log instances
     */
    static protected $_application = null;

    /**
     * Name of the general deneb exception to throw
     *
     * @see setExceptionName()
     */
    static protected $_exceptionName = 'Deneb_Exception';

    /**
     * Name of the not found deneb exception to throw
     *
     * @see setExceptionName()
     */
    static protected $_exceptionNotFoundName = 'Deneb_Exception_NotFound';

    /**
     * Instance of Zend_Log
     *
     * @see getLog()
     */
    static protected $_log = null;

    /**
     * The read instance of Zend_Db_Adapter
     *
     * @var Zend_Db_Adapter
     */
    protected $_readDB = null;

    /**
     * The write instance of Zend_Db_Adapter
     *
     * @var Zend_Db_Adapter
     */
    protected $_writeDB = null;

    /**
     * The name of the selector to use
     *
     * @var string
     */
    protected $_selector = 'default';

    /**
     * The name of the table for use in SQL queries
     *
     * @var string
     */
    protected $_table = null;

    /**
     * The object name for use in exceptions
     *
     * @var string
     */
    protected $_object = null;

    /**
     * The object property values
     *
     * @var array
     */
    protected $_values = array();

    /**
     * The query results
     *
     * @var array
     */
    protected $_results = array();

    /**
     * Instance of Deneb_DB_Selector
     *
     * @var Deneb_DB_Selector
     * @see getReadDB(), getWriteDB()
     */
    protected $_dbSelectorInstance = null;

    /**
     * Loads up the read and write Zend_Db_Adapter objects
     *
     * @return void
     */
    protected function _init()
    {
        $this->_dbSelectorInstance = $this->_createDBSelector();
    }

    // @codeCoverageIgnoreStart
    /**
     * Creates an instance of Deneb_DB_Selector.  Abstracted for testing.
     *
     * @return Deneb_DB_Selector
     */
    protected function _createDBSelector()
    {
        // Instantiate Zend_Db_Adapter instances for read and write
        return new Deneb_DB_Selector(self::getApplication(),
                                     $this->_selector);
    }
    // @codeCoverageIgnoreEnd

    protected function _determineWhere(array $args)
    {
        $where = '';
        foreach ($args as $key => $value) {
            if ($where != '') {
                $where .= ' AND';
            }
            if (is_array($value)) {
                if (count($value)) {
                    $quoted = array_map(
                        array($this, 'quoteArrayContents'), $value
                    );
                    $where .= ' ' . $key . ' IN (' . implode(',', $quoted) . ')';
                }
            } elseif ($value instanceof Zend_Db_Expr) {
                // Notice there's no assignment here - that's for use with
                // bitwise statements
                $where .= ' ' . $key . ' ' . $value;
            } else {
                $where .= ' ' . $key . '=' . $this->_getReadDB()->quote($value);
            }
        }

        if (strlen($where)) {
            $where = 'WHERE ' . $where;
        }

        return $where;
    }

    /**
     * Quotes the values in an array for use in an IN clause
     *
     * @param string $value The element value to quote
     *
     * @return string
     * @see array_map(), _determineWhere()
     */
    public function quoteArrayContents($value)
    {
        return $this->_getReadDB()->quote($value);
    }

    /**
     * Parses any limit, offset, order, group, and having options for the query
     *
     * @param array $options Options array
     *
     * @return string
     */
    protected function _determineOptions(array $options)
    {
        $this->getLog()->debug('options: ' . print_r($options, true));
        if (empty($options)) {
            return null;
        }

        $limits = '';

        if (isset($options['group'])) {
            $limits .= ' GROUP BY ' . $options['group'];
        }

        if (isset($options['having'])) {
            $limits .= ' HAVING ' . $options['having'];
        }

        if (isset($options['order'])) {
            $limits .= ' ORDER BY ' . $options['order'];
        }

        if (isset($options['limit'])) {
            $limits .= ' LIMIT ' . intval($options['limit']);
        }

        if (isset($options['offset'])) {
            $limits .= ' OFFSET ' . intval($options['offset']);
        }

        return $limits;
    }

    /**
     * Returns the {@link $_readDB}.  Abstracted for testing.
     *
     * @return Zend_Db_Adapter
     */
    protected function _getReadDB()
    {
        if ($this->_readDB === null) {
            $this->_readDB  = $this->_dbSelectorInstance->getReadInstance();
        }
        return $this->_readDB;
    }

    /**
     * Returns the {@link $_writeDB}.  Abstracted for testing.
     *
     * @return Zend_Db_Adapter
     */
    protected function _getWriteDB()
    {
        if ($this->_writeDB === null) {
            $this->_writeDB  = $this->_dbSelectorInstance->getWriteInstance();
        }
        return $this->_writeDB;
    }

    /**
     * Gets an instance of the logger
     *
     * @return Zend_Log
     */
    public function getLog()
    {
        if (self::$_log === null) {
            self::$_log = self::getApplication()->getBootstrap()
                                                ->getResource('Log');
        }
        return self::$_log;
    }

    /**
     * Sets a custom instance of Zend_Log
     *
     * @param Zend_Log $log The logger instance
     *
     * @return void
     */
    public static function setLog($log)
    {
        self::$_log = $log;
    }

    /**
     * Sets a local reference to your Zend_Application
     *
     * @param Zend_Application $application The Zend_Application instance
     *
     * @return void
     */
    static public function setApplication(Zend_Application $application)
    {
        self::$_application = $application;
    }

    /**
     * Returns the local reference to the Zend_Application instance
     *
     * @return Zend_Application
     */
    static public function getApplication()
    {
        return self::$_application;
    }

    /**
     * Sets the name of exception to use
     *
     * @param mixed $type base, db, notfound
     * @param mixed $name The class name to throw
     *
     * @return void
     * @throws Deneb_Exception on invalid type
     */
    static public function setExceptionName($type, $name)
    {
        switch ($type) {
            case 'base':
                self::$_exceptionName = $name;
                break;
            case 'notfound':
                self::$_exceptionNotFoundName = $name;
                break;
            default:
                throw new Deneb_Exception('Invalid exception type');
        }
    }
}
