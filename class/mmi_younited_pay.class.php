<?php

// Soc
require_once DOL_DOCUMENT_ROOT.'/societe/class/societe.class.php';
require_once DOL_DOCUMENT_ROOT.'/contact/class/contact.class.php';
// Documents
require_once DOL_DOCUMENT_ROOT.'/compta/facture/class/facture.class.php';
require_once DOL_DOCUMENT_ROOT.'/commande/class/commande.class.php';
require_once DOL_DOCUMENT_ROOT.'/comm/propal/class/propal.class.php';

dol_include_once('mmicommon/class/mmi_singleton.class.php');
dol_include_once('mmipayments/class/mmi_payments.class.php');

class mmi_younited_pay extends MMI_Singleton_2_0
{
	const MOD_NAME = 'mmiyounited';

	const CURL_DEBUG = false;
	const API_DEBUG = false;

	const ROUTES = [
		'shops' => [
			'method' => 'GET',
			'url' => '/pay/shops',
		],
		'personal-loans-offers' => [
			'method' => 'GET',
			'url' => '/pay/personal-loans/offers',
			'query' => [
				'Amount' => '$Amount',
				'ShopCode' => '$ShopCode',
				'Maturity.list' => '12,24,48'
			]
		],
		'personal-loan-create' => [
			'method' => 'POST',
			'url' => '/pay/payments/personal-loan',
			'content_type' => 'application/json',
			'params' => [
				// Beaucoup de paramètres
			]
		],
		'payment-info' => [
			'method' => 'GET',
			'url' => '/pay/payments/$id',
			'content_type' => 'application/json',
			'url_params' => [
				'id',
			],
			'params' => [
				// Beaucoup de paramètres
			]
		],
		'payment-status' => [
			'method' => 'GET',
			'url' => '/pay/payments/$id/status',
			'content_type' => 'application/json',
			'url_params' => [
				'id',
			],
			'params' => [
				// Beaucoup de paramètres
			]
		],
		// old ?
		'contract-initialize' => [
			'method' => 'POST',
			'url' => '/api/1.0/Contract',
			'content_type' => 'application/json',
			'params' => [
				// Beaucoup de paramètres
			]
		],
	];

	public $error;
	public $errors = [];

	protected $payment_mode;
	protected $account_id;

	protected $website_url;
	protected $webhook_url;

	protected $sandbox_mode = false;
	protected $login_url;
	protected $username;
	protected $password;
	protected $token;
	protected $api_url;

	protected $ShopCode;
	protected $MaturityDefaultList = '6,12,24,36,48,60,72,84,96';

	protected $ShopEmail;
	protected $ShopReference;
	protected $MerchantReference;

	protected $api_version = '2025-01-01';

	protected function __construct($db)
	{
		global $conf;
		parent::__construct($db);

		$this->website_url = 'https://'.$_SERVER['SERVER_NAME'];
		$this->webhook_url = $this->website_url.'/custom/mmiyounited/webhook.php';
		$this->sandbox_mode = !empty($conf->global->MMI_YOUNITED_API_MODE_SANDBOX);
		$this->login_url = $this->sandbox_mode ?$conf->global->MMI_YOUNITED_API_SANDBOX_TOKEN_URL :$conf->global->MMI_YOUNITED_API_PRODUCTION_TOKEN_URL;
		$this->username = $this->sandbox_mode ?$conf->global->MMI_YOUNITED_API_SANDBOX_USERNAME :$conf->global->MMI_YOUNITED_API_PRODUCTION_USERNAME;
		$this->password = $this->sandbox_mode ?$conf->global->MMI_YOUNITED_API_SANDBOX_PASSWORD :$conf->global->MMI_YOUNITED_API_PRODUCTION_PASSWORD;
		$this->api_url = $this->sandbox_mode ?$conf->global->MMI_YOUNITED_API_SANDBOX_URL :$conf->global->MMI_YOUNITED_API_PRODUCTION_URL;
		$this->ShopCode = $this->sandbox_mode ?$conf->global->MMI_YOUNITED_API_SANDBOX_SHOPCODE :$conf->global->MMI_YOUNITED_API_PRODUCTION_SHOPCODE;
		$this->ShopEmail = $conf->global->MAIN_INFO_SOCIETE_EMAIL;
		$this->ShopReference = $conf->global->MAIN_INFO_SOCIETE_NAME;
		$this->MerchantReference = $this->sandbox_mode ?$conf->global->MMI_YOUNITED_API_SANDBOX_MERCHANT_REF :$conf->global->MMI_YOUNITED_API_PRODUCTION_MERCHANT_REF;
		$this->payment_mode = $conf->global->MMI_YOUNITED_PAYMENT_MODE;
		$this->account_id = $conf->global->MMI_YOUNITED_ACCOUNT_ID;
	}
	
