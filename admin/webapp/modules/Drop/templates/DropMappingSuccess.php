<?php
	/* @var $drop \Smta\Drop */
	$drop = $this->getContext()->getRequest()->getAttribute('drop', array());
	$data_fields = $this->getContext()->getRequest()->getAttribute('data_fields', array());
	// Rebuild the header array in case the delimiter has changed
	$drop->getHeaderArray(true);
?>
<div class="modal-header">
	<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
	<h4 class="modal-title">Edit Mapping</h4>
</div>
<form method="PUT" id="drop_mapping_form" action="/api/drop-map">
	<input type="hidden" name="_id" value="<?php echo $drop->getId() ?>" />
	<div class="modal-body">
		<div style="overflow-x:scroll;max-width:1000px;min-height:250px;">
			<table class="mapping_fields" cellspacing="0" cellpadding="0" border="0">
				<thead id="mapping_fields_thead">
					<?php if (is_array($drop->getHeaderArray())) { ?>
						<?php 
							foreach ($drop->getHeaderArray() as $key => $header_lines) {
						?>
							<?php if ($key == 0) { ?>
								<tr>
									<?php 
										foreach ($header_lines as $col_key => $header_line) {
									?>
										<td style="min-width:125px;">
											<div id="mapping_field_div_<?php echo $col_key ?>">
												<select name="mapping[<?php echo $col_key ?>][name]" placeholder="Col #<?php echo $col_key ?>" style="width:150px;">
													<option value="<?php echo $drop->getMappingColumn($col_key)->getName() ?>" selected><?php echo $drop->getMappingColumn($col_key)->getName() ?></option>
												</select>
												<div class="single">
													<input type="text" name="mapping[<?php echo $col_key ?>][default_value]" class="form-control small" placeholder="default if blank" value="<?php echo $drop->getMappingColumn($col_key)->getDefaultValue() ?>" style="width:100%;box-sizing: border-box;-moz-box-sizing: border-box;-webkit-box-sizing: border-box;" />
												</div>
											</div>
										</td>
									<?php } ?>
								</tr>
							<?php } ?>
						<?php } ?>
					<?php } ?>
				</thead>
				<tbody id="mapping_fields_tbody">
					<?php if (is_array($drop->getHeaderArray())) { ?>
						<?php 
							foreach ($drop->getHeaderArray() as $header_lines) {
						?>
							<tr>
								<?php if (is_array($header_lines)) { ?>
									<?php foreach ($header_lines as $header_col) { ?>
										<td>&nbsp;<?php echo $header_col ?></td>
									<?php } ?>
								<?php } else { ?>
									<td><?php echo $header_lines ?></td>
								<?php } ?>
							</tr>
						<?php } ?>
					<?php } ?>
				</tbody>
			</table>
		</div>
		<hr />
		<div class="form-group">
			<label for="from_domain">From Domain:</label>
			<input type="text" class="form-control" id="from_domain" name="from_domain" value="<?php echo $drop->getFromDomain() ?>" placeholder="Enter a from domain to use..." />
		</div>		
	</div>
	<div class="modal-footer">
		<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
		<button type="submit" class="btn btn-primary">Save changes</button>
	</div>	
</form>
<script>
//<!--
$(document).ready(function() {	

	$('#drop_mapping_form').form(function(data) {
		$.rad.notify('You have updated this import', 'We have updated this import and it will process it shortly.');
		$('#drop_mapping_modal').modal('hide');
	},{keep_form: true});

	$('#vertical_id,#client_list_id').selectize();

	$('select', '#mapping_fields_thead').selectize({
		valueField: 'key',
		labelField: 'key',
		dropdownWidthOffset: 250,
		searchField: ['key','name','description','tags'],
		options: [
			<?php 
				foreach ($data_fields as $data_field) { 
					$ret_val = $data_field->toArray();
					array_walk_recursive($ret_val, function(&$value) { if ($value instanceof \MongoId) { $value = (string)$value; }});
					echo json_encode($ret_val);
					echo ","; 
				} 
			?>
		],
		render: {
			option: function(item, escape) {
				var label = item.name || item.key;
				var caption = item.description ? item.description : null;
				var keyname = item.key ? item.key : null;
				var tags = item.tags ? item.tags : null;
				var tag_span = '';
				$.each(tags, function(j, tag_item) {
					tag_span += '<span class="pull-right label label-default">' + escape(tag_item) + '</span>';
				});				
				return '<div class="data_field_selectize">' +
					'<b>' + escape(label) + '</b> <span class="pull-right label label-success">' + escape(keyname) + '</span><br />' +
					(caption ? '<span class="text-muted small">' + escape(caption) + ' </span>' : '') +
					tag_span +   
				'</div>';
			}
		}
	});
});
//-->
</script>