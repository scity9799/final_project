<?php include 'db.php';

// [취약점] IDOR: cart_id만 알면 본인 것이 아니어도 삭제 가능
$cart_id = $_GET['cart_id'];

// [IDOR 공격 포인트] 공격자는 URL을 cart_delete_proc.php?cart_id=1001 형태로 변경하여 
// 타인의 장바구니를 무단 삭제할 수 있음
$query = "DELETE FROM cart WHERE cart_id = '$cart_id'";
$conn->query($query);

echo "<script>alert('삭제되었습니다.'); location.href='cart.php';</script>";
?>