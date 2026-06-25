<?php
include 'db.php';
include 'header.php';

// 로그인 상태 체크
if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('로그인이 필요한 서비스입니다, 왈!'); location.href='login.php';</script>";
    exit;
}

$session_id = $_SESSION['user_id'];
$safe_session_id = mysqli_real_escape_string($conn, $session_id);

// 1. 사용자 정보 가져오기
$user_sql = "SELECT * FROM ddm_user WHERE user_id = '$safe_session_id'";
$user_result = mysqli_query($conn, $user_sql);
$user_info = mysqli_fetch_assoc($user_result);

if (!$user_info) {
    $user_info = ['user_name' => '댕댕이', 'user_id' => $session_id, 'user_email' => '', 'user_address' => '', 'created_at' => date('Y-m-d')];
}

// 2. 주문 내역 쿼리 (JOIN 사용으로 상품명 문제 해결)
$order_sql = "SELECT o.*, p.product_name 
              FROM ddm_order o 
              LEFT JOIN ddm_product p ON o.product_id = p.product_id 
              WHERE o.user_id = '$safe_session_id' 
              ORDER BY o.order_id DESC LIMIT 3";
$order_result = mysqli_query($conn, $order_sql);
?>

<div class="container my-5" style="min-height: 75vh;">
    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm text-center p-4 h-100" style="border-radius: 16px;">
                <div class="mx-auto my-3 d-flex align-items-center justify-content-center text-white" style="width: 90px; height: 90px; background-color: #6f42c1; border-radius: 50%; font-size: 2rem;">🐶</div>
                <h4 class="fw-bold text-dark mb-1"><?=htmlspecialchars($user_info['user_name'])?> 님</h4>
                <p class="text-muted small mb-4">@<?=htmlspecialchars($user_info['user_id'])?></p>
                <div class="d-grid gap-2">
                    <button class="btn text-white py-2" style="background-color: #6f42c1;" onclick="location.href='logout.php'">로그아웃</button>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card border-0 shadow-sm p-4 mb-4" style="border-radius: 16px;">
                <h5 class="fw-bold mb-3 border-start border-4 border-indigo ps-2" style="border-color: #6f42c1 !important;">⚙️ 배송지 및 정보 변경</h5>
                <form action="mypage_update.php" method="POST">
                    <div class="row g-3">
                        <div class="col-md-6"><label class="form-label small fw-bold">아이디</label><input type="text" class="form-control bg-light" value="<?=htmlspecialchars($user_info['user_id'])?>" readonly></div>
                        <div class="col-md-6"><label class="form-label small fw-bold">이메일</label><input type="email" name="user_email" class="form-control" value="<?=htmlspecialchars($user_info['user_email'])?>"></div>
                        <div class="col-12"><label class="form-label small fw-bold">주소</label><input type="text" name="user_address" class="form-control" value="<?=htmlspecialchars($user_info['user_address'])?>"></div>
                        <div class="col-12 text-end"><button type="submit" class="btn text-white" style="background-color: #6f42c1;">정보 수정하기 🦴</button></div>
                    </div>
                </form>
            </div>

            <div class="card border-0 shadow-sm p-4" style="border-radius: 16px;">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold m-0 border-start border-4 border-indigo ps-2" style="border-color: #6f42c1 !important;">📦 최근 주문 내역</h5>
                    <a href="order_list_all.php" class="btn btn-sm text-white" style="background-color: #6f42c1;">전체 보기</a>
                </div>
                <table class="table table-hover align-middle mb-0 text-center">
                    <thead style="background-color: #fcfbff;">
                        <tr><th>주문번호</th><th>상품명</th><th>결제금액</th><th>상태</th><th>관리</th></tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($order_result && mysqli_num_rows($order_result) > 0) {
                            while ($order = mysqli_fetch_assoc($order_result)) {
                                // 0원 오류 방지: order_price 컬럼값을 명확히 사용
                                $price = isset($order['order_price']) ? (int)$order['order_price'] : 0;
                        ?>
                        <tr style="cursor:pointer;">
                            <td onclick="location.href='order_view.php?order_id=<?=$order['order_id']?>'">#<?=$order['order_id']?></td>
                            <td class="text-start" onclick="location.href='order_view.php?order_id=<?=$order['order_id']?>'"><?=htmlspecialchars($order['product_name'] ?? '상품정보없음')?></td>
                            <td class="text-danger fw-bold" onclick="location.href='order_view.php?order_id=<?=$order['order_id']?>'"><?=number_format($price)?>원</td>
                            <td onclick="location.href='order_view.php?order_id=<?=$order['order_id']?>'"><span class="badge bg-success"><?=$order['order_status'] ?? '배송준비중'?></span></td>
                            <td>
                                <?php if ($order['order_status'] == '배송준비중'): ?>
                                <form action="order_cancel_proc.php" method="POST" onsubmit="return confirm('정말 취소하시겠습니까, 왈?');">
                                    <input type="hidden" name="order_id" value="<?=$order['order_id']?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger">취소</button>
                                </form>
                                <?php else: ?>
                                <small class="text-muted">취소불가</small>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php } } else { ?>
                        <tr><td colspan="5" class="py-4">주문 내역이 없습니다, 왈! 🪹</td></tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php include 'footer.php'; ?>