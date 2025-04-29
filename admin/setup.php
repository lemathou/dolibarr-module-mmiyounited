<?php
/* Copyright (C) 2004-2017 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2024 MOULIN Mathieu <mathieu@iprospective.fr>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

/**
 * \file    mmiyounited/admin/setup.php
 * \ingroup mmiyounited
 * \brief   MMIYounited setup page.
 */

// Load Dolibarr environment
require_once '../env.inc.php';
require_once '../main_load.inc.php';

// Parameters
$arrayofparameters = array(
	'MMI_YOUNITED_API_MODE_SANDBOX'=>array('type'=>'yesno','enabled'=>1),
	'MMI_YOUNITED_DEBUG'=>array('type'=>'yesno','enabled'=>1),

	'MMI_YOUNITED_BANQ'=>array('type'=>'separator', 'enabled'=>1),
	'MMI_YOUNITED_PAYMENT_MODE'=>array('type'=>'int','enabled'=>1),
	'MMI_YOUNITED_ACCOUNT_ID'=>array('type'=>'int','enabled'=>1),
	
	'MMI_YOUNITED_NOTIFICATION'=>array('type'=>'separator', 'enabled'=>1),
	'MMI_YOUNITED_NOTIFICATION_EMAIL_FROM'=>array('type'=>'string','enabled'=>1),
	'MMI_YOUNITED_NOTIFICATION_EMAIL_TO'=>array('type'=>'string','enabled'=>1),
	
	'MMI_YOUNITED_API_PRODUCTION'=>array('type'=>'separator', 'enabled'=>1),
	'MMI_YOUNITED_API_PRODUCTION_TOKEN_URL'=>array('type'=>'string','enabled'=>1),
	'MMI_YOUNITED_API_PRODUCTION_URL'=>array('type'=>'string','enabled'=>1),
	'MMI_YOUNITED_API_PRODUCTION_USERNAME'=>array('type'=>'string','enabled'=>1),
	'MMI_YOUNITED_API_PRODUCTION_PASSWORD'=>array('type'=>'securekey','enabled'=>1),
	'MMI_YOUNITED_API_PRODUCTION_WEBHOOK_PASSWORD'=>array('type'=>'securekey','enabled'=>1),
	'MMI_YOUNITED_API_PRODUCTION_SHOPCODE'=>array('type'=>'string','enabled'=>1),
	'MMI_YOUNITED_API_PRODUCTION_MERCHANT_REF'=>array('type'=>'string','enabled'=>1),
	//'MMI_YOUNITED_API_PRODUCTION_TOKEN'=>array('type'=>'securekey', 'enabled'=>1),

	'MMI_YOUNITED_API_SANDBOX'=>array('type'=>'separator', 'enabled'=>1),
	'MMI_YOUNITED_API_SANDBOX_TOKEN_URL'=>array('type'=>'string','enabled'=>1),
	'MMI_YOUNITED_API_SANDBOX_URL'=>array('type'=>'string','enabled'=>1),
	'MMI_YOUNITED_API_SANDBOX_USERNAME'=>array('type'=>'string','enabled'=>1),
	'MMI_YOUNITED_API_SANDBOX_PASSWORD'=>array('type'=>'securekey','enabled'=>1),
	'MMI_YOUNITED_API_SANDBOX_WEBHOOK_PASSWORD'=>array('type'=>'securekey','enabled'=>1),
	'MMI_YOUNITED_API_SANDBOX_SHOPCODE'=>array('type'=>'string','enabled'=>1),
	'MMI_YOUNITED_API_SANDBOX_MERCHANT_REF'=>array('type'=>'string','enabled'=>1),
	//'MMI_YOUNITED_API_SANDBOX_TOKEN'=>array('type'=>'securekey', 'enabled'=>1),
);

require_once('../../mmicommon/admin/mmisetup_1.inc.php');