	/* API LOGIN */

	public function api_token()
	{
		// @todo check if token is expired
		if (!empty($_SESSION['younited_token'.($this->sandbox_mode ?'_sandbox' :'')]) && !empty($_SESSION['younited_token_expires'.($this->sandbox_mode ?'_sandbox' :'')]) && $_SESSION['younited_token_expires'.($this->sandbox_mode ?'_sandbox' :'')] > time()) {
			return $_SESSION['younited_token'.($this->sandbox_mode ?'_sandbox' :'')];
		}

		return $this->api_login();
	}

	public function api_payment_id()
	{
		if (empty($_SESSION['younited_payment_id']))
			return;
		
		return $_SESSION['younited_payment_id'];
	}

	public function api_login()
	{
		$post_data = array(
			'grant_type' => 'client_credentials',
			'scope' => 'api://younited-pay/.default',
			'client_id' => $this->username,
			'client_secret' => $this->password
		);
		//var_dump($login_url, $username, $password, $post_data); die();

		$response = $this->curl_request('POST', $this->login_url, $post_data, ['Content-Type: application/x-www-form-urlencoded']);
		if (! isset($response['access_token'])) {
			echo 'Error: ' . $response;
			return;
		}

		$_SESSION['younited_token'.($this->sandbox_mode ?'_sandbox' :'')] = $response['access_token'];
		$_SESSION['younited_token_expires'.($this->sandbox_mode ?'_sandbox' :'')] = time() + $response['expires_in'];

		return $response['access_token'];
		// @todo save in database ?
	}

	public function api_shops()
	{
		return $this->api_request('shops');
	}
	
	/* API PAYMENT */

	public function api_personal_loans_offers($objecttype, $objectid, $params=[])
	{
		$object = mmi_payments::loadobject($objecttype, $objectid);
		$amount = round($object->total_ttc, 2);

		return $this->api_request('personal-loans-offers', ['Amount'=>$amount, 'ShopCode'=>$this->ShopCode, 'Maturity.list'=>$this->MaturityDefaultList]);
	}

	public function api_personal_loan_create($objecttype, $objectid, $amount=NULL, $maturity=NULL)
	{
		$object = mmi_payments::loadobject($objecttype, $objectid);
		//var_dump($object);
		$societe = $object->thirdparty;
		//$contact = $object->thirdparty->;
		$contacts = $object->liste_contact(-1, 'external');
		$contacts_ok = false;
		$customer = new Contact($this->db);
		foreach($contacts as $contact) {
			$contacts_ok = true;
			$customer->fetch($contact['id']);
			//var_dump($contact, $customer); die();
			break;
		}
		// Default params
		if ($amount===NULL)
			$amount = $object->total_ttc;
		$amount = round($amount, 2);
		if ($maturity===NULL)
			$maturity = 24;

		$params = [
			"loanRequest" => [
				"requestedAmount" => $amount,
				"requestedMaturityInMonths" => $maturity,
			],
			"basketDescription" => [
				"items" => [
					[
						"name" => "Matériel pour piscine",
						"quantity" => 1,
						"unitPrice" => $amount,
					],
				]
			],
			"merchantContext" => [
				"shopCode" => $this->ShopCode,
				"merchantReference" => $this->MerchantReference,
			],
			"technicalInformation" => [
				"webhookNotificationUrl" => $this->webhook_url,
				"apiVersion" => "2024-01-01", // $this->api_version,
			],
			"customerInformation" => [
				"firstName" => !empty($customer) ?$customer->firstname :$societe->nom,
				"lastName" => !empty($customer) ?$customer->lastname :$societe->nom,
				"emailAddress" => !empty($customer) ?$customer->email :$societe->email,
				"mobilePhoneNumber" => $this->tel_intl(!empty($customer) ?$customer->phone_mobile :$societe->phone),
				//"birthDate" => "1990-02-20T00:00:00",
				"postalAddress" => [
					"AddressLine1" => !empty($customer) ?$customer->address :$societe->address,
					//"AddressLine2" => '',
					'city' => !empty($customer) ?$customer->town :$societe->town,
					'postalCode' => !empty($customer) ?$customer->zip :$societe->zip,
					'countryCode' => !empty($customer) ?$customer->country_code :$societe->country_code,
				],
			],
			"riskInsights" => [
				//"customerSegmentationCode" => 'standard', // or premium
				"customerIpAddress" => $_SERVER['REMOTE_ADDR'],
			],
			"customExperience" => [
				"allowMaturityChoice" => true,
				"customerRedirectUrl" => $this->website_url."/custom/mmiyounited/return.php",
			]
		];
		if (static::API_DEBUG)
			var_dump($params);

		$response = $this->api_request('personal-loan-create', $params);
		if (static::API_DEBUG)
			var_dump($response);
		if (! isset($response['paymentId'])) {
			echo 'Error: ' . $response;
			return;
		}

		$_SESSION['younited_payment_id'] = $response['paymentId'];
		$sql = 'INSERT INTO '.MAIN_DB_PREFIX.'younitedpay (objecttype, fk_object, amount, maturity, payment_id)
			VALUES ("'.strtolower($objecttype).'", '.$objectid.', '.$amount.', '.$maturity.', "'.$response['paymentId'].'")';
		$q = $this->db->query($sql);
		if (static::API_DEBUG)
			var_dump($q, $this->db);
		
		return $response;
	}

