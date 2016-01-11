<!DOCTYPE html>
<html lang="en">
	<head>
		<meta http-equiv="content-type" content="text/html; charset=UTF-8">
		<meta charset="utf-8">
		<title><?php echo $this->getTitle() ?></title>
		<link rel="icon" href="favicon.ico" />
		<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
		<!--[if lt IE 9]>
			<script src="//html5shim.googlecode.com/svn/trunk/html5.js"></script>
		<![endif]-->

		<!-- Bootstrap and jQuery base classes -->
		<link href="//netdna.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css" rel="stylesheet" />
		<script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
		<script src="//code.jquery.com/jquery-migrate-1.2.1.js"></script>
		<script src="//netdna.bootstrapcdn.com/bootstrap/3.3.4/js/bootstrap.min.js"></script>
		<script src="/scripts/jquery-ui.min.js"></script>

		<!-- Font Awesome library -->
		<link href="//maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css" rel="stylesheet" />
		
		<!-- RAD plugins used for ajax requests, notifications, and form submission -->
		<link href="/scripts/pnotify/pnotify.custom.min.css" rel="stylesheet" type="text/css" />
		<script src="/scripts/pnotify/pnotify.custom.min.js" type="text/javascript" ></script>
		<script src="/scripts/rad/jquery.rad.js" type="text/javascript"></script>
		
		<!-- Selectize plugin for select boxes and comma-delimited fields -->
		<link href="/scripts/selectize/css/selectize.bootstrap3.css" rel="stylesheet" type="text/css" />
		<script src="/scripts/selectize/js/standalone/selectize.min.js" type="text/javascript"></script>
		
		<!-- Moment plugin for formatting dates -->
		<script type="text/javascript" src="/scripts/moment.min.js"></script>
				
		<!-- Timers used for firing events -->
		<script src="/scripts/timers/jquery.timers-1.2.js" type="text/javascript" ></script>
		
		<!-- Default site css -->
		<link href="/css/headless.css" rel="stylesheet">
	</head>
	<body>		
		<div class="container-fluid">
			<?php if (!$this->getErrors()->isEmpty()) { ?>
				<div class="alert alert-warning alert-dismissible" role="alert">
  					<button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
					<?php echo $this->getErrors()->getAllErrors(); ?>
				</div>
			<?php } ?>
			<!-- Insert body here -->
			<?php echo $template["content"] ?>
		</div>
	</body>
</html>