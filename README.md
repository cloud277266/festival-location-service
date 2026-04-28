# 🎉 전국 축제 알리미 (National Festival Notifier)

사용자의 현재 위치를 기반으로 전국 226개 시/군/구의 축제 정보를 실시간으로 제공하는 모바일 친화적 웹 서비스입니다. 공공데이터 API를 활용하여 데이터를 동기화하고, 최적화된 거리 계산 알고리즘을 통해 사용자에게 가장 유효한 축제 정보를 제공합니다.

## 🛠 Tech Stack
- **Backend:** PHP 8.2, MySQL 8.0, PDO
- **Frontend:** HTML5, Vanilla JavaScript, Tailwind CSS
- **Infrastructure:** Docker, Docker Compose, Apache
- **API:** 한국관광공사 국문 관광정보 서비스 API

---

## 💡 개발 과정 및 문제 해결 (PAAR)

### 🚨 Problem (문제 인식)
공공데이터 API에서 제공하는 전국 단위의 방대한 축제 데이터를 실시간으로 호출할 경우, 심각한 렌더링 속도 저하와 API 트래픽 초과 문제가 발생했습니다. 또한, 단순 텍스트 검색이 아닌 '사용자 위치 기반 정렬'을 구현해야 하는 과제가 있었습니다.

### 🔍 Analyze (원인 분석)
1. 매번 외부 API를 호출하여 프론트엔드에서 데이터를 필터링하는 구조는 모바일 환경에서 성능 저하의 주원인이 됩니다.
2. 사용자의 위도/경도(GPS) 좌표를 기반으로 가장 가까운 축제를 찾기 위해서는 지구의 곡률을 반영한 정밀한 거리 계산 로직이 필요했습니다.

### 🚀 Action (해결 로직 구현)
- **Data Synchronization:** `cron` 작업을 통해 주기적으로 공공데이터 API의 수천 개 데이터를 자체 MySQL DB로 동기화(Batch Insert/Update)하는 파이프라인을 구축했습니다.
- **Backend API & Pagination:** 백엔드와 프론트엔드를 분리하고, 대용량 데이터의 안정적인 렌더링을 위해 서버 단에서 `LIMIT`과 `OFFSET`을 활용한 **Offset-based Pagination**을 구현했습니다.
- **Haversine Algorithm:** 데이터베이스 SQL 쿼리 내부에 삼각함수(`acos`, `cos`, `sin`)를 활용한 하버사인(Haversine) 공식을 적용하여, PHP가 아닌 DB 엔진 단에서 즉각적인 거리 계산과 정렬이 이루어지도록 최적화했습니다.
- **UX Improvement:** GPS 사용 불가 환경을 대비해 전국 226개 시/군/구의 중심 좌표를 `regions.js` 모듈로 분리, 외부 API 의존 없이도 역지오코딩과 유사한 수동 지역 선택 및 위치 안내 기능을 구현했습니다.

### 🎯 Result (결과 및 성과)
- 모바일과 PC 모두에서 끊김 없는 반응형 UI를 제공하며, 사용자 위치 기반으로 정확도 높은 데이터를 즉시 로드하는 시스템을 완성했습니다.
- View(화면)와 Controller(API)가 완전히 분리된 아키텍처를 설계하여, 추후 Java/Spring Boot 등 타 프레임워크로의 백엔드 마이그레이션이 매우 용이한 확장성을 확보했습니다.
