<?php
	/* @var $drop \Smta\Drop */
	$drop = $this->getContext()->getRequest()->getAttribute('drop', array());
	$data_fields = $this->getContext()->getRequest()->getAttribute('data_fields', array());
?>
<div class="container-fluid">
	<h1>Create New Drop</h1>

	<!-- Breadcrumbs -->
	<ol class="breadcrumb">
		<li><a href="/default/index">Home</a></li>
		<li><a href="/drop/drop-search">Drops</a></li>
		<li class="active">New Drop</li>
	</ol>
	
	<form action="/api/drop" method="POST" id="drop_upload_form" enctype="multipart/form-data">
		<input type="hidden" name="func" id="func" value="/api/drop" />
		<input type="hidden" name="_id" id="drop_id" value="" />
		<div class="form-group">
			<label class="form-label">Name:</label>
			<input type="text" name="name" class="form-control" value="" placeholder="Enter a name for this drop (i.e. coupon_<?php echo date('Ymd') ?>, uk drop, etc)" />
		</div>
		<div class="form-group">
			<label class="form-label">Description:</label>
			<textarea name="description" class="form-control" value="" placeholder="Enter a description for this drop (i.e. Drop created from IO #12345)"></textarea>
		</div>
		<hr />
		<div class="form-group">
			<div>
				<input type="hidden" name="upload_file_type" id="upload_file_type" value="1" />
				<!-- Nav tabs -->
				<ul class="nav nav-tabs" role="tablist" id="upload_file_type_tab">
					<li role="presentation" class="<?php echo $drop->getUploadFileType() == \Smta\Drop::UPLOAD_FILE_TYPE_UPLOAD ? 'active' : '' ?>"><a href="#upload" aria-controls="upload" role="tab" data-toggle="tab">Upload File</a></li>
					<li role="presentation" class="<?php echo $drop->getUploadFileType() == \Smta\Drop::UPLOAD_FILE_TYPE_FTP ? 'active' : '' ?>"><a href="#local_ftp" aria-controls="profile" role="tab" data-toggle="tab">Local FTP</a></li>
				</ul>
	
				<!-- Tab panes -->
				<div class="tab-content">
					<div role="tabpanel" class="tab-pane tab-pane-box <?php echo $drop->getUploadFileType() == \Smta\Drop::UPLOAD_FILE_TYPE_UPLOAD ? 'active' : '' ?>" id="upload">
						<div class="help-block">Click on browse/choose file to find a file to upload (maximum file size is 128MB)</div>
						<input type="file" name="list_file_location" id="list_file_location_<?php echo \Smta\Drop::UPLOAD_FILE_TYPE_UPLOAD ?>" class="form-control" value="" placeholder="" />
					</div>
					<div role="tabpanel" class="tab-pane tab-pane-box <?php echo $drop->getUploadFileType() == \Smta\Drop::UPLOAD_FILE_TYPE_FTP ? 'active' : '' ?>" id="local_ftp">
						<div class="help-block">Click on browse ftp to find a file that has been uploaded via ftp</div>
						<div class="input-group">
							<input type="text" name="list_file_location" disabled id="list_file_location_<?php echo \Smta\Drop::UPLOAD_FILE_TYPE_FTP ?>" class="form-control" value="" placeholder="click on browse ftp to find file" />
							<div class="input-group-addon" class="btn btn-primary" data-toggle="modal" data-target="#local_ftp_modal" style="cursor:pointer;">browse ftp</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="help-block">Select how the file is delimited</div>
		<div class="form-group">
			<select name="delimiter" id="delimiter" placeholder="choose a delimiter">
				<option value="<?php echo \Smta\Drop::DELIMITER_COMMA ?>" <?php echo $drop->getDelimiter() == \Smta\Drop::DELIMITER_COMMA ? "selected" : "" ?>>Comma</option>
				<option value="<?php echo \Smta\Drop::DELIMITER_TAB ?>" <?php echo $drop->getDelimiter() == \Smta\Drop::DELIMITER_TAB ? "selected" : "" ?>>Tab</option>
				<option value="<?php echo \Smta\Drop::DELIMITER_PIPE ?>" <?php echo $drop->getDelimiter() == \Smta\Drop::DELIMITER_PIPE ? "selected" : "" ?>>Pipe</option>
				<option value="<?php echo \Smta\Drop::DELIMITER_SEMICOLON ?>" <?php echo $drop->getDelimiter() == \Smta\Drop::DELIMITER_SEMICOLON ? "selected" : "" ?>>Semicolon</option>
			</select>
		</div>
		<div id="mapping_options" style="display:none;">
			<hr />
			<label class="form-label">Select how to map the columns in this file</label>
			<div>
				<div style="overflow-x:scroll;">
					<table class="mapping_fields" cellspacing="0" cellpadding="5" border="0">
						<thead id="mapping_fields_thead"></thead>
						<tbody id="mapping_fields_tbody"></tbody>	
					</table>
				</div>
			</div>
			<hr />
			<div class="form-group">
				<label class="form-label">Enter email template to send:</label>
				<div>
					<input type="hidden" name="body_type" id="body_type" value="<?php echo \Smta\Drop::BODY_TYPE_INLINE ?>" />
					<!-- Nav tabs -->
					<ul class="nav nav-tabs" role="tablist" id="upload_body_type_tab">
						<li role="presentation" class="<?php echo $drop->getBodyType() == \Smta\Drop::BODY_TYPE_INLINE ? 'active' : '' ?>"><a href="#body_inline" role="tab" data-toggle="tab">Type Body Template</a></li>
						<li role="presentation" class="<?php echo $drop->getBodyType() == \Smta\Drop::BODY_TYPE_FILENAME ? 'active' : '' ?>"><a href="#body_file" role="tab" data-toggle="tab">Upload Body Template</a></li>
					</ul>
		
					<!-- Tab panes -->
					<div class="tab-content">
						<div role="tabpanel" class="tab-pane tab-pane-box <?php echo $drop->getBodyType() == \Smta\Drop::BODY_TYPE_INLINE ? 'active' : '' ?>" id="body_inline">
							<div class="help-block">Enter email template to send:</div>
							<textarea name="body" class="form-control" id="body_type_<?php echo \Smta\Drop::BODY_TYPE_INLINE ?>" rows="15" placeholder="Enter an email template to send"></textarea>
						</div>
						<div role="tabpanel" class="tab-pane tab-pane-box <?php echo $drop->getBodyType() == \Smta\Drop::BODY_TYPE_FILENAME ? 'active' : '' ?>" id="body_file">
							<div class="help-block">Click on browse ftp to find a body template to send:</div>
							<div class="input-group">
								<input type="text" name="body_filename" disabled id="body_type_<?php echo \Smta\Drop::BODY_TYPE_FILENAME ?>" class="form-control" value="" placeholder="click on browse ftp to find file" />
								<div class="input-group-addon" class="btn btn-primary" data-toggle="modal" data-target="#local_body_ftp_modal" style="cursor:pointer;">browse ftp</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="text-center">
			<div id="submit_buttons">
				<div id="top_submit_buttons">
					<input type="submit" class="btn btn-primary" value="Continue" />
				</div>
				<div id="map_submit_buttons" style="display:none;">
					<input type="button" class="btn btn-warning" id="reupload_file" value="Reupload File" />
					<input type="submit" class="btn btn-primary" value="Save and Process Import" />
				</div>
			</div>
			<div id="submit_progressbar" style="display:none;">
				Please wait while we upload your list...<br />
				<img src="/images/progress_bar2.gif" border="0" />
			</div>
		</div>
	</form>
