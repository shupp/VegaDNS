<?php
/**
 * Deneb_Object_Interface 
 * 
 * PHP Version 5.3.0+
 * 
 * @category  Deneb
 * @package   Deneb
 * @author    Bill Shupp <hostmaster@shupp.org> 
 * @copyright 2010 Empower Campaigns
 * @link      http://github.com/empower/deneb
 * @license   http://www.opensource.org/licenses/bsd-license.php FreeBSD
 */

/**
 * Interface describing method signatures of all single objects
 * 
 * @category  Deneb
 * @package   Deneb
 * @author    Bill Shupp <hostmaster@shupp.org> 
 * @copyright 2010 Empower Campaigns
 * @link      http://github.com/empower/deneb
 * @license   http://www.opensource.org/licenses/bsd-license.php FreeBSD
 */
interface Deneb_Object_Interface
{
    /**
     * Consturctor is used for establishing select parameters.  Use an empty 
     * array for new objects.
     * 
     * @param array $args array of parameters for read requests
     * 
     * @throws Deneb_Exception_NotFound if you provide arguments, but the
     *         object is not found
     * @return Deneb_Object_Interface
     */
    public function __construct(array $args = array());

    /**
     * Creates a new entry into the data store based on the values you've set
     * through {__set()}
     * 
     * @param array $args Optional array of propertey/value pairs to be set
     *                    before creating the object in the data store
     * 
     * @see __set()
     * @return void
     * @throws Deneb_Exception on failure
     */
    public function create(array $args = array());

    /**
     * Updates modified values in the data store
     * 
     * @return void
     * @throws Deneb_Exception on failure
     */
    public function update();

    /**
     * Deletes an object in the data store.  The object must first be
     * instantiated, and the primary key set if the $id argument is not passed.
     * 
     * @param mixed $id Optional value of the object's primary key.  Use this 
     *                  if you do NOT want to have to do a read request before 
     *                  deleting the object.
     * 
     * @return void
     * @throws Deneb_Exception on failure
     */
    public function delete($id = null);

    /**
     * Sets a property's value
     * 
     * @param string $name  The name of the property to set
     * @param mixed  $value The value to set
     * 
     * @return void
     */
    public function __set($name, $value);

    /**
     * Returns a property's value if set, null otherwise
     * 
     * @param string $name The name of the property value to get
     * 
     * @return mixed|null
     */
    public function __get($name);
}
