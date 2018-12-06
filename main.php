#!/usr/bin/php
<?php 
	include ('config.php');
	include ('header.php');

	set_error_handler ('error_handler');

	define ('MYCHAN', $conf['client']['main_channel']);
	define ('MYNICK', $conf['client']['nick']);

	define ('VERSION', '1.0c');

	$min = intval (date ('i'));
	$quit = false;

	echo "PHPBot v" . VERSION . " - (author: SimosNap Coder Staff)\n";
	echo "\n";
	echo "[+] Loading core modules .. \n";
	$mod_list = scandir (MODULES . 'core/');
	$core_modules = array ();
	foreach ($mod_list as $module) {
		if (preg_match ('/^([^ ]+)\.class\.php$/', $module, $modname)) {
			$modname = $modname[1];
			echo "   @ $modname .. ";
			include (MODULES . 'core/' . $module);
			if (!class_exists ($modname)) die ("ERROR !\n\n");
			$core_modules[$modname] = new $modname ();
			echo "OK !\n";
		}
	}
	echo "[+] Core modules loaded .. OK !\n";
	echo "[+] Loading modules .. \n";
	$mod_list = scandir (MODULES);
	$modules = array ();
	foreach ($mod_list as $module) {
		if (preg_match ('/^([^ ]+)\.class\.php$/', $module, $modname)) {
			$modname = $modname[1];
			echo "   @ $modname .. ";
			include (MODULES . $module);
			if (!class_exists ($modname)) die ("ERROR !\n\n");
			$modules[$modname] = new $modname ();
			echo "OK !\n";
		}
	}
	echo "[+] Modules loaded .. OK !\n";
	echo "[+] Sending process to background .. ";

	if (($pid = pcntl_fork ()) < 0) {
		die ("ERROR !\n\n");
	} elseif ($pid == 0) {
		if (!($sock = @fsockopen (($conf['ircd']['ssl'] ? 'ssl://' : '') . $conf['ircd']['server'], $conf['ircd']['port'], $errno, $error, 30))) die ();
		send ('USER %s %s %s :%s', $conf['client']['ident'], $conf['client']['host'], $conf['client']['host'], $conf['client']['realname']);
		send ('NICK %s', $conf['client']['nick']);
		sleep(1);
		if ($conf['client']['oper'] != '') { 
			send ('OPER %s %s', $conf['client']['oper'], $conf['client']['operpass']);
		}
		//send ('MODE %s -x', $conf['client']['nick']);
		send ('JOIN %s', $conf['client']['main_channel']);
		send ('SAMODE %s +v %s', $conf['client']['main_channel'], $conf['client']['nick']);
		send ('PRIVMSG NickServ IDENTIFY bot %s', $conf['client']['nickpass']);
		//if (!connect ()) die ("ERROR !\n\n");
		foreach ($core_modules as $module) {
			if (method_exists ($module, 'onload')) {
				$module->onload ();
			}
		}
		foreach ($modules as $module) {
			if (method_exists ($module, 'onload')) {
				$module->onload ();
			}
		}
		while (true) {
			if ($min != intval (date ('i'))) {
				$min = intval (date ('i'));
				foreach ($core_modules as $module) {
					if (method_exists ($module, 'handle_timer')) {
						$module->handle_timer (explode (' ', date ('i H d m y')));
					}
				}
				foreach ($modules as $module) {
					if (method_exists ($module, 'handle_timer')) {
						$module->handle_timer (explode (' ', date ('i H d m y')));
					}
				}
			}

			if ($text = @fgets ($sock, 1024)) {
				foreach (explode ("\r\n", $text) as $cmd) {
					if (!strlen ($cmd)) continue;
					$command = false;
					if (preg_match ('/^:([^ ]+) ([^ ]+) (.+)$/', $cmd, $matches)) {
						$source = $matches[1];
						$command = strtolower ($matches[2]);
						if (false !== ($pos = strpos ($matches[3], ':'))) {
							$args = explode (' ', trim (substr ($matches[3], 0, $pos)));
							$args[] = substr ($matches[3], $pos + 1);
						} else {
							$args = explode (' ', trim ($matches[3]));
						}
					}
					if (preg_match ('/^([^: ][^ ]+) (.+)$/', $cmd, $matches)) {
						$source = '';
						$command = strtolower ($matches[1]);
						if (false !== ($pos = strpos ($matches[2], ':'))) {
							$args = explode (' ', trim (substr ($matches[2], 0, $pos)));
							$args[] = substr ($matches[2], $pos + 1);
						} else {
							$args = explode (' ', trim ($matches[2]));
						}
					}
					if (false !== $command) {
						$handle = UserManager::get_handle ($source);
						foreach ($core_modules as $name => $module) {
							$function = 'handle_' . $command;
							if (method_exists ($module, $function)) {
								$module->$function ($source, $handle, $args);
							}
						}
						foreach ($modules as $name => $module) {
							$function = 'handle_' . $command;
							if (method_exists ($module, $function)) {
								$module->$function ($source, $handle, $args);
							}
						}
					}
				} // foreach (
			} // if ($text = ...

			if ($quit) break;

			if (feof ($sock)) {
				@fclose ($sock);
				if (false !== ($log = @fopen ('errors.log', 'a'))) {
					fputs ($log, sprintf ("[%s] [SOCK] Socket disconnected\n", date ('M d H:i:s Y')));
					fclose ($log);
				}
				if (! ($sock = @fsockopen (($conf['ircd']['ssl'] ? 'ssl://' : '') . $conf['ircd']['server'], $conf['ircd']['port'], $errno, $error, 30))) {}
				send ('USER %s %s %s :%s', $conf['client']['ident'], $conf['client']['host'], $conf['client']['host'], $conf['client']['realname']);
				send ('NICK %s', $conf['client']['nick']);
				send ('JOIN %s', $conf['client']['main_channel']);
			}

		} // while (true)
		//disconnect ();
	} else {
		if ($fp = @fopen ('PID', 'w')) {
			@fwrite ($fp, $pid . "\n");
			@fclose ($fp);
		}
		echo "OK !\n";
		echo "\n";
		echo "Launched in background (pid: $pid)\n\n";
	}
?>
