<?php 
include 'db.php'; 

// [취약점 노출] CSRF: 토큰 없이 POST 요청만으로 주문 처리
// [취약점 노출] IDOR: cart_id 배열의 유효성을 검증하지 않아 타인의 cart_id를 삽입 시 타인 데이터 삭제/주문 가능
$cart_ids = $_POST['cart_ids']; // 체크박스로 전달받은 장바구니 번호 배열
$user_id = $_SESSION['user_id'];

if (!empty($cart_ids)) {
    // 트랜잭션 시작 (데이터 무결성 보장)
    $conn->begin_transaction();
    
    try {
        foreach ($cart_ids as $cart_id) {
            // 1. 주문 생성: cart 테이블에서 상품 정보를 가져와 orders 테이블에 삽입
            $query = "INSERT INTO orders (user_id, product_number, quantity, order_date) 
                      SELECT user_id, product_number, quantity, NOW() 
                      FROM cart WHERE cart_id = '$cart_id' AND user_id = '$user_id'";
            $conn->query($query);
            
            // 2. 장바구니 삭제
            $conn->query("DELETE FROM cart WHERE cart_id = '$cart_id' AND user_id = '$user_id'");
        }
        $conn->commit();
        echo "<script>alert('선택한 상품 주문이 완료되었습니다.'); location.href='mypage.php';</script>";
    } catch (Exception $e) {
        $conn->rollback();
        echo "<script>alert('주문 처리 중 오류가 발생했습니다.'); history.back();</script>";
    }
} else {
    echo "<script>alert('선택된 상품이 없습니다.'); history.back();</script>";
}
?>