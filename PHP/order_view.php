<?php include 'db.php'; include 'header.php';

// [취약점] 공격자가 URL의 order_id 파라미터를 임의로 변경하면 타인의 주문 정보가 조회됨
$order_id = $_GET['order_id'];
$query = "SELECT * FROM orders WHERE order_id = '$order_id'";
$result = $conn->query($query);
$order = $result->fetch_assoc();

if($order) {
    echo "<h3>주문 상세 정보</h3>";
    echo "주문번호: " . $order['order_id'] . "<br>";
    echo "상품번호: " . $order['product_number'] . "<br>";
} else {
    echo "존재하지 않는 주문입니다.";
}

include 'footer.php'; ?>