<?php

if (!defined('NOLOGIN')) define("NOLOGIN", 1); // This means this output page does not require to be logged.
if (!defined('NOCSRFCHECK')) define("NOCSRFCHECK", 1); // We accept to go on this page from external web site.

require_once 'env.inc.php';
require_once 'main_load.inc.php';

dol_include_once('mmiyounited/class/mmi_younited_pay.class.php');

$mmi_younited_debug = getDolGlobalInt('MMI_YOUNITED_DEBUG', 0);

$action = GETPOST('action', 'alpha');
$objecttype = GETPOST('objecttype', 'alpha');
$objectid = GETPOST('objectid', 'int');
$securekey = GETPOST('securekey', 'alpha');
$amount = GETPOST('amount', 'san_alpha');
$amount = str_replace(',', '.', trim($amount));
$maturity = GETPOST('maturity', 'int');

if ($amount !== '' && (!is_numeric($amount) || (float) $amount <= 0)) {
	echo 'Invalid amount';
	exit;
}

$younited_service = mmi_younited_pay::_instance();


$object = mmi_payments::loadobject($objecttype, $objectid);
if (empty($object) || empty($object->id)) {
	echo 'Object not found';
	exit;
}
$expected_securekey = mmi_payments::securekey($objecttype, $objectid);
if (empty($securekey) || empty($expected_securekey) || !hash_equals((string) $expected_securekey, (string) $securekey)) {
	echo 'Securekey not valid';
	exit;
}

$token = $younited_service->api_token();
//echo '<pre>'.$token.'</pre>';

// Paramètres

$younited_service->api_shops();
$payment = $younited_service->api_personal_loan_create($objecttype, $objectid, $amount, $maturity ?:12);
if (empty($payment) || empty($payment['paymentId'])) {
	echo 'Payment creation failed';
	exit;
}

if (!empty($payment['paymentLink'])) {
	$payment_link = (string) $payment['paymentLink'];
	if (!preg_match('#^https?://#i', $payment_link)) {
		echo 'Invalid payment link';
		exit;
	}
	header('Location: '.$payment_link);
	echo '<script>window.location.href='.json_encode($payment_link).';</script>';
	echo '<a href="'.dol_escape_htmltag($payment_link).'">Accéder au paiement</a>';
} else {
	echo 'Pas de lien de paiement';
}
