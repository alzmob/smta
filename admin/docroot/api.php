<?php
try {
    // +---------------------------------------------------------------------------+
    // | An absolute filesystem path to our webapp/config.php script.              |
    // +---------------------------------------------------------------------------+
    
    require_once(dirname(__FILE__) . '/../webapp/config.php');
    
    // +---------------------------------------------------------------------------+
    // | An absolute filesystem path to the mojavi/mojavi.php script.              |
    // +---------------------------------------------------------------------------+
    require_once(MO_APP_DIR . '/mojavi.php');
    
    // +---------------------------------------------------------------------------+
    // | Create our controller. For this file we're going to use a front           |
    // | controller pattern. This pattern allows us to specify module and action   |
    // | GET/POST parameters and it automatically detects them and finds the       |
    // | expected action.                                                          |
    // +---------------------------------------------------------------------------+
    $controller = \Mojavi\Controller\Controller::newInstance('\Mojavi\Controller\FrontRestController');
    // +---------------------------------------------------------------------------+
    // | Dispatch our request.                                                     |
    // +---------------------------------------------------------------------------+
    $controller->dispatch();
} catch (Exception $e) {
    header('Content-Type: application/json');
    $ajax_form = new \Mojavi\Form\BasicAjaxForm();
    $ajax_form->getErrors()->addError('error', new \Mojavi\Error\Error('API: ' . $e->getMessage()));
    $output = $ajax_form->toArray();
    error_log(json_encode($output));
    echo json_encode($output);
}
