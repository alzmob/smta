<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.4.3/jquery.min.js"></script>
<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.5/jquery-ui.min.js"></script>
<pre style="font-Size:8pt;">
<?php
	$cmd = 'sudo ' . MO_WEBAPP_DIR . '/meta/crons/pmta.sh console';
	$pmta_status_lines = shell_exec($cmd);
	$lines = explode("\n", $pmta_status_lines);
	if (count($lines) > 1) {
		echo implode("\n", $lines);
	} else {
		echo "No log found, check pmta show status";
	}	
?>
</pre>
<script>
	$(document).ready(function() {
		window.setInterval(reloadpage, 5000);
		function reloadpage(){location.reload();}
	});
</script>