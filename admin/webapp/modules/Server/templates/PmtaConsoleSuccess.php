<?php

?>

<div class="container-fluid">
	<h1>PowerMTA Console</h1>

	<!-- Breadcrumbs -->
	<ol class="breadcrumb">
		<li><a href="/default/index">Home</a></li>
		<li><a href="/server/index">Servers</a></li>
		<li class="active">PowerMTA Console</li>
	</ol>
	
	<div>
		<!-- Nav tabs -->
		<ul class="nav nav-tabs" role="tablist">
			<li role="presentation" class="active"><a href="#mainlog" aria-controls="mainlog" role="tab" data-toggle="tab">PowerMTA Console</a></li>
		</ul>

		<!-- Tab panes -->
		<div class="tab-content">
			<div role="tabpanel" class="tab-pane tab-pane-box active" id="mainlog">
				<iframe src="/server/pmta-raw-console" frameborder="0" style="width:100%;height:800px;"></iframe>
			</div>
		</div>
	</div>
</div>