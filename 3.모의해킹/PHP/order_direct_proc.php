<?php
include 'db.php';
if (session_status() == PHP_SESSION_NONE) { session_start(); }

if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('로그인이 만료되었습니다.'); location.href='login.php';</script>";
    exit;
}

$session_id = $_SESSION['user_id'];
$safe_session_id = mysqli_real_escape_string($conn, $session_id);

// 1. POST 파라미터 수령
$product_id = isset($_POST['product_id']) ? mysqli_real_escape_string($conn, $_POST['product_id']) : '';
$product_count = isset($_POST['product_count']) ? (int)$_POST['product_count'] : 1;
$order_price = isset($_POST['total_amount']) ? mysqli_real_escape_string($conn, $_POST['total_amount']) : '';

if (empty($product_id) || empty($order_price)) {
    echo "<script>alert('잘못된 결제 요청 정보입니다, 왈!'); location.href='shop_list.php';</script>";
    exit;
}

// 2. 상품명 가져오기 (배열 에러 방지)
$prod_sql = "SELECT product_name FROM ddm_product WHERE product_id = '$product_id'";
$prod_res = mysqli_query($conn, $prod_sql);
$prod_row = mysqli_fetch_assoc($prod_res);
$product_name = mysqli_real_escape_string($conn, $prod_row['product_name'] ?? '알 수 없는 상품');

// 3. [핵심] 사용자의 현재 배송지 주소를 가져오기 (이게 주문 정보에 박힘!)
$user_sql = "SELECT user_address FROM ddd_user WHERE user_id = '$safe_session_id'";
$user_result = mysqli_query($conn, $user_sql);
$user_row = mysqli_fetch_assoc($user_result);
$order_address = mysqli_real_escape_string($conn, $user_row['user_address'] ?? '기본 주소지 미등록');

// 4. 주문 데이터 삽입 (이제 $order_address가 확실히 들어감)
$sql = "INSERT INTO ddm_order (user_id, product_id, product_count, product_name, order_price, order_address, order_status) 
        VALUES ('$safe_session_id', '$product_id', '$product_count', '$product_name', '$order_price', '$order_address', '배송준비중')";

$result = mysqli_query($conn, $sql);

if ($result) {
    $new_order_id = mysqli_insert_id($conn);
    
    // 장바구니 자동 청소
    $clear_cart = "DELETE FROM ddm_cart WHERE user_id = '$safe_session_id' AND product_id = '$product_id'";
    mysqli_query($conn, $clear_cart);

    echo "<script>alert('결제가 완료되었습니다, 왈! 🦴'); location.href='order_view.php?order_id=$new_order_id';</script>";
} else {
    echo "<script>alert('결제 처리 오류: " . mysqli_error($conn) . "'); history.back();</script>";
}
?>