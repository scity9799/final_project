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

// [핵심] 유저의 기본 배송지 주소를 먼저 가져오기
$user_sql = "SELECT user_address FROM ddd_user WHERE user_id = '$safe_user_id'";
$user_res = mysqli_query($conn, $user_sql);
$user_data = mysqli_fetch_assoc($user_res);
// 주소가 없으면 '기본 주소지 미등록'으로 설정
$order_address = mysqli_real_escape_string($conn, $user_data['user_address'] ?? '기본 주소지 미등록');

if (!empty($cart_ids)) {
    $conn->begin_transaction();
    
    try {
        foreach ($cart_ids as $cart_id) {
            $cart_id = (int)$cart_id;

            $cart_sql = "SELECT c.product_id, c.cart_quantity, p.product_name, p.product_price 
                         FROM ddm_cart c 
                         JOIN ddm_product p ON c.product_id = p.product_id 
                         WHERE c.cart_id = $cart_id AND c.user_id = '$safe_user_id'";
            
            $result = mysqli_query($conn, $cart_sql);
            $item = mysqli_fetch_assoc($result);

            if (!$item) {
                throw new Exception("상품 정보를 찾을 수 없습니다.");
            }

            $p_id = (int)$item['product_id'];
            $p_count = (int)$item['cart_quantity'];
            $p_name = mysqli_real_escape_string($conn, $item['product_name']);
            $order_price = $item['product_price'] * $p_count;
            
            // [수정 완료] INSERT 쿼리에 '$order_address' 추가
            $sql = "INSERT INTO ddm_order 
                    (user_id, product_id, product_count, product_name, order_price, order_address, order_status, created_at) 
                    VALUES 
                    ('$safe_user_id', $p_id, $p_count, '$p_name', $order_price, '$order_address', '배송준비중', NOW())";
            
            if (!mysqli_query($conn, $sql)) {
                throw new Exception("주문 입력 실패: " . mysqli_error($conn));
            }

            $delete_sql = "DELETE FROM ddm_cart WHERE cart_id = $cart_id AND user_id = '$safe_user_id'";
            mysqli_query($conn, $delete_sql);
        }
        
        $conn->commit();
        echo "<script>alert('장바구니 주문이 완료되었습니다, 왈! 🐾'); location.href='mypage.php';</script>";
        
    } catch (Exception $e) {
        $conn->rollback();
        echo "<script>alert('주문 처리 중 에러 발생: " . $e->getMessage() . "'); history.back();</script>";
    }
} else {
    echo "<script>alert('선택된 상품이 없습니다, 왈!'); history.back();</script>";
}
?>