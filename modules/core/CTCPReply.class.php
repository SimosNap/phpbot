<?php
	class CTCPReply {
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
			if (strcasecmp ($args[0], MYNICK))
				return;

			list ($nick, $ident, $host) = preg_split ('/[!@]/', $source);
			if (preg_match ('/^\x01([^\s]+)(\s.+)?\x01$/', $args[1], $matches)) {
				switch (strtolower ($matches[1])) {
					case 'version':
						send ("NOTICE %s :\001VERSION SimosNap IRC Network :: Services Agent\001", $nick);
						break;
					case 'time':
						send (":%s NOTICE %s :\001TIME %s\001", $conf['client']['nick'], $nick, date ('D M d H:i:s Y'));
						break;
					case 'ping':
						send (":%s NOTICE %s :\001PING %s\001", $conf['client']['nick'], $nick, trim ($matches[2]));
						break;
				}
			}
		}
	}
?>