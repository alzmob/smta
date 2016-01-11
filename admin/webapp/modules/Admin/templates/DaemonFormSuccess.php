<?php
	/* @var $daemon \Rdm\DomainGroup */
	$daemon = $this->getContext()->getRequest()->getAttribute('daemon', array());
	$daemon_classes = $this->getContext()->getRequest()->getAttribute('daemon_classes', array());
?>
<?php if (\MongoId::isValid($daemon->getId())) { ?> 
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
		<h4 class="modal-title">Edit Daemon</h4>
	</div>
	<form id="daemon_<?php echo $daemon->getId() ?>_form" action="/api/daemon" method="POST">
		<input type="hidden" name="_id" value="<?php echo $daemon->getId() ?>" />
		<input type="hidden" name="status" value="1" />
		<div class="modal-body">
			<div class="form-group">
				<label for="name">Name:</label>
				<input type="text" class="form-control" placeholder="enter daemon name" id="name" name="name" value="<?php echo $daemon->getName() ?>" size="35" />
			</div>
			<div class="form-group">
				<label for="description">Description:</label>
				<textarea name="description" id="description" class="form-control" placeholder="enter daemon description" cols="35" rows="3"><?php echo $daemon->getDescription() ?></textarea>
			</div>
			<hr />
			<div class="help-block">Specify the low-level options for this daemon such as the PHP class to use to instantiate it</div>
			<div class="form-group">
				<label for="name">Class Name:</label>
				<select name="class_name" id="class_name_<?php echo $daemon->getId() ?>" placeholder="select the php class to use for this daemon">
					<?php foreach ($daemon_classes as $daemon_class) { ?>
						<option value="<?php echo $daemon_class?>" <?php echo $daemon->getClassName() == $daemon_class ? 'selected' : '' ?>><?php echo $daemon_class ?></option>
					<?php } ?>
				</select>
			</div>
			<div class="form-group">
				<label for="name">Type:</label>
				<input type="text" class="form-control" placeholder="enter the type of daemon (unique)" id="type" name="type" value="<?php echo $daemon->getType() ?>" size="35" />
			</div>
			<div class="form-group">
				<label for="name"># of Threads:</label>
				<input type="text" class="form-control" placeholder="enter the maximum # of threads to use" id="threads" name="threads" value="<?php echo $daemon->getThreads() ?>" size="35" />
			</div>
		</div>
		<div class="modal-footer">
			<input type="button" class="btn btn-danger" value="Delete Daemon" class="small" onclick="javascript:confirmDelete();" />
			<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
			<button type="submit" class="btn btn-primary">Save changes</button>
		</div>
	</form>
	<script>
	//<!--
	$(document).ready(function() {
		$('#daemon_<?php echo $daemon->getId() ?>_form').form(function(data) {
			$.rad.notify('You have updated this daemon', 'You have updated this daemon.');
			$('#daemon_search_form').trigger('submit');
			$('#edit_daemon_modal').modal('hide');
		}, {keep_form: true});

		$('#class_name_<?php echo $daemon->getId() ?>').selectize();
	});
	
	function confirmDelete() {
		if (confirm('Are you sure you want to delete this daemon from the system?')) {
			$.rad.del('/api/daemon', {_id: '<?php echo $daemon->getId() ?>'}, function(data) {
				$.rad.notify('You have deleted this daemon', 'You have deleted this daemon.  You will need to refresh this page to see your changes.');
				$('#daemon_search_form').trigger('submit');
				$('#edit_daemon_modal').modal('hide');
			});
		}
	}
	//-->
	</script>
<?php } else { ?>
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
		<h4 class="modal-title">Add Daemon</h4>
	</div>
	<form id="daemon_<?php echo $daemon->getId() ?>_form" action="/api/daemon" method="POST">
		<input type="hidden" name="status" value="1" />
		<div class="modal-body">
			<div class="form-group">
				<label for="name">Name:</label>
				<input type="text" class="form-control" placeholder="enter daemon name" id="name" name="name" value="<?php echo $daemon->getName() ?>" size="35" />
			</div>
			<div class="form-group">
				<label for="description">Description:</label>
				<textarea name="description" id="description" class="form-control" placeholder="enter daemon description" cols="35" rows="3"><?php echo $daemon->getDescription() ?></textarea>
			</div>
			<hr />
			<div class="help-block">Specify the low-level options for this daemon such as the PHP class to use to instantiate it</div>
			<div class="form-group">
				<label for="name">Class Name:</label>
				<select name="class_name" id="class_name_<?php echo $daemon->getId() ?>" placeholder="select the php class to use for this daemon">
					<?php foreach ($daemon_classes as $daemon_class) { ?>
						<option value="<?php echo $daemon_class?>" <?php echo $daemon->getClassName() == $daemon_class ? 'selected' : '' ?>><?php echo $daemon_class ?></option>
					<?php } ?>
				</select>
			</div>
			<div class="form-group">
				<label for="name">Type:</label>
				<input type="text" class="form-control" placeholder="enter the type of daemon (unique)" id="type" name="type" value="<?php echo $daemon->getType() ?>" size="35" />
			</div>
			<div class="form-group">
				<label for="name"># of Threads:</label>
				<input type="text" class="form-control" placeholder="enter the maximum # of threads to use" id="threads" name="threads" value="<?php echo $daemon->getThreads() ?>" size="35" />
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
		$('#daemon_<?php echo $daemon->getId() ?>_form').form(function(data) {
			$.rad.notify('You have added this daemon', 'You have added this daemon.');
			$('#daemon_search_form').trigger('submit');
			$('#edit_daemon_modal').modal('hide');
		}, {keep_form: true});

		$('#class_name_<?php echo $daemon->getId() ?>').selectize();
	});
	//-->
	</script>
<?php } ?>