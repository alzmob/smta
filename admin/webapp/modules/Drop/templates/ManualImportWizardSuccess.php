<?php
/* @var $import_record \Rdm\ImportRecord */
$import_record = $this->getContext()->getRequest()->getAttribute('import_record', array());
$clients = $this->getContext()->getRequest()->getAttribute('clients', array());
$client_lists = $this->getContext()->getRequest()->getAttribute('client_lists', array());
$verticals = $this->getContext()->getRequest()->getAttribute('verticals', array());
$events = $this->getContext()->getRequest()->getAttribute('events', array());
$countries = $this->getContext()->getRequest()->getAttribute('countries', array());
$data_fields = $this->getContext()->getRequest()->getAttribute('data_fields', array());
?>
<div class="page-header">
	<h1><span class="glyphicon glyphicon-import hidden-xs"></span> Manual Import</h1>
	<div class="text-muted">You can use this wizard to import a new list of email addresses into a source.  The email file should have email addresses as the first column.</div>
</div>
<ol class="breadcrumb">
	<li><a href="/client/client-list-search">Incoming Lists</a></li>
	<li><a href="/import/import-search">Imports</a></li>
	<li class="active">Manual Import</li>
</ol>

<div class="">
	<form method="POST" id="import_record_form" action="/api" enctype="multipart/form-data">
		<input type="hidden" id="func" name="func" value="/import/import-record">
		<input type="hidden" id="import_type" name="import_type" value="<?php echo \Rdm\ImportRecord::IMPORT_TYPE_FILE ?>">
		<input type="hidden" id="is_ready_to_import" name="is_ready_to_import" value="0">
		<input type="hidden" id="import_record_id" name="_id" value="">
		
		<h3>1) Select the list that you want to import these emails into</h3>
		<div style="padding-Left:25px;width:350px;" class="form-group">
			<select id="client_list_id" name="client_list[_id]" value="" placeholder="choose a list to import into">
				<option value="" selected></option>
				<?php 
					/* @var $client \Rdm\Client */
					foreach ($clients as $client) {
				?>
					<optgroup label="<?php echo $client->getName() ?>">
					<?php 
						/* @var $client_list \Rdm\ClientList */
						foreach ($client->getClientLists() as $client_list) {
					?>
						<option value="<?php echo $client_list->getId() ?>" <?php echo $import_record->getClientList()->getClientListId() == $client_list->getId() ? "selected" : "" ?>><?php echo $client_list->getName() ?></option>
					<?php } ?>
				</optgroup>
				<?php } ?>
			</select>
		</div>
		<h3>2) Choose where the file with email addresses is located</h3>
		<div style="padding-Left:25px;">
			<input type="hidden" name="is_file_upload" id="is_file_upload" value="<?php echo $import_record->getIsFileUpload() > 0 ? $import_record->getIsFileUpload() : \Rdm\ImportRecord::IMPORT_FILE_TYPE_UPLOAD ?>" />
			<div role="tabpanel" id="is_file_upload_tab">
				<!-- Nav tabs -->
				<ul class="nav nav-tabs" role="tablist">
					<li role="presentation" class="<?php echo ($import_record->getIsFileUpload() == \Rdm\ImportRecord::IMPORT_FILE_TYPE_EMAIL_ADDRESSES) ? 'active' : '' ?>"><a href="#email-addresses" role="tab" data-toggle="tab">Email Addresses</a></li>
					<li role="presentation" class="<?php echo ($import_record->getIsFileUpload() == \Rdm\ImportRecord::IMPORT_FILE_TYPE_UPLOAD) ? 'active' : '' ?>"><a href="#upload-file" role="tab" data-toggle="tab">Upload File</a></li>
					<li role="presentation" class="<?php echo ($import_record->getIsFileUpload() == \Rdm\ImportRecord::IMPORT_FILE_TYPE_LOCAL_FILE) ? 'active' : '' ?>"><a href="#local-ftp" role="tab" data-toggle="tab">Local FTP</a></li>
					<li role="presentation" class="<?php echo ($import_record->getIsFileUpload() == \Rdm\ImportRecord::IMPORT_FILE_TYPE_REMOTE_FILE) ? 'active' : '' ?>"><a href="#remote-ftp" role="tab" data-toggle="tab">Remote FTP</a></li>
				</ul>
				<!-- Tab panes -->
  				<div class="tab-content">
  					<div role="tabpanel" class="tab-pane fade <?php echo ($import_record->getIsFileUpload() == \Rdm\ImportRecord::IMPORT_FILE_TYPE_EMAIL_ADDRESSES) ? 'active in' : '' ?>" id="email-addresses">
  						<div class="help-block">Enter up to 1000 email addresses in the box below:</div>
  						<textarea id="filename_upload_<?php echo \Rdm\ImportRecord::IMPORT_FILE_TYPE_EMAIL_ADDRESSES ?>" class="form-control" name="email_addresses" rows="5" cols="45" style="vertical-align:top;" <?php echo $import_record->getIsFileUpload() == \Rdm\ImportRecord::IMPORT_FILE_TYPE_EMAIL_ADDRESSES ? '' : 'disabled' ?>></textarea>
  					</div>
  					<div role="tabpanel" class="tab-pane fade <?php echo ($import_record->getIsFileUpload() == \Rdm\ImportRecord::IMPORT_FILE_TYPE_UPLOAD) ? 'active in' : '' ?>" id="upload-file">
  						<div class="help-block">Select a file containing email addresses up to <?php echo ini_get('upload_max_filesize') ?>:</div>
  						<input type="file" name="filename" class="form-control" id="filename_upload_<?php echo \Rdm\ImportRecord::IMPORT_FILE_TYPE_UPLOAD ?>" value="" <?php echo $import_record->getIsFileUpload() == \Rdm\ImportRecord::IMPORT_FILE_TYPE_UPLOAD ? '' : 'disabled' ?> />
  					</div>
  					<div role="tabpanel" class="tab-pane fade <?php echo ($import_record->getIsFileUpload() == \Rdm\ImportRecord::IMPORT_FILE_TYPE_LOCAL_FILE) ? 'active in' : '' ?>" id="local-ftp">
  						<div class="help-block">Click on Browse FTP to browse the client's local ftp account:</div>
  						<div class="input-group">
							<input type="text" size="60" class="form-control" name="remote_filename" id="filename_upload_<?php echo \Rdm\ImportRecord::IMPORT_FILE_TYPE_LOCAL_FILE ?>" value="" <?php echo $import_record->getIsFileUpload() == \Rdm\ImportRecord::IMPORT_FILE_TYPE_LOCAL_FILE ? '' : 'disabled' ?> />
							<div id="local_ftp_download_modal" class="input-group-addon" data-toggle="modal" data-target="#local_ftp_modal" style="cursor:pointer;">browse ftp</div>
						</div>
						<div id="filename_upload_warning_<?php echo \Rdm\ImportRecord::IMPORT_FILE_TYPE_LOCAL_FILE ?>" class="hidden text-danger">You have to select a list above before you can choose a file on the local ftp server</div>
  					</div>
  					<div role="tabpanel" class="tab-pane fade <?php echo ($import_record->getIsFileUpload() == \Rdm\ImportRecord::IMPORT_FILE_TYPE_REMOTE_FILE) ? 'active in' : '' ?>" id="remote-ftp">
  						<div class="help-block">Click on Browse FTP to browse the client's remote ftp account:</div>
  						<div class="input-group">
							<input type="text" size="60" class="form-control" name="remote_filename" id="filename_upload_<?php echo \Rdm\ImportRecord::IMPORT_FILE_TYPE_REMOTE_FILE ?>" value="" <?php echo $import_record->getIsFileUpload() == \Rdm\ImportRecord::IMPORT_FILE_TYPE_REMOTE_FILE ? '' : 'disabled' ?> />
							<div id="remote_ftp_download_modal" class="input-group-addon" data-toggle="modal" data-target="#remote_ftp_modal" style="cursor:pointer;">browse ftp</div>
						</div>
						<div id="filename_upload_warning_<?php echo \Rdm\ImportRecord::IMPORT_FILE_TYPE_REMOTE_FILE ?>" class="hidden text-danger">You have to select a list above before you can choose a file on the remote ftp server</div>
  					</div>
  				</div>
			</div>
		</div>
		
		<h3>3) Enter a brief description about where you got these emails and why you are importing them</h3>
		<div style="padding-Left:25px;" class="form-group">
			<textarea id="description" name="description" class="form-control" cols="45" rows="3" placeholder="enter a description for this import"><?php echo $import_record->getDescription() ?></textarea>
		</div>
		<h3>4) Advanced Options</h3>
		<div style="padding-Left:25px;">
			<div class="row">
				<div class="col-sm-8 text-left">
					<div class="help-block">Assign the default country for the incoming emails <span class="small">(A column mapped as the country will override this setting)</span></div>
					<div class="form-group">
						<div class="input-group">
							<select id="country_id" name="country[_id]" placeholder="select a default country">
								<optgroup label="Common Countries">
									<?php 
										/* @var $country \Rdm\Country */
										foreach ($countries as $country) {
									?>
										<?php if ($country->getIsDefault()) { ?>
											<option value="<?php echo $country->getId() ?>" <?php echo $import_record->getCountry()->getId() == $country->getId() ? "selected" : "" ?>><?php echo $country->getName() ?></option>
										<?php } ?>
									<?php } ?>
								</optgroup>
								<optgroup label="All Countries">
									<?php 
										/* @var $country \Rdm\Country */
										foreach ($countries as $country) {
									?>
										<?php if (!$country->getIsDefault()) { ?>
											<option value="<?php echo $country->getId() ?>" <?php echo $import_record->getCountry()->getId() == $country->getId() ? "selected" : "" ?>><?php echo $country->getName() ?></option>
										<?php } ?>
									<?php } ?>
								</optgroup>
							</select>
							<span class="input-group-addon">
								<input type="hidden" name="overwrite_country" id="overwrite_country_0" value="0" />
								<input type="checkbox" name="overwrite_country" id="overwrite_country_1" value="1" data-toggle="tooltip" data-placement="bottom" title="Overwrite duplicate records" <?php echo $import_record->getOverwriteCountry() ? 'checked' : '' ?> />
							</span>
						</div>
					</div>
				</div>
				<div class="col-sm-4 text-left">
					<div class="form-group">
						<div class="form-label help-block" for="use_geoip">Choose how GeoIP Lookups affect the default country</div>
						<select name="use_geoip" id="use_geoip">
							<option value="1" <?php echo $import_record->getUseGeoIp() ? 'selected' : '' ?>>Allow a GeoIP lookup to override the default country, if set</option>
							<option value="0" <?php echo $import_record->getUseGeoIp() ? '' : 'selected' ?>>Just use the default country, do not perform a GeoIP lookup</option>
						</select>
					</div> 
				</div>
			</div>
			<div class="row">
				<div class="col-sm-6 text-left">
					<div class="help-block">Assign one or more verticals to the incoming emails</div>
					<div class="form-group">
						<select id="verticals" name="verticals[][_id]" multiple placeholder="select verticals to assign">
							<?php 
								/* @var $vertical \Rdm\Vertical */
								foreach ($verticals as $vertical) {
							?>
								<option value="<?php echo $vertical->getId() ?>" <?php echo $import_record->isVerticalSelected($vertical->getId()) ? "selected" : "" ?>><?php echo $vertical->getName() ?></option>
							<?php } ?>
						</select>
					</div>
				</div>
				<div class="col-sm-6 text-left">
					<div class="help-block">Assign a default disposition to these emails</div>
					<div class="form-group">
						<div class="input-group">
							<select id="event" name="event[_id]" placeholder="select disposition to assign">
								<optgroup label="Default Events">
								<?php 
									/* @var $event \Rdm\Event */
									foreach ($events as $event) {
								?>
									<?php if (!$event->getIsNegative() && $event->getIsDefault()) { ?>
										<option value="<?php echo $event->getId() ?>" <?php echo $import_record->getEvent()->getId() == $event->getId() ? "selected" : "" ?>><?php echo $event->getName() ?></option>
									<?php } ?>
								<?php } ?>
								</optgroup>
								<optgroup label="Other Positive Events">
								<?php 
									/* @var $event \Rdm\Event */
									foreach ($events as $event) {
								?>
									<?php if (!$event->getIsNegative() && !$event->getIsDefault()) { ?>
										<option value="<?php echo $event->getId() ?>" <?php echo $import_record->getEvent()->getId() == $event->getId() ? "selected" : "" ?>><?php echo $event->getName() ?></option>
									<?php } ?>
								<?php } ?>
								</optgroup>
								<optgroup label="Negative Events">
								<?php 
									/* @var $event \Rdm\Event */
									foreach ($events as $event) {
								?>
									<?php if ($event->getIsNegative()) { ?>
										<option value="<?php echo $event->getId() ?>" <?php echo $import_record->getEvent()->getId() == $event->getId() ? "selected" : "" ?>><?php echo $event->getName() ?></option>
									<?php } ?>
								<?php } ?>
								</optgroup>
							</select>
							<span class="input-group-addon">
								<input type="hidden" name="overwrite_event" id="overwrite_event_0" value="0" />
								<input type="checkbox" name="overwrite_event" id="overwrite_event_1" value="1" data-toggle="tooltip" data-placement="bottom" title="Overwrite duplicate records" <?php echo $import_record->getOverwriteEvent() ? 'checked' : '' ?> />
							</span>
						</div>
					</div>
				</div>
			</div>
			<div class="row">
				<div class="col-sm-6 text-left">
					<div class="help-block">Select how the file is delimited</div>
					<div class="form-group">
						<select name="delimiter" id="delimiter" placeholder="choose a delimiter">
							<option value="1" <?php echo $import_record->getDelimiter() == 1 ? "selected" : "" ?>>Comma</option>
							<option value="2" <?php echo $import_record->getDelimiter() == 2 ? "selected" : "" ?>>Tab</option>
							<option value="3" <?php echo $import_record->getDelimiter() == 3 ? "selected" : "" ?>>Pipe</option>
							<option value="4" <?php echo $import_record->getDelimiter() == 4 ? "selected" : "" ?>>Semicolon</option>
						</select>
					</div>
				</div>
				<div class="col-sm-6 text-left">
					<div class="help-block">Only import data in domains containing at least this # of emails</div>
					<div class="form-group">
						<input type="text" class="form-control" id="minimum_domain_count" name="minimum_domain_count" value="<?php echo $import_record->getMinimumDomainCount() ?>" size="4">
					</div>
				</div>
			</div>
		</div>
		<div id="mapping_options" style="display:none;">
			<h3 id="mapping_label">5) Select how to map the columns in this file</h3>
			<div style="padding-Left:25px;">
				<div style="overflow-x:scroll;min-height:250px;">
					<table class="mapping_fields" cellspacing="0" cellpadding="5" border="0">
						<thead id="mapping_fields_thead"></thead>
						<tbody id="mapping_fields_tbody"></tbody>	
					</table>
				</div>
				<input type="hidden" name="save_mapping_to_list" value="0" />
				<label><input type="checkbox" name="save_mapping_to_list" value="1" /> Save this as the default mapping on this list</label>	
			</div>
		</div>
		<br />
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
<br /><br /><br /><br /><br /><br /><br /><br />

