<?php
include 'db.php';     
include 'header.php'; 

$product_id = $_GET['id'] ?? '';

if (empty($product_id)) {
    echo "<script>alert('올바르지 않은 접근입니다, 왈!'); location.href='shop_list.php';</script>";
    exit;
}

$safe_product_id = mysqli_real_escape_string($conn, $product_id);
$query = "SELECT * FROM ddm_product WHERE product_id = '$safe_product_id'";
$result = mysqli_query($conn, $query);

if ($result && mysqli_num_rows($result) > 0) {
    $product = mysqli_fetch_assoc($result);
    $price = isset($product['product_price']) ? (int)$product['product_price'] : 0;
} else {
    echo "<script>alert('❌ 존재하지 않는 상품입니다, 왈!'); location.href='shop_list.php';</script>";
    exit;
}
?>

<div class="container my-5">
    <div class="mb-4">
        <a href="shop_list.php" class="text-decoration-none text-muted small fw-bold">⬅️ 상품 목록으로 돌아가기</a>
    </div>

    <div class="row g-5">
        <div class="col-md-6 text-center">
            <div class="card border-0 shadow-sm p-3" style="border-radius: 15px;">
                <img src="<?php echo htmlspecialchars($product['product_image'] ?? ''); ?>" 
                     onerror="this.src='https://via.placeholder.com/500x500/f3f0ff/6f42c1?text=DingDongDog'" 
                     class="img-fluid" style="border-radius: 12px; max-height: 500px; object-fit: cover;" alt="이미지 준비중">
            </div>
        </div>

        <div class="col-md-6">
            <div class="p-2">
                <span class="badge mb-2 text-white px-3 py-2" style="background-color: #6f42c1; border-radius: 20px;">
                    <?php echo ($product['product_category'] ?? '') === 'feed' ? '🦴 사료/간식' : '🧼 애견 용품'; ?>
                </span>
                
                <h2 class="fw-bold mb-3 text-dark"><?php echo htmlspecialchars($product['product_name'] ?? '상품명 없음'); ?></h2>
                
                <h3 class="fw-bold mb-4" style="color: #6f42c1;">
                    <?php echo number_format($price); ?><span class="small fs-5 text-secondary"> 원</span>
                </h3>
                
                <hr class="text-muted mb-4">

                <div class="mb-4">
                    <h6 class="fw-bold text-secondary mb-2">🐾 상품 요약 설명</h6>
                    <p class="text-muted lh-lg"><?php echo nl2br(htmlspecialchars($product['product_description'] ?? '')); ?></p>
                </div>

                <form method="POST" id="product_form">
                    <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($product_id); ?>">
                    <input type="hidden" name="product_name" value="<?php echo htmlspecialchars($product['product_name'] ?? ''); ?>">
                    <input type="hidden" name="product_price" value="<?php echo $price; ?>">
                    
                    <input type="hidden" name="total_amount" id="hidden_total_amount" value="<?php echo $price; ?>">

                    <div class="row align-items-center mb-3">
                        <div class="col-4"><label class="form-label text-secondary small fw-bold m-0">수량 선택</label></div>
                        <div class="col-8">
                            <input type="number" class="form-control" id="quantity" name="product_count" value="1" min="1" max="99" style="max-width: 100px; border-radius: 8px;">
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-4 px-2">
                        <span class="fw-bold text-secondary">최종 합계 금액</span>
                        <span class="fw-bold fs-3 text-dark" id="total_price_display">
                            <?php echo number_format($price); ?>원
                        </span>
                    </div>

                    <div class="row g-2">
                        <?php if (isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'a'): ?>
                            <div class="col-sm-6">
                                <a href="product_edit.php?id=<?=htmlspecialchars($product_id)?>" class="btn btn-outline-warning w-100 p-3 fw-bold" style="border-radius: 10px;">✏️ 상품 수정</a>
                            </div>
                            <div class="col-sm-6">
                                <a href="product_delete_proc.php?id=<?=htmlspecialchars($product_id)?>" class="btn btn-danger w-100 p-3 fw-bold" style="border-radius: 10px;" onclick="return confirm('정말 삭제하시겠습니까?');">🗑️ 상품 삭제</a>
                            </div>
                        <?php else: ?>
                            <div class="col-sm-6">
                                <button type="submit" formaction="cart_add.php" class="btn btn-outline-dark w-100 p-3 fw-bold" style="border-radius: 10px; border-color: #6f42c1; color: #6f42c1;">🛒 장바구니 담기</button>
                            </div>
                            <div class="col-sm-6">
                                <button type="submit" formaction="order_direct_proc.php" class="btn text-white w-100 p-3 fw-bold" style="background-color: #6f42c1; border-radius: 10px;">⚡ 즉시 결제하기</button>
                            </div>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
const pricePerItem = <?php echo $price; ?>;
const qtyInput = document.getElementById('quantity');
const totalDisplay = document.getElementById('total_price_display');
const hiddenTotalAmount = document.getElementById('hidden_total_amount');

qtyInput.addEventListener('input', function() {
    let qty = parseInt(this.value);
    if(isNaN(qty) || qty < 1) qty = 1;
    
    const totalPrice = pricePerItem * qty;
    
    totalDisplay.innerText = totalPrice.toLocaleString() + '원';
    
    // 주입되는 hidden 파라미터도 변수 구조에 맞게 실시간 업데이트!
    hiddenTotalAmount.value = totalPrice;
});
</script>

<?php include 'footer.php'; ?>