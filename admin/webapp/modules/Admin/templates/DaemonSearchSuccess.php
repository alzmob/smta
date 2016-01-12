<?php
	/* @var $daemon \Rdm\Daemon */
	$daemon = $this->getContext()->getRequest()->getAttribute('daemon', array());
?>
<div class="container-fluid">
	<div class="page-header">
		<h1><span class="glyphicon glyphicon-transfer hidden-xs"></span> Daemons</h1>
		<div class="text-muted">Daemons are processes that run in the background and monitor different parts of the system.  Some examples of daemons are the real-time feed process and geo ip process.</div>
	</div>
	<ol class="breadcrumb">
		<li><a href="/admin/setting">Admin</a></li>
		<li><a href="/admin/daemon-search">Daemon</a></li>
		<li class="active">Search</li>
	</ol>
	<p />
	<div class="panel panel-primary">
		<div id='daemon-header' class='grid-header panel-heading clearfix'>
			<form id="daemon_search_form" METHOD="GET" action="/api/daemon">
				<input type="hidden" name="format" value="json" />
				<input type="hidden" id="page" name="page" value="1" />
				<input type="hidden" id="items_per_page" name="items_per_page" value="500" />
				<input type="hidden" id="sort" name="sort" value="name" />
				<input type="hidden" id="sord" name="sord" value="asc" />
				<div class="pull-right">
					<input type="text" class="form-control" size="35" placeholder="filter by name" id="txtSearch" name="name" value="" />
				</div>
			</form>
		</div>
		<div id="daemon-grid"></div>
		<div id="daemon-pager" class="panel-footer"></div>
	</div>
	<p />
	<div class="text-center">
		<a id="add_domaingroup_wizard" data-toggle="modal" data-target="#edit_daemon_modal" class="btn btn-primary" href="/admin/daemon-form">add new daemon</a>
	</div>
</div>

<!-- edit domain group modal -->
<div class="modal fade" id="edit_daemon_modal"><div class="modal-dialog"><div class="modal-content"></div></div></div>

<script type='text/javascript'>
//<!--
$(document).ready(function() {	
	var columns = [
  		{id:'id', name:'Id', field:'_id', sort_field:'daemon_id', def_value: ' ', sortable:true, type: 'string', hidden:true, formatter: function(row, cell, value, columnDef, dataContext) {
  			return value;
  		}},
  		{id:'name', name:'name', field:'name', def_value: ' ', sortable:true, type: 'string', formatter: function(row, cell, value, columnDef, dataContext) {
  			ret_val = '<div style="line-height:12pt;">';
  			ret_val += '<a data-toggle="modal" data-target="#edit_daemon_modal" href="/admin/daemon-form?_id=' + dataContext._id + '">' + value + '</a>';
			ret_val += '<div class="small text-muted">' + dataContext.description + '</div>';
			ret_val += '</div>';
			return ret_val;
  		}},
  		{id:'description', name:'description', field:'description', def_value: ' ', sortable:true, hidden: true, type: 'string', formatter: function(row, cell, value, columnDef, dataContext) {
  			return value;
  		}},
  		{id:'class_name', name:'class', field:'class_name', width:125, def_value: ' ', cssClass:'text-center', sortable:true, type: 'string', formatter: function(row, cell, value, columnDef, dataContext) {
  			return value;
  		}},
  		{id:'threads', name:'# threads', field:'threads', width:75, def_value: ' ', cssClass:'text-center', sortable:true, type: 'string', formatter: function(row, cell, value, columnDef, dataContext) {
  			return value;
  		}},
  		{id:'pending_records', name:'# pending', field:'pending_records', width:75, def_value: ' ', cssClass:'text-center', sortable:true, type: 'string', formatter: function(row, cell, value, columnDef, dataContext) {
  	  		if (value > 0) {
  	  	  		ret_val = '<div style="line-height:12pt;">';
  	  			ret_val += $.formatNumber(value, {format:"#,##0", locale:"us"});
  				ret_val += '<div class="small text-muted">' + $.number(dataContext.records_per_minute) + ' records/minute</div>';
  				ret_val += '</div>';
  				return ret_val;
  	  		} else {
				return '<div class="text-muted">' + value + '</div>';
  	  		}
  		}},
  		{id:'start_time', name:'last run', field:'start_time', def_value: ' ', cssClass:'text-center', sortable:true, type: 'string', formatter: function(row, cell, value, columnDef, dataContext) {
  			if (value != undefined) {
  				return moment.unix(value.sec).calendar();
			} else {
				return '<i class="text-danger">-- unknown --</i>';
			}
  		}},
  		{id:'run_status', name:'running', field:'run_status', minWidth:75, maxWidth:75, width:75, def_value: ' ', cssClass:'text-center', hidden: true, sortable:true, type: 'string', formatter: function(row, cell, value, columnDef, dataContext) {
			if (value == 1) {
				return '<div class="text-success">Yes</div>';
			} else {
				return '<i class="text-danger">No</i>';
			}
  		}},
  		{id:'is_running', name:'running', field:'is_running', minWidth:75, maxWidth:75, width:75, def_value: ' ', cssClass:'text-center', sortable:true, type: 'string', formatter: function(row, cell, value, columnDef, dataContext) {
			if (value) {
				return '<div class="text-success">Yes</div>';
			} else {
				return '<i class="text-danger">No</i>';
			}
  		}},
  		{id:'pid', name:'pid', field:'pid', minWidth:75, maxWidth:75, width:75, def_value: ' ', cssClass:'text-center', hidden: true, sortable:true, type: 'string', formatter: function(row, cell, value, columnDef, dataContext) {
			return value;
  		}}
  	];

  	slick_grid = $('#daemon-grid').slickGrid({
  		pager: $('#daemon-pager'),
  		form: $('#daemon_search_form'),
  		columns: columns,
  		useFilter: false,
  		cookie: '<?php echo $_SERVER['PHP_SELF'] ?>',
  		pagingOptions: {
  			pageSize: <?php echo intval(\Smta\Setting::getSetting('ITEMS_PER_PAGE')) > 0 ? \Smta\Setting::getSetting('ITEMS_PER_PAGE') : '25' ?>,
  			pageNum: 1
  		},
  		slickOptions: {
  			defaultColumnWidth: 150,
  			forceFitColumns: true,
  			enableCellNavigation: false,
  			width: 800,
  			rowHeight: 40
  		}
  	});

  	$('#edit_daemon_modal').on('hide.bs.modal', function(e) {
		$(this).removeData('bs.modal');
	});

  	$("#txtSearch").keyup(function(e) {
  		// clear on Esc
  		if (e.which == 27) {
  			this.value = "";
  		} else if (e.which == 13) {
  			$('#daemon_search_form').trigger('submit');
		}
  	});
  	
  	$('#daemon_search_form').trigger('submit');

});
//-->
</script>

