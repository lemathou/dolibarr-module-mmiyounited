<?php
/* Copyright (C) 2026 Mathieu Moulin / iProspective <contact@iprospective.fr>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 */

require_once 'env.inc.php';
require_once 'main_load.inc.php';

$langs->loadLangs(array('mmiyounited@mmiyounited'));
dol_include_once('/mmicommon/lib/mmi_home.lib.php');

$title = $langs->trans('MMIYounitedArea');
$tabs = array(
	'home' => array('url' => dol_buildpath('/mmiyounited/mmiyounitedindex.php', 1), 'label' => $langs->trans('MMIYounitedHome')),
);
MMIHomeHeader($title, $tabs, 'home');
print '<div class="opacitymedium">'.$langs->trans('MMIYounitedHomeIntro').'</div>';
$links = array(
	$langs->trans('Settings') => dol_buildpath('/mmiyounited/admin/setup.php', 1),
);
print MMIHomeBlock($langs->trans('MMIYounitedHomeLinks'), MMIHomeLinks($links));
MMIHomeFooter();
