<?php
// mypage.php
include_once 'header.php';

if (!isset($_SESSION['user_id'])) {
    echo "<script>location.href='login.php';</script>"; exit;
}
$user_id = $_SESSION['user_id'];
$result = mysqli_query($conn, "SELECT * FROM ddm_order WHERE user_id='$user_id' ORDER BY order_date DESC");
?>
<h2>📋 마이페이지 보급 신청 이력</h2>
<table style="width:100%; border-collapse:collapse; margin-top:15px;">
    <tr style="background:#f1f3f5; text-align:center;">
        <th style="padding:10px;">요청일시</th>
        <th style="padding:10px;">주문 고유코드</th>
        <th style="padding:10px;">총 합산액</th>
        <th style="padding:10px;">배송상황</th>
        <th style="padding:10px;">명세</th>
    </tr>
    <?php
    while($row = mysqli_fetch_assoc($result)) {
        echo "<tr style='border-bottom:1px solid #eee; text-align:center;'>
                <td style='padding:12px;'>{$row['order_date']}</td>
                <td style='padding:12px;'>{$row['order_id']}</td>
                <td style='padding:12px;'>".number_format($row['order_total_price'])."원</td>
                <td style='padding:12px;'>{$row['order_status']}</td>
                <td style='padding:12px;'><a href='order_view.php?order_id={$row['order_id']}'>[조회]</a></td>
              </tr>";
    }
    ?>
</table>
<?php include_once 'footer.php'; ?>

<?php
// order_view.php
include_once 'header.php';

if (!isset($_SESSION['user_id'])) { exit; }

$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;

// [취약점 유도] IDOR 설계 구역 (AND user_id='$_SESSION[user_id]' 누락)
$order_info = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM ddm_order WHERE order_id=$order_id"));

if (!$order_info) {
    echo "<script>alert('열람 불가 명세입니다.'); history.back();</script>"; exit;
}
?>
<h2>📄 주문 정보 명세서 (IDOR 노출 테스트 구역)</h2>
<div style="background:#f8f9fa; padding:15px; border-radius:4px; margin-bottom:15px;">
    <p><strong>신청계정 명의자:</strong> <?php echo htmlspecialchars($order_info['user_id']); ?></p>
    <p><strong>인프라 고유코드:</strong> <?php echo $order_info['order_id']; ?></p>
    <p><strong>배송 관제 현황:</strong> <?php echo htmlspecialchars($order_info['order_status']); ?></p>
</div>
<table style="width:100%; border-collapse:collapse;">
    <tr style="background:#e9ecef; text-align:left;"><th style="padding:8px;">물품명</th><th style="padding:8px;">수량</th></tr>
    <?php
    $res = mysqli_query($conn, "SELECT od.*, p.product_name FROM ddm_order_detail od JOIN ddm_product p ON od.product_id=p.product_id WHERE od.order_id=$order_id");
    while($item = mysqli_fetch_assoc($res)) {
        echo "<tr style='border-bottom:1px solid #eee;'><td style='padding:10px;'>".htmlspecialchars($item['product_name'])."</td><td style='padding:10px;'>{$item['detail_quantity']}개</td></tr>";
    }
    ?>
</table>
<?php include_once 'footer.php'; ?>

