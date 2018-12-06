<?php
	class Administration {
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

		function handle_privmsg ($source, $handle, $args) {
			if (strcasecmp ($args[0], MYCHAN))
				return;

			$line = explode (' ', $args[1]);

			if (strcasecmp ($line[0], MYNICK))
				return;

			if (!UserManager::checkrights ($handle, 'n'))
				return;
	
			switch ($line[1]) {
				case 'rehash':
					mychan ("Reloading core modules ...");
					$mod_list = scandir (MODULES . 'core/');
					$GLOBALS['core_modules'] = array ();
					foreach ($mod_list as $module) {
						if (preg_match ('/^([^ ]+)\.class\.php$/', $module, $modname)) {
							if (@runkit_lint_file (MODULES . 'core/' . $module)) {
								@runkit_import (MODULES . 'core/' . $module);
								if (class_exists ($modname[1]))
									$GLOBALS['core_modules'][$modname[1]] = new $modname[1] ();
							} else {
								mychan ("Syntax error in %s", $modname[1]);
							}
						}
					}
					mychan ("Reloading modules ...");
					$mod_list = scandir (MODULES);
					$GLOBALS['modules'] = array ();
					foreach ($mod_list as $module) {
						if (preg_match ('/^([^ ]+)\.class\.php$/', $module, $modname)) {
							if (@runkit_lint_file (MODULES . $module)) {
								@runkit_import (MODULES . $module);
								if (class_exists ($modname[1]))
									$GLOBALS['modules'][$modname[1]] = new $modname[1] ();
							} else {
								mychan ("Syntax error in %s", $modname[1]);
							}
						}
					}
					mychan ("Rehash done !");
					break;
				default:
					$this->parse_command ($handle, $line);
			}
		}

		private function parse_command ($handle, $args) {
			switch ($args[1]) {
				case 'modlist':
					mychan ("\002Loaded Modules\002");
					foreach ($GLOBALS['core_modules'] as $name => $m)
						mychan ("\002[+]\002 %s (core)", $name);
					foreach ($GLOBALS['modules'] as $name => $m)
						mychan ("\002[+]\002 %s", $name);
					break;
				case 'modload':
					if (!preg_match ('/^[a-z]+$/i', $args[2])) { mychan ("Invalid module name (%s)", $args[2]); return; }
					if (array_key_exists ($args[2], $GLOBALS['modules'])) { mychan ("Module %s already loaded", $args[2]); return; }
					$name = sprintf ('%s%s.class.php', MODULES, $args[2]);
					if (!file_exists ($name . '.disabled')) { mychan ("Could not find %s", $args[2]); return; }
					rename ($name . '.disabled', $name);
					if (@runkit_lint_file ($name)) {
						@runkit_import ($name);
						if (class_exists ($args[2]))
							$GLOBALS['modules'][$args[2]] = new $args[2] ();
						mychan ("Module %s loaded", $args[2]);
					} else {
						mychan ("Syntax error in %s", $args[2]);
					}
					break;
				case 'modunload':
					if (!preg_match ('/^[a-z]+$/i', $args[2])) { mychan ("Invalid module name (%s)", $args[2]); return; }
					if (!array_key_exists ($args[2], $GLOBALS['modules'])) { mychan ("Module %s not loaded", $args[2]); return; }
					unset ($GLOBALS['modules'][$args[2]]);
					rename ($name = sprintf ('%s%s.class.php', MODULES, $args[2]), $name . '.disabled');
					mychan ("Module %s unloaded", $args[2]);
					break;
				case 'join':
					if (!preg_match ('/^#(.+)$/', $args[2]))
						return;
					mychan ("Entering %s ...", $args[2]);
					send ("JOIN %s", $args[2]);
					if (false !== ($out = fopen ('channels.dat', 'a'))) {
							fputs ($out, sprintf ("%s\n", $args[2]));
						fclose ($out);
					}
					break;
				case 'part':
					if (!preg_match ('/^#(.+)$/', $args[2]))
						return;
					mychan ("Leaving %s ...", $args[2]);
					send ("PART %s :Leaving", $args[2]);

					$f = "channels.dat";
					$part = $args[2]."\n";
					$arr = file($f);
					foreach ($arr as $key=> $line) {
						if(stristr($line,$part)!== false){unset($arr[$key]);break;}
					}
					$arr = array_values($arr);
					file_put_contents($f, implode($arr));

					break;
				case 'quit':
					$GLOBALS['quit'] = true;
					send ('QUIT :Leaving ...');
					break;
			}
		}
	}
?>