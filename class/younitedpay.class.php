<?php

// @todo Finir et utiliser pour faire plus propre !

class younitedpay extends CommonObject
{
	public $element = 'younitedpay';
	public $table_element = 'younitedpay';

	public $id;
	public $date_creation;
	public $date_modification;
	public $entity;
	public $ref;

	public $amount;
	public $maturity;
	public $status;

	public $objecttype;
	public $objectid;

	public $paymentId;
	//public $paymentLink;
	//public $paymentStatus;

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
		$this->date_modification = dol_now();
	}
}