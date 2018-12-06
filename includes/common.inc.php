<?php
	function alog () {
		if (($arg = func_num_args ()) < 1)
			return;
		$args = func_get_args ();
		$fmt = array_shift ($args);
		$line = (($args > 1) ? (vsprintf ($fmt, $args)) : ($fmt));
		if ($log = fopen ('logs/' . date ('Ymd') . '.log', 'a')) {
			fwrite ($log, date ('[M d H:i:s Y] ') . $line . "\n");
			fclose ($log);
		}
	}

	function error_handler ($errno, $errstr, $errfile, $errline) {
		switch ($errno) {
			case E_ERROR:
				mychan ("\002Fatal error:\002 %s in %s on line %d", $errstr, $errfile, $errline);
				alog ("[ERROR] %s in %s on line %d", $errstr, $errfile, $errline);
				break;
			case E_WARNING:
				if (false !== strstr ($errstr, 'Administration::handle_privmsg()')) break;
				alog ("[WARNING] %s in %s on line %d", $errstr, $errfile, $errline);
				break;
			case E_PARSE:
				mychan ("\002Parse error:\002 %s in %s on line %d", $errstr, $errfile, $errline);
				alog ("[PARSE] %s in %s on line %d", $errstr, $errfile, $errline);
				break;
			case E_NOTICE:
				alog ("[NOTICE] %s in %s on line %d", $errstr, $errfile, $errline);
				break;
		}
		return true;
	}

	if (!function_exists ('runkit_lint_file')) {
		function runkit_lint_file ($filename) {
			if (!file_exists ($filename)) return false;
			return (strpos (shell_exec ('php -l ' . $filename), 'No syntax errors detected') === 0);
		}
	}
?>