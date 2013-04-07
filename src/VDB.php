<?php

class VDB
{
    static protected $_instance = null;
    public static function singleton()
    {
        if (self::$_instance === null) {
            // Let exceptions bubble up for now
            self::$_instance = new PDO(
                'mysql:dbname=' . $GLOBALS['mysql_db'] .';host=' . $GLOBALS['mysql_host'],
                $GLOBALS['mysql_user'],
                $GLOBALS['mysql_pass']
            );
        }

        return self::$_instance;
    }
}
