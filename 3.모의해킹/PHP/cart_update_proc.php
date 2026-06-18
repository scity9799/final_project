<?php
include 'db.php';

// 로그인 상태 체크
if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('로그인이 필요한 서비스입니다, 왈!'); location.href='login.php';</script>";
    exit;
}

$user_id = $_SESSION['user_id'];

// POST 파라미터 정제
$cart_id = isset($_POST['cart_id']) ? (int)$_POST['cart_id'] : 0;
$cart_quantity = isset($_POST['cart_quantity']) ? (int)$_POST['cart_quantity'] : 0;

if ($cart_id > 0 && $cart_quantity > 0) {
    
    // 🛑 [IDOR 취약점 유도 구역]: WHERE 조건절에서 소유권 검증(AND user_id = '$user_id')을 누락함!
    // 공격자가 다른 유저의 cart_id를 가로채서 본문 파라미터로 던지면 타인의 장바구니 수량이 마음대로 위조 변조됩니다.
    $query = "UPDATE ddm_cart 
              SET cart_quantity = $cart_quantity 
              WHERE cart_id = $cart_id";
              
    $result = mysqli_query($conn, $query);

    if ($result) {
        header("Location: cart.php");
        exit;
    } else {
        echo "<script>alert('수량 변경 중 데이터베이스 오류가 발생했습니다.'); history.back();</script>";
        exit;
    }
} else {
    echo "<script>alert('올바르지 않은 수량이거나 잘못된 요청입니다, 왈!'); history.back();</script>";
    exit;
}
?>