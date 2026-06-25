<?php
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    exit("<script>alert('로그인이 필요합니다!'); location.href='login.php';</script>");
}

$session_id = $_SESSION['user_id'];
$user_nickname = mysqli_real_escape_string($conn, $_POST['user_nickname'] ?? '');
$user_email = mysqli_real_escape_string($conn, $_POST['user_email'] ?? '');
$user_address = mysqli_real_escape_string($conn, $_POST['user_address'] ?? '');

// 1. 회원 정보 테이블(ddd_user) 업데이트 (닉네임 추가)
$sql_user = "UPDATE ddd_user 
             SET user_nickname = '$user_nickname',
                 user_email = '$user_email', 
                 user_address = '$user_address' 
             WHERE user_id = '$session_id'";

// 2. 주문 내역(ddm_order) 주소 동기화 (배송 준비 중인 건만)
$sql_order = "UPDATE ddm_order 
              SET order_address = '$user_address' 
              WHERE user_id = '$session_id' 
              AND order_status = '배송준비중'";

mysqli_query($conn, $sql_user);
mysqli_query($conn, $sql_order);
$_SESSION['user_nickname'] = $user_nickname;
echo "<script>alert('닉네임과 회원 정보가 수정되었습니다, 왈! 🦴'); location.href='mypage.php';</script>";
?>