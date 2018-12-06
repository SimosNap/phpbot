<?php
	class UserManager {
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

		public static function save () {
			if (false !== ($out = fopen ('users.dat', 'w'))) {
				foreach ($GLOBALS['users'] as $handle => $data) {
					fputs ($out, sprintf ("%s\t%s\t%s\t%s\n", $handle, $data['password'], implode ('|', $data['masks']), implode ('', $data['rights'])));
				}
				fclose ($out);
			}
		}

		public static function load () {
			$GLOBALS['users'] = array ();
			if (false !== ($users = @file ('users.dat'))) {
				foreach ($users as $user) {
					if (preg_match ('/^([a-z0-9]+)\t([a-f0-9]{32})\t([^\t]*)\t([a-z]*)\n$/i', $user)) {
						list ($handle, $password, $masks, $rights) = explode ("\t", trim ($user));
						$GLOBALS['users'][$handle] = array ('password' => $password, 'masks' => explode ('|', $masks), 'rights' => str_split ($rights));
					}
				}
			}
		}

		function handle_timer ($args) {
			if ($args[0] == 0 && $args[1] == 0) {
				UserManager::save ();
			}
		}

		function onload () {
			UserManager::load ();
		}

		public static function checkrights ($handle, $right) {
			if (!is_string ($handle)) return false;
			if (!array_key_exists ($handle, $GLOBALS['users'])) return false;
			return in_array ($right, $GLOBALS['users'][$handle]['rights']);
		}

		public static function check_password ($handle, $password) {
			if (!is_string ($handle)) return false;
			if (!array_key_exists ($handle, $GLOBALS['users'])) return false;
			return (strcmp ($GLOBALS['users'][$handle]['password'], md5 ($password)) == 0);
		}

		public static function get_handle ($usermask) {
			foreach ($GLOBALS['users'] as $handle => $data)
				foreach ($data['masks'] as $mask)
					if (fnmatch ($mask, $usermask))
						return $handle;
			return false;
		}
	}
?>