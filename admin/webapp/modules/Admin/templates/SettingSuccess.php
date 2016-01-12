<div class="container-fluid">
	<div class="page-header">
		<h1><span class="glyphicon glyphicon-cog hidden-xs"></span> System Settings</h1>
		<div class="text-muted">These are global settings that are used through out the system.  Configure each setting to customize your user experience.</div>
	</div>
	<ol class="breadcrumb">
		<li><a href="/admin/setting">Admin</a></li>
		<li class="active">System Settings</li>
	</ol>
	<p />
	<div class="">
		<div role="tabpanel">
			<!-- Nav tabs -->
			<ul class="nav nav-tabs" role="tablist">
				<li role="presentation" class="active"><a href="#settings" aria-controls="home" role="tab" data-toggle="tab">Global System Settings</a></li>
				<li role="presentation"><a href="#updates" aria-controls="profile" role="tab" data-toggle="tab">Updates</a></li>
				<li role="presentation"><a href="#opcache" aria-controls="profile" role="tab" data-toggle="tab">Opcache</a></li>
			</ul>
			<!-- Tab panes -->
			<div class="tab-content">
				<div role="tabpanel" class="tab-pane fade in active" id="settings">
					<form id="system_values_form"  method="POST" action="/api/setting">
						<h3 class="sub-header">Email Notifications</h3>
						<div class="form-group">
							<div class="help-block">Emails sent when there are <b class="text-danger">issues or bugs</b> found in the site</div>
							<input type="hidden" name="setting_array[support_email]" value="" />
							<input type="text" class="form-control" id="support_email" placeholder="enter email addresses that should receive support emails" name="setting_array[support_email]" value="<?php echo \Smta\Setting::getSetting('SUPPORT_EMAIL'); ?>" />
						</div>
						<div class="form-group">
							<div class="help-block">Emails sent when an <b class="text-success">drops</b> has completed and any errors encountered</div>
							<input type="hidden" name="setting_array[drop_email]" value="" />
							<input type="text" class="form-control" id="drop_email" placeholder="enter email addresses that should receive import summary reports" name="setting_array[drop_email]" value="<?php echo \Smta\Setting::getSetting('DROP_EMAIL'); ?>" />
						</div>
						<br />
						<h3 class="sub-header">Server Settings</h3>
						<div class="form-group">
							<div class="help-block">Set the ip address, root username and root password used to access this server for PowerMTA Maintenance</div>
							<input type="text" name="setting_array[admin_ip_address]" id="admin_ip_address" class="form-control" value="<?php echo \Smta\Setting::getSetting('ADMIN_IP_ADDRESS'); ?>" placeholder="Enter server ip address (x.x.x.x)..." />
							<br />
							<input type="text" name="setting_array[root_username]" id="root_username" class="form-control" value="<?php echo \Smta\Setting::getSetting('ROOT_USERNAME'); ?>" placeholder="Enter root username (root)..." />
							<br />
							<input type="text" name="setting_array[root_password]" id="root_password" class="form-control" value="<?php echo \Smta\Setting::getSetting('ROOT_PASSWORD'); ?>" placeholder="Enter root password..." />
						</div>
						<br />
						<h3 class="sub-header">System Defaults</h3>
						<div class="form-group">
							<div class="help-block">Set a list of domain suffixes to use when adding domain names to domain groups</div>
							<input type="text" name="setting_array[domain_suffixes]" id="domain_suffixes" class="form-control" value="<?php echo \Smta\Setting::getSetting('DOMAIN_SUFFIXES'); ?>" />
						</div>
						<br />
						<h3 class="sub-header">Interface Options</h3>
						<div class="form-group">
							<div class="help-block">Select how many items to show on search pages by default</div>
							<select id="items_per_page" name="setting_array[items_per_page]" placeholder="enter default number of items to show on search pages">
								<option value="10" <?php echo \Smta\Setting::getSetting('ITEMS_PER_PAGE') == '10' ? 'selected' : '' ?>>Show up to 10 records per page</option>
								<option value="25" <?php echo \Smta\Setting::getSetting('ITEMS_PER_PAGE') == '25' ? 'selected' : '' ?>>Show up to 25 records per page</option>
								<option value="50" <?php echo \Smta\Setting::getSetting('ITEMS_PER_PAGE') == '50' ? 'selected' : '' ?>>Show up to 50 records per page</option>
								<option value="100" <?php echo \Smta\Setting::getSetting('ITEMS_PER_PAGE') == '100' ? 'selected' : '' ?>>Show up to 100 records per page</option>
								<option value="200" <?php echo \Smta\Setting::getSetting('ITEMS_PER_PAGE') == '200' ? 'selected' : '' ?>>Show up to 200 records per page</option>
							</select>
						</div>
						<div class="form-group">
							<div class="help-block">Display your company name on the navigation bar</div>
							<input type="hidden" name="setting_array[brand_name]" value="" />
							<input type="text" id="brand_name" placeholder="enter your company name or leave blank for the default" class="form-control" name="setting_array[brand_name]" value="<?php echo \Smta\Setting::getSetting('BRAND_NAME'); ?>" />
						</div>
						<div class="form-group">
							<div class="help-block">Choose whether to show or hide the first time setup tutorial when you login</div>
							<select name="setting_array[show_tutorial]" id="show_tutorial">
								<option value="0" <?php echo \Smta\Setting::getSetting('show_tutorial') == '0' ? 'selected'  : '' ?>>Show the tutorial every time I login</option>
								<option value="1" <?php echo \Smta\Setting::getSetting('show_tutorial') == '1' ? 'selected'  : '' ?>>Don't show the tutorial, I already know how to use the system</option>
							</select>
						</div>
						<hr />
						<div class="text-center">
							<input type="submit" name="" value="Update" class="btn btn-info">
						</div>
					</form>
				</div>
				<div role="tabpanel" class="tab-pane fade in" id="updates">
					<h3 class="sub-header">Platform Information</h3>
					<div class="form-group">
						<div class="help-block">You are currently on version <?php echo \Smta\Setting::getSetting('version') ?>.  You can check for updates using the button below.</div>
						<p />
						<div id="update_btn_div">
							<div class="btn btn-info" id="update_check_btn">Check for updates</div>
						</div>
						<!-- Show the checking for updates div -->
						<div class="hidden" id="update_check_div">
							<span class="fa fa-spinner fa-spin"></span>
							Checking for updates...
						</div>
						<!-- Show the currently updated version -->
						<div class="hidden alert alert-info" id="update_current_version_div">
							<span class="glyphicon glyphicon-ok"></span>
							You are already at the most recent version.  Come back and check for more updates from time to time.
						</div>
						<!-- Show any updates with an update button and progressbar -->
						<div class="hidden" id="update_div">
							<div class="media">
								<div class="media-left">
									<span class="fa-3x glyphicon glyphicon-download"></span>
								</div>
								<div class="media-body">
									<h4 class="media-heading"></h4>
									<div class="media-description"></div>
									<div class="media-version small"></div>
									<div class="btn btn-success" id="update_btn">Update</div>
									<div class="hidden" id="update_progress_div">
										<div class="progress" style="margin-bottom:0px;">
											<div class="progress-bar" role="progressbar" style="width:0%;"></div>
										</div>
										<div class="text-muted small" id="update_progress_status"></div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div role="tabpanel" class="tab-pane fade in" id="opcache">
					<iframe src="/opcache.php" seamless frameborder="0" width="100%" height="700"></iframe>
				</div>
			</div>
		</div>
	</div>
