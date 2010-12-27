<?php
/**
 * Deneb_Collection_Common
 *
 * @uses      Deneb
 * @uses      Iterator, Countable
 * @category  Deneb
 * @package   Deneb
 * @author    Bill Shupp <hostmaster@shupp.org>
 * @copyright 2010 Empower Campaigns
 * @license   http://www.opensource.org/licenses/bsd-license.php FreeBSD
 * @link      http://github.com/empower/deneb
 */

/**
 * Required file
 */
require_once 'Deneb.php';

/**
 * Base class for all colletions of single objects
 *
 * @uses      Deneb
 * @uses      Iterator, Countable
 * @category  Deneb
 * @package   Deneb
 * @author    Bill Shupp <hostmaster@shupp.org>
 * @copyright 2010 Empower Campaigns
 * @license   http://www.opensource.org/licenses/bsd-license.php FreeBSD
 * @link      http://github.com/empower/deneb
 */
abstract class Deneb_Collection_Common
extends Deneb
implements Iterator, Countable
{
    /**
     * Name of the singlur instance.  i.e. "User" for "Deneb_User"
     *
     * @var string
     */
    protected $_object = null;

    /**
     * The current position of the collection
     *
     * @var int
     * @see Iterator
     */
    protected $_position = 0;

    /**
     * Array of single object instances
     *
     * @var array
     */
    protected $_collection = array();

    /**
     * Array of single object instances, indexed by primary key
     *
     * @var array
     */
    protected $_collectionByPrimaryKey = array();

    /**
     * Arguments passed to the constructor, used in later calls e.g. countAll()
     * @var array
     */
    protected $_args = array();

    /**
     * Options passed to the constructor, used in later calls
     * @var array
     */
    protected $_options = array();

    /**
     * Sets parameters and options for collection inclusion, then optionally talks to
     *   the data store and instantiates the objects
     *
     * @param array $args    The key/values that can be used in a where clause
     * @param array $options Optional limits, offset, order, group, having, fetch
     *
     * @return void
     */
    public function __construct(array $args, array $options = array())
    {
        $this->_position = 0;
        $this->_init();
        $this->_args    = $args;
        $this->_options = $options;

        if (!isset($options['fetch']) or $options['fetch'] == true) {
            $this->fetch();
        }
    }

    /**
     * Fetch collection objects - usually done by constuctor, but not if
     *   options['fetch'] = false
     *
     * @return null
     */
    public function fetch()
    {
        $where  = $this->_determineWhere($this->_args);
        $limits = $this->_determineOptions($this->_options);

        $sql = "SELECT * FROM {$this->_table} $where $limits";
        $this->getLog()->debug('Collection SQL: ' . $sql);
        $this->_results = $this->_getReadDB()->fetchAll($sql);
        if (!count($this->_results)) {
            throw new static::$_exceptionNotFoundName(
                'No ' . $this->_name . ' objects found'
            );
        }
        $this->_loadObjects();
    }

    /**
     * Takes {$this->_results} and loads it up into Deneb_Object_Common
     * instances, stored in {$this->_collection}
     *
     * @return void
     */
    protected function _loadObjects()
    {
        foreach ($this->_results as $item) {
            $class    = $this->_object;
            $instance = new $class;
            $instance->set($item);
            $this->_collection[] = $instance;
            $pk                  = $instance->getPrimaryKey();

            $this->_collectionByPrimaryKey[$instance->{$pk}] = $instance;
        }
    }

    /**
     * Returns an object by the primary key of the table
     *
     * @param mixed $key The primary key of the table
     *
     * @return Deneb_Object_Common
     */
    public function getByPrimaryKey($key)
    {
        if (!isset($this->_collectionByPrimaryKey[$key])) {
            throw new static::$_exceptionNotFoundName(
                'Primary key not found: ' . $key
            );
        }
        return $this->_collectionByPrimaryKey[$key];
    }

    /**
     * Returns an array of the primary keys
     *
     * @return array
     */
    public function getPrimaryKeys()
    {
        return array_keys($this->_collectionByPrimaryKey);
    }

    /**
     * Returns the total number of objects that would be returned under the specified
     *   conditions (ignoring options)
     *
     * @return int
     */
    public function countAll()
    {
        $where  = $this->_determineWhere($this->_args);
        $sql = "SELECT COUNT(*) FROM {$this->_table} $where";
        $this->getLog()->debug('Collection countAll SQL: ' . $sql);
        return (int) $this->_getReadDB()->query($sql)->fetchColumn();
    }

    /**
     * Rewinds the collection interator to position 0
     *
     * @see Iterator
     * @return void
     */
    public function rewind()
    {
        $this->_position = 0;
    }

    /**
     * Returns the current collection object
     *
     * @return Deneb_Object_Common
     * @see Iterator
     */
    public function current()
    {
        return $this->_collection[$this->_position];
    }

    /**
     * Returns the numeric position
     *
     * @return int
     * @see Iterator
     */
    public function key()
    {
        return $this->_position;
    }

    /**
     * Increments the position
     *
     * @return void
     * @see Iterator
     */
    public function next()
    {
        ++$this->_position;
    }

    /**
     * Validates whether the current {$_position} exists
     *
     * @return bool
     * @see Iterator
     */
    public function valid()
    {
        return isset($this->_collection[$this->_position]);
    }

    /**
     * Implements the Countable interface
     *
     * @return int
     */
    public function count()
    {
        return count($this->_collection);
    }
}
