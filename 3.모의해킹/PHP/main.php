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
// DB 연동 수정: ddm_product와 ddm_file 테이블을 JOIN하여 이미지 경로를 가져옴
$query = "SELECT p.product_id, p.product_name, p.product_price, f.file_path 
          FROM ddm_product p 
          LEFT JOIN ddm_file f ON p.product_id = f.product_id 
          GROUP BY p.product_id
          ORDER BY RAND() LIMIT 8";

$result = mysqli_query($conn, $query);

if ($result && mysqli_num_rows($result) > 0) {
    while($row = mysqli_fetch_assoc($result)) {
        // 이미지 경로가 없으면 기본 이미지(no_image.png) 표시
        $img_src = !empty($row['file_path']) ? $row['file_path'] : 'images/no_image.png';
        
        echo "<div class='col'><div class='card h-100 p-3'>";
        echo "<img src='".$img_src."' class='card-img-top' alt='상품이미지' style='height: 200px; object-fit: cover;'>";
        echo "<div class='card-body'>";
        echo "<h5><a href='shop_detail.php?id=".$row['product_id']."'>".htmlspecialchars($row['product_name'])."</a></h5>";
        echo "<p class='text-danger'>".number_format($row['product_price'])."원</p>";
        echo "</div></div></div>";
    }
} else {
    echo "<p>상품이 등록되지 않았거나 DB를 불러올 수 없습니다. 테이블명과 조인 설정을 확인하세요.</p>";
}
?>
</div>

<?php include 'footer.php'; ?>