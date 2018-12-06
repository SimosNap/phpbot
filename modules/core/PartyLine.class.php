<?php
	class PartyLine {
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

		private function parse_command ($handle, $args) {
			switch ($args[0]) {
				case 'modlist':
					if (!UserManager::checkrights ($handle, 'n')) return;
					plsend ($GLOBALS['partyline'][$handle], "\002Loaded Modules\002");
					foreach ($GLOBALS['core_modules'] as $name => $m)
						plsend ($GLOBALS['partyline'][$handle], "\002[+]\002 %s (core)", $name);
					foreach ($GLOBALS['modules'] as $name => $m)
						plsend ($GLOBALS['partyline'][$handle], "\002[+]\002 %s", $name);
					break;
				case 'modload':
					if (!UserManager::checkrights ($handle, 'n')) return;
					if (!preg_match ('/^[a-z]+$/i', $args[2])) { plmsg ("Invalid module name (%s)", $args[1]); return; }
					if (array_key_exists ($args[2], $GLOBALS['modules'])) { plmsg ("Module %s already loaded", $args[1]); return; }
					$name = sprintf ('%s%s.class.php', MODULES, $args[2]);
					if (!file_exists ($name . '.disabled')) { plmsg ("Could not find %s", $args[1]); return; }
					rename ($name . '.disabled', $name);
					if (@runkit_lint_file ($name)) {
						@runkit_import ($name);
						if (class_exists ($args[1]))
							$GLOBALS['modules'][$args[1]] = new $args[1] ();
						plmsg ("Module %s loaded", $args[1]);
					} else {
						plsend ($GLOBALS['partyline'][$handle], "Syntax error in %s", $args[1]);
					}
					break;
				case 'modunload':
					if (!UserManager::checkrights ($handle, 'n')) return;
					if (!preg_match ('/^[a-z]+$/i', $args[1])) { plmsg ("Invalid module name (%s)", $args[1]); return; }
					if (!array_key_exists ($args[1], $GLOBALS['modules'])) { plmsg ("Module %s not loaded", $args[1]); return; }
					unset ($GLOBALS['modules'][$args[1]]);
					rename ($name = sprintf ('%s%s.class.php', MODULES, $args[1]), $name . '.disabled');
					plmsg ("Module %s unloaded", $args[1]);
					break;
				case '+user':
					if (!UserManager::checkrights ($handle, 'm')) return;
					if (!preg_match ('/^[a-z0-9]+$/i', $args[1])) { plmsg ("Invalid handle (%s)", $args[1]); return; }
					if (!preg_match ('/^.+$/i', $args[2])) { plmsg ("Invalid password (%s)", $args[2]); return; }
					if (array_key_exists ($args[1], $GLOBALS['users'])) { plmsg ("User \002%s\002, already exists", $args[1]); return; }
					$GLOBALS['users'][$args[1]] = array ('password' => md5 ($args[2]), 'masks' => array (), 'rights' => array ());
					UserManager::save ();
					plsend ($GLOBALS['partyline'][$handle], "User \002%s\002 added", $args[1]);
					break;
				case '-user':
					if (!UserManager::checkrights ($handle, 'm')) return;
					if (!preg_match ('/^[a-z0-9]+$/i', $args[1])) { plmsg ("Invalid handle (%s)", $args[1]); return; }
					if (!array_key_exists ($args[1], $GLOBALS['users'])) { plmsg ("User \002%s\002 doesn't exists", $args[1]); return; }
					unset ($GLOBALS['users'][$args[1]]);
					UserManager::save ();
					plsend ($GLOBALS['partyline'][$handle], "User \002%s\002 removed.");
					break;
				case '+host':
					if (!UserManager::checkrights ($handle, 'n')) $args[1] = $handle;
					if (!preg_match ('/^[a-z0-9]+$/i', $args[1])) { plmsg ("Invalid handle (%s)", $args[1]); return; }
					if (!array_key_exists ($args[1], $GLOBALS['users'])) { plmsg ("User \002%s\002 doesn't exists", $args[1]); return; }
					if (!fnmatch ('*!*@*', $args[2])) { plmsg ("Invalid mask (%s)", $args[2]); return; }
					$GLOBALS['users'][$args[1]]['masks'][] = $args[2];
					UserManager::save ();
					plsend ($GLOBALS['partyline'][$handle], "Host %s added to %s's mask list", $args[2], $args[1]);
					break;
				case '-host':
					if (!UserManager::checkrights ($handle, 'n')) $args[1] = $handle;
					if (!preg_match ('/^[a-z0-9]+$/i', $args[1])) { plmsg ("Invalid handle (%s)", $args[1]); return; }
					if (!array_key_exists ($args[1], $GLOBALS['users'])) { plmsg ("User \002%s\002 doesn't exists", $args[1]); return; }
					if (false === ($i = array_search ($args[2], $GLOBALS['users'][$args[1]]['masks']))) { plmsg ("Host %s doesn't exists", $args[2]); return; }
					unset ($GLOBALS['users'][$args[1]]['masks'][$i]);
					UserManager::save ();
					plsend ($GLOBALS['partyline'][$handle], "Host %s removed from %s's mask list", $args[2], $args[1]);
					break;
				case 'passwd':
					if (!UserManager::checkrights ($handle, 'n')) $args[1] = $handle;
					if (!preg_match ('/^[a-z0-9]+$/i', $args[1])) { plmsg ("Invalid handle (%s)", $args[1]); return; }
					if (!preg_match ('/^.+$/i', $args[2])) { plmsg ("Invalid password (%s)", $args[2]); return; }
					if (!array_key_exists ($args[1], $GLOBALS['users'])) { plmsg ("User \002%s\002 doesn't exists", $args[1]); return; }
					$GLOBALS['users'][$args[1]]['password'] = md5 ($args[2]);
					UserManager::save ();
					plsend ($GLOBALS['partyline'][$handle], "Password changed for user %s", $args[1]);
					break;
				case 'chattr':
					if (!UserManager::checkrights ($handle, 'n')) return;
					if (!preg_match ('/^[a-z0-9]+$/i', $args[1])) { plmsg ("Invalid handle (%s)", $args[1]); return; }
					if (!preg_match ('/^[a-z+-]+$/i', $args[2])) { plmsg ("Invalid modes (%s)", $args[2]); return; }
					if (!array_key_exists ($args[1], $GLOBALS['users'])) { plmsg ("User \002%s\002 doesn't exists", $args[1]); return; }
					$mode = 0; $modes = implode ('', $GLOBALS['users'][$args[1]]['rights']);
					foreach (str_split ($args[2]) as $letter) {
						switch ($letter) {
							case '-': $mode = 1; break;
							case '+': $mode = 0; break;
							default: (($mode == 0) ? ($modes .= $letter) : (str_replace ($letter, '', $modes))); break;
						}
					}
					$GLOBALS['users'][$args[1]]['rights'] = str_split ($modes);
					plsend ($GLOBALS['partyline'][$handle], "User rights changed for %s", $args[1]);
					break;
				case 'who':
					plsend ($GLOBALS['partyline'][$handle], "\002Users List\002 (* = owner, ^ = master)");
					foreach ($GLOBALS['users'] as $handle => $data) {
						$char = '';
						if (in_array ('m', $data['rights'])) $char = '^';
						if (in_array ('n', $data['rights'])) $char = '*';
						plsend ($GLOBALS['partyline'][$handle], "%s %s (+%s)", $char, $handle, implode ('', $data['rights']));
					}
					break;
				case 'whois':
					if (!preg_match ('/^[a-z0-9]+$/i', $args[1])) { plmsg ("Invalid handle (%s)", $args[1]); return; }
					if (!array_key_exists ($args[1], $GLOBALS['users'])) { plmsg ("User \002%s\002 doesn't exists", $args[1]); return; }
					plsend ($GLOBALS['partyline'][$handle], "Handle: %s", $args[1]);
					plsend ($GLOBALS['partyline'][$handle], "Masks: %s", implode (' ', $GLOBALS['users'][$args[1]]['masks']));
					plsend ($GLOBALS['partyline'][$handle], "Rights: +%s", implode ('', $GLOBALS['users'][$args[1]]['rights']));
					break;
				case '+chan':
					if (!UserManager::checkrights ($handle, 'm')) return;
					if (!preg_match ('/^#(.+)$/', $args[1])) return;
					plsend ($GLOBALS['partyline'][$handle], "Entering %s ...", $args[1]);
					send ("JOIN %s", $args[1]);
					break;
				case '-chan':
					if (!UserManager::checkrights ($handle, 'm')) return;
					if (!preg_match ('/^#(.+)$/', $args[1])) return;
					plsend ($GLOBALS['partyline'][$handle], "Leaving %s ...", $args[1]);
					send ("PART %s :Leaving", $args[1]);
					break;
				case 'save':
					if (!UserManager::checkrights ($handle, 'm')) return;
					plmsg ('[%s] Saving users data ...', date ('H:i'));
					UserManager::save ();
					break;
				case 'reload':
					if (!UserManager::checkrights ($handle, 'n')) return;
					plsend ($GLOBALS['partyline'][$handle], 'Reloading users ...');
					UserManager::load ();
					break;
				case 'die':
					if (!UserManager::checkrights ($handle, 'n')) return;
					$GLOBALS['quit'] = true;
					send ('QUIT :Leaving ...');
					break;
				default:
					plsend ($GLOBALS['partyline'][$handle], "Invalid command (%s)", $args[0]);
					break;
			}
		}

		function handle_privmsg ($source, $handle, $args) {
			if (!UserManager::checkrights ($handle, 't')) 
				return;

			list ($nick, $ident, $host) = preg_split ('/[!@]/', $source);
			if (preg_match ('/^\x01DCC CHAT ([^\s]+) (\d+) (\d+)\x01$/', $args[1], $matches)) {
				if (($plid = pcntl_fork ()) == 0) {
					if ($pl = fsockopen (long2ip ($matches[2]), intval ($matches[3]), $errno, $error, 30)) {
						plsend ($pl, "Enter your password.");
						$logged = false;
						while (true) {
							if ($text = @fgets ($pl, 1024)) {
								$text = trim ($text, "\r\n");
								if (!$logged) {
									if (UserManager::check_password ($handle, $text)) {
										$logged = true;
										$GLOBALS['partyline'][$handle] = $pl;
										plmsg ('[%s] DCC connection from %s', date ('H:i'), $source);
										plmsg ('%s joined the party line.', $handle);
										if (false !== ($motd = file ('text/motd'))) {
											foreach ($motd as $line) {
												$line = str_replace (array ('<botnick>', '<version>', '<b>', '<nick>', '<time>'), array (MYNICK, VERSION, "\002", $nick, date ('H:i')), trim ($line));
												plsend ($GLOBALS['partyline'][$handle], $line);
											}
										}
									} else {
										plsend ($pl, 'Negative on that, Houston.');
										break 1;
									}
								} else {
									if (0 === strpos ($text, '.')) {
										$args = explode (' ', substr ($text, 1));
										switch ($args[0]) {
											case 'quit':
												plmsg ('[%s] DCC connection closed (%s)', date ('H:i'), $source);
												plmsg ('%s left the party line.', $handle);
												fclose ($GLOBALS['partyline'][$handle]);
												unset ($GLOBALS['partyline'][$handle]);
												break 2;
											case 'rehash':
												plsend ($GLOBALS['partyline'][$handle], 'Rehashing.');
												plmsg ('[%s] Reloading core modules ...', date ('H:i'));
												$mod_list = scandir (MODULES . 'core/');
												$GLOBALS['core_modules'] = array ();
												foreach ($mod_list as $module) {
													if (preg_match ('/^([^ ]+)\.class\.php$/', $module, $modname)) {
														if (@runkit_lint_file (MODULES . 'core/' . $module)) {
															@runkit_import (MODULES . 'core/' . $module);
															if (class_exists ($modname[1]))
																$GLOBALS['core_modules'][$modname[1]] = new $modname[1] ();
														} else {
															plmsg ('[%s] Syntax error in %s', date ('H:i'), $modname[1]);
														}
													}
												}
												plmsg ('[%s] Reloading modules ...', date ('H:i'));
												$mod_list = scandir (MODULES);
												$GLOBALS['modules'] = array ();
												foreach ($mod_list as $module) {
													if (preg_match ('/^([^ ]+)\.class\.php$/', $module, $modname)) {
														if (@runkit_lint_file (MODULES . $module)) {
															@runkit_import (MODULES . $module);
															if (class_exists ($modname[1]))
																$GLOBALS['modules'][$modname[1]] = new $modname[1] ();
														} else {
															plmsg ('[%s] Syntax error in %s', date ('H:i'), $modname[1]);
														}
													}
												}
												break;
											default:
												$this->parse_command ($handle, $args);
										}
										plmsg ('[%s] <%s> %s', date ('H:i'), $nick, $args[0]);
									} else {
										plmsg ('[%s] <%s> %s', date ('H:i'), $nick, $text);
									}
								}
							}
						}
						@fclose ($pl);
						die ();
					}
				}
			}
		}
	}
?>