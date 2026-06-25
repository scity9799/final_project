<?php
include 'db.php';
// [Session Fixation 취약점 포인트] 
// 로그인 성공 후 session_regenerate_id()를 호출하지 않고 공격자가 심어둔 기존 세션을 그대로 유지합니다.
session_start(); 

// ★ 모의해킹 주 담당자의 실습(SQL Injection 로그인 우회)을 위해 
// 의도적으로 mysqli_real_escape_string 및 어떠한 필터링 함수도 사용하지 않고 날것 그대로 받습니다.
$user_id = $_POST['user_id'] ?? '';
$user_pw = $_POST['user_pw'] ?? '';
$login_type = $_POST['login_type'] ?? 'user'; // 체크박스 미선택 시 기본값 'user'

// DB 세션 인코딩 강제 설정 (한글 데이터 깨짐 방지 및 정상 인증 유도)
mysqli_set_charset($conn, "utf8mb4");

if ($login_type === 'admin') {
    // 1) 관리자 테이블 검색 (취약한 동적 쿼리 구문)
    // 실제 DB 스펙 적용: 소문자 테이블 ddd_admin / 소문자 컬럼 admin_id, admin_password
    $query = "SELECT * FROM ddd_admin WHERE admin_id = '$user_id' AND admin_password = '$user_pw'";
    $result = mysqli_query($conn, $query);
    
    // SQL 구문 오류 디버깅용 안전장치 (실습 중 문법 에러 확인용)
    if (!$result) {
        die("관리자 쿼리 실행 실패: " . mysqli_error($conn));
    }
    
    if ($row = mysqli_fetch_assoc($result)) {
        $_SESSION['user_id'] = $row['admin_id'];
        $_SESSION['user_type'] = 'A'; // 관리자 권한 설정
        echo "<script>alert('관리자님 환영합니다!'); location.href='main.php';</script>";
        exit;
    }
} else {
    // 2) 일반 회원(보호소) 테이블 검색 (취약한 동적 쿼리 구문)
    // 실제 DB 스펙 적용: 소문자 테이블 ddd_user / 소문자 컬럼 user_id, user_password
    $query = "SELECT * FROM ddd_user WHERE user_id = '$user_id' AND user_password = '$user_pw'";
    $result = mysqli_query($conn, $query);
    
    // SQL 구문 오류 디버깅용 안전장치 (실습 중 문법 에러 확인용)
    if (!$result) {
        die("일반회원 쿼리 실행 실패: " . mysqli_error($conn));
    }
    
    if ($row = mysqli_fetch_assoc($result)) {
        $_SESSION['user_id'] = $row['user_id'];
        $_SESSION['user_type'] = $row['user_type']; // 더미데이터에 입력된 소문자 's' (보호소) 권한이 그대로 세션에 박힙니다.
        echo "<script>alert('환영합니다!'); location.href='main.php';</script>";
        exit;
    }
}

// 위의 exit 분기를 하나도 타지 못하고 인증에 최종 실패한 경우
echo "<script>alert('정보가 일치하지 않습니다. ID/PW 또는 입력값을 확인하세요.'); history.back();</script>";
?>