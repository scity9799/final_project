<?php include 'db.php'; include 'header.php';

$p_no = $_GET['p_no'] ?? 0;
$query = "SELECT * FROM product WHERE product_number = '$p_no'";
$result = $conn->query($query);
$product = $result->fetch_assoc();
?>

<div class="row">
    <div class="col-md-6">
        <div class="p-5 bg-light">이미지 영역</div>
    </div>
    <div class="col-md-6">
        <h2><?= htmlspecialchars($product['product_name']) ?></h2>
        <p class="h4"><?= number_format($product['price']) ?>원</p>
        
        <form action="cart_add_proc.php" method="POST">
            <input type="hidden" name="p_no" value="<?= $p_no ?>">
            <input type="number" name="qty" value="1" min="1" class="form-control mb-2" style="width: 100px;">
            <button type="submit" class="btn btn-outline-purple">장바구니 담기</button>
            <a href="order_direct_proc.php?p_no=<?= $p_no ?>" class="btn btn-purple text-white">바로 구매</a>
        </form>
    </div>
</div>

<?php include 'footer.php'; ?>