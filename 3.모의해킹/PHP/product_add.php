<?php
include 'db.php';
include 'header.php';

// 관리자만 접근 가능하도록 설정
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'a') {
    echo "<script>alert('관리자만 접근 가능합니다.'); location.href='main.php';</script>";
    exit;
}
?>

<div class="container my-5" style="max-width: 600px;">
    <div class="card border-0 shadow-sm p-4" style="border-radius: 16px;">
        <h3 class="fw-bold mb-4 text-center">상품 등록 🐾</h3>
        
        <form action="product_add_proc.php" method="POST" enctype="multipart/form-data">
            
            <div class="mb-3">
                <label class="form-label fw-bold text-secondary">상품명</label>
                <input type="text" name="product_name" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold text-secondary">카테고리</label>
                <select name="product_category" class="form-select">
                    <option value="feed">사료/간식</option>
                    <option value="item">애견 용품</option>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold text-secondary">가격</label>
                <input type="number" name="product_price" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold text-secondary">상품 이미지</label>
                <input type="file" name="product_image" class="form-control" accept="image/*" required>
            </div>

            <div class="mb-4">
                <label class="form-label fw-bold text-secondary">상세 설명</label>
                <textarea name="product_description" class="form-control" rows="5" required></textarea>
            </div>

            <button type="submit" class="btn text-white w-100 py-3 fw-bold" style="background-color: #6f42c1; border-radius: 12px;">
                등록하기 🦴
            </button>
        </form>
    </div>
</div>

<?php include 'footer.php'; ?>