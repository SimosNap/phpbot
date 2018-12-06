<?php

	class YoutubeLogger {
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

		/*function handle_timer ($args) {
        		if ($args[0] % 90 == 0) {
                		send ('PRIVMSG #chatitaly :https://www.chatitaly.it/tuber.html - Chatitaly YouTube Collection, la raccolta dei video condivisi dagli utenti in chat!', $args[0]);
			}
		}*/

		function handle_privmsg ($source, $handle, $args) {

			$word = explode (' ', $args[1]);

			$youtube_regexp = "/^((?:https?:)?\/\/)?((?:www|m)\.)?((?:youtube\.com|youtu.be))(\/(?:[\w\-]+\?v=|embed\/|v\/)?)([\w\-]+)(\S+)?$/";
			foreach ($word as $url) {
				preg_match($youtube_regexp, $url, $matches);
				$matches = array_filter($matches, function($var) {
					return($var !== '');
				});

				if (sizeof($matches) >= 5) {
					$youtube = $matches;
					$info = explode('!', $source);
					if (substr( $args[0], 0, 1 ) === "#") {
						query("INSERT INTO youtube (tubeid, nick, ts) values ('".$youtube[5]."', '".$info[0]."', ".time()." )" );
					}
				}
			}

		}
	}
?>
