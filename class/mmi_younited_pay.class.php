<?php

dol_include_once('mmicommon/class/mmi_singleton.class.php');

class mmi_younited_pay extends MMI_Singleton_2_0
{
	const MOD_NAME = 'mmiyounited';

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
		'contract-initialize' => [
			'method' => 'POST',
			'url' => '/api/1.0/Contract',
			'content_type' => 'application/json',
			'params' => [
				// Beaucoup de paramètres
			]
		],
	];

	protected $website_url;
	protected $webhook_url;

	protected $sandbox_mode = false;
	protected $login_url;
	protected $username;
	protected $password;
	protected $token;
	protected $api_url;

	protected $ShopCode;
	protected $MaturityDefaultList = '6,12,24,48';

	protected $ShopEmail;
	protected $ShopReference;

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
	}

	public function api_token()
	{
		if (empty($_SESSION['younited_token']))
			$this->api_login();
		
		return $_SESSION['younited_token'];
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

		$response = $this->curl_request('POST', $this->login_url, http_build_query($post_data), ['Content-Type: application/x-www-form-urlencoded']);
		if (isset($response['access_token'])) {
			$token = $response['access_token'];
		}
		else {
			echo 'Error: ' . $response;
			return;
		}
		var_dump($token);

		$_SESSION['younited_token'] = $token;
		// @todo save in database ?
	}

	public function api_shops()
	{
		return $this->api_request('shops');
	}

	public function api_personal_loans_offers($amount, $params=[])
	{
		return $this->api_request('personal-loans-offers', ['Amount'=>$amount, 'ShopCode'=>$this->ShopCode, 'Maturity.list'=>$this->MaturityDefaultList]);
	}

	public function api_contract_initialize($objecttype, $objectid, $amount, $maturity)
	{
		global $conf;

		$object = mmi_payments::loadobject($objecttype, $objectid);
		//var_dump($object);
		$societe = $object->thirdparty;
		//$contact = $object->thirdparty->;

		$params = [
			"requestedMaturity" => $maturity ,
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

		$response = $this->curl_request($route_info['method'], $this->api_url.$route_info['url'], $data, $headers);
		var_dump($response);
		return $response;
	}

	public function curl_request($type, $url, $data=[], $headers=[])
	{
		$ch = curl_init();

		$headers = array_merge([], $headers);
		var_dump($type, $url, $data, $headers);

		if ($type =='POST') {
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_POST, 1);
			if (!empty($headers) && in_array('Content-Type: application/json', $headers)) {
				var_dump(json_encode($data));
				curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
			}
			else {
				curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
			}
			//curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		}
		else {
			curl_setopt($ch, CURLOPT_URL, $url.'?'.http_build_query($data));
			var_dump($url.'?'.http_build_query($data));
		}
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		
		// receive server response ...
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		
		$server_output = curl_exec ($ch);
		echo '<pre>'.htmlspecialchars($server_output).'</pre>';
		$response = json_decode($server_output, true);
		curl_close ($ch);

		return $response;
	}

	public function payment_link($objecttype, $objectid, $securekey, $amount)
	{
		return '/custom/mmiyounited/request.php?action=create&objecttype=' . $objecttype . '&objectid=' . $objectid . '&securekey=' . $securekey .'&amount=' . $amount;
	}

	public function payment_status()
	{

	}

	public function payment_create()
	{

	}

	public function payment_approve()
	{

	}

	public function payment_accept()
	{

	}

	public function payment_execute()
	{

	}

	public function payment_cancel()
	{

	}
}

mmi_younited_pay::__init();
