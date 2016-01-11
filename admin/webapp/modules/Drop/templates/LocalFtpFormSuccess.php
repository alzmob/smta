<div class="modal-header">
	<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
	<h4 class="modal-title">Browse Local FTP</h4>
</div>
<div class="modal-body">
	You can use this wizard to browse the local FTP server and find a file to import
	<p />
	<?php echo $this->getContext()->getUser()->getUserDetails()->getFtp()->getFtp()->getUsername() ?>@<?php echo $this->getContext()->getUser()->getUserDetails()->getFtp()->getFtp()->getHostname() ?>
	<p />
	<iframe style="border:1px solid #C8C8C8;" src="/default/ftp-explorer?html_input_element_id=<?php echo $this->getContext()->getRequest()->getParameter('html_input_element_id', 'list_file_location_' . \Smta\Drop::UPLOAD_FILE_TYPE_FTP) ?>&hostname=<?php echo $this->getContext()->getUser()->getUserDetails()->getFtp()->getFtp()->getHostname() ?>&username=<?php echo $this->getContext()->getUser()->getUserDetails()->getFtp()->getFtp()->getUsername() ?>&password=<?php echo $this->getContext()->getUser()->getUserDetails()->getFtp()->getFtp()->getPassword() ?>" width="100%" height="500" frameborder="0"></iframe>
</div>
<div class="modal-footer">
	<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
</div>