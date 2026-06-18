<?php
include 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('로그인이 만료되었습니다.'); location.href='login.php';</script>";
    exit;
}

$session_id = $_SESSION['user_id'];

// 사용자의 배송지 주소를 자동으로 긁어오기
$user_sql = "SELECT user_address FROM ddm_user WHERE user_id = '$session_id'";
$user_result = mysqli_query($conn, $user_sql);
$user_row = mysqli_fetch_assoc($user_result);
$order_address = $user_row['user_address'] ?? '기본 주소지 미등록';

// POST 파라미터 수령
$product_id = isset($_POST['product_id']) ? $_POST['product_id'] : '';
$product_count = isset($_POST['product_count']) ? $_POST['product_count'] : 1;
$order_price = isset($_POST['total_amount']) ? $_POST['total_amount'] : ''; // 사용자가 조작 가능한 결제 요청 금액

if (empty($product_id) || empty($order_price)) {
    echo "<script>alert('잘못된 결제 요청 정보입니다, 왈!'); location.href='shop_list.php';</script>";
    exit;
}

// 🛑 [모의해킹 진단 포인트: 데이터 조작 및 결제 우회 (Parameter Tampering)]
// 서버 단에서 '이 상품의 실제 가격 * 수량'이 클라이언트가 보낸 '$order_price'와 일치하는지 재검증하는 로직이 완전히 빠져있습니다.
// 공격자가 프록시(Burp Suite)나 F12 개발자 도구를 켜서 total_amount 가치를 '100'원으로 변조해 전송하면,
// DB에는 100원으로 결제 완료 정보가 들어가 시스템이 승인하는 심각한 논리 취약점이 발생합니다.

// 주문 테이블(ddm_order)에 데이터 인서트 진행
$sql = "INSERT INTO ddm_order (user_id, product_id, product_count, order_price, order_address, order_status) 
        VALUES ('$session_id', $product_id, $product_count, $order_price, '$order_address', '배송준비중')";
$result = mysqli_query($conn, $sql);

if ($result) {
    // 가장 최근에 인서트된 주문의 고유 AUTO_INCREMENT ID 획득
    $new_order_id = mysqli_insert_id($conn);
    
    // 만약 장바구니를 통한 결제였다면, 결제 완료된 상품은 장바구니에서 자동 청소 제거
    $clear_cart = "DELETE FROM ddm_cart WHERE user_id = '$session_id' AND product_id = $product_id";
    mysqli_query($conn, $clear_cart);

    // 성공 시 영수증 상세 페이지(order_view.php)로 주문번호를 달고 리다이렉트
    echo "<script>alert('원클릭 결제가 완료되었습니다, 왈! 🦴'); location.href='order_view.php?order_id=$new_order_id';</script>";
} else {
    echo "<script>alert('결제 처리 오류가 발생했습니다.'); history.back();</script>";
}
?>