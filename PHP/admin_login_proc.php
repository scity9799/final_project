<?php
// admin_login_proc.php : 관리자 로그인 판단 로직
include 'db.php';

$admin_id = $_POST['admin_id'];
$password = $_POST['password'];

$query = "SELECT * FROM users WHERE user_id = '$admin_id' AND password = '$password' AND user_type = 'A'";
$result = mysqli_query($conn, $query);

if ($result && mysqli_num_rows($result) > 0) {
    $admin = mysqli_fetch_assoc($result);
    $_SESSION['user_id'] = $admin['user_id'];
    $_SESSION['user_type'] = $admin['user_type']; 
    echo "<script>alert('통제센터 마스터 세션 수립.'); location.href='admin.php';</script>";
} else {
    echo "<script>alert('잘못된 접근 식별자 크레덴셜입니다.'); history.back();</script>";
}
?>