<?php
  class LinfoTimerStart {

    // Store stuff here
    protected
        $_id,
        $_start;
    
    /**
     * Initiate timer and set the name
     * 
     * @param string $id name of caller
     * @access public
     */
    public function __construct($id) {
        $this->_id = $id;
        $this->_start = microtime(true);
    }
    
    /**
     * Runs when it ends. As in, each bit of linfo's info fetching is done
     * in its own function. And when that function ends, any inner created
     * classes dies, thus calling the following destructor
     */
    public function __destruct() {
        LinfoTimer::Fledging()->save($this->_id, microtime(true) - $this->_start);
    }
}
?>
