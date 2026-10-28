<?php

// Hack
if (isset($_GET['input'])) {
	$input = json_decode($_GET['input'], true);
	unset($_GET['input']);
	unset($_REQUEST['input']);
	$_SERVER['REQUEST_URI'] = '/custom/mmiyounited/webhook.php';
	$_SERVER['QUERY_STRING'] = '';
}

// We accept to go on this page from external web site.
define('NOLOGIN', 1);
define('NOSESSION', 1);
define("NOCSRFCHECK", 1);
define("NOIPCHECK", 1);

require_once 'env.inc.php';
require_once 'main_load.inc.php';

dol_include_once('mmiyounited/class/mmi_younited_pay.class.php');

// Journal hors racine web (DOL_DATA_ROOT), jamais accessible en HTTP.
define('MMI_YOUNITED_WEBHOOK_LOG', true);
define('MMI_YOUNITED_WEBHOOK_LOG_FILENAME', DOL_DATA_ROOT.'/mmiyounited/webhook.log');

// Journal minimal : jamais de dump de $_SERVER / $_REQUEST (cookies, Authorization…).
$fp = null;
if (MMI_YOUNITED_WEBHOOK_LOG) {
	$log_dir = dirname(MMI_YOUNITED_WEBHOOK_LOG_FILENAME);
	if (!is_dir($log_dir)) {
		@mkdir($log_dir, 0770, true);
	}
	$fp = @fopen(MMI_YOUNITED_WEBHOOK_LOG_FILENAME, 'a');
	if ($fp) {
		flock($fp, LOCK_EX);
		fwrite($fp, '----------'."\n".date('Y-m-d H:i:s').' '.($_SERVER['REMOTE_ADDR'] ?? '')."\n");
	}
}
if (empty($input)) {
	$inputJSON = file_get_contents('php://input');
	$input = json_decode($inputJSON, TRUE); //convert JSON into array
}

if (empty($input) || !is_array($input)) {
	if ($fp) {
		flock($fp, LOCK_UN);
		fclose($fp);
	}
	die();
}

if ($fp) {
	fwrite($fp, 'TYPE: '.(isset($input['type']) ? $input['type'] : '')."\n");
}

// Payment update
if (!empty($input['type']) && $input['type']==='payment.updated'
	&& !empty($input['data']) && !empty($input['data']['paymentId'])) {

	if ($fp) {
		fwrite($fp, 'PAYMENT: '.$input['data']['paymentId']."\n");
	}

	$younited_service = mmi_younited_pay::_instance();
	$result = $younited_service->webhook_update($input['data']['paymentId'], $input);
	if ($fp) {
		fwrite($fp, 'RESULT: '.(empty($younited_service->errors) ? 'ok' : implode('; ', $younited_service->errors))."\n");
	}
}

if ($fp) {
	flock($fp, LOCK_UN);
	fclose($fp);
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
