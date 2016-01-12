<!DOCTYPE html>
<html lang="en">
<head>
	<title><?php echo $this->getTitle() ?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1"/>
	<link rel="shortcut icon" href="/images/favicon.gif" type="image/gif" />
	
	<!-- JQuery Plugins -->
	<script src="http://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
	<script src="http://code.jquery.com/jquery-migrate-1.2.1.js"></script>
	<script src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.11.2/jquery-ui.min.js"></script>
	
	<!-- Bootstrap Plugins -->
	<link rel="stylesheet" href="/css/bootstrap.css" />
	<!-- <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap-theme.min.css" />  -->
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
	<!-- <link rel="stylesheet" href="//ajax.googleapis.com/ajax/libs/jqueryui/1.11.2/themes/cupertino/jquery-ui.css" /> -->
	
	<!-- Bootstrap dropdown plugin -->
	<script src="/scripts/bootstrap-hover-dropdown/bootstrap-hover-dropdown.min.js"></script>
	
	<!-- Font Awesome library -->
	<link href="http://maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css" rel="stylesheet" />
	
	<!-- Font Awesome library -->
	<link href="http://maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css" rel="stylesheet" />
	
	<!-- Pnotify Plugin used by RAD -->
	<script type="text/javascript" src="/scripts/pnotify/pnotify.custom.min.js"></script>
	<link rel="stylesheet" href="/scripts/pnotify/pnotify.custom.min.css"></link>
	
	<!--  RAD Plugin for Ajax Requests -->
	<script type="text/javascript" src="/scripts/rad/jquery.rad.js"></script>
	
	<!-- Cookie plugin -->
	<script type="text/javascript" src="/scripts/jquery.cookie.js"></script>
	
	<!-- Datepicker plugin -->
	<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.5.0/js/bootstrap-datepicker.min.js"></script>
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.5.0/css/bootstrap-datepicker.min.css" />
	
	<!-- Colorpicker plugin -->
	<script type="text/javascript" src="/scripts/bootstrap-colorpicker/js/bootstrap-colorpicker.min.js"></script>
	<link rel="stylesheet" href="/scripts/bootstrap-colorpicker/css/bootstrap-colorpicker.min.css" />
	
	<!-- Numbers plugin -->
	<script type="text/javascript" src="/scripts/jquery.number.min.js"></script>
	
	<!-- Timers plugin -->
	<script type="text/javascript" src="/scripts/jquery.timers-1.2.js"></script>
	
	<!-- Moment plugin -->
	<script type="text/javascript" src="/scripts/momentjs/moment.min.js"></script>
	
	<!-- Switch for checkboxes plugin -->
	<script type="text/javascript" src="/scripts/switch/bootstrap-switch.min.js"></script>
	<link rel="stylesheet" href="/scripts/switch/bootstrap-switch.min.css"></link>
	
	<!-- Slick Grid plugins -->
	<script src="http://crypto-js.googlecode.com/svn/tags/3.1.2/build/rollups/md5.js"></script>
	<script type="text/javascript" src="/scripts/slick-grid-1.4/lib/jquery.event.drag.min.2.0.js"></script>
	<script type="text/javascript" src="/scripts/slick-grid-1.4/slick.model.rad.js"></script>
	<script type="text/javascript" src="/scripts/slick-grid-1.4/slick.pager.rad.js"></script>
	<script type="text/javascript" src="/scripts/slick-grid-1.4/slick.columnpicker.rad.js"></script>
	<script type="text/javascript" src="/scripts/slick-grid-1.4/slick.grid.rad.js"></script>
	<script type="text/javascript" src="/scripts/slick-grid-1.4/jquery.slickgrid.rad.js"></script>
	<link rel="stylesheet" href="/scripts/slick-grid-1.4/css/slick.columnpicker.css"></link>
	<link rel="stylesheet" href="/scripts/slick-grid-1.4/css/slick.pager.css"></link>
	<link rel="stylesheet" href="/scripts/slick-grid-1.4/css/slick.ui.css"></link>
	<link rel="stylesheet" href="/scripts/slick-grid-1.4/css/slick.grid.css"></link>
		
	<!-- Jquery selectize plugin -->
	<script type="text/javascript" src="/scripts/selectize/js/standalone/selectize.js"></script>
	<link rel="stylesheet" href="/scripts/selectize/css/selectize.bootstrap3.css"></link>
	
	<!-- Hash table Plugin used by slick grid -->
	<script type="text/javascript" src="/scripts/jshashtable-2.1.js"></script>
	
	<!-- Number formatted and date formatter -->
	<script type="text/javascript" src="/scripts/jquery.numberformatter-1.2.1.js"></script>
	
	<!-- Smart resize plugin used for chart redrawing -->
    <script src="/scripts/jquery-smartresize/jquery.debouncedresize.js" type="text/javascript" ></script>
	
	<!-- 
	<script type="text/javascript" src="/js/jquery-ui-timepicker-addon.js"></script>
	-->
	
	<link rel="stylesheet" href="/css/main.css"></link>
</head>

