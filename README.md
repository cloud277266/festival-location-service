# 🎉 전국 축제 알리미 (Original PHP Edition)

<div align="left">
  <img src="https://img.shields.io/badge/PHP_8.2-777BB4?style=for-the-badge&logo=php&logoColor=white">
  <img src="https://img.shields.io/badge/Apache-D22128?style=for-the-badge&logo=apache&logoColor=white">
  <img src="https://img.shields.io/badge/MySQL_8.0-4479A1?style=for-the-badge&logo=mysql&logoColor=white">
  <img src="https://img.shields.io/badge/Docker-2496ED?style=for-the-badge&logo=docker&logoColor=white">
  <img src="https://img.shields.io/badge/JavaScript-F7DF1E?style=for-the-badge&logo=javascript&logoColor=black">
  <img src="https://img.shields.io/badge/Tailwind_CSS-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white">
</div>
<br>

> **💡 Polyglot Backend Project**
> 본 프로젝트는 프론트엔드와 백엔드를 완전히 분리하고, 동일한 비즈니스 로직을 3가지 다른 백엔드 기술 스택으로 구현하여 시스템 아키텍처의 유연성을 증명하는 다국어 백엔드 프로젝트의 **모태가 된 원본 버전**입니다.
> - **[Java Spring Boot 버전 보러가기](https://github.com/cloud277266/festival-location-service-java)**
> - **[Node.js 버전 보러가기](https://github.com/cloud277266/festival-location-service-node)**

## 📌 프로젝트 소개

**전국 축제 알리미 (PHP Version)**는 한국관광공사 공공데이터 API를 활용하여 전국 축제 정보를 수집하고, 사용자 위치 기반으로 큐레이션하는 서비스의 **프로토타입이자 핵심 데이터 파이프라인이 구축된 프로젝트**입니다. Docker를 활용한 인프라 구성부터 cURL 기반의 데이터 동기화 스크립트까지, 서비스의 전체적인 흐름을 설계하는 데 집중했습니다.

## ✨ 핵심 기술적 특징 (Key Features)

* **Robust Data Pipeline:** `fetch_festivals.php` 스크립트를 통해 공공데이터 API의 수천 건의 데이터를 배치 처리하고 `ON DUPLICATE KEY UPDATE` 구문으로 효율적인 DB 최신화 구현
* **Native SQL Optimization:** PHP 레벨의 연산을 최소화하기 위해 MySQL 내장 함수(`acos`, `cos`, `sin`, `radians`)를 활용한 **하버사인(Haversine) 공식**을 쿼리에 직접 적용
* **Infrastructure as Code (Docker):** Apache, PHP, MySQL 환경을 Docker 컨테이너로 규격화하여 개발 및 배포 환경의 일관성 확보
* **Vanilla JS Responsive UI:** 외부 라이브러리 의존성을 최소화하고 Vanilla JavaScript와 Tailwind CSS만을 활용하여 가볍고 빠른 반응형 웹 인터페이스 구현
* **Security Layer:** DB 연결 정보 및 API 키를 별도의 `config/db.php`로 분리하고 `.gitignore` 및 샘플 파일을 제공하여 보안성 준수

### 🚀 Action (해결 로직 구현)
- **데이터 동기화 설계:** 외부 API의 응답 속도와 트래픽 제한을 고려하여, 실시간 호출 대신 자체 DB에 동기화하는 배치 처리 아키텍처 설계.
- **위치 기반 검색 엔진:** 사용자의 위도/경도 좌표를 기반으로 가장 가까운 축제를 거리순으로 정렬하는 기능을 PHP PDO와 MySQL 엔진의 조합으로 최적화.
- **사용자 경험(UX) 개선:** GPS 권한 거부 시를 대비한 `regions.js` 기반의 수동 지역 선택 시스템 및 데이터 부하를 줄이기 위한 숫자 페이지네이션(Pagination) 도입.

### 🎯 Result (결과 및 성과)
- 방대한 공공데이터를 안정적으로 관리할 수 있는 백엔드 기초 시스템을 완성하였으며, 이는 이후 Java와 Node.js로 시스템을 확장하는 데 결정적인 데이터 모델이 되었습니다.
- 백엔드와 프론트엔드가 철저히 분리된 구조를 설계하여, 향후 백엔드 엔진을 어떤 언어로 교체하더라도 서비스 지속이 가능한 **유연한 아키텍처의 기반**을 마련했습니다.

<br>

## ⚙️ 실행 방법 (Getting Started)

1.  **환경 설정 파일 준비:**
    ```bash
    cp config/config.sample.php config/db.php
    ```
    *(해당 파일 내에 DB 비밀번호 및 공공데이터 API 키 입력)*
2.  **Docker 컨테이너 빌드 및 실행:**
    ```bash
    docker-compose up -d
    ```
3.  **축제 데이터 초기 동기화:**
    ```bash
    docker exec -it festival_web php /var/www/html/tasks/fetch_festivals.php
    ```
4.  **브라우저 접속:** `http://localhost:8080`
