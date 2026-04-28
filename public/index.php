<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>전국 축제 알리미</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* 탭 전환 시 부드러운 효과 */
        .tab-content { display: none; }
        .tab-content.active { display: block; animation: fadeIn 0.3s ease-in-out; }
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
    </style>
</head>
<body class="bg-gray-100 text-gray-800 font-sans">

<header class="bg-blue-600 text-white text-center py-4 shadow-md sticky top-0 z-50">
    <h1 class="text-xl font-bold">🎉 전국 축제 알리미</h1>
</header>

<nav class="flex justify-around bg-white border-b sticky top-14 z-40">
    <button onclick="switchTab(1)" id="btn-tab-1" class="w-1/3 py-3 font-semibold text-blue-600 border-b-2 border-blue-600">내 주변</button>
    <button onclick="switchTab(2)" id="btn-tab-2" class="w-1/3 py-3 font-semibold text-gray-500 hover:text-blue-500">진행 중</button>
    <button onclick="switchTab(3)" id="btn-tab-3" class="w-1/3 py-3 font-semibold text-gray-500 hover:text-blue-500">오픈 예정</button>
</nav>

<main class="p-4 mb-10 max-w-7xl mx-auto"> <section id="tab-1" class="tab-content active">
        <div class="mb-4 flex space-x-2">
            <select id="sido-select" onchange="updateSigungu()" class="w-1/2 p-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 font-semibold text-gray-700 bg-white">
                <option value="current">📍 현재 내 위치</option>
                <option value="서울">서울특별시</option>
                <option value="부산">부산광역시</option>
                <option value="대구">대구광역시</option>
                <option value="인천">인천광역시</option>
                <option value="광주">광주광역시</option>
                <option value="대전">대전광역시</option>
                <option value="울산">울산광역시</option>
                <option value="세종">세종특별자치시</option>
                <option value="경기">경기도</option>
                <option value="강원">강원특별자치도</option>
                <option value="충북">충청북도</option>
                <option value="충남">충청남도</option>
                <option value="전북">전북특별자치도</option>
                <option value="전남">전라남도</option>
                <option value="경북">경상북도</option>
                <option value="경남">경상남도</option>
                <option value="제주">제주특별자치도</option>
            </select>

            <select id="sigungu-select" onchange="changeLocation()" disabled class="w-1/2 p-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 font-semibold text-gray-700 bg-gray-100">
                <option value="">시/군/구 선택</option>
            </select>
        </div>

        <div id="location-status" class="text-sm text-center text-gray-500 mb-4">📍 위치 정보를 가져오는 중...</div>

        <div id="list-1" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6"></div>
    </section>

    <section id="tab-2" class="tab-content">
        <div id="list-2" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6"></div>
        <div id="pagination-2" class="flex justify-center items-center space-x-2 mt-10 mb-10"></div>
    </section>

    <section id="tab-3" class="tab-content">
        <div id="list-3" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6"></div>
        <div id="pagination-3" class="flex justify-center items-center space-x-2 mt-10 mb-10"></div>
    </section>
</main>

<script src="/js/regions.js"></script>

