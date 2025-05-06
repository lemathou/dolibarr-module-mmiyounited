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

$younited_service->api_shops();
$ret = $younited_service->api_personal_loans_offers($objecttype, $objectid);
var_dump($ret);
//var_dump($object);