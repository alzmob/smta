<?php
	header("Content-Type: text/json");
	/* @var $ajax_form BasicAjaxForm */ 
	$ajax_form = $this->getContext()->getRequest()->getAttribute('ajax_form');
	
	if (is_null($ajax_form)) {
		$ajax_form = new \Mojavi\Form\BasicAjaxForm();
	}
?>
<?php $output = $ajax_form->toArray(); ?>
<?php $json = json_encode($output) ?>
<?php if ($json === false) { ?>
<?php echo json_encode(array('result' => 'FAILED', 'errors' => array(0 => json_last_error_msg()))) ?>
<?php } else { ?>
<?php echo $json ?>
<?php } ?>