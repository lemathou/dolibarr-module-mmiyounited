<?php
/**
 * Copyright (C) 2026       Mathieu Moulin          <mathieu@iprospective.fr>
 *
 * Test CLI (RM3307) : ordre des boutons de la page publique de paiement.
 *
 * public/payment/newpayment.php affiche les moyens de paiement via le hook
 * doAddButton, dans l'ordre de HookManager::initHooks() (clé "<priority>:<module>").
 * Younited Pay doit passer APRÈS E-Transactions (CB/Paypal, priority 60 depuis RM1811) ;
 * virement et chèque (mmipayments) sont renvoyés en fin de page par JS.
 *
 * Usage (php avec mysqli, ex. php8.3) :
 *   php8.3 test/hook_order_newpayment.php [/chemin/vers/htdocs/master.inc.php]
 * Sans argument : master.inc.php du Dolibarr qui contient ce module (son conf.php
 * doit exister ; il indique la base, donc les modules actifs).
 *
 * Code retour : 0 = OK, 1 = échec, 2 = test non applicable (module inactif).
 */

define('NOSESSION', '1');
define('NOREQUIREMENU', '1');
define('NOREQUIREHTML', '1');
define('NOREQUIREAJAX', '1');
define('NOLOGIN', '1');

$htdocs = dirname(__DIR__, 3);
$master = !empty($argv[1]) ? $argv[1] : $htdocs.'/master.inc.php';
require $master;

$modules = array('mbietransactions', 'mmipayments', 'mmiyounited');
foreach ($modules as $module) {
	if (!isModEnabled($module)) {
		echo "SKIP : module $module inactif sur cette instance\n";
		exit(2);
	}
}

require_once DOL_DOCUMENT_ROOT.'/core/class/hookmanager.class.php';
$hookmanager = new HookManager($db);
$hookmanager->initHooks(array('newpayment'));

$order = array();
foreach (array_keys($hookmanager->hooks['newpayment']) as $key) {
	list(, $module) = explode(':', $key, 2);
	if (in_array($module, $modules))
		$order[] = $module;
}
echo 'Ordre newpayment : '.implode(' > ', array_keys($hookmanager->hooks['newpayment']))."\n";

$ok = array_search('mbietransactions', $order) < array_search('mmiyounited', $order);
echo ($ok ? 'OK' : 'ECHEC')." : mmiyounited après mbietransactions (CB/Paypal avant Younited Pay)\n";
exit($ok ? 0 : 1);
