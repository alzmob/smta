<?php
	/* @var $drop_log \Smta\Link\DropLog */
	$drop_log = $this->getContext()->getRequest()->getAttribute('drop_log', array());
?>
<div class="modal-header">
	<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
	<h4 class="modal-title">View Log</h4>
</div>
<div class="modal-body">
	<div style="background-color:#0f0f0f;overflow:auto;max-width:1000px;min-height:250px;">
		<code id="drop_raw_log_contents" style="background-color:#0f0f0f;height:700px;"><?php echo $drop_log->getLogContents() ?></code>
	</div>
</div>
<div class="modal-footer">
	<form id="drop_log_refresh_form" method="GET" action="/api/drop-raw-log">
		<input type="hidden" name="_id" value="<?php echo $drop_log->getId() ?>" />
		<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
		<input type="submit" class="btn btn-primary" value="Refresh Log" id="refresh_log_btn" />
	</form>
</div>	
<script>
//<!--
$(document).ready(function() {	

	$('#drop_log_refresh_form').form(function(data) {
		if (data.record) {
			$('#drop_raw_log_contents').html(data.record.log_contents);
		}
	},{keep_form: true});
});
//-->
</script>