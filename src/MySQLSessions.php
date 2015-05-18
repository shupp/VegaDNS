<?php

require_once 'src/VDB.php';

class MySQLSessions
{
    protected $table = 'php_sessions';
    static protected $instance = null;
    protected $db = null;

    protected function __construct()
    {
        $this->db = VDB::singleton();

        // Only check for new tables at /
        if(!isset($_REQUEST['state'])) {
            $this->createTableIfNotExists();
        }

        $this->setSaveHandlers();
        session_start();
    }

    public function open()
    {
        return true;
    }

    public function close()
    {
        return true;
    }

    public function read($id)
    {
        $q = "SELECT data FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->db->prepare($q);
        $stmt->execute(array(':id' => $id)) or die(print_r($stmt->errorInfo()));
        $result = $stmt->fetch();
        return $result['data'];
    }

    public function write($id, $data)
    {
        $access = time();
        $q = "REPLACE INTO " . $this->table . " VALUES (:id, :access, :data)";

        $params = array(
            ':id' => $id,
            ':access' => $access,
            ':data' => $data
        );
        $stmt = $this->db->prepare($q);
        $stmt->execute($params) or die(print_r($stmt->errorInfo()));
        return true;
    }

    public function destroy($id)
    {
        $q = "DELETE FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->db->prepare($q);
        $stmt->execute(array(':id' => $id)) or die(print_r($stmt->errorInfo()));
        return true;
    }

    public function gc($max)
    {
        // Calculate what is to be deemed old
        $old = time() - $max;
        $q = "DELETE * FROM " . $this->table . " WHERE access < :old";

        $stmt = $this->db->prepare($q);
        $stmt->execute(array(':old' => $old)) or die(print_r($stmt->errorInfo()));

        return true;
    }

    public function createTableIfNotExists()
    {
        $q = "CREATE TABLE IF NOT EXISTS `" . $this->table . "`
            (
              `id` varchar(32) NOT NULL,
              `access` int(10) unsigned DEFAULT NULL,
              `data` text,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB CHARSET=utf8;";
        $this->db->query($q) or die(print_r($this->db->errorInfo()));
    }

    protected function setSaveHandlers()
    {
        session_set_save_handler(
            array($this, "open"),
            array($this, "close"),
            array($this, "read"),
            array($this, "write"),
            array($this, "destroy"),
            array($this, "gc")
        );
    }

    public static function singleton()
    {
        if (self::$instance === null) {
            self::$instance = new MySQLSessions();
        }
        return self::$instance;
    }
}
?>
