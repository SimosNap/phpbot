<?php
	class ChannelsManager {
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
			if (false !== ($out = fopen ('channels.dat', 'w'))) {
				foreach ($GLOBALS['channels'] as $channel => $data) {
					fputs ($out, sprintf ("%s\n", $channel));
				}
				fclose ($out);
			}
		}

		public static function load () {
			$GLOBALS['channels'] = array ();
			if (false !== ($channels = @file ('channels.dat'))) {
				foreach ($channels as $channel) {
					send ('JOIN %s', $channel);
					send ('SAMODE %s +v %s', $channel, $conf['client']['nick']);
				}
			}
		}

		function handle_timer ($args) {
			if ($args[0] == 0 && $args[1] == 0) {
				ChannelsManager::save ();
			}
		}

		function onload () {
			ChannelsManager::load ();
		}

	}
?>
