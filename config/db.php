<?php
// config/db.php

// 도커 컨테이너 환경의 DB 설정
define('DB_HOST', 'db');
define('DB_NAME', 'festival_db');
define('DB_USER', 'festival_user');
define('DB_PASS', 'festival_pass');
define('DB_PORT', '3306');

/**
 * 공공데이터 포털(data.go.kr) 서비스키 (Decoding)
 * 주의: 이 키는 외부에 노출되지 않도록 주의하세요.
 * .gitignore에 이 파일을 추가하는 것을 추천합니다.
 */
define('FESTIVAL_API_KEY', 'f504acd875f34d5e901630a686c28dff2d2dd86df7b9a1b6f019889e135e4822');

try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";port=" . DB_PORT . ";charset=utf8mb4";
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $e) {
    // 실제 서비스에서는 에러 메시지를 로그로 남기고 사용자에게는 간단한 메시지만 보여줍니다.
    error_log($e->getMessage());
    die("데이터베이스 연결에 실패했습니다.");
}
