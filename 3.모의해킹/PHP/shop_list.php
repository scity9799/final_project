<?php
include 'db.php'; 
include 'header.php'; 

// 1. 파라미터 받기 (보안을 위해 escape 처리 추가)
$limit = 8;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$category = isset($_GET['category']) ? mysqli_real_escape_string($conn, $_GET['category']) : '';
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';

// 2. 조건절(WHERE) 동적 생성
$where_clauses = [];
if (!empty($category)) {
    $where_clauses[] = "product_category = '$category'";
}
if (!empty($search)) {
    $where_clauses[] = "product_name LIKE '%$search%'";
}

$where_sql = "";
if (count($where_clauses) > 0) {
    $where_sql = " WHERE " . implode(" AND ", $where_clauses);
}

// 3. 총 개수 조회
$count_query = "SELECT COUNT(*) as total FROM ddm_product" . $where_sql;
$count_result = mysqli_query($conn, $count_query);
$count_row = mysqli_fetch_assoc($count_result);
$total_products = $count_row['total'];
$total_pages = ceil($total_products / $limit);

// 4. 상품 데이터 조회
$sql = "SELECT * FROM ddm_product" . $where_sql . " ORDER BY product_id DESC LIMIT $offset, $limit";
$result = mysqli_query($conn, $sql);
?>

<div class="container my-5" style="min-height: 70vh;">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold border-start border-4 border-indigo ps-2" style="border-color: #6f42c1 !important;">
            <?php 
                if($category == 'feed') echo "🐶 사료/간식";
                elseif($category == 'item') echo "✨ 애견 용품";
                else echo "🛍️ 딩동몰 상품 게시판";
            ?>
        </h3>
        <form action="shop_list.php" method="GET" class="d-flex">
            <input type="hidden" name="cate" value="<?=$category?>">
            <input type="text" name="search" class="form-control me-2" placeholder="상품명 검색" value="<?=$search?>">
            <button type="submit" class="btn btn-outline-indigo">🔍</button>
        </form>
    </div>

    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4">
        <?php
        if (mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $img_path = !empty($row['product_image']) ? $row['product_image'] : 'https://via.placeholder.com/300x300/f3f0ff/6f42c1?text=DingDongMall';
        ?>
            <div class="col">
                <div class="card h-100 shadow-sm border-0">
                    <a href="shop_detail.php?id=<?=$row['product_id']?>"><img src="<?=$img_path?>" class="card-img-top" style="height: 220px; object-fit: cover;"></a>
                    <div class="card-body">
                        <h5 class="card-title fw-bold text-dark"><?=htmlspecialchars($row['product_name'])?></h5>
                        <p class="text-danger fw-bold"><?=number_format($row['product_price'])?>원</p>
                    </div>
                </div>
            </div>
        <?php } } else { echo '<div class="col-12 text-center my-5">등록된 상품이 없습니다.</div>'; } ?>
    </div>

    <?php if ($total_pages > 1): ?>
    <nav class="mt-5">
        <ul class="pagination justify-content-center">
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <li class="page-item <?=$page == $i ? 'active' : ''?>">
                    <a class="page-link" href="?cate=<?=$category?>&search=<?=$search?>&page=<?=$i?>"><?=$i?></a>
                </li>
            <?php endfor; ?>
        </ul>
    </nav>
    <?php endif; ?>
</div>

<?php include 'footer.php'; ?>