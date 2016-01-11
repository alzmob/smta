<div class="container-fluid">
	<h1>Test IP Addresses</h1>

	<!-- Breadcrumbs -->
	<ol class="breadcrumb">
		<li><a href="/default/index">Home</a></li>
		<li><a href="/server/index">Servers</a></li>
		<li class="active">Test IPs</li>
	</ol>

	<form method="POST" action="/api/ip-test-start" id="send_ip_test_form">
		<div class="row">
			<div class="col-md-3 col-lg-2">
				<h4>1) Enter IPs to test</h4>
				<div class="form-group">
					<textarea name="ip_range_array" class="form-control" cols="25" rows="10"></textarea>
					<br />
					<div class="small">Up to 10 random IPs will be tested</div>
				</div>
				<h4>2) Enter Seed Account</h4>
				<div class="form-group">
					<input type="text" class="form-control" id="seed_account" name="seed_account" value="" />
				</div>
				<h4>3) Enter From Domain</h4>
				<div class="form-group">
					<input type="text" class="form-control" id="from_domain" name="from_domain" value="" />
				</div>
				<h4>4) Choose Options</h4>
				<div class="form-group">
					<input type="hidden" id="use_pipelining_0" name="use_pipelining" value="0" />
					<label><input type="checkbox" id="use_pipelining_1" name="use_pipelining" value="1" checked /> Use pipelining when sending mail <i>(recommended)</i></label>
					<br />
					<input type="hidden" id="verbose_0" name="verbose" value="0" />
					<label><input type="checkbox" id="verbose_1" name="verbose" value="1" checked /> Show verbose messages <i>(recommended)</i></label>
					<br />
					<input type="hidden" id="disconnect_early_0" name="disconnect_early" value="0" />
					<label><input type="checkbox" id="disconnect_early_1" name="disconnect_early" value="1" /> Disconnect before sending message</label>
				</div>
				<div class="form-group">
					<input type="submit" name="btn_load_template" id="btn_load_template" value="test ips" class="btn btn-lg btn-primary form-control" />
				</div>
			</div>
			<div class="col-md-9 col-lg-10">
				<div class="well well-default">
					<div style="height:700px;background-color:#0f0f0f;overflow:auto;">
						<code id="processing_log" style="background-color:#0f0f0f;height:700px;">&nbsp;</code>
					</div>
					<p />
					<div class="btn btn-info btn-sm" id="reload_button">reload</div>
				</div>
			</div>
		</div>
	</form>
</div>
<script>
//<!--
$(document).ready(function() {
	$('#send_ip_test_form').form(function(data) {
		$.rad.notify('Testing in Progress', 'We are testing the IPs now, you will see the results in the right screen.');
		refreshLog();
	},{keep_form:true});

	$('#reload_button').button().click(function() {
		refreshLog();
	});
});

function refreshLog() {
	$.rad.get('/api/ip-test-raw-log', { }, function(data) {
		if (data.record) {
			$('#processing_log').html(data.record.log_contents);
			if (data.record.is_running == '1') {
				setTimeout(refreshLog(), 1000);
			}
		}
	});
}
//-->
</script>