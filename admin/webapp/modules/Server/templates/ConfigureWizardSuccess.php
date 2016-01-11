<?php
	/* @var $pmta_config \Smta\PmtaConfig */
	$pmta_config = $this->getContext()->getRequest()->getAttribute('pmta_config', array());
?>

<div class="container-fluid">
	<h1>Configure Server</h1>

	<!-- Breadcrumbs -->
	<ol class="breadcrumb">
		<li><a href="/default/index">Home</a></li>
		<li><a href="/server/index">Servers</a></li>
		<li class="active">Configure Server</li>
	</ol>
	
	<form method="POST" action="/api/pmta-config" id="pmta_config_form">
		<div>
			<!-- Nav tabs -->
			<ul class="nav nav-tabs" role="tablist">
				<li role="presentation" class="active"><a href="#mainconfig" aria-controls="mainconfig" role="tab" data-toggle="tab">PowerMTA Config</a></li>
				<li role="presentation"><a href="#domainconfig" aria-controls="domainconfig" role="tab" data-toggle="tab">PowerMTA Domain Config</a></li>
				<li role="presentation"><a href="#backoffconfig" aria-controls="backoffconfig" role="tab" data-toggle="tab">PowerMTA Backoff Config</a></li>
			</ul>
	
			
			<!-- Tab panes -->
			<div class="tab-content">
				<div role="tabpanel" class="tab-pane tab-pane-box active" id="mainconfig">
					<textarea class="form-control" rows="20" name="main_config"><?php echo $pmta_config->getMainConfig() ?></textarea>
				</div>
				<div role="tabpanel" class="tab-pane tab-pane-box" id="domainconfig">
					<textarea class="form-control" rows="20" name="domain_config"><?php echo $pmta_config->getDomainConfig() ?></textarea>
				</div>
				<div role="tabpanel" class="tab-pane tab-pane-box" id="backoffconfig">
					<textarea class="form-control" rows="20" name="backoff_config"><?php echo $pmta_config->getBackoffConfig() ?></textarea>
				</div>
			</div>
		</div>
		<br />
		<div class="text-center">
			<input type="submit" class="btn btn-primary" name="btn_submit" value="Save Configs" />
		</div>
	</form>
</div>
<script>
//<!--
$(document).ready(function() {
	$('#pmta_config_form').form(function(data) {
		$.rad.notify('Configs Saved', 'The config files have been saved to the server and PowerMTA has been restarted');
	}, {keep_form:1});
});
//-->
</script>