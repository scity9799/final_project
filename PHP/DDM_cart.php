<?php
// cart.php
include_once 'header.php';

if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('로그인이 필요합니다.'); location.href='login.php';</script>";
    exit;
}
$user_id = $_SESSION['user_id'];
$result = mysqli_query($conn, "SELECT c.cart_id, c.cart_quantity, p.product_id, p.product_name, p.product_price FROM ddm_cart c JOIN ddm_product p ON c.product_id=p.product_id WHERE c.user_id='$user_id'");
?>

<h2>🛒 장바구니 보급 현황</h2>
<form action="cart_order_proc.php" method="POST">
<table style="width: 100%; border-collapse: collapse;">
    <tr style="background:#e9ecef;">
        <th style="padding:10px;"><input type="checkbox" checked onclick="allCheck(this)"></th>
        <th style="padding:10px; text-align:left;">용품명</th>
        <th style="padding:10px;">수량</th>
        <th style="padding:10px;">금액</th>
        <th style="padding:10px;">관리</th>
    </tr>
    <?php
    $total = 0;
    while($row = mysqli_fetch_assoc($result)) {
        $sub = $row['product_price'] * $row['cart_quantity'];
        $total += $sub;
        echo "<tr style='border-bottom:1px solid #eee; text-align:center;'>
                <td style='padding:10px;'><input type='checkbox' name='cart_ids[]' value='{$row['cart_id']}' class='chk' checked></td>
                <td style='padding:10px; text-align:left;'>".htmlspecialchars($row['product_name'])."</td>
                <td style='padding:10px;'>{$row['cart_quantity']}개</td>
                <td style='padding:10px;'>".number_format($sub)."원</td>
                <td style='padding:10px;'><a href='cart_delete_proc.php?cart_id={$row['cart_id']}' style='color:red;'>[삭제]</a></td>
              </tr>";
    }
    ?>
</table>
<?php if($total > 0): ?>
    <div style="text-align:right; margin:20px 0;"><strong>합계 금액: <?php echo number_format($total); ?>원</strong></div>
    <div style="text-align:center;"><button type="submit" style="background:#228be6; color:white; padding:12px 30px; border:none; cursor:pointer; border-radius:4px;">선택 물품 일괄 보급 신청</button></div>
<?php endif; ?>
</form>
<script>
function allCheck(master) {
    let chks = document.getElementsByClassName('chk');
    for(let i=0; i<chks.length; i++) chks[i].checked = master.checked;
}
</script>
<?php include_once 'footer.php'; ?>

<?php
// cart_delete_proc.php
include_once 'db.php';

if (!isset($_SESSION['user_id'])) { exit; }

$cart_id = isset($_GET['cart_id']) ? intval($_GET['cart_id']) : 0;

// [취약점 유도] IDOR 취약 구역 (소유주 크로스체크 누락)
$query = "DELETE FROM ddm_cart WHERE cart_id = $cart_id";

if (mysqli_query($conn, $query)) {
    echo "<script>alert('장바구니에서 소거되었습니다.'); location.href='cart.php';</script>";
}
mysqli_close($conn);
?>

<?php
// cart_order_proc.php
include_once 'db.php';

if (!isset($_SESSION['user_id']) || empty($_POST['cart_ids'])) {
    echo "<script>alert('잘못된 접근입니다.'); location.href='cart.php';</script>";
    exit;
}

$user_id = $_SESSION['user_id'];
$id_list = implode(',', array_map('intval', $_POST['cart_ids']));

$sum_row = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(p.product_price * c.cart_quantity) AS tot FROM ddm_cart c JOIN ddm_product p ON c.product_id=p.product_id WHERE c.cart_id IN ($id_list)"));
$total_price = $sum_row['tot'] ?? 0;

if (mysqli_query($conn, "INSERT INTO ddm_order (user_id, order_total_price, order_status) VALUES ('$user_id', $total_price, '주문완료')")) {
    $order_id = mysqli_insert_id($conn);
    $res = mysqli_query($conn, "SELECT c.product_id, c.cart_quantity, p.product_price FROM ddm_cart c JOIN ddm_product p ON c.product_id=p.product_id WHERE c.cart_id IN ($id_list)");
    while ($item = mysqli_fetch_assoc($res)) {
        mysqli_query($conn, "INSERT INTO ddm_order_detail (order_id, product_id, detail_quantity, detail_price) VALUES ($order_id, {$item['product_id']}, {$item['cart_quantity']}, {$item['product_price']})");
    }
    mysqli_query($conn, "DELETE FROM ddm_cart WHERE cart_id IN ($id_list)");
    echo "<script>alert('장바구니 물품 일괄 승인 완료'); location.href='mypage.php';</script>";
}
mysqli_close($conn);
?>