<body>
	<nav class="navbar-collapse navbar-inverse" role="navigation" id="top">
		<div class="container-fluid">
			<!-- Brand and toggle get grouped for better mobile display -->
			<div class="navbar-header">
				<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar-collapse-1">
					<span class="sr-only">Toggle navigation</span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
				</button>
				<a class="navbar-brand" href="#"><?php echo \Smta\Setting::getSetting('BRAND_NAME') != '' ? \Smta\Setting::getSetting('BRAND_NAME') : 'smta simple mailer' ?></a>
			</div>
			<form class="navbar-form hidden-xs hidden-sm navbar-right" id="nav_search_form" role="search" action="/search">
                <div class="form-group">
                    <input type="text" class="form-control selectize" id="nav_search" name="keywords" style="width:300px;" size="35" placeholder="search..." value="">
                </div>
            </form>
		</div>
	</nav>
	<nav class="navbar-collapse navbar-default" role="navigation">
		<!-- Collect the nav links, forms, and other content for toggling -->
	    <div class="collapse navbar-collapse" id="navbar-collapse-1">
			<?php if ($this->getMenu() !== null) { ?>
				<ul class="nav navbar-nav">
				<?php
					/* @var $page Zend\Navigation\Page */
					foreach ($this->getMenu()->getPages() as $page) {
				?>
					<?php if ($page->hasChildren()) { ?>
						<li class="dropdown">
							<a class="hidden-xs dropdown-toggle" data-hover="dropdown" data-delay="1000" data-close-others="true" aria-expanded="false" role="button" href="<?php echo $page->getHref() ?>" class="<?php echo $page->getClass() ?>"><?php echo $page->getLabel() ?><span class="caret"></span></a>
							<a class="visible-xs dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false" href="#"><?php echo $page->getLabel() ?><span class="caret"></span></a>
							<ul class="dropdown-menu" role="menu">
							<?php
								/* @var $child_page \Zend\Navigation\Page */
								foreach ($page->getPages() as $child_page) {
							?>
								<?php if ($child_page->getLabel() != '') { ?>
									<li><a href="<?php echo $child_page->getHref() ?>" class="<?php echo $child_page->getClass() ?>"><?php echo $child_page->getLabel() ?></a></li>
								<?php } else { ?>
									<li class="divider"></li>
								<?php } ?>
							<?php } ?>
							</ul>
						</li>
					<?php } else { ?>
						<li><a href="<?php echo $page->getHref() ?>" role="button" aria-expanded="false"><?php echo $page->getLabel() ?></a></li>
					<?php } ?>
				<?php } ?>
				</ul>
			<?php } ?>
			<ul class="nav navbar-nav navbar-right">
                <li><a href="/logout" class="navbar-link">Logout</a></li>
            </ul>
		</div>
		
	</nav>
	
	<div id="body-container">
		<?php if (!$this->getErrors()->isEmpty()) { ?>
			<div class="container">
				<p />
				<div class="alert alert-danger alert-dismissible" role="alert">
					<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					<?php echo $this->getErrors()->getAllErrors(); ?>
				</div>
			</div>
		<?php } ?>
		<!-- Insert body here -->
		<?php echo $template["content"] ?>
	</div>
	
    <div class="footer small hidden-xs">
        <div class="container-fluid">
            <ul class="nav navbar-nav">
                <li><a href="/default/index">dashboard</a></li>
                <li><a href="/report/dashboard">reports</a></li>
                <li><a href="/default/logout">logout</a></li>
            </ul>
            <p class="navbar-text navbar-right">Smta Simple Mailer version 1.0.1-<?php echo \Smta\Setting::getSetting('migration_version') ?>. All Rights Reserved&nbsp;&nbsp;&nbsp;&nbsp;</p>
        </div>
    </div>
    <script>
    //<!--
    $(document).ready(function() {
        
    	$('#nav_search').selectize({
        	valueField: 'url',
            labelField: 'name',
            searchField: ['description','name'],
            options: [],
            dropdownWidthOffset: 100,
            optgroupField: 'optgroup',
            optgroups: [
                { label: 'drops', value: 'drops' },
            ],
            create: false,
            render: {
            	optgroup_header: function(item, escape) {
                    return '<b class="optgroup-header">' +
                        escape(item.label) +
                       '</b>';
                  },
                option: function(item, escape) {
                    return '<div>' +
                        '<a href="' + escape(item.url) + '">' +
                        '<span class="title">' +
                            '<span class="name">' + escape(item.name) + '</span>' +
                        '</span>' +
                        '<span class="description">' + escape(item.description) + '</span>' + 
                        '<span class="description">' + ((item.meta) ? escape(item.meta) : '') + '</span>' +
                        '</a>' +
                    '</div>';
                }
            },
            load: function(query, callback) {
                if (!query.length) return callback();
                this.clearOptions();
                $.ajax({
                    url: '/api/search',
                    type: 'GET',
                    dataType: 'json',
                    data: {
                        keywords: query
                    },
                    error: function() {
                        callback();
                    },
                    success: function(res) {
                        callback(res.entries);
                    }
                });
            },
            onItemAdd: function(value,item) {
                // Redirect to whatever was selected
                location.replace(value);
            }
        });
    });
    //-->
	</script>
</body>
</html>