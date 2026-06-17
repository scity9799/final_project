<?php include 'db.php';
// [취약점] Privilege Escalation(권한 상승):
// 세션 권한 검증 없이 삭제 기능을 수행하여 일반 사용자가 강제로 삭제 시도 가능
$p_no = $_GET['p_no'];
$conn->query("DELETE FROM product WHERE product_number = '$p_no'");
echo "<script>alert('삭제되었습니다.'); location.href='shop_list.php';</script>";
?>