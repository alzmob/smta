<?php
	/* @var $domain_group DaoList_Form_DomainGroup */
	$domain_group = $this->getContext()->getRequest()->getAttribute('domain_group', array());
?>
<div class="container-fluid">
	<div class="page-header">
		<h1>Domain Groups</h1>
		<div class="text-muted">Domain groups are used throughout the system to group strategies and domains together.  Assign related domains and related strategies to the same domain group.</div>
	</div>
	<ol class="breadcrumb">
		<li><a href="/admin/setting">Admin</a></li>
		<li><a href="/admin/domain-group-search">Domain Groups</a></li>
		<li class="active">Search</li>
	</ol>
	<p />
	<div class="">
		<div class="panel panel-primary">
			<div id="domain-header" class="grid-header panel-heading clearfix">
				<form id="domain_group_search_form" METHOD="GET" action="/api/domain-group">
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
			<div id="domain-grid"></div>
			<div id="domain-pager" class="panel-footer"></div>
		</div>
		<p />
		<div class="text-center">
			<a id="add_domaingroup_wizard" data-toggle="modal" data-target="#edit_domain_group_modal" class="btn btn-primary" href="/admin/domain-group-form">add new domain group</a>
		</div>
	</div>
</div>
<!-- edit domain group modal -->
<div class="modal fade" id="edit_domain_group_modal"><div class="modal-dialog"><div class="modal-content"></div></div></div>

<script type='text/javascript'>
//<!--
$(document).ready(function() {	
	var columns = [
  		{id:'id', name:'Id', field:'_id', sort_field:'domain_group_id', def_value: ' ', sortable:true, type: 'string', hidden:true, formatter: function(row, cell, value, columnDef, dataContext) {
  			return value;
  		}},
  		{id:'name', name:'name', field:'name', def_value: ' ', sortable:true, type: 'string', formatter: function(row, cell, value, columnDef, dataContext) {
  			ret_val = '<div style="line-height:12pt;">';
  			ret_val += '<a data-toggle="modal" data-target="#edit_domain_group_modal" href="/admin/domain-group-form?_id=' + dataContext._id + '">' + value + '</a>';
  			ret_val += '<div class="small text-muted">' + dataContext.description + '</div>';
			ret_val += '</div>';
			return ret_val;
  		}},
  		{id:'description', name:'description', field:'description', def_value: ' ', sortable:true, hidden: true, type: 'string', formatter: function(row, cell, value, columnDef, dataContext) {
  			return value;
  		}},
  		{id:'is_gi_default', name:'default', field:'is_gi_default', def_value: ' ', cssClass:'text-center', sortable:true, type: 'string', formatter: function(row, cell, value, columnDef, dataContext) {
			if (value == '1') {
				return '<span class="text-success">Yes</span>';
			} else {
  				return '<span class="text-muted">No</span>';
			}
  		}},
  		{id:'domains', name:'domains', field:'domains', def_value: ' ', sortable:true, type: 'string', formatter: function(row, cell, value, columnDef, dataContext) {
			if (dataContext.is_gi_default == '1') {
				return '<i class="text-muted">-- all unassigned domains --</i>';
			} else {
  				return value.join(", ");
			}
  		}},
  		{id:'use_global_suffixes', name:'use global suffixes', field:'use_global_suffixes', cssClass: 'text-center', def_value: ' ', sortable:true, type: 'string', formatter: function(row, cell, value, columnDef, dataContext) {
			if (dataContext.use_global_suffixes == '1') {
				return '<i class="text-success">Yes</i>';
			} else {
  				return '<i class="text-muted">No</i>';
			}
  		}},
  		{id:'email_count', name:'# records', field:'email_count', cssClass:'text-center', def_value: ' ', sortable:true, type: 'string', formatter: function(row, cell, value, columnDef, dataContext) {
  			return $.formatNumber(value, {format:"#,##0", locale:"us"});
  		}},
  		{id:'color', name:'color', field:'color', def_value: ' ', sortable:true, minWidth: 98, width: 98, maxWidth: 98, type: 'string', formatter: function(row, cell, value, columnDef, dataContext) {
  			return '<div class="ui-corner-all"><img class="ui-corner-all" src="/images/transparent-psd.png" border="0" width="90" height="32" style="background-Color:' + value + ';">&nbsp;</div>';
  		}}
  	];

  	slick_grid = $('#domain-grid').slickGrid({
  		pager: $('#domain-pager'),
  		form: $('#domain_group_search_form'),
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

  	$('#edit_domain_group_modal').on('hide.bs.modal', function(e) {
		$(this).removeData('bs.modal');
	});

  	$("#txtSearch").keyup(function(e) {
  		// clear on Esc
  		if (e.which == 27) {
  			this.value = "";
  		} else if (e.which == 13) {
  			$('#domain_group_search_form').trigger('submit');
		}
  	});
  	
  	$('#domain_group_search_form').trigger('submit');

});
//-->
</script>

