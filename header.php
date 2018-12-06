<?php
	define ('BASEDIR', '/home/chatitaly/TubeBot/');

	define ('MODULES', BASEDIR . 'modules/');
	define ('INCLUDES', BASEDIR . 'includes/');
	define ('LOGS', $conf['logs_dir']);

	include (INCLUDES . 'db.inc.php');
	include (INCLUDES . 'send.inc.php');
	include (INCLUDES . 'common.inc.php');
?>
