<?php
include 'db.php';
include 'header.php';

// 로그인 상태 체크
if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('로그인이 필요한 서비스입니다, 왈!'); location.href='login.php';</script>";
    exit;
}

// URL 파라미터로 주문 ID 수령
$order_id = isset($_GET['order_id']) ? $_GET['order_id'] : '';

if (empty($order_id)) {
    echo "<script>alert('올바르지 않은 주문 번호입니다.'); location.href='mypage.php';</script>";
    exit;
}

// 🛑 [모의해킹 진단 포인트: IDOR (인증 우회 / 타인 정보 열람)]
// 쿼리문에서 'WHERE order_id = $order_id'로만 조회하고, 이 주문이 '현재 로그인한 사용자($session_id)'의 주문이 맞는지 
// 크로스 체크하는 검증 로직이 누락되어 있습니다. 
// 공격자가 주소창의 order_id 숫자를 조작하면 다른 회원들의 주문 정보와 배송지 주소를 그대로 훔쳐볼 수 있습니다.
$sql = "SELECT o.*, p.product_name, p.product_image, p.product_category 
        FROM ddm_order o
        JOIN ddm_product p ON o.product_id = p.product_id
        WHERE o.order_id = $order_id";
$result = mysqli_query($conn, $sql);
$order = mysqli_fetch_assoc($result);

if (!$order) {
    echo "<script>alert('주문 내역을 찾을 수 없습니다, 왈!'); location.href='mypage.php';</script>";
    exit;
}
?>

<div class="container my-5" style="min-height: 75vh;">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card border-0 shadow-sm p-4" style="border-radius: 20px; background-color: #ffffff;">
                <div class="text-center pb-4 mb-4" style="border-bottom: 2px dashed #e1dbf7;">
                    <div class="display-6 mb-2">🎉</div>
                    <h4 class="fw-bold text-dark m-0">주문이 정상 완료되었습니다!</h4>
                    <p class="text-muted small mt-2 mb-0">주문번호: <span class="fw-bold text-dark">#<?=$order['order_id']?></span></p>
                </div>

                <h5 class="fw-bold text-dark mb-3 border-start border-4 border-indigo ps-2" style="border-color: #6f42c1 !important;">
                    📦 주문 상품 정보
                </h5>
                <div class="d-flex align-items-center gap-3 p-3 mb-4" style="background-color: #fcfbff; border-radius: 12px; border: 1px solid #f1effb;">
                    <img src="<?=$order['product_image']?>" alt="" style="width: 80px; height: 80px; object-fit: cover; border-radius: 8px;">
                    <div>
                        <span class="badge mb-1" style="background-color: #ebe5fc; color: #6f42c1; font-size: 0.75rem;">
                            <?=$order['product_category'] == 'feed' ? '사료' : '배변패드'?>
                        </span>
                        <h6 class="fw-bold text-dark m-0"><?=htmlspecialchars($order['product_name'])?></h6>
                        <small class="text-muted">수량: <?=$order['product_count']?>개</small>
                    </div>
                </div>

                <h5 class="fw-bold text-dark mb-3 border-start border-4 border-indigo ps-2" style="border-color: #6f42c1 !important;">
                    🚚 배송지 정보
                </h5>
                <div class="p-3 mb-4 text-dark" style="background-color: #fcfbff; border-radius: 12px; border: 1px solid #f1effb; font-size: 0.95rem;">
                    <div class="mb-2"><strong>수령인:</strong> <?=htmlspecialchars($order['user_id'])?> 님</div>
                    <div><strong>배송 주소:</strong> <?=htmlspecialchars($order['order_address'])?></div>
                </div>

                <h5 class="fw-bold text-dark mb-3 border-start border-4 border-indigo ps-2" style="border-color: #6f42c1 !important;">
                    💳 결제 내역
                </h5>
                <div class="p-3 mb-4" style="background-color: #fdfcff; border-radius: 12px; border: 1px solid #f1effb;">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">결제 수단</span>
                        <span class="text-dark fw-bold">딩동 원클릭 즉시 결제</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center pt-2" style="border-top: 1px solid #f1effb;">
                        <span class="fw-bold">최종 결제 금액</span>
                        <span class="fs-4 fw-bold text-danger"><?=number_format($order['order_price'])?>원</span>
                    </div>
                </div>

                <div class="d-grid mt-4">
                    <a href="shop_list.php" class="btn text-white py-2" style="background-color: #6f42c1; border-radius: 10px; font-weight: bold;">
                        쇼핑 계속하기 🐾
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>