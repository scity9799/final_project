<?php
// db.php : MariaDB 연결 및 데이터베이스 글로벌 인스턴스 정의
$host = "10.200.40.2";
$user = "team04db";
$pass = "team04!";
$dbname = "ddm_db";

$conn = mysqli_connect($host, $user, $pass, $dbname);
mysqli_set_charset($conn, "utf8mb4");
if (!$conn) {
    die("데이터베이스 연결 실패: " . mysqli_connect_error());
}

// 전역 세션 보관소 시작
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>