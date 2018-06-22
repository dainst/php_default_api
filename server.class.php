<?php
abstract class server {

    public $returnCode = "200";

	public $return = array();
	
	public $debug = array();
		
	public $settings = array();
		
	public $log = null;
	
	function __construct($data, $logger, $settings = array()) {
		$this->settings = $settings;
		$this->log = $logger;
		$this->data = $data;
		$this->debug = isset($this->settings['debug']) and $this->settings['debug'];
	}

	abstract function start();
	
	abstract function finish(); // not __destruct becuse it should not be called in case of error
	
	function errorHandler($fehlercode, $fehlertext, $fehlerdatei, $fehlerzeile) {
	    $this->returnCode = "500";
		$this->log->warning("Error $fehlercode in $fehlerdatei row $fehlerzeile: $fehlertext");
	}
	
	function call($task) {
		// security settings here

        if ($task === false) {
            throw new Exception("No task defined!");
        }
		
		if (!method_exists($this, $task)) {
            $this->returnCode = 404;
			throw new Exception("Task does not exist: " .  $task);
		}
		
		$this->$task();
	}
	
	
}

?>