	public function api_payment_info($id)
	{
		return $this->api_request('payment-info', ['id'=>$id]);
	}

	public function api_payment_status($id)
	{
		return $this->api_request('payment-status', ['id'=>$id]);
	}

	public function api_contract_initialize($objecttype, $objectid, $amount, $maturity)
	{
		global $conf;

		$object = mmi_payments::loadobject($objecttype, $objectid);
		$societe = $object->thirdparty;
		//$contact = $object->thirdparty->;

		$params = [
			"requestedMaturity" => $maturity,
			"segmentation" => "STANDARD",
			// @todo use socpeople if possible, not societe
			"personalInformation" => [
				"firstName" => "Dale",
				"lastName" => "Stephens",
				"genderCode" => "MALE",
				"emailAddress" => $societe->email,
				"cellPhoneNumber" => $societe->phone,
				"birthDate" => "1990-02-20T00:00:00",
				"address" => [
					"streetNumber" => "21",
					"streetName" => "rue de chateaudun",
					"additionalAddress" => "not necessary",
					"city" => "Paris",
					"postalCode" => "75009",
					"countryCode" => "FR",
				],
			],
			"basket" => [
				"basketAmount" => $amount,
				// @todo loop products in order
				"items" => [
					[
						"itemName" => "Matériel pour piscine",
						"quantity" => 1,
						"unitPrice" => $amount,
					]
				],
			],
			"merchantUrls" => [
				"onGrantedWebhookUrl" => $this->webhook_url."?action=granted",
				"onCanceledWebhookUrl" => $this->webhook_url."?action=canceled",
				"onWithdrawnWebhookUrl" => $this->webhook_url."?action=withdrawn",
				"onActivateWebhookUrl" => $this->webhook_url."?action=activate",
				"onApplicationSucceededRedirectUrl" => $this->website_url."/custom/mmiyounited/success.php",
				"onApplicationFailedRedirectUrl" => $this->website_url."/custom/mmiyounited/failure.php",
			],
			"merchantOrderContext" => [
				"channel" => "ONLINE",
				"shopCode" => $this->ShopCode,
				"agentEmailAddress" => $this->ShopEmail,
				"merchantReference" => $this->ShopReference,
			],
		];
		
		return $this->api_request('contract-initialize', $params);
	}

