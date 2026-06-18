<?php
include 'db.php'; 
include 'header.php'; 

// 1. 파라미터 받기 (GET 변수명을 축약 없이 'category'로 명확히 바인딩!)
$limit = 8;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$category = isset($_GET['category']) ? mysqli_real_escape_string($conn, $_GET['category']) : '';
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'id_desc'; // 기본정렬: 최신순

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

// 3. 가격순 정렬(ORDER BY) 분기 로직
switch ($sort) {
    case 'price_asc':
        $order_sql = " ORDER BY product_price ASC";
        break;
    case 'price_desc':
        $order_sql = " ORDER BY product_price DESC";
        break;
    case 'id_desc':
    default:
        $order_sql = " ORDER BY product_id DESC";
        break;
}

// 4. 총 개수 조회
$count_query = "SELECT COUNT(*) as total FROM ddm_product" . $where_sql;
$count_result = mysqli_query($conn, $count_query);
$count_row = mysqli_fetch_assoc($count_result);
$total_products = $count_row['total'];
$total_pages = ceil($total_products / $limit);

// 5. 상품 데이터 조회 (정렬 문법 $order_sql 바인딩)
$sql = "SELECT * FROM ddm_product" . $where_sql . $order_sql . " LIMIT $offset, $limit";
$result = mysqli_query($conn, $sql);
?>

<div class="container my-5" style="min-height: 70vh;">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="fw-bold border-start border-4 border-indigo ps-2" style="border-left: 4px solid #6f42c1 !important;">
            <?php 
                if($category == 'feed') echo "🐶 사료/간식";
                elseif($category == 'item') echo "✨ 애견 용품";
                else echo "🛍️ 딩동몰 상품 게시판";
            ?>
        </h3>
        <form action="shop_list.php" method="GET" class="d-flex">
            <input type="hidden" name="category" value="<?=htmlspecialchars($category)?>">
            <input type="hidden" name="sort" value="<?=htmlspecialchars($sort)?>">
            <input type="text" name="search" class="form-control me-2" placeholder="상품명 검색" value="<?=htmlspecialchars($search)?>">
            <button type="submit" class="btn btn-outline-indigo" style="color: #6f42c1; border-color: #6f42c1;">🔍</button>
        </form>
    </div>

    <div class="d-flex justify-content-end mb-4 border-bottom pb-2">
        <div class="btn-group btn-group-sm" role="group" aria-label="Sort Filter">
            <a href="?category=<?=urlencode($category)?>&search=<?=urlencode($search)?>&sort=id_desc" 
               class="btn <?=($sort == 'id_desc') ? 'btn-indigo text-white' : 'btn-outline-secondary'?>" style="<?=($sort == 'id_desc') ? 'background-color: #6f42c1;' : ''?>">⏱️ 최신순</a>
            <a href="?category=<?=urlencode($category)?>&search=<?=urlencode($search)?>&sort=price_asc" 
               class="btn <?=($sort == 'price_asc') ? 'btn-indigo text-white' : 'btn-outline-secondary'?>" style="<?=($sort == 'price_asc') ? 'background-color: #6f42c1;' : ''?>">📉 가격 낮은순</a>
            <a href="?category=<?=urlencode($category)?>&search=<?=urlencode($search)?>&sort=price_desc" 
               class="btn <?=($sort == 'price_desc') ? 'btn-indigo text-white' : 'btn-outline-secondary'?>" style="<?=($sort == 'price_desc') ? 'background-color: #6f42c1;' : ''?>">📈 가격 높은순</a>
        </div>
    </div>

    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4">
        <?php
        if (mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $img_path = !empty($row['product_image']) ? $row['product_image'] : 'images/no_image.png';
        ?>
            <div class="col">
                <a href="shop_detail.php?id=<?=$row['product_id']?>" class="text-decoration-none text-dark">
                    <div class="card h-100 shadow-sm border-0 p-2" style="cursor: pointer;">
                        <img src="<?=htmlspecialchars($img_path)?>" onerror="this.src='images/no_image.png'" class="card-img-top img-fluid w-100" style="height: 220px; object-fit: cover;" alt="상품이미지">
                        <div class="card-body px-1 pb-1">
                            <h5 class="card-title fw-bold text-dark mb-2"><?=htmlspecialchars($row['product_name'])?></h5>
                            <p class="text-danger fw-bold m-0"><?=number_format($row['product_price'])?>원</p>
                        </div>
                    </div>
                </a>
            </div>
        <?php 
            } 
        } else { 
            echo '<div class="col-12 text-center my-5">등록된 상품이 없습니다.</div>'; 
        } 
        ?>
    </div>

    <?php if ($total_pages > 1): ?>
    <nav aria-label="Page navigation">
        <ul class="pagination justify-content-center mt-4">
            <?php
            for ($i = 1; $i <= $total_pages; $i++) {
                $active_class = ($i == $page) ? 'active' : '';
                
                // 💡 [페이징 링크 조립] &category= 명칭으로 일체화하여 정렬/검색 데이터 증발 현상 완벽 방어!
                $page_link = "?page=" . $i;
                if (!empty($category)) {
                    $page_link .= "&category=" . urlencode($category);
                }
                if (!empty($search)) {
                    $page_link .= "&search=" . urlencode($search);
                }
                if (!empty($sort)) {
                    $page_link .= "&sort=" . urlencode($sort);
                }

                $style = ($i == $page) ? 'background-color: #6f42c1; border-color: #6f42c1; color: white;' : 'color: #6f42c1;';

                echo "<li class='page-item {$active_class}'>";
                echo "  <a class='page-link' href='{$page_link}' style='{$style}'>{$i}</a>";
                echo "</li>";
            }
            ?>
        </ul>
    </nav>
    <?php endif; ?>
</div>

<?php include 'footer.php'; ?>