<?php
include 'db.php'; 

if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('로그인이 필요합니다.'); location.href='login.php';</script>";
    exit;
}

// URL 파라미터(GET)로 전달받은 장바구니 고유 ID
$cart_id = isset($_GET['cart_id']) ? $_GET['cart_id'] : '';

if (empty($cart_id)) {
    echo "<script>alert('삭제할 상품이 지정되지 않았습니다.'); location.href='cart.php';</script>";
    exit;
}

// 🛑 [모의해킹 진단 포인트: CSRF (사이트 간 요청 위조)]
// 핵심 조작 행위를 안전한 POST 방식이나 CSRF 토큰(난수) 검증 없이 단순 GET 주소로만 처리하고 있습니다.
// 공격자가 다른 게시판에 <img src="http://[타겟IP]/cart_delete.php?cart_id=12"> 요소를 심어두면, 
// 일반 사용자가 그 글을 읽는 순간 자신의 장바구니 상품이 강제로 무단 삭제되는 취약점이 발생합니다.
$sql = "DELETE FROM ddm_cart WHERE cart_id = $cart_id";
$result = mysqli_query($conn, $sql);

if ($result) {
    echo "<script>alert('장바구니에서 상품을 정상적으로 걷어냈습니다, 왈! 🐾'); location.href='cart.php';</script>";
} else {
    echo "<script>alert('삭제 처리 중 에러가 발생했습니다.'); history.back();</script>";
}
?>