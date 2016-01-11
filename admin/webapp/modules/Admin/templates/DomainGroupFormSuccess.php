<?php
	/* @var $domain_group \Rdm\DomainGroup */
	$domain_group = $this->getContext()->getRequest()->getAttribute('domain_group', 'DaoList', 'DomainGroup');
?>
<?php if (\MongoId::isValid($domain_group->getId())) { ?> 
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
		<h4 class="modal-title">Edit Domain Group</h4>
	</div>
	<form id="domain_group_<?php echo $domain_group->getId() ?>_form" action="/api/domain-group" method="POST">
		<input type="hidden" name="_id" value="<?php echo $domain_group->getId() ?>" />
		<input type="hidden" name="use_global_suffixes" value="0" />
		<div class="modal-body">
			<div class="form-group">
				<label for="name">Name:</label>
				<input type="text" class="form-control" placeholder="enter domain group name" id="name" name="name" value="<?php echo $domain_group->getName() ?>" size="35" />
			</div>
			<div class="form-group">
				<label for="description">Description:</label>
				<textarea name="description" id="description" class="form-control" placeholder="enter domain group description" cols="35" rows="3"><?php echo $domain_group->getDescription() ?></textarea>
			</div>
			<hr />
			<div class="help-block">Select whether to use this domain group with specific domains or as the default for all domains</div>
			<div class="form-group">
				<label><input type="radio" id="is_gi_default_1" name="is_gi_default" value="1" <?php echo $domain_group->getIsGiDefault() == '1' ? 'checked' : '' ?> /> Any unassigned domain will belong to this domain group</label><br />
				<label><input type="radio" id="is_gi_default_0" name="is_gi_default" value="0" <?php echo $domain_group->getIsGiDefault() == '0' ? 'checked' : '' ?> /> Only the assigned domains will belong to this domain group:</label><br />
				<div style="padding-Left:25px;">
					<input type="text" style="height:100px;" name="domains" id="domains_<?php echo $domain_group->getId() ?>" value="<?php echo implode(",", $domain_group->getDomains()) ?>" <?php echo $domain_group->getIsGiDefault() == '1' ? 'disabled' : '' ?> />
					<label><input type="checkbox" name="use_global_suffixes" value="1" <?php echo $domain_group->getUseGlobalSuffixes() == '1' ? 'checked' : '' ?>> Append the global suffix list to this list of domains above <i class="small">(any domain missing a period)</i></label>
				</div>
			</div>
			<hr />
			<div class="help-block">Select the color used when displaying this domain group in graphs and charts</div>
			<div class="form-group">
				<div class="input-group" id="colorpicker">
					<input type="text" name="color" value="<?php echo $domain_group->getColor() ?>" class="form-control" />
					<span class="input-group-addon"><i></i></span>
				</div>
			</div>
		</div>
		<div class="modal-footer">
			<input type="button" class="btn btn-danger" value="Delete Domain Group" class="small" onclick="javascript:confirmDelete();" />
			<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
			<button type="submit" class="btn btn-primary">Save changes</button>
		</div>
	</form>
	<script>
	//<!--
	$(document).ready(function() {		
		$('#domain_group_<?php echo $domain_group->getId() ?>_form').form(function(data) {
			$.rad.notify('You have updated this domain group', 'You have updated this domain group.');
			$('#domain_group_search_form').trigger('submit');
			$('#edit_domain_group_modal').modal('hide');
		}, {keep_form: true});
	});
	
	function confirmDelete() {
		if (confirm('Are you sure you want to delete this domain group from the system?')) {
			$.rad.del('/api/domain-group', {_id: '<?php echo $domain_group->getId() ?>'}, function(data) {
				$.rad.notify('You have deleted this domain group', 'You have deleted this domain group.  You will need to refresh this page to see your changes.');
				$('#domain_group_search_form').trigger('submit');
				$('#edit_domain_group_modal').modal('hide');
			});
		}
	}
	//-->
	</script>
<?php } else { ?>
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
		<h4 class="modal-title">Add Domain Group</h4>
	</div>
	<form id="domain_group_<?php echo $domain_group->getId() ?>_form" action="/api/domain-group" method="POST">
		<input type="hidden" name="use_global_suffixes" value="0" />
		<div class="modal-body">
			<div class="form-group">
				<label for="name">Name:</label>
				<input type="text" class="form-control" placeholder="enter domain group name" id="name" name="name" value="<?php echo $domain_group->getName() ?>" size="35" />
			</div>
			<div class="form-group">
				<label for="description">Description:</label>
				<textarea name="description" id="description" class="form-control" placeholder="enter domain group description" cols="35" rows="3"><?php echo $domain_group->getDescription() ?></textarea>
			</div>
			<hr />
			<div class="help-block">Select whether to use this domain group with specific domains or as the default for all domains</div>
			<div class="form-group">
				<label><input type="radio" id="is_gi_default_1" name="is_gi_default" value="1" <?php echo $domain_group->getIsGiDefault() == '1' ? 'checked' : '' ?> /> Any unassigned domain will belong to this domain group</label><br />
				<label><input type="radio" id="is_gi_default_0" name="is_gi_default" value="0" <?php echo $domain_group->getIsGiDefault() == '0' ? 'checked' : '' ?> /> Only the assigned domains will belong to this domain group:</label><br />
				<div style="padding-Left:25px;">
					<input type="text" style="height:100px;" name="domains" placeholder="enter individual domains" id="domains_<?php echo $domain_group->getId() ?>" value="<?php echo implode(",", $domain_group->getDomains()) ?>" <?php echo $domain_group->getIsGiDefault() == '1' ? 'disabled' : '' ?> />
					<label><input type="checkbox" name="use_global_suffixes" value="1" <?php echo $domain_group->getUseGlobalSuffixes() ? 'checked' : '' ?>> Append the global suffix list to this list of domains above <i class="small">(any domain missing a period)</i></label>
				</div>
			</div>
			<hr />
			<div class="help-block">Select the color used when displaying this domain group in graphs and charts</div>
			<div class="form-group">
				<div class="input-group" id="colorpicker">
					<input type="text" name="color" value="<?php echo $domain_group->getColor() ?>" class="form-control" />
					<span class="input-group-addon"><i></i></span>
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
		$('#domain_group_<?php echo $domain_group->getId() ?>_form').form(function(data) {
			$.rad.notify('You have added this domain group', 'You have added this domain group.');
			$('#domain_group_search_form').trigger('submit');
			$('#edit_domain_group_modal').modal('hide');
		}, {keep_form: true});
	});
	//-->
	</script>
<?php } ?>
<script>
//<!--
$(document).ready(function() {
	$('#colorpicker').colorpicker();
	
	$('#is_gi_default_1,#is_gi_default_0').click(function() {
		if ($('#is_gi_default_1').is(':checked')) {
			domain_selectize[0].selectize.disable();
		} else {
			domain_selectize[0].selectize.enable();
		}
	});
	
	var domain_selectize = $('#domains_<?php echo $domain_group->getId() ?>').selectize({
		delimiter: ',',
		persist: false,
		create: function(input) {
			return {
				value: input,
				text: input
			}
		}
	});

	<?php if ($domain_group->getIsGiDefault() == '1') { ?>
		domain_selectize[0].selectize.disable();
	<?php } ?>	
});
//-->
</script>