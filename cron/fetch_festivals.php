<?php
// cron/fetch_festivals.php
require_once __DIR__ . '/../config/db.php';

/**
 * 한국관광공사_국문 관광정보 서비스 축제정보조회 API
 * 매일 아침 9시에 실행되어 축제 정보를 동기화합니다.
 */

// 1. API 파라미터 설정
$serviceKey = FESTIVAL_API_KEY;
$pageNo = 1;
$numOfRows = 20; // 한 번에 가져올 데이터 양 (필요에 따라 조절)
$mobileApp = "FestivalTracker";
$mobileOS = "ETC";
$listYN = "Y";
$arrange = "A";
$eventStartDate = date('Ymd'); // 오늘 날짜 기준으로 데이터 수집

$apiUrl = "http://apis.data.go.kr/B551011/KorService1/searchFestival1";
$params = [
    'serviceKey' => $serviceKey,
    'pageNo' => $pageNo,
    'numOfRows' => $numOfRows,
    'MobileApp' => $mobileApp,
    'MobileOS' => $mobileOS,
    'arrange' => $arrange,
    'listYN' => $listYN,
    'eventStartDate' => $eventStartDate,
    '_type' => 'json'
];

$url = $apiUrl . "?" . http_build_query($params);

// 2. API 호출
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);

$data = json_decode($response, true);

if (!isset($data['response']['body']['items']['item'])) {
    die("데이터를 가져오는 데 실패했거나 데이터가 없습니다.");
}

$items = $data['response']['body']['items']['item'];

// 3. DB 저장 (INSERT ... ON DUPLICATE KEY UPDATE)
$sql = "INSERT INTO festivals (contentid, title, addr1, tel, firstimage, mapx, mapy, eventstartdate, eventenddate)
        VALUES (:contentid, :title, :addr1, :tel, :firstimage, :mapx, :mapy, :eventstartdate, :eventenddate)
        ON DUPLICATE KEY UPDATE 
            title = VALUES(title),
            addr1 = VALUES(addr1),
            tel = VALUES(tel),
            firstimage = VALUES(firstimage),
            mapx = VALUES(mapx),
            mapy = VALUES(mapy),
            eventstartdate = VALUES(eventstartdate),
            eventenddate = VALUES(eventenddate)";

$stmt = $pdo->prepare($sql);

$count = 0;
foreach ($items as $item) {
    try {
        $stmt->execute([
            ':contentid'      => $item['contentid'],
            ':title'          => $item['title'],
            ':addr1'          => $item['addr1'],
            ':tel'            => $item['tel'] ?? '',
            ':firstimage'     => $item['firstimage'] ?? '',
            ':mapx'           => (double)$item['mapx'],
            ':mapy'           => (double)$item['mapy'],
            ':eventstartdate' => date('Y-m-d', strtotime($item['eventstartdate'])),
            ':eventenddate'   => date('Y-m-d', strtotime($item['eventenddate'])),
        ]);
        $count++;
    } catch (Exception $e) {
        error_log("Error inserting contentid " . $item['contentid'] . ": " . $e->getMessage());
    }
}

echo "성공적으로 {$count}개의 축제 정보를 업데이트했습니다. (" . date('Y-m-d H:i:s') . ")\n";
