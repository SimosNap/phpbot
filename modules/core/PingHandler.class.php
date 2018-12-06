<?php
	class PingHandler {
		private $_name;

		function __construct () {
			$this->_name = __CLASS__;
		}

		function name () {
			return $this->_name;
		}

		function author () {
			return 'SimosNap Coder Staff';
		}

		function handle_ping ($source, $handle, $args) {
			send ('PONG :%s', $args[0]);
		}
	}
?>