<?php

// Hack
if (isset($_GET['input'])) {
	$input = json_decode($_GET['input'], true);
	unset($_GET['input']);
	unset($_REQUEST['input']);
	$_SERVER['REQUEST_URI'] = '/custom/mmiyounited/webhook.php';
	$_SERVER['QUERY_STRING'] = '';
}

require_once 'env.inc.php';
require_once 'main_load.inc.php';

dol_include_once('mmiyounited/class/mmi_younited_pay.class.php');

define('MMI_YOUNITED_WEBHOOK_LOG', true);
define('MMI_YOUNITED_WEBHOOK_LOG_FILENAME', 'logs/webhook.log');

if (empty($input)) {
	$inputJSON = file_get_contents('php://input');
	$input = json_decode($inputJSON, TRUE); //convert JSON into array
}

if (MMI_YOUNITED_WEBHOOK_LOG) {
	$msg = '----------'."\n".date('Y-m-d H:i:s')."\n";
	$msg .= 'POST: '.var_export($_POST, true)."\n";
	$msg .= 'GET: '.var_export($_GET, true)."\n";
	$msg .= 'JSONINPUT: '.var_export($input, true)."\n";
	$msg .= 'REQUEST: '.var_export($_REQUEST, true)."\n";
	$msg .= 'SERVER: '.var_export($_SERVER, true)."\n";

	$fp = fopen(MMI_YOUNITED_WEBHOOK_LOG_FILENAME, 'a');
	fwrite($fp, $msg);
	fclose($fp);
}

if (empty($input) || !is_array($input)) {
	echo 'No input';
	die();
}

// Payment update
if (!empty($input['type']) && $input['type']==='payment.updated'
	&& !empty($input['data']) && !empty($input['data']['paymentId'])) {

	$younited_service = mmi_younited_pay::_instance();
	$result = $younited_service->webhook_update($input['data']['paymentId'], $input);
}

/* // Webhook Data format
{
	"type": "payment.updated",
	"notificationId": "5ef9f8e6-2bc0-456b-82d7-f028be989b33",
	"data": {
	  "type": "payment",
	  "paymentId": "YPAY_e99376c0-4d41-4155-b773-5f61b68bef6d",
	  "status": "Executed",
	  "updatedAt": "2024-12-26T18:39:42.9927602"
	},
	"createdAt": "2024-12-26T18:39:47.3428149Z"
}
*/
