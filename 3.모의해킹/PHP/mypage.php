<?php
include 'db.php';
include 'header.php';

// [실습용 설정] 로그인 상태 체크 (실제 환경에서는 세션 검증이 엄격해야 합니다)
if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('로그인이 필요한 서비스입니다, 왈!'); location.href='login.php';</script>";
    exit;
}

$session_id = $_SESSION['user_id'];

// 사용자의 상세 정보 및 모의 주문 내역 가져오기
$user_sql = "SELECT * FROM ddm_user WHERE user_id = '$session_id'";
$user_result = mysqli_query($conn, $user_sql);
$user_info = mysqli_fetch_assoc($user_result);

// 주문 내역 쿼리 (가장 최근 주문 3개만 바인딩)
$order_sql = "SELECT * FROM ddm_order WHERE user_id = '$session_id' ORDER BY order_id DESC LIMIT 3";
$order_result = mysqli_query($conn, $order_sql);
?>

<div class="container my-5" style="min-height: 75vh;">
    <div class="row g-4">
        <!-- 1. 왼쪽 사이드바: 프로필 요약 카드 -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm text-center p-4 h-100" style="border-radius: 16px;">
                <div class="mx-auto my-3 d-flex align-items-center justify-content-center text-white" 
                     style="width: 90px; height: 90px; background-color: #6f42c1; border-radius: 50%; font-size: 2rem;">
                    🐶
                </div>
                <h4 class="fw-bold text-dark mb-1"><?=htmlspecialchars($user_info['user_name'])?> 님</h4>
                <p class="text-muted small mb-4">@<?=$user_info['user_id']?></p>
                
                <div class="p-3 mb-4 text-start" style="background-color: #f8f6ff; border-radius: 12px;">
                    <div class="d-flex justify-content-between small mb-2">
                        <span class="text-muted">회원 등급</span>
                        <span class="fw-bold" style="color: #6f42c1;">딩동 브론즈 🐾</span>
                    </div>
                    <div class="d-flex justify-content-between small">
                        <span class="text-muted">가입일</span>
                        <span class="text-dark"><?=$user_info['created_at'] ?? '2026-06-18'?></span>
                    </div>
                </div>

                <div class="d-grid gap-2">
                    <button class="btn text-white py-2" style="background-color: #6f42c1; border-radius: 8px;" onclick="location.href='logout.php'">
                        로그아웃
                    </button>
                </div>
            </div>
        </div>

        <!-- 2. 오른쪽 메인 영역: 정보 수정 및 최근 주문 내역 -->
        <div class="col-lg-8">
            <div class="d-flex flex-column gap-4">
                
                <!-- [섹션 A] 회원 정보 관리 (Stored XSS 진단 포인트) -->
                <div class="card border-0 shadow-sm p-4" style="border-radius: 16px;">
                    <h5 class="fw-bold text-dark mb-3 border-start border-4 border-indigo ps-2" style="border-color: #6f42c1 !important;">
                        ⚙️ 배송지 및 정보 변경
                    </h5>
                    
                    <form action="mypage_update.php" method="POST">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">아이디 (변경 불가)</label>
                                <input type="text" class="form-control bg-light" value="<?=$user_info['user_id']?>" readonly style="border-radius: 8px;">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">이메일 주소</label>
                                <input type="email" name="user_email" class="form-control border-muted" value="<?=$user_info['user_email']?>" style="border-radius: 8px;">
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-bold text-muted">기본 배송지 주소</label>
                                <!-- [보안 취약점 포인트]: htmlspecialchars 처리를 생략하여 배송지에 스크립트를 저장 후 마이페이지 열람 시 탈취 공격(Stored XSS) 시연 유도 -->
                                <input type="text" name="user_address" class="form-control border-muted" value="<?=$user_info['user_address']?>" style="border-radius: 8px;">
                            </div>
                            <div class="col-12 text-end mt-4">
                                <button type="submit" class="btn text-white px-4" style="background-color: #6f42c1; border-radius: 8px;">
                                    정보 수정하기 🦴
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- [섹션 B] 최근 주문 내역 (그리드/테이블 스타일 조합) -->
                <div class="card border-0 shadow-sm p-4" style="border-radius: 16px;">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="fw-bold text-dark m-0 border-start border-4 border-indigo ps-2" style="border-color: #6f42c1 !important;">
                            📦 최근 주문 내역 (최근 3건)
                        </h5>
                        <span class="badge bg-light text-dark p-2" style="border: 1px solid #e1dbf7; border-radius: 6px;">배송 조회</span>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 text-center">
                            <thead style="background-color: #fcfbff; color: #6f42c1;">
                                <tr>
                                    <th class="py-3 border-0">주문번호</th>
                                    <th class="py-3 border-0 text-start">상품 정보</th>
                                    <th class="py-3 border-0">결제금액</th>
                                    <th class="py-3 border-0">주문상태</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                if (mysqli_num_rows($order_result) > 0) {
                                    while ($order = mysqli_fetch_assoc($order_result)) {
                                ?>
                                        <tr style="border-bottom: 1px solid #f1effb;">
                                            <td class="fw-bold text-muted py-3">#<?=$order['order_id']?></td>
                                            <td class="text-start py-3 fw-bold">
                                                <span class="text-dark"><?=htmlspecialchars($order['product_name'])?></span>
                                            </td>
                                            <td class="py-3 text-danger fw-bold"><?=number_format($order['order_price'])?>원</td>
                                            <td class="py-3">
                                                <span class="badge" style="background-color: #eefdf4; color: #198754; padding: 6px 12px; border-radius: 20px;">
                                                    <?=$order['order_status'] ?? '배송중'?>
                                                </span>
                                            </td>
                                        </tr>
                                <?php
                                    }
                                } else {
                                    echo '<tr><td colspan="4" class="text-center py-5 text-muted">아직 딩동몰에서 주문한 내역이 없습니다, 왈! 🪹</td></tr>';
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<?php
include 'footer.php';
?>