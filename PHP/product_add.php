<?php include 'db.php'; include 'header.php';
// 관리자 권한 검증 (간략화)
if ($_SESSION['user_type'] !== 'A') {
    die("<script>alert('관리자만 접근 가능합니다.'); location.href='main.php';</script>");
}
?>
<h2 class="text-purple">상품 등록</h2>
<form action="product_add_proc.php" method="POST" enctype="multipart/form-data">
    <input type="text" name="name" class="form-control mb-2" placeholder="상품명" required>
    <input type="number" name="price" class="form-control mb-2" placeholder="가격" required>
    <input type="file" name="product_img" class="form-control mb-2" required>
    <button type="submit" class="btn btn-purple text-white">등록</button>
</form>
<?php include 'footer.php'; ?>