	public function api_request($route, $data=[], $headers=[])
	{
		global $conf;
		if (empty(static::ROUTES[$route])) {
			echo 'Error: Route not found';
			return;
		}
		$route_info = static::ROUTES[$route];
		$token = $this->api_token();
		$headers = array_merge(['Authorization: Bearer ' . $token, 'X-Api-Version: '.$this->api_version], $headers);
		if (!empty($route_info['content_type'])) {
			$headers[] = 'Content-Type: '.$route_info['content_type'];
			if ($route_info['content_type']=='application/json') {
				$headers[] = 'accept: '.$route_info['content_type'];
			}
		}

		$url = $this->api_url.$route_info['url'];
		if (!empty($route_info['url_params'])) {
			foreach ($route_info['url_params'] as $key) {
				if (isset($data[$key])) {
					$url = str_replace('$'.$key, $data[$key], $url);
					unset($data[$key]);
				}
			}
		}

		$response = $this->curl_request($route_info['method'], $url, $data, $headers);
		if (static::API_DEBUG)
			var_dump($response);
		return $response;
	}

	/* Payment info */

	public function payment_id_info($id)
	{
		$sql = 'SELECT * FROM '.MAIN_DB_PREFIX.'younitedpay WHERE payment_id="'.$id.'"';
		$q = $this->db->query($sql);
		if (static::API_DEBUG)
			var_dump($q, $this->db);
		
		if ($q) {
			return $this->db->fetch_object($q);
		}
		$this->errors[] = 'Error: Payment not found';
		return null;
	}

	/* CURL */

	public function curl_request($type, $url, $data=[], $headers=[])
	{
		$ch = curl_init();

		$headers = array_merge([], $headers);
		if (static::CURL_DEBUG)
			var_dump($type, $url, $data, $headers);

		if ($type =='POST') {
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_POST, 1);
			if (!empty($headers) && in_array('Content-Type: application/json', $headers)) {
				//var_dump($headers, json_encode($data)); die();
				curl_setopt($ch, CURLOPT_POSTFIELDS, is_array($data) ?json_encode($data) :$data);
			}
			else {
				curl_setopt($ch, CURLOPT_POSTFIELDS, is_array($data) ?http_build_query($data) :$data);
			}
			//curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
			//curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		}
		else {
			curl_setopt($ch, CURLOPT_URL, $url.'?'.(is_array($data) ?http_build_query($data) :$data));
			if (static::CURL_DEBUG)
				var_dump($url.'?'.http_build_query($data));
		}
		
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		// receive server response ...
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		
		$server_output = curl_exec ($ch);
		if (static::CURL_DEBUG)
			echo '<pre>'.htmlspecialchars($server_output).'</pre>';
		$response = json_decode($server_output, true);
		if (static::CURL_DEBUG)
			var_dump($response);
		curl_close ($ch);

		return $response;
	}

	public function payment_link($objecttype, $objectid, $securekey, $amount)
	{
		return '/custom/mmiyounited/request.php?action=create&objecttype=' . $objecttype . '&objectid=' . $objectid . '&securekey=' . $securekey .'&amount=' . $amount;
	}

	/* email */

	public function notification_email($object, $subject, $message)
	{
		global $conf, $mysoc;

		$user = new User($this->db);
		$user->fetch(1); // Admin @todo créer user spécifique pour trucs auto ?

		$email_to = [];
		$contacts = $object->liste_contact(-1, 'internal');
		$contacts_ok = false;
		foreach($contacts as $contact) {
			$contacts_ok = true;
			if (!empty($contact->email) && !in_array($contact->email, $email_to))
				$email_to[] = $contact->email;
		}
		$client = $object->thirdparty;
		$contacts = $client->getSalesRepresentatives($user);
		foreach($contacts as $contact) {
			$contacts_ok = true;
			if (!empty($contact['email']) && !in_array($contact['email'], $email_to))
				$email_to[] = $contact['email'];
		}
		
		if (empty($email_to))
			$email_to = (!empty($conf->global->MMI_YOUNITED_NOTIFICATION_EMAIL_TO) ?$conf->global->MMI_YOUNITED_NOTIFICATION_EMAIL_TO :$mysoc->email);
		
		$email_from = (!empty($conf->global->MMI_YOUNITED_NOTIFICATION_EMAIL_FROM) ?$conf->global->MMI_YOUNITED_NOTIFICATION_EMAIL_FROM :$mysoc->email);
		
		return mail(implode(',', $email_to),
			$subject,
			$message,
			$email_headers = 'From: '.$email_from."\r\n"
				.'Content-Type: text/plain; charset=UTF-8'."\r\n");
	}

