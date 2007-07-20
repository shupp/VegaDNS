<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Framework_User
 *
 * @author Joe Stump <joe@joestump.net>
 * @copyright Joe Stump <joe@joestump.net>
 * @license http://www.opensource.org/licenses/bsd-license.php
 * @package Framework
 * @filesource
 */

/**
 * Framework_User
 *
 * This is the default user class. The default behavior is to take the $userID
 * passed to the constructor and fetch the record based on the various
 * FRAMEWORK_USER_* defines (see above). The PHP5 overloading is then used to
 * fetch and set data to the user class. NOTE: this class does NOT interface
 * with the database other than to select basic information.
 *
 * @author Joe Stump <joe@joestump.net>
 * @package Framework
 */
class Framework_User extends Framework_Object_DB
{
    /**
     * $data
     *
     * @access private
     * @var array $data Record from users table
     */
    protected $data = array();

    /**
     * __construct
     *
     * @author Joe Stump <joe@joestump.net>
     * @access public
     * @return void
     */
    public function __construct($userID=null)
    {
        parent::__construct();
        $this->getUserData($userID);

    }

    protected function getUserData($userID)
    {
        if (is_null($userID)) {
            $session = & Framework_Session::singleton(); 
            $userID = $session->{Framework::$site->config->user->userField};
            if (is_null($userID)) {
                $userID = (string)Framework::$site->config->user->defaultUser; 
            } else {
                $userID = $session->{(string)Framework::$site->config->user->userField};
            }
        } 

        $sql = "SELECT *
                FROM ".Framework::$site->config->user->userTable."
                WHERE ".Framework::$site->config->user->userField."='".$userID."'";

        $result = $this->db->getRow($sql);
        if (!PEAR::isError($result) && is_array($result)) {
            $this->data = $result;
        } else {
            throw new Framework_Exception('Could not look up userID');
        }
    }

    /**
     * toArray - Overriding base toArray to make it useful
     *
     * Traverses self::data and returns that information
     *
     * @return  array
     */
    function toArray()
    {
        $aArray = Array();
        
        foreach($this->data as $k=>$v) {
            $aArray[$k] = $v;
        }
        
        return $aArray;
    }
    
    /**
     * __get
     *
     * @access public
     * @param string $var Name of var 
     * @return mixed Null if the var does not exist, var's value if it does 
     */
    public function __get($var)
    {
        if (isset($this->data[$var])) {
            return $this->data[$var];
        }

        return null;
    }

    /**
     * __set
     *
     * @access public
     * @param string $var Name of var to set
     * @param mixed $val Value of given var
     * @return void
     * @see Framework_User::$data
     */
    public function __set($var,$val) 
    {
        $this->data[$var] = $val;
    }

    /**
     * __isset  
     * 
     * @access public
     * @param string $var Name of var to check if it's in $data
     * @return boolean
     * @see Framework_User::$data
     */
    public function __isset($var)
    {
        return isset($this->data[$var]);
    }

    /**
     * isDefault
     *
     * @access public
     * @return boolean
     */
    public function isDefault()
    {
        return ($this->{Framework::$site->config->user->userField} == Framework::$site->config->user->defaultUser);
    }

    /**
     * singleton
     *
     * @access public
     * @return mixed PEAR_Error on failure, user class on success
     * @static
     */
    static public function singleton()
    {
        static $user = null;
        
        if (is_null($user)) {
            if (!isset(Framework::$site->config->user->userClass)) {
                $file = null;
                $class = 'Framework_User';
            } else {
                $file = 'Framework/User/'.Framework::$site->config->user->userClass.'.php';
                $class = 'Framework_User_'.Framework::$site->config->user->userClass;
            }
            
            if (!is_null($file)) {
                if (!include_once($file)) {
                    return PEAR::raiseError('Could not load class file: '.$file);
                }
            }

            if (class_exists($class)) {
                $user = new $class();
            } else {
                return PEAR::raiseError('Unable to load class: '.Framework::$site->userClass);
            }
        }

        return $user;
    }
}

?>
