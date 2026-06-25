<?php
// 세션이 아직 시작되지 않았다면 일괄 시작
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// [모의해킹 진단 포인트: Session Fixation (세션 고정 취약점)]
// 로그인 성공 후 session_regenerate_id()를 통해 세션 식별자를 재발급하지 않는 구조를 가져갑니다.
// 공격자가 사전에 발급받은 PHPSESSID를 피해자에게 주입(XSS 링크 등)하고, 피해자가 이 헤더를 통해 로그인하면
// 공격자도 동일한 세션 ID로 인증 상태를 탈취할 수 있는 취약점 테스트 환경입니다.
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🐾 딩동몰 - DingDongMall</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Noto Sans KR', sans-serif; background-color: #faf9fe; }
        .bg-indigo { background-color: #6f42c1 !important; }
        .text-indigo { color: #6f42c1 !important; }
        .nav-link:hover { color: #ffe600 !important; }
        .active-cate { border-bottom: 3px solid #ffe600; font-weight: bold; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-indigo shadow-sm py-3">
    <div class="container">
        <a class="navbar-brand fw-bold fs-4 d-flex align-items-center gap-2" href="main.php">
            🐶 <span>딩동몰</span>
        </a>
        
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="mainNavbar">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-4 gap-3">
                <li class="nav-item">
                    <a class="nav-link text-white <?= (isset($_GET['category']) && $_GET['category']=='feed') ? 'active-cate' : '' ?>" href="shop_list.php?category=feed">사료/간식</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white <?= (isset($_GET['category']) && $_GET['category']=='item') ? 'active-cate' : '' ?>" href="shop_list.php?category=item">애견 용품</a>
                </li>
            </ul>

            <div class="d-flex align-items-center gap-3">
    <?php if (isset($_SESSION['user_id'])): ?>
       	<span class="text-white small">
        	<strong><?= htmlspecialchars($_SESSION['user_nickname'] ?? $_SESSION['user_id']) ?></strong>님 환영합니다 🐾
    	</span>
        <?php if ($_SESSION['user_type'] === 'a'): ?>
            <a href="product_add.php" class="btn btn-sm btn-warning fw-bold px-3">상품등록</a>
            <a href="logout.php" class="btn btn-sm btn-danger px-2">로그아웃</a>
        <?php else: ?>
            <a href="cart.php" class="btn btn-sm btn-outline-light px-3">🛒 장바구니</a>
            <a href="mypage.php" class="btn btn-sm btn-light text-indigo fw-bold px-3">마이페이지</a>
            <a href="logout.php" class="btn btn-sm btn-danger px-2">로그아웃</a>
        <?php endif; ?>

    <?php else: ?>
        <a href="login.php" class="btn btn-sm btn-light text-indigo fw-bold px-3">로그인 🦴</a>
    <?php endif; ?>

        </div>
    </div>
</nav>