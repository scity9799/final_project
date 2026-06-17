<?php // login.php
include_once 'db.php'; ?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title>딩동몰 로그인</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f1f3f5; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .login-box { width: 320px; padding: 30px; background: white; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
        .form-group input, .form-group select { width: 100%; padding: 10px; box-sizing: border-box; border: 1px solid #ced4da; border-radius: 4px; }
        .btn-submit { width: 100%; padding: 12px; background-color: #007BFF; color: white; border: none; font-size: 16px; border-radius: 4px; cursor: pointer; font-weight: bold; }
        .btn-submit:hover { background-color: #0056b3; }
    </style>
</head>
<body>

<div class="login-box">
    <h2 style="text-align: center; margin-bottom: 20px;">DingDongDog 로그인</h2>
    <form action="login_proc.php" method="POST">
        <div class="form-group">
            <label for="user_type">회원 유형 선택</label>
            <select name="user_type" id="user_type">
                <option value="S">보호소 회원 (User)</option>
                <option value="A">지사 관리자 (Admin)</option>
            </select>
        </div>
        <div class="form-group">
            <label for="user_id">아이디</label>
            <input type="text" name="user_id" id="user_id" required>
        </div>
        <div class="form-group">
            <label for="user_pw">비밀번호</label>
            <input type="password" name="user_pw" id="user_pw" required>
        </div>
        <button type="submit" class="login-box btn-submit">로그인</button>
    </form>
</div>

</body>
</html>

<?php
// login_proc.php
include_once 'db.php';

$user_id   = isset($_POST['user_id']) ? $_POST['user_id'] : '';
$user_pw   = isset($_POST['user_pw']) ? $_POST['user_pw'] : '';
$user_type = isset($_POST['user_type']) ? $_POST['user_type'] : 'S';

// [취약점 유도] SQL Injection 구역
$query = "SELECT * FROM ddd_user WHERE user_id = '$user_id' AND user_pw = '$user_pw' AND user_type = '$user_type'";
$result = mysqli_query($conn, $query);

if ($result && mysqli_num_rows($result) > 0) {
    $user = mysqli_fetch_assoc($result);

    // [취약점 유도] Session Fixation (세션 고정 취약점)
    // 안전한 코드가 되려면 session_regenerate_id(true); 를 사용해야 함
    
    $_SESSION['user_id']   = $user['user_id'];
    $_SESSION['user_type'] = $user['user_type'];
    $_SESSION['user_name'] = $user['user_name']; 

    if ($_SESSION['user_type'] === 'A') {
        echo "<script>alert('지사 관리자 로그인 성공'); location.href='product_add.php';</script>";
    } else {
        echo "<script>alert('보호소 회원 로그인 성공'); location.href='main.php';</script>";
    }
} else {
    echo "<script>alert('인증 정보가 올바르지 않습니다.'); history.back();</script>";
}
mysqli_close($conn);
?>

<?php
// logout.php
include_once 'db.php';
$_SESSION = array();
if (ini_get("session_use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"]);
}
session_destroy();
echo "<script>alert('로그아웃되었습니다.'); location.href='login.php';</script>";
exit;
?>