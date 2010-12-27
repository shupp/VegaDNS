<?php
/**
 * Deneb_DB_Selector 
 * 
 * PHP Version 5.3.2+
 * 
 * @category   Deneb
 * @package    Deneb
 * @subpackage DB
 * @author     Bill Shupp <hostmaster@shupp.org> 
 * @copyright  2010 Empower Campaigns
 * @link       http://github.com/empower/deneb
 * @license    http://www.opensource.org/licenses/bsd-license.php FreeBSD
 */

/**
 * Required files 
 */
require_once 'Deneb/DB/Exception.php';
require_once 'Zend/Db.php';

/**
 * A light loader for DB connections based on the ZF config.
 * Here's an example application.ini section
 * 
 * <code>
 *  db.selectors.user = "default"
 *  db.adapter = 'PDO_MYSQL'
 *  db.pools.default.write.username = "app_write"
 *  db.pools.default.write.password = "secret"
 *  db.pools.default.write.host = "1.2.3.4"
 *  db.pools.default.write.port = "3306"
 *  db.pools.default.write.dbname = "production"
 *  db.pools.default.read.username = "app_read"
 *  db.pools.default.read.password = "secret"
 *  db.pools.default.read.host = "5.6.7.8"
 *  db.pools.default.read.port = "3306"
 *  db.pools.default.read.dbname = "production"
 * </code>
 * 
 * And here's example usage:
 * 
 * <code>
 *  $application = new Zend_Application(
 *      APPLICATION_ENV,
 *      APPLICATION_PATH . '/configs/application.ini'
 *  );
 *  $application->bootstrap();
 * 
 *  $selector = new Deneb_DB_Selector($application, 'user');
 *  $readDB   = $selector->getReadInstance();
 *  $writeDB  = $selector->getWriteInstance();
 * </code>
 * 
 * @category   Deneb
 * @package    Deneb
 * @subpackage DB
 * @author     Bill Shupp <hostmaster@shupp.org> 
 * @copyright  2010 Empower Campaigns
 * @link       http://github.com/empower/deneb
 * @license    http://www.opensource.org/licenses/bsd-license.php FreeBSD
 */
class Deneb_DB_Selector
{
    /**
     * The db.* portion of the application config as an array
     * 
     * @var array
     */
    protected $_dbConfig = null;

    /**
     * The pool that the selector points to
     * 
     * @var string
     */
    protected $_pool = null;

    /**
     * A static array of Zend_Db_Adapter instances
     * 
     * @var array
     */
    static protected $_instances = array();

    public function __construct(Zend_Application $application, $selector)
    {
        $this->_dbConfig = $application->getOption('db');
        if (!isset($this->_dbConfig['selectors'][$selector])) {
            throw new Deneb_DB_Exception(
                'Selector not defined: ' . $selector
            );
        }
        $this->_pool = $this->_dbConfig['selectors'][$selector];
    }

    /**
     * Returns a Zend_Db_Adapter for the 'read' pool of a selector
     * 
     * @see getInstance()
     * @return Zend_Db_Adapter
     */
    public function getReadInstance()
    {
        return $this->getInstance('read');
    }

    /**
     * Returns a Zend_Db_Adapter for the 'write' pool of a selector
     * 
     * @see getInstance()
     * @return Zend_Db_Adapter
     */
    public function getWriteInstance()
    {
        return $this->getInstance('write');
    }

    /**
     * Returns a Zend_Db_Adapter instance based on a selector.
     * 
     * @param string $type read or write
     * 
     * @return Zend_Db_Adapter
     */
    protected function getInstance($type)
    {
        if (!isset($this->_dbConfig['pools'][$this->_pool][$type])) {
            $details = 'pool: ' . $this->_pool . ', type: ' . $type;
            throw new Deneb_DB_Exception(
                'Pool not defined. Details: ' . $details
            );
        }
        if (!isset(self::$_instances[$this->_pool][$type])) {
            $instance = $this->_createInstance($type);
            self::$_instances[$this->_pool][$type] = $instance;
        }
        return self::$_instances[$this->_pool][$type];
    }

    // @codeCoverageIgnoreStart
    /**
     * Creates a Zend_Db_Adapter instance. Abstracted for testing.
     * 
     * @param string $type read or write
     * 
     * @return Zend_Db_Adapter_Pdo
     */
    protected function _createInstance($type)
    {
        return Zend_Db::factory(
            $this->_dbConfig['adapter'],
            $this->_dbConfig['pools'][$this->_pool][$type]
        );
    }
    // @codeCoverageIgnoreEnd
}