</div>

<script>
//<!--
$(document).ready(function() {
	$('#update_check_btn').click(function() {
		// Check for updates
		checkForUpdates();
	});

	$('#update_btn').click(function() {
		$('#update_btn').addClass('hidden');
		$('#update_progress_div').removeClass('hidden');
		$.rad.get('/api', { func: '/admin/update-start' }, function(data) {
			checkProgress();
		});
	});
	
	$('#support_email,#drop_email,#domain_suffixes').selectize({
		delimiter: ',',
		persist: false,
		create: function(input) {
			return {
				value: input,
				text: input
			}
		}
	});

	$('#items_per_page,#show_tutorial').selectize();

	$('#system_values_form').form(function(data) {
		$.rad.notify('Settings saved', 'The settings have been saved to the system successfully.');
	},{keep_form:1});
});

function checkProgress() {
	$.rad.get('/api', {func: '/admin/update-progress' }, function(data) {
		if (data.record) {
			$('.progress-bar', '#update_progress_div').css('width', (data.record.percent_complete) + '%');
			$('#update_progress_status', '#update_progress_div').html(data.record.update_message);
			if (!data.record.is_updating) {
				// We are all done updating, so stop checking
				$('.progress-bar', '#update_progress_div').css('width', '100%');
				$('#update_progress_status', '#update_progress_div').html('Completing update');
				$(document).oneTime(1000, function () { checkForUpdates(); });
			} else {
				$(document).oneTime(1000, function () { checkProgress(); });
			}		
		}
		
	}, 'json', { show_indicator: false });
}

function checkForUpdates() {
	$('#update_check_div').removeClass('hidden');
	$('#update_div').addClass('hidden');
	$('#update_progress_div').addClass('hidden');
	$('#update_btn_div').addClass('hidden');
	$('#update_current_version_div').addClass('hidden');
	$('.progress-bar', '#update_progress_div').css('width', '0%');
	$('#update_progress_status', '#update_progress_div').html('');
	$.rad.get('/api', { func: '/admin/update-check' }, function(data) {
		if (data.record) {
			if (data.record.newest_package.release > data.record.installed_package.release) {
				$('.media-heading', '#update_div').html(data.record.newest_package.name + ' (' + data.record.newest_package.version + '.' + data.record.newest_package.release + ')');
				$('.media-description', '#update_div').html(data.record.newest_package.description);
				$('.media-version', '#update_div').html(data.record.newest_package.size);
				$('#update_div').removeClass('hidden');
				if (data.record.is_updating) {
					$('#update_btn').addClass('hidden');
					$('#update_progress_div').removeClass('hidden');
					checkProgress();
				} else {
					$('#update_btn').removeClass('hidden');
				}
			} else {
				$('#update_current_version_div').removeClass('hidden');
			}
		}
		$('#update_check_div').addClass('hidden');
	});
}
//-->
</script>