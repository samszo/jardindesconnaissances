<?php
//merci à  https://stackoverflow.com/questions/1623681/comparing-2-phpinfo-settings/1623691#1623691
function ini_flatten($config) {
	$flat = array();
	foreach ($config as $key => $info) {
		$flat[$key] = $info['local_value'];
	}
	return $flat;
}

function ini_diff($config1, $config2) {
	return array_diff_assoc(ini_flatten($config1), ini_flatten($config2));
}

$config1 = ini_get_all();

$export_script = 'http://localhost/jdc/services/exportIni.php';
$config2 = unserialize(file_get_contents($export_script));

$diff = ini_diff($config1, $config2);
?>
<pre><?php print_r($diff) ?></pre>