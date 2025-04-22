<?php

require_once 'env.inc.php';
require_once 'main_load.inc.php';

dol_include_once('mmipayments/class/mmi_payments.class.php');
dol_include_once('mmiyounited/class/mmi_younited_pay.class.php');

$action = GETPOST('action', 'alpha');
$objecttype = GETPOST('objecttype', 'alpha');
$objectid = GETPOST('objectid', 'int');
$securekey = GETPOST('securekey', 'alpha');
$amount = GETPOST('amount', 'alpha');

$younited_service = mmi_younited_pay::_instance();

var_dump($action, $objecttype, $objectid, $securekey);

$object = mmi_payments::loadobject($objecttype, $objectid);
if (empty($object)) {
	echo 'Object not found';
	exit;
}
var_dump(mmi_payments::securekey($objecttype, $objectid));
if ($securekey != mmi_payments::securekey($objecttype, $objectid)) {
	echo 'Securekey not valid';
	exit;
}

$token = $younited_service->api_token();

// Paramètres
$maturity_default_list = '12,24,48';

echo '<pre>'.$token.'</pre>';
$younited_service->api_shops();
$younited_service->api_personal_loans_offers(5000);
$younited_service->api_contract_initialize($objecttype, $objectid, $amount, 12);

//var_dump($object);