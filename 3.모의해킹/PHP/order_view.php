<?php
include 'db.php';
include 'header.php';

// 로그인 상태 체크
if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('로그인이 필요한 서비스입니다, 왈!'); location.href='login.php';</script>";
    exit;
}

$order_id = isset($_GET['order_id']) ? mysqli_real_escape_string($conn, $_GET['order_id']) : '';

if (empty($order_id)) {
    echo "<script>alert('주문 번호가 필요합니다.'); location.href='mypage.php';</script>";
    exit;
}

// 1. 주문 정보만 먼저 단독 조회
$sql = "SELECT * FROM ddm_order WHERE order_id = '$order_id'";
$result = mysqli_query($conn, $sql);
$order = mysqli_fetch_assoc($result);

if (!$order) {
    echo "<script>alert('주문 내역을 찾을 수 없습니다.'); location.href='mypage.php';</script>";
    exit;
}

// 2. 상품 정보 따로 조회
$product_sql = "SELECT product_name, product_image, product_category FROM ddm_product WHERE product_id = '" . $order['product_id'] . "'";
$product_res = mysqli_query($conn, $product_sql);
$product = mysqli_fetch_assoc($product_res);

// 3. 사용자 닉네임 따로 조회
$user_sql = "SELECT user_nickname FROM ddd_user WHERE user_id = '" . $order['user_id'] . "'";
$user_res = mysqli_query($conn, $user_sql);
$user = mysqli_fetch_assoc($user_res);

// 데이터 병합
$order['product_name'] = $product['product_name'] ?? '상품 정보 없음';
$order['product_image'] = $product['product_image'] ?? 'images/default.png';
$order['product_category'] = $product['product_category'] ?? '기타';
$order['user_nickname'] = $user['user_nickname'] ?? '알 수 없는 유저';
// [기존 코드 아래에 추가/수정]
// 주문 테이블에 주소가 비어있다면(empty), 회원 정보의 주소를 대신 가져옴
if (empty($order['order_address'])) {
    $user_sql = "SELECT user_address FROM ddd_user WHERE user_id = '" . $order['user_id'] . "'";
    $user_res = mysqli_query($conn, $user_sql);
    $user_data = mysqli_fetch_assoc($user_res);
    $order['order_address'] = $user_data['user_address'] ?? '배송지 정보가 없습니다.';
}
$is_cancelled = ($order['order_status'] === '주문취소');
?>

<div class="container my-5" style="min-height: 75vh;">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            
            <?php if ($is_cancelled): ?>
                <div class="alert alert-danger text-center fw-bold p-3 mb-4" style="border-radius: 16px;">
                    🚫 이 주문은 현재 [주문취소] 처리된 내역입니다.
                </div>
            <?php endif; ?>

            <div class="card border-0 shadow-sm p-4" style="border-radius: 20px; background-color: #ffffff; <?php echo $is_cancelled ? 'opacity: 0.8; background-color: #f8f9fa;' : ''; ?>">
                <div class="text-center pb-4 mb-4" style="border-bottom: 2px dashed #e1dbf7;">
                    <div class="display-6 mb-2"><?= $is_cancelled ? '❌' : '🎉' ?></div>
                    <h4 class="fw-bold text-dark m-0"><?=$is_cancelled ? '취소된 주문입니다' : '주문 상세 정보'?></h4>
                    <p class="text-muted small mt-2 mb-0">주문번호: <span class="fw-bold text-dark">#<?=$order['order_id']?></span></p>
                </div>

                <h5 class="fw-bold text-dark mb-3 border-start border-4 border-indigo ps-2" style="border-color: #6f42c1 !important;">📦 주문 상품 정보</h5>
                <div class="d-flex align-items-center gap-3 p-3 mb-4" style="background-color: #fcfbff; border-radius: 12px; border: 1px solid #f1effb;">
                    <img src="<?=$order['product_image']?>" alt="상품이미지" style="width: 80px; height: 80px; object-fit: cover; border-radius: 8px;">
                    <div>
                        <span class="badge mb-1" style="background-color: #ebe5fc; color: #6f42c1; font-size: 0.75rem;">
                            <?=$order['product_category'] == 'feed' ? '사료/간식' : '애견 용품'?>
                        </span>
                        <h6 class="fw-bold text-dark m-0"><?=htmlspecialchars($order['product_name'])?></h6>
                        <small class="text-muted">수량: <?=$order['product_count'] ?? 1?>개</small>
                    </div>
                </div>

                <h5 class="fw-bold text-dark mb-3 border-start border-4 border-indigo ps-2" style="border-color: #6f42c1 !important;">🚚 배송지 정보</h5>
                <div class="p-3 mb-4 text-dark" style="background-color: #fcfbff; border-radius: 12px; border: 1px solid #f1effb; font-size: 0.95rem;">
                    <div class="mb-2"><strong>수령인:</strong> <?=htmlspecialchars($order['user_nickname'])?> 님</div>
                    <div><strong>배송 주소:</strong> <?=htmlspecialchars($order['order_address'] ?? '배송지 정보가 없습니다.')?></div>
                </div>

                <h5 class="fw-bold text-dark mb-3 border-start border-4 border-indigo ps-2" style="border-color: #6f42c1 !important;">💳 결제 내역</h5>
                <div class="p-3 mb-4" style="background-color: #fdfcff; border-radius: 12px; border: 1px solid #f1effb;">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">결제 수단</span>
                        <span class="text-dark fw-bold">딩동 원클릭 결제</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center pt-2" style="border-top: 1px solid #f1effb;">
                        <span class="fw-bold">최종 결제 금액</span>
                        <span class="fs-4 fw-bold text-danger"><?=number_format($order['order_price'] ?? 0)?>원</span>
                    </div>
                </div>

                <div class="d-grid mt-2">
                    <a href="mypage.php" class="btn text-white py-2" style="background-color: #6f42c1; border-radius: 10px; font-weight: bold;">
                        마이페이지로 돌아가기 🐾
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>