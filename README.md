# 🎉 전국 축제 알리미 (위치 기반 스마트 축제 탐색 서비스)

<div align="left">
  <img src="https://img.shields.io/badge/PHP_8.2-777BB4?style=for-the-badge&logo=php&logoColor=white">
  <img src="https://img.shields.io/badge/MySQL_8.0-4479A1?style=for-the-badge&logo=mysql&logoColor=white">
  <img src="https://img.shields.io/badge/JavaScript-F7DF1E?style=for-the-badge&logo=javascript&logoColor=black">
  <img src="https://img.shields.io/badge/Tailwind_CSS-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white">
  <img src="https://img.shields.io/badge/Docker-2496ED?style=for-the-badge&logo=docker&logoColor=white">
</div>
<br>

## 📌 프로젝트 소개

**전국 축제 알리미**는 한국관광공사 공공데이터 API를 활용하여 전국 226개 시/군/구의 축제 정보를 실시간으로 제공하는 모바일 친화적 웹 서비스입니다. 방대한 공공데이터를 브라우저에서 직접 처리할 때 발생하는 렌더링 지연 문제를 해결하기 위해 **백엔드 API 기반 아키텍처**를 독립적으로 구축하였으며, 다수의 데이터를 지연 없이 처리하기 위한 **비동기 페이징(Pagination) 처리**와 **자체 거리 계산 알고리즘 최적화**에 집중하여 개발했습니다.

## ✨ 핵심 기능 (Key Features)

* **자체 거리 계산 알고리즘:** 데이터베이스 SQL 쿼리 내에 지구의 곡률을 반영한 **하버사인(Haversine) 공식**을 적용하여, 사용자 GPS 좌표 기준 가장 가까운 축제를 즉각적으로 연산 및 정렬
* **대용량 데이터 페이징 최적화:** 수천 건의 축제 데이터를 한 번에 로드하지 않고, 서버 단에서 `LIMIT`과 `OFFSET`을 활용해 분할 응답하는 **Offset-based Pagination** 구현으로 클라이언트 메모리 부하 최소화
* **공공데이터 API 자동 동기화:** 크론(`cron`) 작업을 통해 외부 공공데이터 API의 최신 축제 데이터를 자체 데이터베이스로 지속 동기화(Batch Insert/Update)하여, 외부 API 서버 장애 시에도 끊김 없는 서비스 제공
* **스마트 지역 선택 (Fallback UI):** 브라우저 위치(GPS) 권한 거부 상황을 대비해 전국 17개 시/도 및 226개 시/군/구 좌표를 모듈화(`regions.js`)하여, 별도의 유료 Geocoding API 없이도 정밀한 수동 검색 지원
* **동적 예외 처리 및 반응형 UI:** Tailwind CSS를 활용한 디바이스 맞춤형 Grid 레이아웃 적용 및 이미지 누락 데이터를 위한 Base64 기반의 SVG Fallback 이미지 동적 렌더링 적용

<br>

## 💡 기술적 의사결정 및 문제 해결 (PAAR)

* **Problem (문제):** 공공데이터 API에서 전국 단위의 데이터를 실시간 호출할 경우 응답 속도 저하 발생.
* **Analyze (분석):** 모바일 환경에서 클라이언트가 직접 데이터를 필터링하고 연산하는 것은 성능에 치명적이라 판단.
* **Action (행동):** View(화면)와 Controller(API)를 분리. PHP 내부 API를 구축하여 무거운 연산(거리 계산, 페이징 분할)을 MySQL 데이터베이스 엔진 단으로 이관.
* **Result (결과):** 대규모 데이터에서도 브라우저 렌더링 지연이 없는 쾌적한 UX를 달성하였으며, 추후 백엔드를 Spring Boot 등 타 언어로 마이그레이션하기 용이한 확장성 확보.

