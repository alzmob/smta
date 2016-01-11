<?php
	/* @var $data_field \Smta\DataField */
	$data_field = $this->getContext()->getRequest()->getAttribute('data_field', array());
?>
<div class="container-fluid">
	<div class="page-header">
		<h1><span class="fa fa-check hidden-xs"></span> Data Fields</h1>
		<div class="text-muted">Data Fields are used throughout the system to to format columns and map incoming data for exports.</div>
	</div>
	
	<ol class="breadcrumb">
		<li><a href="/admin/setting">Admin</a></li>
		<li><a href="/admin/data-field-search">Data Fields</a></li>
		<li class="active">Search</li>
	</ol>
	
	<div class="">
		<div class="panel panel-primary">
			<div id='data_field-header' class='grid-header panel-heading clearfix'>
				<form id="data_field_search_form" METHOD="GET" action="/api/data-field">
					<input type="hidden" name="func" value="/api/data-field" />
					<input type="hidden" name="format" value="json" />
					<input type="hidden" id="page" name="page" value="1" />
					<input type="hidden" id="items_per_page" name="items_per_page" value="500" />
					<input type="hidden" id="sort" name="sort" value="name" />
					<input type="hidden" id="sord" name="sord" value="asc" />
					<div class="pull-right">
						<input type="text" class="form-control" placeholder="filter by name or tags" size="35" id="txtSearch" name="name" value="" />
					</div>
				</form>
			</div>
			<div id="data_field-grid"></div>
			<div id="data_field-pager" class="panel-footer"></div>
		</div>
		<p />
		<center>
			<a id="add_data_field_wizard" data-toggle="modal" data-target="#edit_data_field_modal" class="btn btn-primary" href="/admin/data_field-form">add new data field</a>
		</center>
	</div>
</div>
<!-- edit data_field modal -->
<div class="modal fade" id="edit_data_field_modal"><div class="modal-lg modal-dialog"><div class="modal-content"></div></div></div>
<script type='text/javascript'>
//<!--
$(document).ready(function() {
	var columns = [
  		{id:'id', name:'Id', field:'_id', sort_field:'data_field_id', def_value: ' ', sortable:true, type: 'string', hidden:true, formatter: function(row, cell, value, columnDef, dataContext) {
  			return value;
  		}},
  		{id:'name', name:'name', field:'name', def_value: ' ', sortable:true, type: 'string', formatter: function(row, cell, value, columnDef, dataContext) {
  			ret_val = '<div style="line-height:12pt;">';
  			ret_val += '<a data-toggle="modal" data-target="#edit_data_field_modal" href="/admin/data_field-form?_id=' + dataContext._id + '">' + value + '</a>';
  			ret_val += '<div class="small text-muted">' + dataContext.description + '</div>';
			ret_val += '</div>';
			return ret_val;
  		}},
  		{id:'description', name:'description', field:'description', cssClass:'', def_value: ' ', sortable:true, hidden: true, type: 'string', formatter: function(row, cell, value, columnDef, dataContext) {
  			return value;
  		}},
  		{id:'field_name', name:'internal field', field:'field_name', cssClass:'text-center', def_value: ' ', hidden:true, sortable:true, type: 'string', formatter: function(row, cell, value, columnDef, dataContext) {
			return '<span class="label label-warning">' + value + '</span>';
  		}},
  		{id:'key', name:'key', field:'key', cssClass:'text-center', def_value: ' ', sortable:true, type: 'string', formatter: function(row, cell, value, columnDef, dataContext) {
			return '<span class="label label-success">' + value + '</span>';
  		}},
  		{id:'tags', name:'tags names', field:'tags', def_value: ' ', sortable:true, type: 'string', formatter: function(row, cell, value, columnDef, dataContext) {
			if (value instanceof Array) {
				if (value.join(", ") == "") {
					return '<span class="text-muted">-- no tags --</span>';
				} else {
  					return '<span class="label label-info">' + value.join('</span> <span class="label label-info">') + '</span>';
				}
			} else {
				return '<span class="text-muted">-- no tags --</span>';
			}
  		}},
  		{id:'request_fields', name:'request fields', field:'request_fields', def_value: ' ', hidden:true, sortable:true, type: 'string', formatter: function(row, cell, value, columnDef, dataContext) {
			if (value instanceof Array) {
				if (value.join(", ") == "") {
					return '<span class="text-muted">-- no fields or derived data --</span>';
				} else {
  					return '<span class="label label-info">' + value.join('</span> <span class="label label-info">') + '</span>';
				}
			} else {
				return '<span class="text-muted">-- no fields or derived data --</span>';
			}
  		}}
  	];

  	slick_grid = $('#data_field-grid').slickGrid({
  		pager: $('#data_field-pager'),
  		form: $('#data_field_search_form'),
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

  	$("#txtSearch").keyup(function(e) {
  		// clear on Esc
  		if (e.which == 27) {
  			this.value = "";
  		} else if (e.which == 13) {
  			$('#data_field_search_form').trigger('submit');
		}
  	});

  	$('#edit_data_field_modal').on('hide.bs.modal', function(e) {
		$(this).removeData('bs.modal');
	});
  	
  	$('#data_field_search_form').trigger('submit');

});
//-->
</script>

