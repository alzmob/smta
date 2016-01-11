<?php
	$drops = $this->getContext()->getRequest()->getAttribute('drops', array());
?>
<div class="container-fluid">
	<h1>Search Drops</h1>

	<!-- Breadcrumbs -->
	<ol class="breadcrumb">
		<li><a href="/default/index">Home</a></li>
		<li>Drops</li>
	</ol>
	
	<div class="">
		<div class="panel panel-primary">
			<div id='drop-header' class='grid-header panel-heading clearfix'>
				<form id="drop_search_form" METHOD="GET" action="/api/drop">
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
			<div id="drop-grid"></div>
			<div id="drop-pager" class="panel-footer"></div>
		</div>
	</div>
</div>
<script type='text/javascript'>
//<!--
$(document).ready(function() {
	var columns = [
  		{id:'id', name:'Id', field:'_id', sort_field:'drop_id', def_value: ' ', sortable:true, type: 'string', hidden:true, formatter: function(row, cell, value, columnDef, dataContext) {
  			return value;
  		}},
  		{id:'name', name:'name', field:'name', def_value: ' ', sortable:true, type: 'string', formatter: function(row, cell, value, columnDef, dataContext) {
  			ret_val = '<div style="line-height:12pt;">';
  			ret_val += '<a href="/drop/drop?_id=' + dataContext._id + '">' + value + '</a>';
  			ret_val += '<div class="small text-muted">' + dataContext.description + '</div>';
			ret_val += '</div>';
			return ret_val;
  		}},
  		{id:'description', name:'description', field:'description', cssClass:'', def_value: ' ', sortable:true, hidden: true, type: 'string', formatter: function(row, cell, value, columnDef, dataContext) {
  			return value;
  		}},
  		{id:'total_records', name:'# emails', field:'report_stats.list_size', cssClass:'text-center', def_value: ' ', sortable:true, type: 'string', formatter: function(row, cell, value, columnDef, dataContext) {
			return $.number(value);
  		}},
  		{id:'list_file_location', name:'original file', field:'list_file_location', cssClass:'text-center', def_value: ' ', sortable:true, type: 'string', formatter: function(row, cell, value, columnDef, dataContext) {
			return value;
  		}},
  		{id:'is_error', name:'status', field:'is_error', cssClass:'text-center', def_value: ' ', sortable:true, type: 'string', formatter: function(row, cell, value, columnDef, dataContext) {
			if (value) {
				ret_val = '<div style="line-height:12pt;">';
	  			ret_val += '<div class="small text-danger">' + dataContext.error_message + '</div>';
				ret_val += '</div>';
				return ret_val;
			} else {
				return '<span class="text-muted">-- no errors --</span>';
			}
  		}},
  		{id:'percent_complete', name:'%', field:'percent_complete', def_value: ' ', cssClass:'text-center', minWidth: 130, width:130, maxWidth: 130, sortable:true, type: 'string', formatter: function(row, cell, value, columnDef, dataContext) {
            var percent_container = $('<div />');

            var percent_div = '<div class="progress" style="height:30px;">';
				percent_div += '<div class="progress-bar progress-bar-success" role="progressbar" style="width:' + value + '%;">';
				percent_div += '<span class="sr-only">' + value + '%</span>';
				percent_div += '</div>';
            	percent_div += '</div>';

            percent_container.html(percent_div);

            return percent_container.html();
    	}}
  	];

  	slick_grid = $('#drop-grid').slickGrid({
  		pager: $('#drop-pager'),
  		form: $('#drop_search_form'),
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
  			$('#drop_search_form').trigger('submit');
		}
  	});
  	
  	$('#drop_search_form').trigger('submit');

});
//-->
</script>
