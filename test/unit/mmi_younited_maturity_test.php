<?php
/**
 * Tests unitaires de mmi_younited_maturity::compute() — RM3581.
 * Sans dépendance (ni Dolibarr ni phpunit) : php test/unit/mmi_younited_maturity_test.php
 * Code de sortie 0 si tout passe.
 */

require_once __DIR__.'/../../class/mmi_younited_maturity.class.php';

$fail = 0;
$n = 0;
function check($label, $cond)
{
	global $fail, $n;
	$n++;
	if ($cond) {
		echo "ok   $label\n";
	} else {
		$fail++;
		echo "FAIL $label\n";
	}
}
function l($pa_ht, $qty, $total_ht)
{
	$o = new stdClass();
	$o->pa_ht = $pa_ht;
	$o->qty = $qty;
	$o->total_ht = $total_ht;
	return $o;
}

$D = '12,24,48';
$F = '60,84';

check('désactivé → défaut', mmi_younited_maturity::compute(false, $F, $D, 100, 1.2, [l(10, 1, 200)]) === $D);
check('liste libre vide → défaut', mmi_younited_maturity::compute(true, '', $D, 100, 1.2, [l(10, 1, 200)]) === $D);
check('montant sous le minimum → défaut', mmi_younited_maturity::compute(true, $F, $D, 100, 1.2, [l(10, 1, 50)]) === $D);
check('marge sous le minimum → défaut', mmi_younited_maturity::compute(true, $F, $D, 100, 1.2, [l(100, 1, 110)]) === $D);
check('achat nul → marge nulle → défaut', mmi_younited_maturity::compute(true, $F, $D, 100, 1.2, [l(0, 1, 200)]) === $D);
check('éligible → défaut + libre', mmi_younited_maturity::compute(true, $F, $D, 100, 1.2, [l(10, 1, 200)]) === "$D,$F");
check('plusieurs lignes, éligible', mmi_younited_maturity::compute(true, $F, $D, 100, 1.2, [l(10, 2, 100), l(20, 1, 150)]) === "$D,$F");
check('plusieurs lignes, marge insuffisante', mmi_younited_maturity::compute(true, $F, $D, 100, 1.2, [l(100, 1, 100), l(50, 1, 60)]) === $D);

echo "\n$n test(s), $fail échec(s)\n";
exit($fail ? 1 : 0);
