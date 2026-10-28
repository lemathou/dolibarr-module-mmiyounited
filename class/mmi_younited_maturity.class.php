<?php
/* Copyright (C) 2026 MMI Mathieu Moulin <contact@iprospective.fr>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 */

/**
 * \file    mmiyounited/class/mmi_younited_maturity.class.php
 * \brief   Calcul pur de la liste des échéances proposées (sans dépendance Dolibarr).
 */

class mmi_younited_maturity
{
	/**
	 * Calcule la liste d'échéances à proposer.
	 *
	 * @param bool   $enabled       Offre "échéances libres" activée
	 * @param string $freeList      Échéances libres (ex. "60,84")
	 * @param string $defaultList   Échéances par défaut (ex. "12,24,48")
	 * @param float  $minAmount     Montant minimum de vente HT
	 * @param float  $minCoeff      Coefficient de marge minimum
	 * @param array  $lines         Lignes (objets avec pa_ht, qty, total_ht)
	 * @return string
	 */
	public static function compute($enabled, $freeList, $defaultList, $minAmount, $minCoeff, $lines)
	{
		if (!$enabled || empty($freeList)) {
			return $defaultList;
		}

		$buy_ht = 0;
		$sell_ht = 0;
		foreach ($lines as $line) {
			$buy_ht += (float) $line->pa_ht * (float) $line->qty;
			$sell_ht += (float) $line->total_ht;
		}

		$margin_coeff = $buy_ht > 0 ? ($sell_ht / $buy_ht) : null;

		if ($sell_ht < $minAmount || $margin_coeff < $minCoeff) {
			return $defaultList;
		}

		return $defaultList.','.$freeList;
	}
}
