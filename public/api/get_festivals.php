<?php
// api/get_festivals.php
require_once __DIR__ . '/../../config/db.php';

header('Content-Type: application/json; charset=utf-8');

$lat = isset($_GET['lat']) ? (float)$_GET['lat'] : 37.5665;
$lng = isset($_GET['lng']) ? (float)$_GET['lng'] : 126.9780;
$type = isset($_GET['type']) ? (int)$_GET['type'] : 1;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = 12;
$offset = ($page - 1) * $limit;

try {
    $whereClause = "";
    $params = [];

    // 필터 조건 설정
    if ($type === 3) {
        $whereClause = "WHERE eventstartdate > CURDATE()";
    } else {
        // 내 주변(1)과 진행 중(2)은 모두 현재 진행 중인 축제 기준
        $whereClause = "WHERE eventstartdate <= CURDATE() AND eventenddate >= CURDATE()";
    }

    // 1. 전체 개수 조회 (페이지네이션 계산용)
    $countSql = "SELECT COUNT(*) FROM festivals $whereClause";
    $countStmt = $pdo->prepare($countSql);
    $countStmt->execute();
    $totalRows = $countStmt->fetchColumn();
    $totalPages = ceil($totalRows / $limit);

    // 2. 실제 데이터 조회
    if ($type === 1) {
        $sql = "SELECT *, (6371 * acos(LEAST(1.0, GREATEST(-1.0, cos(radians(:lat1)) * cos(radians(mapy)) * cos(radians(mapx) - radians(:lng)) + sin(radians(:lat2)) * sin(radians(mapy)))))) AS distance 
                FROM festivals $whereClause ORDER BY distance ASC LIMIT :limit OFFSET :offset";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':lat1', $lat);
        $stmt->bindValue(':lat2', $lat);
        $stmt->bindValue(':lng', $lng);
    } else {
        $orderBy = ($type === 2) ? "eventenddate ASC" : "eventstartdate ASC";
        $sql = "SELECT * FROM festivals $whereClause ORDER BY $orderBy LIMIT :limit OFFSET :offset";
        $stmt = $pdo->prepare($sql);
    }

    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $results = $stmt->fetchAll();

    echo json_encode([
        'status' => 'success',
        'data' => $results,
        'total_pages' => $totalPages, // 전체 페이지 수 반환
        'current_page' => $page
    ], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}