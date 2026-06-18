<?php
// main.php : 메인 랜딩 대시보드
include 'header.php'; 
include 'db.php';
?>

<div class="container mt-4">
    <div class="mt-4 p-3 border rounded mx-auto" style="max-width: 600px; background-color: #f3f0ff;">
        <form action="shop_list.php" method="GET" class="d-flex">
            <input type="text" name="search" class="form-control me-2" placeholder="상품명을 입력하세요 (예: 사료, 쿨매트)" aria-label="Search">
            <button class="btn" type="submit" style="background-color: #6f42c1; color: white;">🔍</button>
        </form>
    </div>
</div>

<h3 class="mt-4 mb-3">🐾 오늘의 추천 상품</h3>
<div class="row row-cols-1 row-cols-md-4 g-4">
<?php
// 별도의 파일 테이블 없이 ddm_product 테이블에서 이미지(product_image)를 바로 조회
$query = "SELECT product_id, product_name, product_price, product_image 
          FROM ddm_product 
          ORDER BY RAND() LIMIT 8";

$result = mysqli_query($conn, $query);

if ($result && mysqli_num_rows($result) > 0) {
    while($row = mysqli_fetch_assoc($result)) {
        // DB 내부의 product_image 값이 비어있지 않다면 해당 경로를 쓰고, 비어있으면 기본 이미지 표시
        $img_src = !empty($row['product_image']) ? $row['product_image'] : 'images/no_image.png';
        
        // 1. <a> 태그를 카드 맨 바깥에 배치하여 그리드 자체를 클릭 가능하게 변경
        // 2. text-decoration-none과 text-dark를 주어 상품명과 카드 내부 링크의 밑줄 및 파란 글씨 제거
        echo "<div class='col'>";
        echo "  <a href='shop_detail.php?id=".$row['product_id']."' class='text-decoration-none text-dark'>";
        echo "    <div class='card h-100 p-3 shadow-sm' style='transition: transform 0.2s; cursor: pointer;'>";
        
        // 3. w-100과 img-fluid를 주어 그리드 상단 박스 크기에 이미지가 꽉 맞물리도록 커스텀
        echo "      <img src='".htmlspecialchars($img_src)."' class='card-img-top img-fluid w-100' alt='상품이미지' style='height: 220px; object-fit: cover;'>";
        
        echo "      <div class='card-body px-0 pb-0'>";
        echo "        <h5 class='card-title fw-bold mb-2'>".htmlspecialchars($row['product_name'])."</h5>";
        echo "        <p class='card-text text-danger fw-bold m-0'>".number_format($row['product_price'])."원</p>";
        echo "      </div>";
        echo "    </div>";
        echo "  </a>";
        echo "</div>";
    }
} else {
    echo "<p>상품이 등록되지 않았거나 DB를 불러올 수 없습니다. 테이블명과 컬럼 설정을 확인하세요.</p>";
}
?>
</div>

<?php include 'footer.php'; ?>