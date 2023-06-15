<?php
ini_set('memory_limit', '2048M');

require_once '../config.php';
require_once 'Monstro_parser.php';

header('Content-Type: application/json');

if (isset($_POST['group'])) {
	$profile_table = domain_count_parse($_POST['group']);
	echo json_encode($profile_table);
}