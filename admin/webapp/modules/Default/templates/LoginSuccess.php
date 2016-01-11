<div class="panel panel-default loginbox">
	<div class="panel-heading">
		<div class="text-center header">
			<h1 class="text-uppercase"><i class="fa fa-envelope"></i> Simple Mailer</h1>
		</div>
	</div>
	<div class="panel-body">
		<?php foreach ($this->getErrors()->getErrors() as $error) { ?>
			<div class="alert alert-warning">
				<a class="close" data-dismiss="alert" href="#">x</a><?php echo $error->getMessage() ?>
			</div>
		<?php } ?>
		<form name="login_form" method="POST" action="/login" class="form-horizontal">
			<input type="hidden" name="forward" value="/<?php echo isset($_REQUEST['module']) ? strtolower($_REQUEST['module']) : 'index' ?><?php echo isset($_REQUEST['action']) ? ('/' . strtolower($_REQUEST['action'])) : '' ?>" />
			<div class="form-group">
				<label class="col-sm-2 control-label hidden-xs" for="name">Email</label>
				<div class="col-sm-10">
					<input type="text" id="username" class="form-control" name="username" value="" placeholder="Enter email to login" />
				</div>
			</div>
			<div class="form-group">
				<label class="col-sm-2 control-label hidden-xs" for="name">Password</label>
				<div class="col-sm-10">
					<input type="password" id="password" class="form-control" name="password" placeholder="Enter password to login" />
				</div>
			</div>
			<div class="form-group">
				<div class="text-center">
					<input type="submit" name="submit" class="btn btn-success btn-sm" value="Sign In" />
				</div>
			</div>
		</form>
	</div>
</div>