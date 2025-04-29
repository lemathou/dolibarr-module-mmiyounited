<?php

require_once 'env.inc.php';
require_once 'main_load.inc.php';

dol_include_once('mmiyounited/class/mmi_younited_pay.class.php');

$mmi_younited_debug = getDolGlobalInt('MMI_YOUNITED_DEBUG', 0);

$action = GETPOST('action', 'alpha');
$objecttype = GETPOST('objecttype', 'alpha');
$objectid = GETPOST('objectid', 'int');
$securekey = GETPOST('securekey', 'alpha');
$amount = GETPOST('amount', 'alpha');

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
$maturity_default_list = '12,24,48';

$younited_service->api_shops();
$younited_service->api_personal_loans_offers(5000);
$payment = $younited_service->api_personal_loan_create($objecttype, $objectid, $amount, 12);
if ($mmi_younited_debug)
	var_dump($payment);
echo '<a href="'.$payment['paymentLink'].'">Accéder au paiement</a>';
$payment_info = $younited_service->api_payment_info($payment['paymentId']);
if ($mmi_younited_debug)
	var_dump($payment_info);
$payment_status = $younited_service->api_payment_status($payment['paymentId']);
if ($mmi_younited_debug)
	var_dump($payment_status);

//var_dump($object);