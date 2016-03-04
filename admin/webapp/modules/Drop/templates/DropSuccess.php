<?php 
	/* @var $drop \Smta\Drop */
	$drop = $this->getContext()->getRequest()->getAttribute('drop', array());
?>
<div class="container-fluid">
	<div class="page-header">
		<h1><span class="fa fa-check hidden-xs"></span> Drop #<?php echo $drop->getId() ?></h1>
		<div class="text-muted">You can manage this drop and monitor its status from this page.</div>
	</div>
	
	<ol class="breadcrumb">
		<li><a href="/drop/drop-search">Drops</a></li>
		<li class="active">Drop #<?php echo $drop->getId() ?></li>
	</ol>
	<p />
	<div class="well well-default">
		<div class="progress">
			<div class="progress-bar" id="progress-bar-div" role="progressbar" aria-valuenow="<?php echo $drop->getPercentComplete() ?>" aria-valuemin="0" aria-valuemax="100" style="width: <?php echo $drop->getPercentComplete() ?>%;"></div>
		</div>
	
		<a class="btn btn-success btn-sm" href="/drop/drop-mapping?_id=<?php echo $drop->getId() ?>" data-toggle="modal" data-target="#drop_mapping_modal"><i class="fa fa-pencil"></i> Edit Mapping</a>
		<a id="drop_reset" class="btn btn-info btn-sm <?php echo ($drop->getIsRunning() || $drop->getIsReadyToRun()) ? 'btn-warning' : ''?>" href="#"><?php echo ($drop->getIsRunning() || $drop->getIsReadyToRun()) ? '<i class="fa fa-stop"></i> Stop' : ' <i class="fa fa-play"></i> Start'?></a>
		<a class="btn btn-danger btn-sm" href="javascript:confirmDelete();"><i class="fa fa-times"></i> Delete</a>
	</div>
	
	<div class="help-block">
		Original File: <?php echo $drop->getListFileLocation() ?><p />
		List File: <?php echo $drop->getFilename() ?><p />
		From Domain: <?php echo $drop->getFromDomain() ?><p />
		Log File: <?php echo $drop->getLogFilename() ?> (<a href="/drop/drop-log?_id=<?php echo $drop->getId() ?>" data-toggle="modal" data-target="#drop_log_modal">view log</a>)
	</div>
	<hr />
	<h3>Drop Stats</h3>
	<table class="table table-bordered">
		<thead>
			<tr>
				<th>List Size</th>
				<th>Queued</th>
				<th>Delivered</th>
				<th>Bounced</th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td><?php echo number_format($drop->getReportStats()->getListSize(), 0, null, ',') ?></td>
				<td><?php echo number_format($drop->getReportStats()->getQueueSize(), 0, null, ',') ?></td>
				<td><?php echo number_format($drop->getReportStats()->getDeliveredSize(), 0, null, ',') ?></td>
				<td><?php echo number_format($drop->getReportStats()->getBounceSize(), 0, null, ',') ?></td>
			</tr>
		</tbody>
	</table>
	<hr />
	<h3>Mapping</h3>
	<table class="table table-bordered">
		<thead>
			<tr>
				<?php 
					for ($i=0;$i<count($drop->getMapping());$i++) {
				?>
					<td><?php echo $drop->getMappingColumn($i)->getName() ?></td>
				<?php } ?>
			</tr>
		</thead>
		<tbody>
			<?php 
				/* @var $mapping_col \Flux\DropMapping */
				foreach ($drop->getHeaderArray() as $key => $mapping_col) {
			?>	<tr>
					<?php foreach ($mapping_col as $mapping_val) { ?>
						<td><?php echo $mapping_val ?></td>
					<?php } ?>
				</tr>
			<?php } ?>
		</tbody>
	</table>
	<hr />
	<h3>Body</h3>
	<?php if ($drop->getBodyType() == \Smta\Drop::BODY_TYPE_INLINE) { ?>
		<pre><?php echo $drop->getBody() ?></pre>
	<?php } else { ?>
		<pre><?php echo file_get_contents($drop->getBodyFilename()) ?></pre>
		<i class="small">Read from <?php echo $drop->getBodyFilename() ?></i>
	<?php } ?>
</div>

<!-- mapping modal -->
<div class="modal fade" id="drop_mapping_modal"><div class="modal-lg modal-dialog"><div class="modal-content"></div></div></div>
<!-- view log modal -->
<div class="modal fade" id="drop_log_modal"><div class="modal-lg modal-dialog"><div class="modal-content"></div></div></div>

<script>
//<!--
$(document).ready(function() {
	$(document).everyTime(5000, function () {
		$.rad.get('/api/drop', {_id:'<?php echo $drop->getId() ?>' }, function(data) {
			if (data.record) {
				$('#progress-bar-div').css('width',data.record.percent_complete + '%');
				if (data.record.is_running || data.record.is_ready_to_run) {
					$('#drop_reset').html('<i class="fa fa-stop"></i> Stop').removeClass('btn-info').addClass('btn-warning');
				} else {
					$('#drop_reset').html('<i class="fa fa-play"></i> Start').removeClass('btn-warning').addClass('btn-info');
				}
			}
		});
	});

	$('#drop_reset').click(function() {
		$.rad.get('/api/drop', {_id: '<?php echo $drop->getId() ?>'}, function(data) {
			if (data.record.is_running || data.record.is_ready_to_run) {
				if (confirm('You have selected to stop this drop.  This will cancel any pending emails.\n\nAre you sure you want to stop this drop?')) {
					$.rad.put('/api/drop-stop', {_id: '<?php echo $drop->getId() ?>' }, function(data) {
						$.rad.notify('Drop stopped', 'We have stopped this drop.  You can start it when you are ready');
						$('#drop_reset').html('<span class="fa fa-spinner fa-spin"></span> Stopping');
					});
				}
			} else {
				$.rad.put('/api/drop-start', {_id: '<?php echo $drop->getId() ?>' }, function(data) {
					$.rad.notify('Drop started', 'We have started this drop.');
					$('#drop_reset').html('<span class="fa fa-spinner fa-spin"></span> Starting');
				});
			}
		});
	});
});

function confirmDelete() {
	if (confirm('Are you sure you want to delete this drop?')) {
		$.rad.del('/api/drop', {_id: '<?php echo $drop->getId() ?>'}, function(data) {
			location.replace('/drop/drop-search');
		});
	}
}
//-->
</script>