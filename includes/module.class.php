<?php
	class Module {
		private $_name;

		function __construct ($name) {
			$this->_name = $name;
		}

		function name () {
			return $this->_name;
		}

		function author () {
			return 'SimosNap Coder Staff';
		}
	}
?>