<!-- Dummy div used for mapping -->
<div id="mapping_field_div" style="display:none;">
	<div id="mapping_field_div_dummy-id">
		<select name="mapping[col_dummy-id][field]" placeholder="Col #dummy-id"></select>
		<div class="selectize-control single">
			<input type="text" name="mapping[col_dummy-id][default_value]" placeholder="default if blank" value="" style="width:100%;box-sizing: border-box;-moz-box-sizing: border-box;-webkit-box-sizing: border-box;" />
		</div>
	</div>
</div>

<!-- remote ftp browse modal -->
<div class="modal fade" id="remote_ftp_modal"><div class="modal-dialog"><div class="modal-content"></div></div></div>

<!-- local ftp browse modal -->
<div class="modal fade" id="local_ftp_modal"><div class="modal-dialog"><div class="modal-content"></div></div></div>

<script>
//<!--
$(document).ready(function() {
	
	$('[data-toggle="tooltip"]','#import_record_form').tooltip();
	
	// Define the data fields
	var $selectize_options = {
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
	}

	$('#toggle_advanced_options_top,#toggle_advanced_options_map').click(function() {
		$('#advanced_options').fadeToggle('fast', function() {
			if ($(this).is(':hidden')) {
				$('#toggle_advanced_options_top,#toggle_advanced_options_map').html('Show Advanced Options');
			} else {
				$('#toggle_advanced_options_top,#toggle_advanced_options_map').html('Hide Advanced Options');
			}
		});
	});

	var default_mapping = [];
	$('#client_list_id').selectize().on('change', function(e) {
		if ($(this).val() != '') {
			$.rad.get('/api', { func: '/client/client-list', _id: $(this).val() }, function(data) {
				if (data.record) {
					$('#delimiter').selectize()[0].selectize.setValue(data.record.delimiter);
					$('#minimum_domain_count').val(data.record.minimum_domain_count);
					$('#use_geoip').selectize()[0].selectize.setValue(data.record.use_geoip);
					$('#country_id').selectize()[0].selectize.setValue(data.record.country._id);
					$('#event').selectize()[0].selectize.setValue(data.record.event._id);
					$verticals = [];
					$.each(data.record.verticals, function(i, item) {
						$verticals.push(item.vertical_id);
					});
					$('#verticals').selectize()[0].selectize.setValue($verticals);
					default_mapping = data.record.mapping;
				}
			});
		}
		$('#is_file_upload_tab').trigger('shown.bs.tab');
	});
	
	$('#country_id,#verticals,#event,#country_id,#delimiter,#use_geoip').selectize();
	
	$('#import_record_form').form(
		function(data) {
			if ($('#func').val() == '/import/import-record-map') {
				// We submitted a completed mapping, so go to the import page
				$.rad.notify('You have uploaded a new email list', 'We have received your new list and will process it shortly.');
				$('#submit_progressbar').hide();
				$('#submit_buttons').show();
				window.location = '/import/import-record?_id=' + $('#import_record_id').val();
			} else {
				// We still need to map the data, so do that now
				$.rad.notify('Please complete mapping', 'We have received your new list, please complete the mapping.');
				if (data.record._id) {
					$('#import_record_id').val(data.record._id);
					$('#func').val('/import/import-record-map');
					//$('#import_record_form').removeAttr('target');
					$('#is_ready_to_import').val('1');

					// Hide the progress bar and show the submit buttons
					$('#submit_progressbar').hide();
					$('#submit_buttons').show();
					$('#top_submit_buttons').hide();
					$('#map_submit_buttons').show();
					$('#mapping_options').show();

					if (data.record.header_array) {
						var tr_head = $('<tr />').appendTo($('#mapping_fields_thead'));
						$.each(data.record.header_array, function(i, line) {
							var tr = $('<tr />').appendTo($('#mapping_fields_tbody'));
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
						window.location = '/import/import-record?_id=' + data.record._id;
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

	$('#is_file_upload_tab').on('shown.bs.tab', function (e) {		
		$('#filename_upload_<?php echo \Rdm\ImportRecord::IMPORT_FILE_TYPE_EMAIL_ADDRESSES ?>').attr('disabled', 'disabled');
		$('#filename_upload_<?php echo \Rdm\ImportRecord::IMPORT_FILE_TYPE_UPLOAD ?>').attr('disabled', 'disabled');
		$('#filename_upload_<?php echo \Rdm\ImportRecord::IMPORT_FILE_TYPE_LOCAL_FILE ?>').attr('disabled', 'disabled');
		$('#filename_upload_<?php echo \Rdm\ImportRecord::IMPORT_FILE_TYPE_REMOTE_FILE ?>').attr('disabled', 'disabled');
		$('#filename_upload_warning_<?php echo \Rdm\ImportRecord::IMPORT_FILE_TYPE_LOCAL_FILE ?>').addClass('hidden');
		$('#filename_upload_warning_<?php echo \Rdm\ImportRecord::IMPORT_FILE_TYPE_REMOTE_FILE ?>').addClass('hidden');
		if ($('#email-addresses').hasClass('active')) {
			$('#filename_upload_<?php echo \Rdm\ImportRecord::IMPORT_FILE_TYPE_EMAIL_ADDRESSES ?>').removeAttr('disabled');
			$('#is_file_upload').val(<?php echo \Rdm\ImportRecord::IMPORT_FILE_TYPE_EMAIL_ADDRESSES ?>);
		} else if ($('#upload-file').hasClass('active')) {
			$('#filename_upload_<?php echo \Rdm\ImportRecord::IMPORT_FILE_TYPE_UPLOAD ?>').removeAttr('disabled');
			$('#is_file_upload').val(<?php echo \Rdm\ImportRecord::IMPORT_FILE_TYPE_UPLOAD ?>);
		} else if ($('#local-ftp').hasClass('active')) {
			if ($('#client_list_id').val() != '') {
				$('#filename_upload_<?php echo \Rdm\ImportRecord::IMPORT_FILE_TYPE_LOCAL_FILE ?>').removeAttr('disabled');
			} else {
				$('#filename_upload_warning_<?php echo \Rdm\ImportRecord::IMPORT_FILE_TYPE_LOCAL_FILE ?>').removeClass('hidden');
			}
			$('#is_file_upload').val(<?php echo \Rdm\ImportRecord::IMPORT_FILE_TYPE_LOCAL_FILE ?>);
		} else if ($('#remote-ftp').hasClass('active')) {
			if ($('#client_list_id').val() != '') {
				$('#filename_upload_<?php echo \Rdm\ImportRecord::IMPORT_FILE_TYPE_REMOTE_FILE ?>').removeAttr('disabled');
			} else {
				$('#filename_upload_warning_<?php echo \Rdm\ImportRecord::IMPORT_FILE_TYPE_REMOTE_FILE ?>').removeClass('hidden');
			}
			$('#is_file_upload').val(<?php echo \Rdm\ImportRecord::IMPORT_FILE_TYPE_REMOTE_FILE ?>);
		}
	});

	$('#reupload_file').click(function() {
		if (confirm('This will allow you to reupload the file, but you will lose your current settings.\n\nDo you want to proceed?')) {
			$.rad.del('/api', { func: '/import/import-record', _id: $('#import_record_id').val() }, function(data) {
				$('#mapping_fields_thead').html('');
				$('#mapping_fields_tbody').html('');
				
				$('#mapping_options').hide();
				$('#submit_progressbar').hide();
				$('#submit_buttons').show();
				$('#map_submit_buttons').hide();
				$('#top_submit_buttons').show();

				$('#func').val('/import/import-record');
				$('#is_ready_to_import').val('0');
				$('#import_record_id').val('');
			});
		}
	});

	$('#filename_upload_<?php echo \Rdm\ImportRecord::IMPORT_FILE_TYPE_UPLOAD ?>').on('change', function() {
		if ($('#description').val() == '' && ($('#upload-file').hasClass('active'))) {
			path = $('#filename_upload_<?php echo \Rdm\ImportRecord::IMPORT_FILE_TYPE_UPLOAD ?>').val();
			var filename = path.replace(/^.*\\/, "");
			$('#description').val(filename);
		}
	});

	$('#remote_ftp_modal').on('show.bs.modal', function(e) {
		$('.modal-content','#remote_ftp_modal').load('/import/manual-import-remote-ftp-form', { _id: $('#client_list_id')[0].selectize.getValue(), html_input_element_id: 'filename_upload_<?php echo \Rdm\ImportRecord::IMPORT_FILE_TYPE_REMOTE_FILE ?>' });
	});

	$('#local_ftp_modal').on('show.bs.modal', function(e) {
		$('.modal-content','#local_ftp_modal').load('/import/manual-import-local-ftp-form', { _id: $('#client_list_id')[0].selectize.getValue(), html_input_element_id: 'filename_upload_<?php echo \Rdm\ImportRecord::IMPORT_FILE_TYPE_LOCAL_FILE ?>' });
	});

	$('#remote_ftp_modal,#local_ftp_modal').on('hide.bs.modal', function(e) {
		$(this).removeData('bs.modal');
		
		if ($('#description').val() == '' && ($('#local-ftp').hasClass('active'))) {
			$('#description').val($('#filename_upload_<?php echo \Rdm\ImportRecord::IMPORT_FILE_TYPE_LOCAL_FILE ?>').val());
		} else if ($('#description').val() == '' && ($('#remote-ftp').hasClass('active'))) {
			$('#description').val($('#filename_upload_<?php echo \Rdm\ImportRecord::IMPORT_FILE_TYPE_REMOTE_FILE ?>').val());
		}
	});
});
//-->
</script>