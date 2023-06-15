<?php
require_once 'PostGres.class.php';
require_once 'Monstro_parser.php';
//require_once '../config.php';


$db = new \SeinopSys\PostgresDb($db_name, $host, $user, $password);
$db -> getConnection();

function domain_count_parse($party) {
	global $db;
	
	$limit = 1500; // Можно настроить в соответствии с вашими потребностями
	$offset = 0;
	$continue = true;
	
	$profiles_list = [];
	
	while ($continue) {
		$cookies_list = $db->query("SELECT pid, cookies FROM public.profiles where party = '{$party}' LIMIT {$limit} OFFSET {$offset}");
		
		// Если больше нет данных, то останавливаем цикл
		if (count($cookies_list) == 0) {
			$continue = false;
		} else {
			foreach ($cookies_list as $value) {
				$profiles_list[$value['pid']] = cookies_check($value['cookies']);
			}
			
			$offset += $limit; // Увеличиваем смещение для следующей страницы
		}
	}

	
	uasort($profiles_list, function($a, $b) {
		return $b['count_domains'] - $a['count_domains'];
	});
	
	
	$counts = [
		'>200' => 0,
		'>150' => 0,
		'>100' => 0,
		'>70' => 0,
		'>50' => 0,
		'>20' => 0,
		'>10' => 0,
	];

	$profile_days = [];

	foreach ($profiles_list as $profile) {
		$count_domains = $profile['count_domains'];
		$days = $profile['profile_days'];
	
		if ($count_domains > 200) $counts['>200']++;
		if ($count_domains > 150) $counts['>150']++;
		if ($count_domains > 100) $counts['>100']++;
		if ($count_domains > 70) $counts['>70']++;
		if ($count_domains > 50) $counts['>50']++;
		if ($count_domains > 20) $counts['>20']++;
		if ($count_domains > 10) $counts['>10']++;
	
		if (!isset($profile_days[$days])) {
			$profile_days[$days] = 1;
		} else {
			$profile_days[$days]++;
		}
	}
	
	
	$group_data = array('counts' => $counts, 'profile_days' => $profile_days, 'profiles_list' => $profiles_list);
	
	return $group_data;
}




function list_party() {
	global $db;
		$db_list = $db->query("SELECT count(*) as count, party FROM public.profiles GROUP BY party order by count DESC");
		
		$list_party = [];
		foreach ($db_list as $value) {
				$list_party[$value['party']] = $value['count'];
		}
		
		
		return $list_party;
}



function cookies_check($cookie_base64) {
	global $db;

	$cookies = base64_decode($cookie_base64);
	$cookies = json_decode($cookies, true);
	
	$count_domains = 0;
	$metrika_date = 0;
	$domains = [];
	$metrika_dates = [];
	
	if ($cookies['cookies']) {
		foreach ($cookies['cookies'] as $value) {
			if ($value['name'] == '_ym_uid') {
					$metrika_dates[] = substr($value['value'], 0, 10);
					$count_domains++; //считаем колво уник доменов с метрикой
			}
		}
	}

		sort($metrika_dates, SORT_NUMERIC);
		$metrika_date = format_date($metrika_dates[0]);
	
	return array('count_domains' => $count_domains, 'create_date' => $metrika_date['create_date'], 'profile_days' => $metrika_date['profile_days']);
}


function format_date($timestamp) {
	$timestamp = (int)$timestamp;
	$profile_days = (time() - $timestamp) / 86400;
	$profile_days = round($profile_days, 0);
	$create_date = gmdate("Y-m-d", $timestamp);

	return array('profile_days' => $profile_days, 'create_date' => $create_date);;
}