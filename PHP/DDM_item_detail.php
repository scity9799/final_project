<?php
// shop_detail.php
include_once 'header.php';

if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('로그인이 필요합니다.'); location.href='login.php';</script>";
    exit;
}

$product_id = isset($_GET['product_id']) ? intval($_GET['product_id']) : 0;

$query = "SELECT p.*, f.file_save_name FROM ddm_product p 
          LEFT JOIN ddm_file f ON p.product_id = f.product_id 
          WHERE p.product_id = $product_id";
$result = mysqli_query($conn, $query);
$product = mysqli_fetch_assoc($result);

if (!$product) {
    echo "<script>alert('매칭 데이터가 존재하지 않습니다.'); history.back();</script>";
    exit;
}
?>

<div style="display: flex; gap: 40px; margin-top: 20px;">
    <div style="width: 350px; height: 350px; background: #e9ecef; display: flex; align-items: center; justify-content: center; border-radius: 8px;">
        <?php if (!empty($product['file_save_name'])): ?>
            <img src="uploads/<?php echo htmlspecialchars($product['file_save_name']); ?>" style="width: 100%; height: 100%; object-fit: cover; border-radius: 8px;">
        <?php else: ?>
            <span style="color: #aaa;">No Image</span>
        <?php endif; ?>
    </div>

    <div style="flex: 1; display: flex; flex-direction: column; justify-content: space-between;">
        <div>
            <span style="background: #20c997; color: white; padding: 3px 6px; border-radius: 4px; font-size: 13px;"><?php echo htmlspecialchars($product['product_category']); ?></span>
            <h2 style="margin: 10px 0;"><?php echo htmlspecialchars($product['product_name']); ?></h2>
            <p style="font-size: 22px; color: #ff6b6b; font-weight: bold;"><?php echo number_format($product['product_price']); ?> 원</p>
            <p style="color: #666; line-height: 1.5; background:#f8f9fa; padding:15px; border-radius:6px;"><?php echo nl2br(htmlspecialchars($product['product_description'] ?? '')); ?></p>
        </div>

        <div style="background: #e9ecef; padding: 15px; border-radius: 6px;">
            <form id="order_form" method="POST" action="">
                <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                    <label style="font-weight: bold;">신청 수량</label>
                    <input type="number" name="quantity" value="1" min="1" style="width: 60px; padding: 6px; text-align: center;">
                </div>
                <div style="display: flex; gap: 10px;">
                    <button type="button" onclick="formSubmit('cart_add_proc.php')" style="flex: 1; background: #495057; color: white; border: none; padding: 12px; font-weight: bold; cursor: pointer; border-radius:4px;">🛒 카트 담기</button>
                    <button type="button" onclick="formSubmit('order_direct_proc.php')" style="flex: 1; background: #e03131; color: white; border: none; padding: 12px; font-weight: bold; cursor: pointer; border-radius:4px;">⚡ 바로 구매</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function formSubmit(actionPath) {
    const f = document.getElementById('order_form');
    f.action = actionPath;
    f.submit();
}
</script>

<?php include_once 'footer.php'; ?>

<?php
// cart_add_proc.php
include_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('로그인이 필요합니다.'); location.href='login.php';</script>";
    exit;
}

$user_id    = $_SESSION['user_id'];
$product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
$quantity   = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;

// [취약점 유도] Anti-CSRF 토큰 누락 구역
$chk = mysqli_query($conn, "SELECT * FROM ddm_cart WHERE user_id='$user_id' AND product_id=$product_id");

if (mysqli_num_rows($chk) > 0) {
    mysqli_query($conn, "UPDATE ddm_cart SET cart_quantity=cart_quantity+$quantity WHERE user_id='$user_id' AND product_id=$product_id");
} else {
    mysqli_query($conn, "INSERT INTO ddm_cart (user_id, product_id, cart_quantity) VALUES ('$user_id', $product_id, $quantity)");
}

echo "<script>if(confirm('장바구니에 적재되었습니다. 이동하시겠습니까?')) { location.href='cart.php'; } else { history.back(); }</script>";
mysqli_close($conn);
?>

<?php
// order_direct_proc.php
include_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('로그인이 필수입니다.'); location.href='login.php';</script>";
    exit;
}

$user_id    = $_SESSION['user_id'];
// [취약점 유도] REQUEST 지정을 통한 GET 방식 링크 공격 승인 허용
$product_id = isset($_REQUEST['product_id']) ? intval($_REQUEST['product_id']) : 0;
$quantity   = isset($_REQUEST['quantity']) ? intval($_REQUEST['quantity']) : 1;

$prod = mysqli_fetch_assoc(mysqli_query($conn, "SELECT product_price FROM ddm_product WHERE product_id=$product_id"));
if (!$prod) { exit('상품 에러'); }

$tot = $prod['product_price'] * $quantity;

if (mysqli_query($conn, "INSERT INTO ddm_order (user_id, order_total_price, order_status) VALUES ('$user_id', $tot, '주문완료')")) {
    $order_id = mysqli_insert_id($conn);
    mysqli_query($conn, "INSERT INTO ddm_order_detail (order_id, product_id, detail_quantity, detail_price) VALUES ($order_id, $product_id, $quantity, {$prod['product_price']})");
    echo "<script>alert('⚡ [CSRF 타겟 작동] 원클릭 직통 주문이 완료되었습니다.'); location.href='mypage.php';</script>";
}
mysqli_close($conn);
?>