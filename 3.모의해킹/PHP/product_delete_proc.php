<?php
include 'db.php';
session_start();

// URL 파라미터로 넘어온 상품 고유 ID 수령
$product_id = isset($_GET['id']) ? $_GET['id'] : '';

if (empty($product_id)) {
    echo "<script>alert('잘못된 요청입니다.'); location.href='shop_list.php';</script>";
    exit;
}

// 🛑 [모의해킹 진단 포인트: CSRF 및 취약한 인증]
// 관리자 세션 체크와 일회성 토큰 검증이 없으므로 일반 사용자 권한을 가진 공격자가 
// 주소창에 직접 타겟 상품 ID를 적어 실행하면(예: admin_product_delete.php?id=3) 상품이 무단 삭제됩니다.
$sql = "DELETE FROM ddm_product WHERE product_id = $product_id";
$result = mysqli_query($conn, $sql);

if ($result) {
    echo "<script>alert('해당 상품이 상점에서 영구 삭제되었습니다, 왈!'); location.href='shop_list.php';</script>";
} else {
    echo "<script>alert('삭제 처리 실패'); history.back();</script>";
}
?>