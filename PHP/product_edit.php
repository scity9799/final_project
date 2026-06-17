<?php include 'db.php'; include 'header.php';
// 관리자 권한 검증
if ($_SESSION['user_type'] !== 'A') {
    die("<script>alert('관리자만 접근 가능합니다.'); location.href='main.php';</script>");
}

$p_no = $_GET['p_no'];
$query = "SELECT * FROM product WHERE product_number = '$p_no'";
$product = $conn->query($query)->fetch_assoc();
?>
<h2 class="text-purple">상품 수정</h2>
<form action="product_edit_proc.php" method="POST" enctype="multipart/form-data">
    <input type="hidden" name="p_no" value="<?= $p_no ?>">
    <input type="text" name="name" value="<?= htmlspecialchars($product['product_name']) ?>" class="form-control mb-2">
    <input type="number" name="price" value="<?= $product['price'] ?>" class="form-control mb-2">
    <p>기존 이미지: <?= $product['img_path'] ?> <a href="uploads/<?= $product['img_path'] ?>" download>다운로드</a></p>
    <input type="file" name="product_img" class="form-control mb-2">
    <button type="submit" class="btn btn-purple text-white">수정하기</button>
</form>
<?php include 'footer.php'; ?>