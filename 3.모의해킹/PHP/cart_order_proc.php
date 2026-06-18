<?php 
include 'db.php'; 

// 1. 로그인 세션 가드
if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('로그인이 필요합니다, 왈!'); location.href='login.php';</script>";
    exit;
}

$cart_ids = isset($_POST['cart_ids']) ? $_POST['cart_ids'] : [];
$user_id = $_SESSION['user_id'];
$safe_user_id = mysqli_real_escape_string($conn, $user_id);

if (!empty($cart_ids)) {
    // 트랜잭션 시작
    $conn->begin_transaction();
    
    try {
        foreach ($cart_ids as $cart_id) {
            $cart_id = (int)$cart_id;

            // 2. 장바구니에서 상품 정보 가져오기 (컬럼명 cart_quantity 확인!)
            $cart_sql = "SELECT c.product_id, c.cart_quantity, p.product_name, p.product_price 
                         FROM ddm_cart c 
                         JOIN ddm_product p ON c.product_id = p.product_id 
                         WHERE c.cart_id = $cart_id AND c.user_id = '$safe_user_id'";
            
            $result = mysqli_query($conn, $cart_sql);
            $item = mysqli_fetch_assoc($result);

            // 데이터가 없으면 예외 발생
            if (!$item) {
                throw new Exception("상품 정보를 찾을 수 없습니다. (Cart ID: $cart_id)");
            }

            // 3. 주문 테이블(ddm_order)에 데이터 INSERT
            $p_id = (int)$item['product_id'];
            $p_count = (int)$item['cart_quantity']; // 여기서 cart_quantity 사용!
            $p_name = mysqli_real_escape_string($conn, $item['product_name']);
            $order_price = $item['product_price'] * $p_count;
            
            $sql = "INSERT INTO ddm_order 
                    (user_id, product_id, product_count, product_name, order_price, order_status, created_at) 
                    VALUES 
                    ('$safe_user_id', $p_id, $p_count, '$p_name', $order_price, '배송준비중', NOW())";
            
            if (!mysqli_query($conn, $sql)) {
                throw new Exception("주문 입력 실패: " . mysqli_error($conn));
            }

            // 4. 장바구니 데이터 삭제
            $delete_sql = "DELETE FROM ddm_cart WHERE cart_id = $cart_id AND user_id = '$safe_user_id'";
            if (!mysqli_query($conn, $delete_sql)) {
                throw new Exception("장바구니 삭제 실패: " . mysqli_error($conn));
            }
        }
        
        // 모든 작업 성공 시 커밋
        $conn->commit();
        echo "<script>alert('주문이 완료되었습니다, 왈! 🐾'); location.href='mypage.php';</script>";
        
    } catch (Exception $e) {
        // 하나라도 실패하면 롤백
        $conn->rollback();
        echo "<script>alert('주문 처리 중 에러 발생: " . $e->getMessage() . "'); history.back();</script>";
    }
} else {
    echo "<script>alert('선택된 상품이 없습니다, 왈!'); history.back();</script>";
}
?>