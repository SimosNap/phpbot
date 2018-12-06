<?php
	define ('HOST', 'localhost');
	define ('USER', 'phpbot');
	define ('PASS', 'changethis');
	define ('DB', 'phpbot');

	$db = null;

	function connect () {
		global $db;
		if (false === ($db = mysqli_connect(HOST, USER, PASS, DB))) return false;
		//return mysql_select_db (DB, $db);
	}

	function query () {
		global $db;
		if (!is_resource ($db)) connect ();
		if (func_num_args () == 0) return false;
		$args = func_get_args ();
		$fmt = array_shift ($args);
		$ret = $db->query ($fmt);
		//var_dump($fmt);
		//sleep (2);
		/*if (mysql_errno () != 0) {
			if (false !== ($log = @fopen ('mysql_error.log', 'a'))) {
				fputs ($log, sprintf ("[%s] ERROR #%d: %s\n[%s] query: %s\n", date ('M d H:i:s Y'), mysql_errno (), mysql_error (), date ('M d H:i:s Y'), $query));
				fclose ($log);
			}
		}*/
		return $ret;
	}

	function disconnect () {
		global $db;
		return mysql_close ($db);
	}
?>
