<?php
/**
 * Copyright (C) 2025       Mathieu Moulin          <mathieu@iprospective.fr>
 */

dol_include_once('mmicommon/class/mmi_actions.class.php');
dol_include_once('mmiyounited/class/mmi_younited_pay.class.php');

class ActionsMMIYounited extends MMI_Actions_1_0
{
	const MOD_NAME = 'mmiyounited';

	protected $payment_service;

	public function __construct($db)
	{
		parent::__construct($db);

		// $this->name = 'ActionsMMIYounited';
		// $this->description = 'Actions for MMIYounited module';
		// $this->version = '1.0.0';
		// $this->family = 'custom';
		// $this->module_position = 100;

		$this->payment_service = mmi_younited_pay::_instance();
		//var_dump($this->payment_service); die();
	}

	// Payment page

	/**
	 * Check Object OK
	 */
	function doCheckStatus($parameters, &$object, &$action, $hookmanager)
	{
		$this->doValidatePayment($parameters, $object, $action, $hookmanager);
		$objecttype = get_class($object);

		if (in_array($objecttype, ['Propal'])) {
			// Vérif devis ok, pas relié commande, etc.
		}

		return 0;
	}

	/**
	 * Check Object OK
	 */
	function addOnlinePaymentMeans($parameters, &$object, &$action, $hookmanager)
	{
		$objecttype = get_class($object);

		if (in_array($objecttype, ['Propal', 'Commande', 'Facture'])) {
			$hookmanager->results['useonlinepayment'] = true;
		}

		return 0;
	}
	
	// Boutons moyens de paiement
	function doaddButton($parameters, &$object, &$action, $hookmanager)
	{
		global $db, $langs, $conf;

		// var_dump($object);
		// die();
		$time = time();

		$objecttype = get_class($object);
		$deja = mmi_payments::total_regle($objecttype, $object->id);
		//var_dump($deja);
		$reste = ($deja>0 ?max(0, round($object->total_ttc-$deja, 2)) :$object->total_ttc);
		//var_dump($object->fin_validite, $time, empty($object->fin_validite) || $object->fin_validite < $time);

		// Paiement normal complet
		if (true) {
			$amount = (!empty($parameters['amount']) ?$parameters['amount'] :$reste);
			//var_dump(get_class($object), $object->id, $amount, 1, true);
			// @todo securekey pas top
			$securekey = GETPOST('securekey', 'alpha');
			$link = $this->payment_service->payment_link($objecttype, $object->id, $securekey, round($amount, 2));
			//var_dump($link); die();

			$this->payment_service->api_shops();
			$ret = $this->payment_service->api_personal_loans_offers($objecttype, $object->id);
			
			print '<div class="button buttonpayment" id="div_dopayment_mmiyounited" style="pointer: cursor;">
			<input class="" type="submit" id="dopayment_mmiyounited" name="dopayment_mmiyounited" value="'.$langs->trans("MMIYounitedDoPayment").'">';
			print '<br />';
			print '<span class="buttonpaymentsmall">
			<img src="/custom/mmiyounited/img/younited-logo.png" alt="CB Visa Mastercard" class="img_cb" style="width: 50%;height: auto;" />
			</span>';
			foreach($ret as $offer) {
				echo '<p style="margin:0;" data-maturity="'.$offer['characteristics']['maturityInMonths'].'">'.$offer['characteristics']['maturityInMonths'].' mois : '.$offer['details']['monthlyInstallmentAmount'].'/mois</p>';
			}
			print '</div>';
		}

		// var_dump($parameters['amount']);
		// var_dump($object->array_options['options_acompte']);
		// var_dump($object->total_ttc);

		print '<script>
			$( document ).ready(function() {
				$("#div_dopayment_mmiyounited p").click(function(e){
					let maturity = $(this).data("maturity");
					let link = \''.$link.'&maturity=\'+maturity;
					document.location.href=link;
					$(this).css( \'cursor\', \'wait\' );
					e.stopPropagation();
					return false;
				});
			});
			</script>';

		return 0;
	}

	// Payment means
	function doValidatePayment($parameters, &$object, &$action, $hookmanager)
	{
		//var_dump($parameters); var_dump(get_class($object)); var_dump($action);
		$parameters['validpaymentmethod']['mmiyounited'] = true;

		return 0;
	}

	// This hook is used to show the embedded form to make payments with external payment modules (ie Payzen, ...)
	function doPayment($parameters, &$object, &$action, $hookmanager)
	{
		global $db, $conf, $mysoc, $user;
		//var_dump($mysoc); die();
		//echo $parameters['paymentmethod'];

		// A Conserver ?

		return 0;
	}
}

ActionsMMIYounited::__init();
