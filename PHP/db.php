<?php
// db.php : MariaDB 연결 및 데이터베이스 글로벌 인스턴스 정의
$host = "localhost";
$user = "root";
$pass = "password";
$dbname = "shopping_db";

$conn = mysqli_connect($host, $user, $pass, $dbname);

if (!$conn) {
    die("데이터베이스 연결 실패: " . mysqli_connect_error());
}

// 전역 세션 보관소 시작
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>