<script>
    // 앱 초기화
    document.addEventListener('DOMContentLoaded', () => {
        loadFestivals(2, 'list-2'); // 진행 중 로드
        loadFestivals(3, 'list-3'); // 오픈 예정 로드
        getUserLocationAndLoadNearest(); // 위치 권한 묻고 내 주변 로드
    });

    // 1 Depth (시/도) 변경 시 2 Depth (시/군/구) 목록 업데이트
    function updateSigungu() {
        const sidoSelect = document.getElementById('sido-select');
        const sigunguSelect = document.getElementById('sigungu-select');
        const selectedSido = sidoSelect.value;

        if (selectedSido === 'current') {
            sigunguSelect.innerHTML = '<option value="">시/군/구 선택</option>';
            sigunguSelect.disabled = true;
            sigunguSelect.classList.add('bg-gray-100');
            getUserLocationAndLoadNearest();
            return;
        }

        sigunguSelect.disabled = false;
        sigunguSelect.classList.remove('bg-gray-100');
        sigunguSelect.innerHTML = '';

        if (typeof regionData === 'undefined') {
            console.error("regions.js 파일이 정상적으로 로드되지 않았습니다!");
            return;
        }

        const sigungus = regionData[selectedSido];

        if (sigungus) {
            sigungus.forEach(area => {
                const option = document.createElement('option');
                option.value = `${area.lat},${area.lng}`;
                option.textContent = area.name;
                sigunguSelect.appendChild(option);
            });
            changeLocation();
        }
    }

    // 2 Depth (시/군/구) 변경 시 서버(API)로 위치 데이터 전송
    function changeLocation() {
        const sidoSelect = document.getElementById('sido-select');
        const sigunguSelect = document.getElementById('sigungu-select');
        const val = sigunguSelect.value;
        const statusText = document.getElementById('location-status');
        const container = document.getElementById('list-1');

        if (!val) return;

        container.innerHTML = `<div class="text-center py-10"><span class="animate-pulse text-blue-500">데이터를 불러오는 중입니다...</span></div>`;

        const [lat, lng] = val.split(',');
        const sidoName = sidoSelect.options[sidoSelect.selectedIndex].text;
        const sigunguName = sigunguSelect.options[sigunguSelect.selectedIndex].text;

        statusText.innerHTML = `선택하신 <b>${sidoName} ${sigunguName}</b> 기준 가장 가까운 축제입니다.`;
        loadFestivals(1, 'list-1', parseFloat(lat), parseFloat(lng));
    }

    // 하버사인 거리 계산 공식
    function getDistance(lat1, lon1, lat2, lon2) {
        const R = 6371;
        const dLat = (lat2 - lat1) * Math.PI / 180;
        const dLon = (lon2 - lon1) * Math.PI / 180;
        const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
            Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
            Math.sin(dLon/2) * Math.sin(dLon/2);
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
        return R * c;
    }

    // 내 좌표와 가장 가까운 지역 이름 찾기
    function getRegionNameFromCoords(lat, lng) {
        if (typeof regionData === 'undefined') return "알 수 없는 지역";

        let closestRegion = "";
        let minDistance = Infinity;

        for (const sido in regionData) {
            regionData[sido].forEach(area => {
                const dist = getDistance(lat, lng, area.lat, area.lng);
                if (dist < minDistance) {
                    minDistance = dist;
                    let fullSidoName = sido;
                    const sidoSelect = document.getElementById('sido-select');
                    Array.from(sidoSelect.options).forEach(opt => {
                        if(opt.value === sido) fullSidoName = opt.text;
                    });
                    closestRegion = `${fullSidoName} ${area.name}`;
                }
            });
        }
        return closestRegion;
    }

    // 사용자 위치 가져오기
    function getUserLocationAndLoadNearest() {
        const statusText = document.getElementById('location-status');

        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                (position) => {
                    const lat = position.coords.latitude;
                    const lng = position.coords.longitude;
                    const regionName = getRegionNameFromCoords(lat, lng);
                    statusText.innerHTML = `📍 현재 위치(<b>${regionName} 부근</b>) 기반으로 가까운 축제를 찾았습니다!`;
                    loadFestivals(1, 'list-1', lat, lng);
                },
                (error) => {
                    statusText.innerHTML = "위치 권한이 거부되었거나 오류가 발생했습니다. (기본값: <b>서울특별시 중구 부근</b>)";
                    loadFestivals(1, 'list-1', 37.5665, 126.9780);
                }
            );
        } else {
            statusText.textContent = "이 브라우저에서는 위치 기능을 지원하지 않습니다.";
        }
    }

    // 🚨 [수정됨] 백엔드 API 호출 및 데이터 렌더링 (숫자 페이지네이션 적용)
    async function loadFestivals(type, containerId, lat = null, lng = null, page = 1) {
        let url = `/api/get_festivals.php?type=${type}&page=${page}`;
        if (lat && lng) url += `&lat=${lat}&lng=${lng}`;

        try {
            const response = await fetch(url);
            const result = await response.json();
            const container = document.getElementById(containerId);

            // 페이지 이동 시 자연스럽게 상단으로 스크롤 이동
            if (page > 1) window.scrollTo({ top: 0, behavior: 'smooth' });

            if (result.status === 'success' && result.data.length > 0) {
                // 카드를 덮어씌움 (append 방식 아님)
                container.innerHTML = result.data.map(festival => createCard(festival, type)).join('');

                // 데이터 렌더링 후 하단에 숫자 페이지네이션 생성
                renderPagination(type, result.total_pages, result.current_page, lat, lng);
            } else {
                container.innerHTML = `<div class="col-span-full text-center text-gray-500 py-20">해당하는 축제 정보가 없습니다.</div>`;
                const paginationContainer = document.getElementById(`pagination-${type}`);
                if (paginationContainer) paginationContainer.innerHTML = '';
            }
        } catch (error) {
            console.error("데이터 로드 실패:", error);
        }
    }

    // 🚨 [새로 추가됨] 숫자 페이지네이션 생성 함수
    function renderPagination(type, totalPages, currentPage, lat, lng) {
        const container = document.getElementById(`pagination-${type}`);
        if (!container) return; // 내 주변(Type 1) 등 페이징 영역이 없으면 무시

        let html = '';
        const latParam = lat !== null ? lat : 'null';
        const lngParam = lng !== null ? lng : 'null';

        // 1. 이전 페이지 버튼 (◀)
        const prevDisabled = currentPage === 1 ? 'opacity-30 pointer-events-none' : '';
        html += `<button onclick="loadFestivals(${type}, 'list-${type}', ${latParam}, ${lngParam}, ${currentPage - 1})" class="${prevDisabled} px-3 py-2 border rounded-lg hover:bg-gray-100 shadow-sm">&lt;</button>`;

        // 2. 모바일 화면을 고려하여 앞뒤로 최대 5개 페이지만 노출
        let startPage = Math.max(1, currentPage - 2);
        let endPage = Math.min(totalPages, currentPage + 2);

        if (totalPages <= 5) {
            startPage = 1;
            endPage = totalPages;
        } else {
            if (currentPage <= 3) endPage = 5;
            else if (currentPage + 2 >= totalPages) startPage = totalPages - 4;
        }

        // 3. 숫자 버튼 렌더링
        for (let i = startPage; i <= endPage; i++) {
            const activeClass = i === currentPage
                ? 'bg-blue-600 text-white border-blue-600 shadow-md'
                : 'bg-white text-gray-700 hover:bg-gray-100 shadow-sm';
            html += `<button onclick="loadFestivals(${type}, 'list-${type}', ${latParam}, ${lngParam}, ${i})" class="px-4 py-2 border rounded-lg font-bold transition ${activeClass}">${i}</button>`;
        }

        // 4. 다음 페이지 버튼 (▶)
        const nextDisabled = currentPage === totalPages ? 'opacity-30 pointer-events-none' : '';
        html += `<button onclick="loadFestivals(${type}, 'list-${type}', ${latParam}, ${lngParam}, ${currentPage + 1})" class="${nextDisabled} px-3 py-2 border rounded-lg hover:bg-gray-100 shadow-sm">&gt;</button>`;

        container.innerHTML = html;
    }

    // 개별 축제 카드 HTML 생성
    function createCard(festival, type) {
        const defaultImage = "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='500' height='300' viewBox='0 0 500 300'%3E%3Crect width='500' height='300' fill='%23f3f4f6'/%3E%3Ctext x='50%25' y='45%25' font-size='50' text-anchor='middle' dominant-baseline='middle'%3E🎉%3C/text%3E%3Ctext x='50%25' y='60%25' font-size='18' text-anchor='middle' fill='%236b7280' font-weight='bold' font-family='sans-serif'%3E축제 이미지 준비 중%3C/text%3E%3C/svg%3E";
        const imageUrl = festival.firstimage ? festival.firstimage : defaultImage;
        let distanceHtml = '';

        if (type === 1 && festival.distance) {
            distanceHtml = `<span class="inline-block bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded-full mb-2 font-bold">${festival.distance.toFixed(1)} km 앞</span>`;
        }

        return `
                <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-200 flex flex-col">
                    <img class="w-full aspect-video object-cover" src="${imageUrl}" alt="${festival.title}">
                    <div class="p-4 flex-1 flex flex-col">
                        <div>
                            ${distanceHtml}
                            <h2 class="font-bold text-lg text-gray-800 mb-1 line-clamp-1">${festival.title}</h2>
                            <p class="text-sm text-gray-600 mb-2 line-clamp-1">📍 ${festival.addr1}</p>
                        </div>
                        <div class="mt-auto flex justify-between items-center text-xs text-gray-500 pt-3 border-t">
                            <span>📅 ${festival.eventstartdate} ~ ${festival.eventenddate}</span>
                            ${festival.tel ? `<span class="truncate ml-2">📞 ${festival.tel}</span>` : ''}
                        </div>
                    </div>
                </div>
            `;
    }

    // 탭 전환(UI 변경) 기능
    function switchTab(tabId) {
        document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
        document.querySelectorAll('nav button').forEach(btn => {
            btn.classList.remove('text-blue-600', 'border-b-2', 'border-blue-600');
            btn.classList.add('text-gray-500');
        });
        document.getElementById(`tab-${tabId}`).classList.add('active');
        const activeBtn = document.getElementById(`btn-tab-${tabId}`);
        activeBtn.classList.remove('text-gray-500');
        activeBtn.classList.add('text-blue-600', 'border-b-2', 'border-blue-600');
    }
</script>
</body>
</html>