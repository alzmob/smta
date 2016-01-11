<?php
	/* @var $ftp \Smta\Ftp */
	$ftp = $this->getContext()->getRequest()->getAttribute('ftp', array());
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<!-- JQuery Plugins -->
	<script src="http://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
	<script src="http://code.jquery.com/jquery-migrate-1.2.1.js"></script>
	
	<!-- Bootstrap Plugins -->
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/css/bootstrap.min.css" />
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/css/bootstrap-theme.min.css" />
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/js/bootstrap.min.js"></script>
	<link rel="stylesheet" href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.11.2/themes/cupertino/jquery-ui.css" />
	
	<!-- Dropzone Plugin -->
	<script src="/scripts/dropzone/dropzone.min.js"></script>
	<link rel="stylesheet" href="/scripts/dropzone/dropzone.css" />
	
	<style>
	body {
		margin: 5px;
		padding: 0px;
	}
	
	table {
		font-Size: 8pt;
		line-height: 8pt;
		color: #666666;
		margin: 5px;
		padding: 0px;
	}
	</style>
</head>
<body>
<?php 
	try { 
		$files = $ftp->getFiles();
?>
	<div id="dropzone">
		<form method="post" class="dropzone dz-clickable" action="/admin/ftp-upload" id="file_upload_form" class="form-inline" enctype="multipart/form-data">
			<input type="hidden" name="folder_name" value="<?php echo $ftp->getFolderName() ?>" />
			<input type="hidden" name="username" value="<?php echo $ftp->getUsername() ?>" />
			<input type="hidden" name="password" value="<?php echo $ftp->getPassword() ?>" />
			<input type="hidden" name="hostname" value="<?php echo $ftp->getHostname() ?>" />
			<input type="hidden" name="use_active_mode" value="<?php echo $ftp->getUseActiveMode() ? '1' : '0' ?>" />
			<div class="dz-message">
				Drop files here or click to upload to this folder
				<div class="small text-muted">Note: changing folders will cancel any pending uploads</div>
				<div class="small text-muted">(maximum upload file size <?php echo ini_get('upload_max_filesize') ?>)</div>
			</div>
		</form>
	</div>
	<table cellpadding="2" cellspacing="0" border="0" width="99%">
		<tr>
			<td style="width:28px;"><a href="/default/ftp-explorer?html_input_element_id=<?php echo $ftp->getHtmlInputElementId() ?>&username=<?php echo $ftp->getUsername() ?>&password=<?php echo $ftp->getPassword() ?>&hostname=<?php echo $ftp->getHostname() ?>&use_active_mode=<?php echo $ftp->getUseActiveMode() ? '1' : '0' ?>&folder_name=<?php echo dirname($ftp->getFolderName()) ?>"><img src="/images/folder.png" border="0" align="top" /></a></td>
			<td colspan="2"><a href="/default/ftp-explorer?html_input_element_id=<?php echo $ftp->getHtmlInputElementId() ?>&username=<?php echo $ftp->getUsername() ?>&password=<?php echo $ftp->getPassword() ?>&hostname=<?php echo $ftp->getHostname() ?>&use_active_mode=<?php echo $ftp->getUseActiveMode() ? '1' : '0' ?>&folder_name=<?php echo dirname($ftp->getFolderName()) ?>"> ..</a></td>
		</tr>
		<?php 
			foreach ($files as $filename => $file) {
		?>
			<?php if ($file['type'] == 'directory') { ?>
				<tr>
					<td style="width:28px;"><a href="/default/ftp-explorer?html_input_element_id=<?php echo $ftp->getHtmlInputElementId() ?>&username=<?php echo $ftp->getUsername() ?>&password=<?php echo $ftp->getPassword() ?>&hostname=<?php echo $ftp->getHostname() ?>&use_active_mode=<?php echo $ftp->getUseActiveMode() ? '1' : '0' ?>&folder_name=<?php echo $ftp->getFolderName() ?>/<?php echo $filename ?>"><img src="/images/folder.png" border="0" align="top" /></a></td>
					<td colspan="2"><a href="/default/ftp-explorer?html_input_element_id=<?php echo $ftp->getHtmlInputElementId() ?>&username=<?php echo $ftp->getUsername() ?>&password=<?php echo $ftp->getPassword() ?>&hostname=<?php echo $ftp->getHostname() ?>&use_active_mode=<?php echo $ftp->getUseActiveMode() ? '1' : '0' ?>&folder_name=<?php echo $ftp->getFolderName() ?>/<?php echo $filename ?>"> <?php echo $filename ?></a></td>
				</tr>
			<?php } ?>
		<?php } ?>
		<?php 
			foreach ($files as $filename => $file) {
		?>
			<?php if ($file['type'] != 'directory') { ?>
				<tr>
					<?php if (trim($ftp->getHtmlInputElementId()) != '') { ?>
						<td style="width:24px;"><a href="javascript:setParentFilename('<?php echo $ftp->getFolderName() ?><?php echo ($ftp->getFolderName() != '' ? '/' : '') ?><?php echo $filename ?>');"><img src="/images/offer.png" border="0" align="top" /></a></td>
						<td><a href="javascript:setParentFilename('<?php echo $ftp->getFolderName() ?><?php echo ($ftp->getFolderName() != '' ? '/' : '') ?><?php echo $filename ?>');"><?php echo $filename ?></a></td>
						<td style="text-Align:right;"><?php echo number_format($file['size'], 0, null, ',') ?> bytes</td>
					<?php } else { ?>
						<td style="width:24px;"><img src="/images/offer.png" border="0" align="top" /></td>
						<td><?php echo $filename ?></td>
						<td style="text-Align:right;"><?php echo number_format($file['size'], 0, null, ',') ?> bytes</td>
					<?php } ?>
				</tr>
			<?php } ?>
		<?php } ?>
	</table>
	<script>
	//<!--
	function setParentFilename(filename) {
		if ('<?php echo $ftp->getHtmlInputElementId() ?>' != '') {
			$('#<?php echo $ftp->getHtmlInputElementId() ?>', window.parent.document).val('<?php echo $this->getContext()->getUser()->getUserDetails()->getFtp()->getFtp()->getHomeFolder() . DIRECTORY_SEPARATOR ?>' + filename);
		}
	}
	//-->
	</script>
<?php } catch (\Exception $e) { ?>
	<div class="alert alert-danger alert-dismissible fade in" role="alert">
		<button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">x</span><span class="sr-only">Close</span></button>
		<?php echo $e->getMessage() ?>
	</div>
<?php } ?>
<script>
//<!--
$(document).ready(function() {
	$('#ftp_explorer_loading_div', window.parent.document).hide();
});
//-->
</script>
</body>
</html>