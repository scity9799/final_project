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

// 전체 주문 내역 쿼리 (상품 테이블 JOIN)
$sql = "SELECT o.*, p.product_name 
        FROM ddm_order o 
        LEFT JOIN ddm_product p ON o.product_id = p.product_id 
        WHERE o.user_id = '$safe_session_id' 
        ORDER BY o.order_id DESC";
$result = mysqli_query($conn, $sql);
?>

<div class="container my-5" style="min-height: 70vh;">
    <div class="card border-0 shadow-sm p-4" style="border-radius: 16px;">
        <h4 class="fw-bold text-dark mb-4 border-start border-4 border-indigo ps-2" style="border-color: #6f42c1 !important;">
            📜 전체 주문 내역
        </h4>
        
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead style="background-color: #fcfbff; color: #6f42c1;">
                    <tr>
                        <th>주문번호</th><th>상품명</th><th>결제금액</th><th>주문일자</th><th>상태</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($result && mysqli_num_rows($result) > 0) {
                        while ($row = mysqli_fetch_assoc($result)) {
                    ?>
                    <tr onclick="location.href='order_view.php?order_id=<?=$row['order_id']?>'" style="cursor:pointer;">
                        <td class="fw-bold">#<?=$row['order_id']?></td>
                        <td><?=htmlspecialchars($row['product_name'] ?? '상품정보없음')?></td>
                        <td class="text-danger fw-bold"><?=number_format($row['order_price'] ?? 0)?>원</td>
                        <td class="text-muted"><?=isset($row['order_date']) ? substr($row['order_date'], 0, 10) : '날짜없음'?></td>
                        <td>
                            <span class="badge bg-primary"><?=$row['order_status'] ?? '배송준비중'?></span>
                        </td>
                    </tr>
                    <?php
                        }
                    } else {
                        echo '<tr><td colspan="5" class="text-center py-5">주문 내역이 하나도 없어요, 왈! 🐾</td></tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>
        <div class="mt-3">
            <a href="mypage.php" class="btn btn-outline-secondary">마이페이지로 돌아가기</a>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>