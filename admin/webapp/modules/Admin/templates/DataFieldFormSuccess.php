<?php
	/* @var $data_field \Rdm\DataField */
	$data_field = $this->getContext()->getRequest()->getAttribute('data_field', array());
	$fields = $this->getContext()->getRequest()->getAttribute('fields', array());
	$tags = $this->getContext()->getRequest()->getAttribute('tags', array());
?>
<?php if (\MongoId::isValid($data_field->getId())) { ?> 
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
		<h4 class="modal-title">Edit Data Field</h4>
	</div>
	<form id="data_field_<?php echo $data_field->getId() ?>_form" action="/api/data-field" method="POST">
		<input type="hidden" name="_id" value="<?php echo $data_field->getId() ?>" />
		<div class="modal-body">
			<!-- Nav tabs -->
			<ul class="nav nav-tabs" role="tablist" id="data_field_tab">
				<li role="presentation" class="active"><a href="#basic" role="tab" data-toggle="tab">Basic</a></li>
				<li role="presentation" class=""><a href="#advanced" role="tab" data-toggle="tab">Advanced</a></li>
			</ul>
			<!-- Tab panes -->
	  		<div class="tab-content">
	  			<div role="tabpanel" class="tab-pane fade in active" id="basic">
					<div class="help-block">Enter a name and description for this data field that will be shown in the system</div>
					<div class="form-group">
						<input type="text" class="form-control" placeholder="enter data field name" id="name_<?php echo $data_field->getId() ?>" name="name" value="<?php echo $data_field->getName() ?>" size="35" />
					</div>
					<div class="form-group">
						<textarea class="form-control" placeholder="enter description for this data field" rows="2" id="description_<?php echo $data_field->getId() ?>" name="description"><?php echo $data_field->getDescription() ?></textarea>
					</div>
					<hr />
					<div class="help-block">Use this placeholder for any POST url fields matching this list</div>
					<div class="form-group">
						<input type="text" class="form-control" placeholder="enter POST url parameters that can match to this data field" id="request_fields_<?php echo $data_field->getId() ?>" name="request_fields" value="<?php echo implode(",", $data_field->getRequestFields()) ?>" size="35" />
					</div>
					
					<div class="help-block">Assign a placeholder name enclosed in square brackets without spaces (i.e. [EMAIL], [PHONE], [FNAME], etc)</div>
					<div class="form-group">
						<input type="text" class="form-control" placeholder="enter placeholder name (unique)" id="key_<?php echo $data_field->getId() ?>" name="key" value="<?php echo $data_field->getKey() ?>" size="25" />
					</div>
					<hr />
					<div class="help-block">Enter tags to help identify this data field when selecting it</div>
					<div class="form-group">
						<input type="text" id="tags_<?php echo $data_field->getId() ?>" placeholder="Enter tags to help identify this data field when selecting it" name="tags" value="<?php echo implode(",", $data_field->getTags()) ?>" size="35" />
					</div>
					<hr />
					<div class="help-block">This field will be shown when displaying POST urls on imports and client list pages</div>
					<div class="col-md-9">
						<label for="is_default_field_<?php echo $data_field->getId() ?>">Assign as a common field</label>
					</div>
					<div class="col-md-3">
						<div class="pull-right">
							<input type="hidden" name="is_common_field" value="0" />
							<input type="checkbox" id="is_common_field_<?php echo $data_field->getId() ?>" name="is_common_field" value="1" <?php echo $data_field->getIsCommonField() ? 'checked' : '' ?> />
						</div>
					</div>
					<div class="clearfix"></div>
				</div>
				<div role="tabpanel" class="tab-pane fade in" id="advanced">
					<div class="form-group">
						<div class="help-block">Define a custom function that you can use to convert this field to a value that the API accepts</div>
						<p />
						<div class="help-text">
							<span class="text-success">
							/**<br />
							&nbsp;* Custom mapping function<br />
							&nbsp;* $value - Value from mapping<br />
							&nbsp;* $line - Array of line entries<br />
							&nbsp;*/<br />
							</span>
							<strong>
							$mapping_func = function ($value, $line) {
							</strong>
						</div>
						<div class="col-sm-offset-1">
							<textarea id="custom_code_<?php echo $data_field->getId() ?>" class="form-control" name="custom_code" rows="6" placeholder="return $value;"><?php echo $data_field->getCustomCode() ?></textarea>
						</div>
						<div class="help-text"><strong>}</strong></div>
					</div>
				</div>
			</div>
		</div>
		<div class="modal-footer">
			<input type="button" class="btn btn-danger" value="Delete Data Field" class="small" onclick="javascript:confirmDelete();" />
			<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
			<button type="submit" class="btn btn-primary">Save changes</button>
		</div>
	</form>
	<script>
	//<!--
	$(document).ready(function() {
		$('#is_common_field_<?php echo $data_field->getId() ?>').bootstrapSwitch({
			size: 'small',
			onText: 'Yes',
			offText: 'No'
		});
		
		$('#tags_<?php echo $data_field->getId() ?>').selectize({
			delimiter: ',',
			persist: true,
			searchField: ['name'],
			valueField: 'name',
			labelField: 'name',
			options: [
				{ name: "<?php echo implode('"}, {name: "', $tags) ?>" }
			],
			create: function(input) {
				return {name: input}
			}
		});

		$('#request_fields_<?php echo $data_field->getId() ?>').selectize({
			delimiter: ',',
			persist: false,
			searchField: ['name'],
			valueField: 'name',
			labelField: 'name',
			create: function(input) {
				return { name: input, value: input }
			}
		});

		$('#show_advanced_options_div_<?php echo $data_field->getId() ?>').click(function() {
			$('#advanced_options_div_<?php echo $data_field->getId() ?>').toggleClass('hidden');
			if ($(this).html() == 'Show Advanced Options') {
				$(this).html('Hide Advanced Options');
			} else {
				$(this).html('Show Advanced Options');
			}
		});
		
		$('input[type=submit],input[type=button]').button();
		
		$('#data_field_<?php echo $data_field->getId() ?>_form').form(function(data) {
			$.rad.notify('You have updated this data_field', 'You have updated this data field.');
			$('#data_field_search_form').trigger('submit');
			$('#edit_data_field_modal').modal('hide');
		}, {keep_form: true});
	});
	
	function confirmDelete() {
		if (confirm('Are you sure you want to delete this data field from the system?')) {
			$.rad.del('/api/data-field', {_id: '<?php echo $data_field->getId() ?>'}, function(data) {
				$.rad.notify('You have deleted this data field', 'You have deleted this data field.  You will need to refresh this page to see your changes.');
				$('#data_field_search_form').trigger('submit');
				$('#edit_data_field_modal').modal('hide');
			});
		}
	}
	//-->
	</script>
<?php } else { ?>
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
		<h4 class="modal-title">Add Data Field</h4>
	</div>
	<form id="data_field_<?php echo $data_field->getId() ?>_form" action="/api/data-field" method="POST">
		<input type="hidden" name="func" value="/api/data-field" />
		<div class="modal-body">
			<!-- Nav tabs -->
			<ul class="nav nav-tabs" role="tablist" id="data_field_tab">
				<li role="presentation" class="active"><a href="#basic" role="tab" data-toggle="tab">Basic</a></li>
				<li role="presentation" class=""><a href="#advanced" role="tab" data-toggle="tab">Advanced</a></li>
			</ul>
			<!-- Tab panes -->
	  		<div class="tab-content">
	  			<div role="tabpanel" class="tab-pane fade in active" id="basic">
					<div class="help-block">Enter a name and description for this data field that will appear throughout the system</div>
					<div class="form-group">
						<input type="text" class="form-control" placeholder="enter data field name" id="name_<?php echo $data_field->getId() ?>" name="name" value="<?php echo $data_field->getName() ?>" size="35" />
					</div>
					<div class="form-group">
						<textarea class="form-control" placeholder="enter description for this data field" rows="2" id="description_<?php echo $data_field->getId() ?>" name="description"><?php echo $data_field->getDescription() ?></textarea>
					</div>
					<hr />
					<div class="help-block">Assign a key name enclosed in hash signs (i.e. #email#, #fname#, #phone#)</div>
					<div class="form-group">
						<input type="text" class="form-control" placeholder="enter key name (unique)" id="key_<?php echo $data_field->getId() ?>" name="key" value="<?php echo $data_field->getKey() ?>" size="25" />
					</div>
					<div class="help-block">Use this placeholder for any POST url fields matching this list</div>
					<div class="form-group">
						<input type="text" class="form-control" placeholder="enter POST url parameters that can match to this data field" id="request_fields_<?php echo $data_field->getId() ?>" name="request_fields" value="<?php echo implode(",", $data_field->getRequestFields()) ?>" size="35" />
					</div>
					<hr />
					<div class="help-block">Enter tags to help identify this data field when selecting it</div>
					<div class="form-group">
						<input type="text" id="tags_<?php echo $data_field->getId() ?>" placeholder="Enter tags to help identify this data field when selecting it" name="tags" value="<?php echo implode(",", $data_field->getTags()) ?>" size="35" />
					</div>
					<hr />
					<div class="help-block">This field will be shown at the top of lists as a commonly used field</div>
					<div class="col-md-9">
						<label for="is_default_field_<?php echo $data_field->getId() ?>">Assign as a common field</label>
					</div>
					<div class="col-md-3">
						<div class="pull-right">
							<input type="hidden" name="is_common_field" value="0" />
							<input type="checkbox" id="is_common_field_<?php echo $data_field->getId() ?>" name="is_common_field" value="1" <?php echo $data_field->getIsCommonField() ? 'checked' : '' ?> />
						</div>
					</div>
					<div class="clearfix"></div>
				</div>
				<div role="tabpanel" class="tab-pane fade in" id="advanced">
					<div class="form-group">
						<div class="help-block">Define a custom function that you can use to convert this field to a value that the API accepts</div>
						<p />
						<div class="help-text">
							<span class="text-success">
							/**<br />
							&nbsp;* Custom mapping function<br />
							&nbsp;* $value - Value from mapping<br />
							&nbsp;* $line - Array of line entries<br />
							&nbsp;*/<br />
							</span>
							<strong>
							$mapping_func = function ($value, $line) {
							</strong>
						</div>
						<div class="col-sm-offset-1">
							<textarea id="custom_code_<?php echo $data_field->getId() ?>" class="form-control" name="custom_code" rows="6" placeholder="return $value;"><?php echo $data_field->getCustomCode() ?></textarea>
						</div>
						<div class="help-text"><strong>}</strong></div>
					</div>
				</div>
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
		$('#is_common_field_<?php echo $data_field->getId() ?>').bootstrapSwitch({
			size: 'small',
			onText: 'Yes',
			offText: 'No'
		});
		
		$('#tags_<?php echo $data_field->getId() ?>').selectize({
			delimiter: ',',
			persist: true,
			searchField: ['name'],
			valueField: 'name',
			labelField: 'name',
			options: [
				{ name: "<?php echo implode('"}, {name: "', $tags) ?>" }
			],
			create: function(input) {
				return {name: input}
			}
		});

		$('#request_fields_<?php echo $data_field->getId() ?>').selectize({
			delimiter: ',',
			persist: false,
			searchField: ['name'],
			valueField: 'name',
			labelField: 'name',
			create: function(input) {
				return { name: input, value: input }
			}
		});		

		$('#show_advanced_options_div_<?php echo $data_field->getId() ?>').click(function() {
			$('#advanced_options_div_<?php echo $data_field->getId() ?>').toggleClass('hidden');
			if ($(this).html() == 'Show Advanced Options') {
				$(this).html('Hide Advanced Options');
			} else {
				$(this).html('Show Advanced Options');
			}
		});
				
		$('#data_field_<?php echo $data_field->getId() ?>_form').form(function(data) {
			$.rad.notify('You have added this data field', 'You have added this data field.');
			$('#data_field_search_form').trigger('submit');
			$('#edit_data_field_modal').modal('hide');
		}, {keep_form: true});
	});
	//-->
	</script>
<?php } ?>