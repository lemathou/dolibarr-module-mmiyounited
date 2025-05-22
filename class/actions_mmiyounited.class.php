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
		if ( !getDolGlobalInt('MMI_YOUNITED_ENABLE'))
			return 0;
		if ($reste <= 0)
			return 0;
		if (!empty($object->fin_validite) && $object->fin_validite < $time)
			return 0;
		if (!$this->payment_service->payment_enable($object, $reste))
			return 0;
	
		$amount = (!empty($parameters['amount']) ?$parameters['amount'] :$reste);
		//var_dump(get_class($object), $object->id, $amount, 1, true);
		// @todo securekey pas top
		$securekey = GETPOST('securekey', 'alpha');
		$link = $this->payment_service->payment_link($objecttype, $object->id, $securekey, round($amount, 2));
		//var_dump($link); die();

		$this->payment_service->api_shops();
		$ret = $this->payment_service->api_personal_loans_offers($objecttype, $object->id);
		
		print '<div class="button buttonpayment" id="div_dopayment_mmiyounited">
		<input class="" type="submit" id="dopayment_mmiyounited" name="dopayment_mmiyounited" value="'.$langs->trans("MMIYounitedDoPayment").'">';
		print '<p style="margin-bottom: 0;">Achetez maintenant et payez à votre rythme</p>';
		print '<span class="buttonpaymentsmall">
		<img src="/custom/mmiyounited/img/younited-logo.png" alt="Younited Pay" class="img_cb" style="width: 50%;height: auto;" />
		</span>';
		$offers_show = [];
		foreach($ret as $offer) {
			$offers_show[$offer['characteristics']['maturityInMonths']] = $offer;
		}
		asort($offers_show);
		//var_dump($offers_show);
		echo '<p class="offer" style="margin:0;" data-maturity="12">De '.$offers_show[12]['characteristics']['maturityInMonths'].' mois pour <b>'.$offers_show[12]['details']['monthlyInstallmentAmount'].'/mois</b></p>';
		echo '<p class="offer" style="margin:0;" data-maturity="84">à '.$offers_show[84]['characteristics']['maturityInMonths'].' mois pour <b>'.$offers_show[84]['details']['monthlyInstallmentAmount'].'/mois</b></p>';
		echo '<div style="margin: 5px 20px;border: 1px purple solid;padding: 5px;background-color: #FAECFF;">';
		echo '<p>Choisissez la durée de remboursement :</p>';
		echo '<p><select class="offers" name="maturity"><option value="">Durée :</option>';
		foreach($offers_show as $offer) {
			$amount = $offer['details']['monthlyInstallmentAmount'];
			$maturity = $offer['characteristics']['maturityInMonths'];
			echo '<option value="'.$maturity.'">'.$maturity.' mois pour '.$amount.'&euro;/mois</option>';
		}
		echo '</select></p>';
		echo '<div class="younitedpay_details">';
		echo '<p>Montant à financer : <span class="enhance" id="younited_pay_mtcred"></span><br />';
		echo 'Durée : <span class="enhance" id="younited_pay_duree"></span><br />';
		echo 'Total mois : <span class="enhance" id="younited_pay_mtmois"></span></p>';
		echo '<p>Montant du crédit : <span id="younited_pay_mt"></span><br />';
		echo '+ intérêt du crédit : <span id="younited_pay_int"></span><br />';
		echo '= montant total dû : <span id="younited_pay_du"></span></p>';
		echo '<p>TAEG fixe : <span id="younited_pay_taeg"></span><br />';
		echo 'Taux débiteur fixe : <span id="younited_pay_tx"></span></p>';
		echo '</div>';
		echo '<div id="div_dopayment_mmiyounited_real" class="button buttonpayment disabled"><p>Connectez simplement et de manière sécurisée votre compte bancaire</p></div>';
		echo '</div>';
		echo '<p style="font-size: 0.8em;">Un crédit vous engage et doit être remboursé. Vérifiez vos capacités de remboursement avant de vous engager.</p>';
		print '</div>';

		// var_dump($parameters['amount']);
		// var_dump($object->array_options['options_acompte']);
		// var_dump($object->total_ttc);

		print '<style type="text/css">
		#div_dopayment_mmiyounited.buttonpayment {
			cursor: auto;
		}
		#div_dopayment_mmiyounited p.offer, #div_dopayment_mmiyounited 	option.offer {
			cursor: pointer;
		}
		.younitedpay_details {
			display: none;
			padding: 0 5px;
		}
		.younitedpay_details p {
			text-align: left;
			margin: 10px 0 0 0;
		}
		.younitedpay_details span {
			float: right;
		}
		.younitedpay_details span.enhance {
			font-weight: bold;
		}
		</style>';
		echo '<script>';
		echo 'let offers={};';
		foreach($offers_show as $offer) {
			$maturity = $offer['characteristics']['maturityInMonths'];
			echo 'offers["'.$maturity.'"] = '.json_encode($offer).';';
		}
		echo '$( document ).ready(function() {
				var younitedpay_link = \''.$link.'\';
				$("#div_dopayment_mmiyounited p.offer").click(function(e){
					let maturity = $(this).data("maturity");
					let link = younitedpay_link+\'&maturity=\'+maturity;
					document.location.href=link;
					$(this).css( \'cursor\', \'wait\' );
					e.stopPropagation();
					return false;
				});
				$("#div_dopayment_mmiyounited select.offers").change(function(e){
					let maturity = $("option:selected", this).val();
					let link = younitedpay_link+\'&maturity=\'+maturity;
					let infos = offers[maturity];
					//alert(infos);
					$(".younitedpay_details #younited_pay_mtcred").text(infos.requestedAmount+" €");
					$(".younitedpay_details #younited_pay_duree").text(infos.characteristics.maturityInMonths+" mois");
					$(".younitedpay_details #younited_pay_mtmois").text(infos.details.monthlyInstallmentAmount+" €/mois");
					$(".younitedpay_details #younited_pay_mt").text(infos.characteristics.amount+" €");
					$(".younitedpay_details #younited_pay_int").text(infos.details.interestsAmount+" €");
					$(".younitedpay_details #younited_pay_du").text(infos.details.totalDueAmount+" €");
					$(".younitedpay_details #younited_pay_taeg").text(Math.round(infos.details.annualPercentageRate*10000)/100+" %");
					$(".younitedpay_details #younited_pay_tx").text(Math.round(infos.characteristics.interestRate*10000)/100+" %");
					$(".younitedpay_details").show();
					$("#div_dopayment_mmiyounited_real").removeClass("disabled");
					$("#div_dopayment_mmiyounited_real").click(function(e){
						$(this).css( \'cursor\', \'wait\' );
						document.location.href=link;
						e.stopPropagation();
						return false;
					});
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
