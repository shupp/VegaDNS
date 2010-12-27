<?php
/**
 * Deneb_Object_Common
 *
 *
 * @uses      Deneb
 * @uses      Deneb_Object_Interface
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
require_once 'Deneb.php';
require_once 'Deneb/Object/Interface.php';

/**
 * Implementation of single object Denebs.
 *
 * @uses      Deneb
 * @uses      Deneb_Object_Interface
 * @category  Deneb
 * @package   Deneb
 * @author    Bill Shupp <hostmaster@shupp.org>
 * @copyright 2010 Empower Campaigns
 * @link      http://github.com/empower/deneb
 * @license   http://www.opensource.org/licenses/bsd-license.php FreeBSD
 */
abstract class Deneb_Object_Common
    extends Deneb
    implements Deneb_Object_Interface
{
    /**
     * The primary key name
     *
     * @var string
     */
    protected $_primaryKey = 'id';

    /**
     * Whether to automatically populate a date_created column with the
     * current timestamp.  Defaults to false
     *
     * @var bool
     */
    protected $_enableDateCreated = false;

    /**
     * Calls {@link _init()} and sets the values of arguments were passed
     *
     * Read example:
     * <code>
     * $user = new Deneb_User(array('username' => 'shupp'));
     * </code>
     *
     *
     * Create example:
     * <code>
     * $user = new Deneb_User();
     * $user->username = 'shupp';
     * $user->email = 'bshupp@empowercampaigns.com';
     * $user->create();
     * </code>
     *
     * @param array $args Arguments to used in a "where clause"
     *
     * @return void
     */
    public function __construct(array $args = array())
    {
        $this->_init();

        if (!empty($args)) {
            $where = $this->_determineWhere($args);

            $sql = "SELECT * FROM {$this->_table} $where";
            $this->getLog()->debug("Object SQL: " . $sql);
            $this->_results = $this->_getReadDB()->fetchAll($sql);
            if (!count($this->_results)) {
                throw new static::$_exceptionNotFoundName(
                    'No ' . $this->_object . ' found: '

                    . print_r($args, true)
                );
            }
            $this->_values = current($this->_results);
        }
    }

    /**
     * Magic __get() method implementation.  Allows for easy property access
     *
     * <code>
     * $username = $user->username;
     * </code>
     *
     * @param mixed $name
     *
     * @return mixed|null
     */
    public function __get($name)
    {
        if (isset($this->_values[$name])) {
            return $this->_values[$name];
        }
        return null;
    }

    /**
     * Magic __set() method implementation.  Allows for easy property value
     * assignment
     *
     * <code>
     * $user->username = 'shupp';
     * </code>
     *
     * @param string $name  The property name to set
     * @param mixed  $value The value to set
     *
     * @return mixed|null
     */
    public function __set($name, $value)
    {
        $this->_values[$name] = $value;
    }

    /**
     * Magic __unset () method implementation.  Allows for easily unsetting
     * property values.
     *
     * @param string $name The property value to unset
     *
     * @return void
     */
    public function __unset($name)
    {
        unset($this->_values[$name]);
    }

    /**
     * Magic __isset() method implementation.  Allows for easy checking
     * whether a value is set or not
     *
     * @param string $name The property name to check
     *
     * @return bool
     */
    public function __isset($name)
    {
        return isset($this->_values[$name]);
    }

    /**
     * Sets all values in an object, removing any previously set values.
     *
     * @param array $values Associative array of property/values
     *
     * @return void
     */
    public function set(array $values)
    {
        $this->_values = $values;
    }

    /**
     * Gets the current property values of an object as an associative array
     *
     * @return array
     */
    public function get()
    {
        return $this->_values;
    }

    /**
     * Creates an object in the data store.
     *
     * @param array $args
     *
     * @return void
     */
    public function create(array $args = array())
    {
        if (!empty($args)) {
            $this->set($args);
        }

        if (isset($this->_values[$this->_primaryKey])) {
            throw new static::$_exceptionName('Primary key value already set');
        }

        if ($this->_enableDateCreated
            && !isset($this->_values['date_created'])) {

            $this->_values['date_created'] = new Zend_Db_Expr('NOW()');
        }

        $this->_getWriteDB()->insert($this->_table, $this->_values);
        $id = $this->_getWriteDB()->lastInsertId();
        $this->_values[$this->_primaryKey] = $id;
    }

    /**
     * Stores any changes to an object in the data store
     *
     * @return void
     * @throws Deneb_Exception on failure
     */
    public function update()
    {
        if (!isset($this->_values[$this->_primaryKey])) {
            throw new static::$_exceptionName('Primary key value is not set');
        }

        $where = "{$this->_primaryKey} = {$this->_values[$this->_primaryKey]}";
        $this->_getWriteDB()->update($this->_table, $this->_values, $where);
    }

    /**
     * Deletes an object form the data store
     *
     * @param mixed $id Optional primary key value to use instead of the
     *                  current object's value. Used when you don't want to
     *                  determine first if an object exists
     *
     * @return void
     */
    public function delete($id = null)
    {
        if ($id !== null) {
            $this->{$this->_primaryKey} = $id;
        }

        if (!isset($this->_values[$this->_primaryKey])) {
            throw new static::$_exceptionName('Primary key value is not set');
        }

        $where  = $this->_primaryKey . ' = ';
        $where .= $this->_values[$this->_primaryKey];
        $this->_getWriteDB()->delete($this->_table, $where);
    }

    /**
     * Returns {@see $_primaryKey}
     *
     * @return string
     */
    public function getPrimaryKey()
    {
        return $this->_primaryKey;
    }
}
