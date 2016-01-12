<?php

?>

<div class="container-fluid">
	<h1>PowerMTA Logs</h1>

	<!-- Breadcrumbs -->
	<ol class="breadcrumb">
		<li><a href="/default/index">Home</a></li>
		<li><a href="/server/index">Servers</a></li>
		<li class="active">PowerMTA Logs</li>
	</ol>
	<p />
	<div>
		<!-- Nav tabs -->
		<ul class="nav nav-tabs" role="tablist">
			<li role="presentation" class="active"><a href="#mainlog" aria-controls="mainlog" role="tab" data-toggle="tab">Main Log</a></li>
			<li role="presentation"><a href="#startuplog" aria-controls="startuplog" role="tab" data-toggle="tab">Startup Log</a></li>
		</ul>

		<!-- Tab panes -->
		<div class="tab-content">
			<div role="tabpanel" class="tab-pane tab-pane-box active" id="mainlog">
				<iframe src="/server/pmta-raw-log" frameborder="0" style="width:100%;height:800px;"></iframe>
			</div>
			<div role="tabpanel" class="tab-pane tab-pane-box" id="startuplog">
				<iframe src="/server/pmta-raw-log?type=startup" frameborder="0" style="width:100%;height:800px;"></iframe>
			</div>
		</div>
	</div>
</div>