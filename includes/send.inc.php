<?php
	function send () {
		global $sock;
		if (func_num_args () == 0) return false;
		$args = func_get_args ();
		$fmt = array_shift ($args);
		if (false === ($ret = @fputs ($sock, ($text = vsprintf ($fmt, $args)) . "\r\n")))
			alog ("[ERROR] Send Failed: '%s'", $text);
		return $ret;
	}

	function mychan () {
		global $conf;
		if (func_num_args () == 0) return false;
		$args = func_get_args ();
		$fmt = array_shift ($args);
		return send (vsprintf ('PRIVMSG ' . $conf['client']['main_channel'] . ' :' . $fmt, $args));
	}

	function plmsg () {
		if (func_num_args () < 1) return false;
		$args = func_get_args ();
		$fmt = array_shift ($args);
		$line = vsprintf ($fmt, $args) . "\r\n";
		if (is_array ($GLOBALS['partyline'])) {
			foreach ($GLOBALS['partyline'] as $sock)
				fputs ($sock, $line);
			return true;
		}
		return false;
	}

	function plsend () {
		if (func_num_args () < 2) return false;
		$args = func_get_args ();
		$sock = array_shift ($args);
		$fmt = array_shift ($args);
		$line = vsprintf ($fmt, $args);
		if (!preg_match ('/\r\n$/', $line)) $line .= "\r\n";
		return fputs ($sock, $line);
	}
?>