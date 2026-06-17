<?php include 'db.php';
// [취약점] CSRF: 토큰 검증 없이 파라미터만으로 요청을 처리
$p_no = $_POST['p_no'];
$qty = $_POST['qty'];
$user_id = $_SESSION['user_id'];

$query = "INSERT INTO cart (user_id, product_number, quantity) VALUES ('$user_id', '$p_no', '$qty')";
if ($conn->query($query)) {
    echo "<script>alert('장바구니에 담겼습니다.'); location.href='cart.php';</script>";
}
?>