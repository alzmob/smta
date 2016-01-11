<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.4.3/jquery.min.js"></script>
<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.5/jquery-ui.min.js"></script>
<pre style="font-Size:8pt;">
<?php
	if (isset($_REQUEST['type']) && $_REQUEST['type'] == 'startup') { 
		$cmd = 'sudo ' . MO_WEBAPP_DIR . '/meta/crons/pmta.sh logstartup';
		$pmta_status_lines = shell_exec($cmd);
		$lines = explode("\n", $pmta_status_lines);
		$lines = array_reverse($lines);
		
		echo "PMTA Startup Logs for " . trim(`hostname`) . " on " . date('m/d/Y g:i:s a') . "\n";
		echo str_repeat("-", 80) . "\n";
		if (count($lines) > 1) {
			echo implode("\n", $lines);
		} else {
			echo "No log found, check /var/log/messages";
		}	
	} else {
		$cmd = 'sudo ' . MO_WEBAPP_DIR . '/meta/crons/pmta.sh log';
		$pmta_status_lines = shell_exec($cmd);
		$lines = explode("\n", $pmta_status_lines);
		$lines = array_reverse($lines);
		
		echo "PMTA Logs for " . trim(`hostname`) . " on " . date('m/d/Y g:i:s a') . "\n";
		echo str_repeat("-", 80) . "\n";
		if (count($lines) > 1) {
			echo implode("\n", $lines);
		} else {
			echo "No log found, check /var/log/pmta/log";
		}
	}
?>
</pre>
<script>
	$(document).ready(function() {
		window.setInterval(reloadpage, 5000);
		function reloadpage(){location.reload();}
	});
</script>