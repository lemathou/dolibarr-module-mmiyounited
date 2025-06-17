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
$amount = GETPOST('amount', 'alpha');
$maturity = GETPOST('maturity', 'int');

$younited_service = mmi_younited_pay::_instance();

//var_dump($action, $objecttype, $objectid, $securekey);

$object = mmi_payments::loadobject($objecttype, $objectid);
if (empty($object)) {
	echo 'Object not found';
	exit;
}
//var_dump(mmi_payments::securekey($objecttype, $objectid));
if ($securekey != mmi_payments::securekey($objecttype, $objectid)) {
	echo 'Securekey not valid';
	exit;
}

$token = $younited_service->api_token();
//echo '<pre>'.$token.'</pre>';

// Paramètres

$younited_service->api_shops();
$payment = $younited_service->api_personal_loan_create($objecttype, $objectid, $amount, $maturity ?:12);
if ($mmi_younited_debug)
	var_dump($payment);
$payment_info = $younited_service->api_payment_info($payment['paymentId']);
if ($mmi_younited_debug)
	var_dump($payment_info);
$payment_status = $younited_service->api_payment_status($payment['paymentId']);
if ($mmi_younited_debug)
	var_dump($payment_status);

if (!empty($payment['paymentLink'])) {
	header('Location: '.$payment['paymentLink']);
	echo '<script>window.location.href="'.$payment['paymentLink'].'";</script>';
	echo '<a href="'.$payment['paymentLink'].'">Accéder au paiement</a>';
} else {
	echo 'Pas de lien de paiement';
}
