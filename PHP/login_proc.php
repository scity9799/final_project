<?php
include 'db.php'; // session_start()가 포함되어 있음

$user_id = $_POST['user_id'];
$user_pw = $_POST['user_pw'];
$user_type = $_POST['user_type'];

// [취약점 1] SQL Injection: 입력값을 검증하지 않고 쿼리에 직접 결합
$query = "SELECT * FROM ddd_user WHERE user_id = '$user_id' AND user_pw = '$user_pw' AND user_type = '$user_type'";
$result = $conn->query($query);

if ($result && $result->num_rows > 0) {
    // [취약점 2] Session Fixation: 로그인 전 세션 ID를 재생성(session_regenerate_id)하지 않음
    $_SESSION['user_id'] = $user_id;
    $_SESSION['user_type'] = $user_type;
    echo "<script>alert('로그인 성공!'); location.href='main.php';</script>";
} else {
    echo "<script>alert('아이디 또는 비밀번호가 틀렸습니다.'); history.back();</script>";
}
?>