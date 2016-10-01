<?php

require_once __DIR__ . '/../src/Orange/Lang/Lang.php';

use \Orange\Lang\Lang;

Lang::addLangFilesDir(__DIR__ . '/lang');

$l = Lang::getInstance('en');
echo 'EN: <b>' . $l->get('example.good-morning') . '</b> (default)<br/>';
echo 'EN: <b>' . $l->get('example.good-evening', 'Good evening') . '</b> (use default parameter)<br/>';

$l = Lang::getInstance('es');
echo 'ES: <b>' . $l->get('example.good-morning') . '</b> (specific)<br/>';

$l = Lang::getInstance('ge');
echo 'GE: <b>' . $l->get('example.good-morning') . '</b> (not found, uses default)<br/>';

echo 'EN: <b>' . Lang::t('example.good-morning') . '</b> (use first defined instance)<br/>';