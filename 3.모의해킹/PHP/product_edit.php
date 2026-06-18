<?php
include 'db.php';
include 'header.php';

$product_id = isset($_GET['id']) ? $_GET['id'] : '';

// 수정할 기존 상품 데이터 가져오기
$sql = "SELECT * FROM ddm_product WHERE product_id = $product_id";
$result = mysqli_query($conn, $sql);
$product = mysqli_fetch_assoc($result);

if (!$product) {
    echo "<script>alert('존재하지 않는 상품입니다.'); history.back();</script>";
    exit;
}
?>

<div class="container my-5" style="min-height: 75vh;">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm p-4" style="border-radius: 16px;">
                <h4 class="fw-bold text-dark mb-4 border-start border-4 border-indigo ps-2" style="border-color: #6f42c1 !important;">
                    🛠️ 상품 정보 수정 (관리자용)
                </h4>
                
                <form action="product_edit_proc.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="product_id" value="<?=$product['product_id']?>">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted">카테고리</label>
                            <select name="product_category" class="form-select border-muted" style="border-radius: 8px;">
                                <option value="feed" <?=$product['product_category'] == 'feed' ? 'selected' : ''?>>사료/간식</option>
                                <option value="item" <?=$product['product_category'] == 'item' ? 'selected' : ''?>>애견 용품</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted">상품 가격 (원)</label>
                            <input type="number" name="product_price" class="form-control border-muted" value="<?=$product['product_price']?>" style="border-radius: 8px;" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold text-muted">상품 이름</label>
                            <input type="text" name="product_name" class="form-control border-muted" value="<?=htmlspecialchars($product['product_name'])?>" style="border-radius: 8px;" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold text-muted">상품 요약 설명</label>
                            <input type="text" name="product_description" class="form-control border-muted" value="<?=htmlspecialchars($product['product_description'])?>" style="border-radius: 8px;" required>
                        </div>

                        <div class="col-12">
                            				<label class="form-label small fw-bold text-muted">현재 이미지</label>
				<div class="mb-3 d-flex align-items-center gap-3">
    				<img src="<?=$product['product_image']?>" alt="상품 이미지" style="width: 100px; height: 100px; object-fit: cover; border-radius: 8px;">
    
    				<a href="<?=$product['product_image']?>" 
 				 class="btn btn-sm btn-outline-secondary" 
      				 download="<?=$product['product_name'] . '_image'?>">
       				 ⬇️ 이미지 다운로드
   				</a>
				</div>
                            <label class="form-label small fw-bold text-muted">변경할 이미지 (선택)</label>
                            <input type="file" name="product_image" class="form-control border-muted" style="border-radius: 8px;">
                        </div>
                        <div class="col-12 text-end mt-4">
                            <button type="button" class="btn btn-light me-2" onclick="history.back();" style="border-radius: 8px;">취소</button>
                            <button type="submit" class="btn text-white px-4" style="background-color: #6f42c1; border-radius: 8px;">
                                수정 완료하기 🦴
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>