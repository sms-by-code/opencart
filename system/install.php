<?php
header('Content-Type: application/json');
echo json_encode(array());
flush();

require_once DIR_APPLICATION . "controller/extension/module/startsend.php";

$modification_controller = new ControllerExtensionModuleStartsend($this->registry);
@$modification_controller->install();

$this->load->model('extension/extension');
$this->model_extension_extension->uninstall('module', "startsend");
@$this->model_extension_extension->install('module', "startsend");

die;