<?php
// db.php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$db_host = "localhost";
$db_user = "root";      // 환경에 맞게 수정
$db_pass = "password";  // 환경에 맞게 수정
$db_name = "your_db";   // 환경에 맞게 생성한 데이터베이스명 입력

$conn = mysqli_connect($db_host, $db_user, $db_pass, $db_name);

if (!$conn) {
    die("데이터베이스 연결 실패: " . mysqli_connect_error());
}

mysqli_set_charset($conn, "utf8mb4");
?>

<?php
// index.php
include_once 'db.php';

if (isset($_SESSION['user_id'])) {
    if ($_SESSION['user_type'] === 'A') {
        header("Location: product_add.php");
    } else {
        header("Location: main.php");
    }
} else {
    header("Location: login.php");
}
exit;
?>

<?php
// header.php
include_once 'db.php';
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title>딩동몰</title>
    <style>
        body { font-family: 'Malgun Gothic', sans-serif; margin: 0; padding: 0; background-color: #f9f9f9; }
        header { background-color: #333; color: white; padding: 15px; display: flex; justify-content: space-between; align-items: center; }
        header a { color: white; text-decoration: none; margin-left: 15px; }
        .container { width: 1000px; margin: 30px auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0px 0px 10px rgba(0,0,0,0.05); }
        .nav-links { display: flex; gap: 15px; }
        .search-box { margin: 20px 0; display: flex; gap: 10px; }
        .search-box input[type="text"] { width: 70%; padding: 10px; font-size: 16px; border: 1px solid #ddd; border-radius: 4px; }
        .search-box button { width: 25%; background-color: #28a745; color: white; border: none; font-size: 16px; cursor: pointer; border-radius: 4px; }
    </style>
</head>
<body>

<header>
    <div class="logo">
        <a href="main.php" style="font-size: 20px; font-weight: bold; margin-left: 0;">🐾 DingDongDog Mall</a>
    </div>
    <div class="nav-links">
        <?php if (isset($_SESSION['user_id'])): ?>
            <span><strong><?php echo htmlspecialchars($_SESSION['user_name'] ?? $_SESSION['user_id']); ?></strong>님 [<?php echo $_SESSION['user_type'] == 'A' ? '지사관리자' : '보호소회원'; ?>]</span>
            <a href="main.php">메인</a>
            <a href="shop_list.php">상품목록</a>
            <a href="cart.php">장바구니</a>
            <a href="mypage.php">마이페이지</a>
            <a href="logout.php" style="color: #ff6b6b;">로그아웃</a>
        <?php else: ?>
            <a href="login.php">로그인</a>
        <?php endif; ?>
    </div>
</header>

<div class="container">

</div> <footer style="background: #333; color: #888; text-align: center; padding: 20px; margin-top: 50px; font-size: 13px;">
    &copy; 2026 DingDongDog 모의해킹 훈련 인프라. All rights reserved.
</footer>

</body>
</html>