</div>

<!-- Dummy div used for mapping -->
<div id="mapping_field_div" style="display:none;">
	<div id="mapping_field_div_dummy-id">
		<select name="mapping[dummy-id][name]" placeholder="Col #dummy-id"></select>
		<div class="selectize-control single">
			<input type="text" name="mapping[dummy-id][default_value]" placeholder="default if blank" value="" style="width:100%;box-sizing: border-box;-moz-box-sizing: border-box;-webkit-box-sizing: border-box;" />
		</div>
	</div>
</div>

<!-- local ftp browse modal -->
<div class="modal fade" id="local_ftp_modal"><div class="modal-dialog"><div class="modal-content"></div></div></div>
<!-- local ftp browse modal -->
<div class="modal fade" id="local_body_ftp_modal"><div class="modal-dialog"><div class="modal-content"></div></div></div>

<script>
//<!--
$(document).ready(function() {
	// Define the data fields
	var $selectize_options = {
		valueField: 'key',
		labelField: 'key',
		dropdownWidthOffset: 250,
		dropdownParent: "body",
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
	}

	
	$('#drop_upload_form').form(function(data) {
			if ($('#drop_upload_form').attr('action') == '/api/drop-map') {
				// We submitted a completed mapping, so go to the import page
				$.rad.notify('You have uploaded a new email list', 'We have received your new list and will process it shortly.');
				$('#submit_progressbar').hide();
				$('#submit_buttons').show();
				window.location = '/drop/drop?_id=' + $('#drop_id').val();
			} else {
				// We still need to map the data, so do that now
				$.rad.notify('Please complete mapping', 'We have received your new list, please complete the mapping.');
				if (data.record._id) {
					$('#drop_id').val(data.record._id);
					$('#drop_upload_form').attr('action', '/api/drop-map')

					// Hide the progress bar and show the submit buttons
					$('#submit_progressbar').hide();
					$('#submit_buttons').show();
					$('#top_submit_buttons').hide();
					$('#map_submit_buttons').show();
					$('#mapping_options').show();

					if (data.record.header_array) {
						var tr_head = $('<tr />').appendTo($('#mapping_fields_thead'));
						console.log(data.record.header_array);
						$.each(data.record.header_array, function(i, line) {
							var tr = $('<tr />').appendTo($('#mapping_fields_tbody'));
							console.log(line);
							$.each(line, function(j, item) {
								if (i == 0) {
									$new_mapping_field_div = $('#mapping_field_div').clone(true,true);
									$new_mapping_field_div.html(function(i, oldHTML) {
										oldHTML = oldHTML.replace(/dummy-id/g, j);
										return oldHTML;
									});
									$new_mapping_field_div.removeAttr('id');
									$selectize = $('select', $new_mapping_field_div).selectize($selectize_options);
									$.each(data.record.default_header_data_fields, function(mapping_col, mapping_data) {
										if ((mapping_col == j) && (mapping_data != null)) {
											$selectize[0].selectize.setValue(mapping_data.field);
										}
									});
									
									$new_mapping_field_div.show();
									$('<td />').append($new_mapping_field_div).appendTo(tr_head);
								}
								$('<td />').html('&nbsp;' + item).appendTo(tr);
							});
						});
					} else {
						// We can't find the header array, probably because this is a zip file, so display a warning
						$.rad.notify('You have uploaded a new email list', 'We have received your new list and will process it shortly.');
						window.location = '/drop/drop?_id=' + data.record._id;
					}
				} else {
					$('#submit_buttons').show();
				}
			}
		}, 
		{ 
			prepare:function() {
				// Hide the submit buttons and show the progress button
				$('#submit_buttons').hide();
				$('#submit_progressbar').show();
				return true;
			}, 
			onerror:function(data, textStatus, xhr) {
				// Hide the progress bar and show the submit buttons
				$('#submit_progressbar').hide();
				$('#submit_buttons').show();
			},
			keep_form: true
		}
	);

	$('#reupload_file').click(function() {
		if (confirm('This will allow you to reupload the file, but you will lose your current settings.\n\nDo you want to proceed?')) {
			$.rad.del('/api', { func: '/drop/drop', _id: $('#drop_id').val() }, function(data) {
				$('#mapping_fields_thead').html('');
				$('#mapping_fields_tbody').html('');
				
				$('#mapping_options').hide();
				$('#submit_progressbar').hide();
				$('#submit_buttons').show();
				$('#map_submit_buttons').hide();
				$('#top_submit_buttons').show();
				$('#drop_upload_form').attr('action', '/api/drop')
				$('#func').val('/api/drop');
				$('#drop_id').val('');
			});
		}
	});
	
	$('#local_ftp_modal').on('show.bs.modal', function(e) {
		$('.modal-content','#local_ftp_modal').load('/drop/local-ftp-form', { html_input_element_id: 'list_file_location_<?php echo \Smta\Drop::UPLOAD_FILE_TYPE_FTP ?>' });
	});

	$('#local_body_ftp_modal').on('show.bs.modal', function(e) {
		$('.modal-content','#local_body_ftp_modal').load('/drop/local-ftp-form', { html_input_element_id: 'body_type_<?php echo \Smta\Drop::BODY_TYPE_FILENAME ?>' });
	});

	$('#local_body_ftp_modal').on('hide.bs.modal', function(e) {
		$(this).removeData('bs.modal');
	});
	
	$('#local_ftp_modal').on('hide.bs.modal', function(e) {
		$(this).removeData('bs.modal');
	
		if ($('#description').val() == '' && ($('#local-ftp').hasClass('active'))) {
			$('#description').val($('#list_file_location_<?php echo \Smta\Drop::UPLOAD_FILE_TYPE_FTP ?>').val());
		}
	});

	$('#delimiter').selectize();

	$('#upload_file_type_tab').on('shown.bs.tab', function (e) {		
		$('#list_file_location_<?php echo \Smta\Drop::UPLOAD_FILE_TYPE_UPLOAD ?>').attr('disabled','disabled');
		$('#list_file_location_<?php echo \Smta\Drop::UPLOAD_FILE_TYPE_FTP ?>').attr('disabled','disabled');
		if ($('#upload').hasClass('active')) {
			$('#list_file_location_<?php echo \Smta\Drop::UPLOAD_FILE_TYPE_UPLOAD ?>').removeAttr('disabled');
			$('#upload_file_type').val(<?php echo \Smta\Drop::UPLOAD_FILE_TYPE_UPLOAD ?>);
		} else {
			$('#list_file_location_<?php echo \Smta\Drop::UPLOAD_FILE_TYPE_FTP ?>').removeAttr('disabled');
			$('#upload_file_type').val(<?php echo \Smta\Drop::UPLOAD_FILE_TYPE_FTP ?>);
		}
	});

	$('#upload_body_type_tab').on('shown.bs.tab', function (e) {		
		$('#body_type_<?php echo \Smta\Drop::BODY_TYPE_INLINE ?>').attr('disabled','disabled');
		$('#body_type_<?php echo \Smta\Drop::BODY_TYPE_FILENAME ?>').attr('disabled','disabled');
		if ($('#body_inline').hasClass('active')) {
			$('#body_type_<?php echo \Smta\Drop::BODY_TYPE_INLINE ?>').removeAttr('disabled');
			$('#body_type').val(<?php echo \Smta\Drop::BODY_TYPE_INLINE ?>);
		} else {
			$('#body_type_<?php echo \Smta\Drop::BODY_TYPE_FILENAME ?>').removeAttr('disabled');
			$('#body_type').val(<?php echo \Smta\Drop::BODY_TYPE_FILENAME ?>);
		}
	});
});
//-->
</script>