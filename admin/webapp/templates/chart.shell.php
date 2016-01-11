<?php
	header('Content-Type: text/javascript');
	/* @var $ajax_form BasicAjaxForm */ 
	$ajax_form = $this->getContext()->getRequest()->getAttribute('ajax_form');
	if (is_null($ajax_form)) {
		$ajax_form = new BasicAjaxForm();
	}
	$request_id = 0;
	if (isset($_REQUEST['tqx'])) {
		$request_id = str_replace('reqId:', '', $_REQUEST['tqx']);
	}

$response = array(
			"reqId" => $request_id,
			"version" => "0.6",
			"status" => "ok",
			"sig" => "1029305520",
			"table" => array(
					"cols" => $ajax_form->getRecord()->getCols(),
					"rows" => $ajax_form->getRecord()->getRows()
			) 
);
?>
// Data table response
google.visualization.Query.setResponse(<?php echo @json_encode($response) ?>);