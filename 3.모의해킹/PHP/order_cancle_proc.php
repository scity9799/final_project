<?php
include 'db.php';

if (!isset($_SESSION['user_id']) || !isset($_POST['order_id'])) {
    exit("<script>alert('잘못된 접근입니다, 왈!'); history.back();</script>");
}

$user_id = $_SESSION['user_id'];
$order_id = mysqli_real_escape_string($conn, $_POST['order_id']);

// 취소 가능한 상태인지(배송 준비 중) 확인 후 업데이트
$update_sql = "UPDATE ddm_order SET order_status = '주문취소' 
               WHERE order_id = '$order_id' AND user_id = '$user_id' AND order_status = '배송준비중'";

if (mysqli_query($conn, $update_sql)) {
    echo "<script>alert('주문이 취소되었습니다, 왈!'); location.href='mypage.php';</script>";
} else {
    echo "<script>alert('취소 실패! 관리자에게 문의하세요, 왈!'); history.back();</script>";
}
?>