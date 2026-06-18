<?php
include 'db.php';
session_start();

$user_id = mysqli_real_escape_string($conn, $_POST['user_id']);
$user_pw = mysqli_real_escape_string($conn, $_POST['user_pw']);
$login_type = $_POST['login_type'] ?? 'user'; // 체크박스 안 누르면 'user'

if ($login_type === 'admin') {
    // 관리자 테이블 검색
    $query = "SELECT * FROM ddd_admin WHERE admin_id = '$user_id' AND admin_password = '$user_pw'";
    $result = mysqli_query($conn, $query);
    if ($row = mysqli_fetch_assoc($result)) {
        $_SESSION['user_id'] = $row['admin_id'];
        $_SESSION['user_type'] = 'a';
        echo "<script>alert('관리자님 환영합니다!'); location.href='main.php';</script>";
    }
} else {
    // 유저 테이블 검색
    $query = "SELECT * FROM ddd_user WHERE user_id = '$user_id' AND user_password = '$user_pw'";
    $result = mysqli_query($conn, $query);
    if ($row = mysqli_fetch_assoc($result)) {
        $_SESSION['user_id'] = $row['user_id'];
        $_SESSION['user_type'] = $row['user_type']; // 'c' 또는 's'
        echo "<script>alert('환영합니다!'); location.href='main.php';</script>";
    }
}
// 실패 시
echo "<script>alert('정보가 일치하지 않습니다.'); history.back();</script>";
?>