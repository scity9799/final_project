<?php
include 'db.php';
session_start(); // 👈 이거 꼭 있어야 세션을 읽을 수 있어!

// 1. 로그인 확인
if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('로그인이 필요합니다!'); location.href='login.php';</script>";
    exit;
}

// 2. 값 수집 (상세 페이지에서 넘어온 name 값과 일치해야 해!)
$product_id = $_POST['product_id'] ?? ''; // 상세 페이지의 input name="product_id"
$quantity = (int)($_POST['quantity'] ?? 1); // 상세 페이지의 input name="quantity"
$user_id = $_SESSION['user_id'];

// 3. 데이터 삽입 (테이블명 ddm_cart, 컬럼명 확인 완료)
$query = "INSERT INTO ddm_cart (user_id, product_id, cart_quantity) VALUES ('$user_id', '$product_id', $quantity)";

if (mysqli_query($conn, $query)) {
    echo "<script>alert('장바구니에 담겼습니다! 🐾'); location.href='shop_list.php';</script>";
} else {
    // 쿼리 에러 확인용 (화면이 안 넘어가면 여기서 에러가 나는 거야!)
    echo "<script>alert('실패: " . mysqli_error($conn) . "'); history.back();</script>";
}
?>