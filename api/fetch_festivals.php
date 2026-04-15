<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once(__DIR__ . '/../config/config.php');
require_once(__DIR__ . '/../includes/db.php');

// API URL
$url = "https://apis.data.go.kr/B551011/KorService1/searchFestival1?serviceKey="
    . urlencode(API_KEY)
    . "&numOfRows=10&pageNo=1&MobileOS=ETC&MobileApp=App&_type=json";

$response = file_get_contents($url);

if (!$response) {
    die("API 호출 실패");
}

$data = json_decode($response, true);

if (!isset($data['response']['body']['items']['item'])) {
    die("데이터 없음");
}

$items = $data['response']['body']['items']['item'];

foreach ($items as $item) {

    $title = $conn->real_escape_string($item['title'] ?? '');
    $addr1 = $conn->real_escape_string($item['addr1'] ?? '');
    $mapx = $item['mapx'] ?? 0;
    $mapy = $item['mapy'] ?? 0;

    $start = $item['eventstartdate'] ?? null;
    $end = $item['eventenddate'] ?? null;

    $sql = "INSERT INTO festivals (title, addr1, mapx, mapy, eventstartdate, eventenddate)
            VALUES ('$title', '$addr1', '$mapx', '$mapy', '$start', '$end')";

    if (!$conn->query($sql)) {
        echo "SQL 오류: " . $conn->error . "<br>";
    }
}

echo "완료";