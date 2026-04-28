<?php
// tasks/fetch_festivals.php
require_once __DIR__ . '/../config/db.php';

// 1. API 기본 설정
$apiUrl = "https://apis.data.go.kr/B551011/KorService2/searchFestival2";
$serviceKey = FESTIVAL_API_KEY;

$pageNo = 1;
$numOfRows = 1000; // 한 번에 가져올 최대 개수
$eventStartDate = (date('Y')) . "0101"; // 올해 1월 1일부터 모든 축제 검색 (진행 중 누락 방지)
$totalCount = 0;
$successCount = 0;

echo "--- 🚀 전국 축제 데이터 수집을 시작합니다 ---\n";

// DB 저장용 준비 구문 (한 번만 준비)
$sql = "INSERT INTO festivals (contentid, title, addr1, tel, firstimage, mapx, mapy, eventstartdate, eventenddate)
        VALUES (:contentid, :title, :addr1, :tel, :firstimage, :mapx, :mapy, :eventstartdate, :eventenddate)
        ON DUPLICATE KEY UPDATE 
            title = VALUES(title), addr1 = VALUES(addr1), tel = VALUES(tel),
            firstimage = VALUES(firstimage), mapx = VALUES(mapx), mapy = VALUES(mapy),
            eventstartdate = VALUES(eventstartdate), eventenddate = VALUES(eventenddate)";
$stmt = $pdo->prepare($sql);

// 2. 페이징 자동화 루프 (전체 데이터를 가져올 때까지 반복)
do {
    $params = [
        'serviceKey'     => $serviceKey,
        'numOfRows'      => $numOfRows,
        'pageNo'         => $pageNo,
        'MobileOS'       => 'ETC',
        'MobileApp'      => 'FestivalApp',
        '_type'          => 'json',
        'arrange'        => 'C',
        'eventStartDate' => $eventStartDate,
    ];

    $url = $apiUrl . "?" . http_build_query($params);

    // API 호출
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    curl_close($ch);

    $data = json_decode($response, true);

    // 에러 방어
    if (!isset($data['response']['header']['resultCode']) || $data['response']['header']['resultCode'] !== '0000') {
        $errorMsg = $data['response']['header']['resultMsg'] ?? 'API 응답 오류';
        die("❌ [페이지 {$pageNo}] API 호출 실패: {$errorMsg}\n");
    }

    // 첫 페이지 호출 시 전체 데이터 수 파악
    if ($pageNo === 1) {
        $totalCount = (int)$data['response']['body']['totalCount'];
        echo "총 {$totalCount}개의 축제 데이터가 발견되었습니다.\n";
    }

    $items = $data['response']['body']['items']['item'] ?? [];

    if (empty($items)) {
        break; // 가져올 아이템이 없으면 루프 종료
    }

    // DB 저장
    foreach ($items as $item) {
        try {
            // 위도/경도가 없는 비정상 데이터 방어
            $mapx = !empty($item['mapx']) ? (double)$item['mapx'] : 0;
            $mapy = !empty($item['mapy']) ? (double)$item['mapy'] : 0;

            $stmt->execute([
                ':contentid'      => $item['contentid'],
                ':title'          => $item['title'],
                ':addr1'          => $item['addr1'] ?? '',
                ':tel'            => $item['tel'] ?? '',
                ':firstimage'     => $item['firstimage'] ?? '',
                ':mapx'           => $mapx,
                ':mapy'           => $mapy,
                ':eventstartdate' => date('Y-m-d', strtotime($item['eventstartdate'])),
                ':eventenddate'   => date('Y-m-d', strtotime($item['eventenddate'])),
            ]);
            $successCount++;
        } catch (Exception $e) {
            // 중복 등의 사소한 에러는 넘어가고 로그만 남김
            error_log("DB 저장 오류 (ID {$item['contentid']}): " . $e->getMessage());
        }
    }

    echo "✅ [{$pageNo}페이지] " . count($items) . "개 처리 완료...\n";

    $pageNo++; // 다음 페이지로 이동

// 현재까지 가져온 수보다 전체 수가 더 많으면 계속 반복
} while (($pageNo - 1) * $numOfRows < $totalCount);

echo "\n🎉 작업 완료: " . date('Y-m-d H:i:s') . "\n";
echo "총 {$successCount}개의 축제 데이터를 DB에 최신화했습니다!\n";