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
$ret = $younited_service->api_personal_loans_offers($objecttype, $objectid);
