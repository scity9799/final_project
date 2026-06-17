<?php 
include 'db.php'; 

// [취약점] CSRF: 토큰 검증 없이 GET 파라미터(p_no)만으로 상태 변경(주문 생성)을 수행
$p_no = $_GET['p_no'] ?? 0;
$user_id = $_SESSION['user_id'] ?? '';

if ($p_no > 0 && !empty($user_id)) {
    // 주문 테이블에 바로 INSERT
    $query = "INSERT INTO orders (user_id, product_number, order_date) VALUES ('$user_id', '$p_no', NOW())";
    
    if ($conn->query($query)) {
        echo "<script>alert('즉시 구매가 완료되었습니다!'); location.href='mypage.php';</script>";
    } else {
        echo "<script>alert('구매 실패'); history.back();</script>";
    }
} else {
    echo "<script>alert('잘못된 접근입니다.'); location.href='index.php';</script>";
}
?>