	/* Payment */

	public function payment_add($payment, $data)
	{
		$trans = $payment->paymentId;

		$infos = [
			'date' => $data['updatedAt'],
			'amount' => $payment->amount,
			'mode' => $this->payment_mode, // paymentId
			'num' => $trans,
			'note' => 'Younited Pay ID '.$trans.' pour '.$payment->objecttype.' #'.$payment->fk_object,
			'accountid' => $this->account_id, // account
			//'chqemetteur' => '',
			//'chqbank' => '',
			'module_oid'=>$payment->rowid,
		];
		
		return mmi_payments::add($payment->objecttype, $payment->fk_object, $infos);
	}

	/* WEBHOOKS */

	public function webhook_update($id, $input)
	{
		global $user;

		$payment = $this->payment_id_info($id);
		if (empty($payment)) {
			$this->errors[] = 'Error: Webhook payment ID not found';
			return;
		}
		$objecttype = $payment->objecttype;
		$objectid = $payment->fk_object;
		$object = mmi_payments::loadobject($objecttype, $objectid);
		$object_url = mmi_payments::object_url($objecttype, $objectid);
		$client = $object->thirdparty;

		// LOG
		$sql = 'INSERT INTO '.MAIN_DB_PREFIX.'younitedpay_webhook_log
			(`datec`, `payment_id`, `data`)
			VALUES
			(NOW(), "'.$id.'", "'.addslashes(json_encode($input)).'")';
		$q = $this->db->query($sql);

		$data = $input['data'];
		$data = $input['data'];
		$status = $data['status'];
		//var_dump($status);
		$updatedAt = str_replace('T', ' ', substr($data['updatedAt'], 0, 19));

		if (true || $status != $payment->status) {
			$sql = 'UPDATE '.MAIN_DB_PREFIX.'younitedpay SET payment_status="'.$status.'", payment_updatedat="'.$updatedAt.'" WHERE payment_id="'.$id.'"';
			$q = $this->db->query($sql);
			if (static::API_DEBUG)
				var_dump($q, $this->db);

			// Selon statut
			if ($status == 'initiualized') {
				// 
			}
			elseif ($status == 'Accepted') {
				// email accepted ?
				// @todo attention paiement en doublon !!
				$r = $this->payment_add($payment, ['updatedAt'=>$data['updatedAt']]);
				// Email message
				$email_info = 'Le paiement a été accepté par Younited Pay.'."\r\n"
				.'Payment Id: '.$id."\r\n"
				.'Réf: '.$object->ref."\r\n"
				.'Mt: '.$payment->amount."\r\n"
				."\r\n"
				.'Client Réf: '.$client->code_client."\r\n"
				.'Client: '.$client->name."\r\n"
				."\r\n"
				."Rendez-vous dans le Backoffice :\r\n- ".$object_url."\r\n";
				// Send email
				$this->notification_email($object,
						$objecttype.' '.$object->ref.' : Paiement Younited Pay',
						$email_info);
			
				// Modification statut
				if($objecttype == 'Propal') {
					//var_dump($user, $object::STATUS_SIGNED);
					if (! in_array($object->status, [2, 4])) // $object::STATUS_SIGNED OR $object::STATUS_BILLED
						$r = $object->closeProposal($user, $object::STATUS_SIGNED, 'Suite paiement Younited Pay');
					//var_dump($r);
				}
				//var_dump($r);
			}
			elseif ($status == 'Executed') {
				// email executed ??
			}
			elseif ($status == 'Cancelled') {
				// email cancel
			}
		}
	}

	public function webhook_approve($payment)
	{

	}

	public function webhook_accept($payment)
	{

	}

	public function webhook_cancel($payment)
	{

	}

	public function tel_intl($tel)
	{
		$tel = trim($tel);
		if (substr($tel, 0, 2) == '00') {
			$tel = '+'.substr($tel, 2);
		}
		elseif(substr($tel, 0, 1) == '0') {
			$tel = '+33'.substr($tel, 1);
		}
		elseif(substr($tel, 0, 1) != '+') {
			$tel = '';
		}
		$tel = preg_replace('/[^0-9]/', '', $tel);
		return '+'.$tel;
	}
}

mmi_younited_pay::__init();
