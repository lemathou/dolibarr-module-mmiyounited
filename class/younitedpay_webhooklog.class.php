<?php

// @todo Finir et utiliser pour faire plus propre !

/* // Webhook Data format
{
	"type": "payment.updated",
	"notificationId": "5ef9f8e6-2bc0-456b-82d7-f028be989b33",
	"data": {
	  "type": "payment",
	  "paymentId": "YPAY_e99376c0-4d41-4155-b773-5f61b68bef6d",
	  "status": "Executed",
	  "updatedAt": "2024-12-26T18:39:42.9927602"
	},
	"createdAt": "2024-12-26T18:39:47.3428149Z"
}
*/

class younitedpay_webhook_log extends CommonObject
{
	public $element = 'younitedpay_webhook_log';
	public $table_element = 'younitedpay_webhook_log';

	public $id;
	public $date_creation;
	public $entity;
	//public $ref;

	public $type;
	public $notificationId;

	public $fk_younitedpay;
	public $data_type;
	public $data_paymentId;
	public $data_status;
	public $data_updatedAt;

	public $fields = array();

	/**
	 * Constructor
	 *
	 * @param DoliDB    $db      Database handler
	 */
	function __construct($db)
	{
		global $conf;

		parent::__construct($db);

		$this->entity = $conf->entity;
		$this->date_creation = dol_now();
	}
}