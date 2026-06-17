<?php
// shop_list.php
include_once 'header.php';

if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('로그인이 필요합니다.'); location.href='login.php';</script>";
    exit;
}

$search_keyword = isset($_GET['search_keyword']) ? $_GET['search_keyword'] : '';
$category       = isset($_GET['category']) ? $_GET['category'] : '';
$sort           = isset($_GET['sort']) ? $_GET['sort'] : 'product_id DESC'; 

// [취약점 유도] 문자열 다이렉트 바인딩 결합을 통한 SQL 인젝션 활성화
$query = "SELECT * FROM ddm_product WHERE 1=1";

if ($category !== '') {
    $query .= " AND product_category = '$category'";
}
if ($search_keyword !== '') {
    $query .= " AND product_name LIKE '%$search_keyword%'";
}

$query .= " ORDER BY $sort";

// 디버깅 레이아웃 가시화
echo "<div style='background:#f8d7da; color:#721c24; padding:10px; margin-bottom:20px; font-family:monospace; border-radius:4px;'>
        <strong>[LAB DEBUG 쿼리 모니터]:</strong> " . $query . "
      </div>";

$result = mysqli_query($conn, $query);
?>

<h2>📦 보급품 리스트 게시판</h2>
<div style="margin-bottom: 20px; font-size: 14px;">
    정렬 스위치: 
    <a href="shop_list.php?search_keyword=<?php echo urlencode($search_keyword); ?>&category=<?php echo urlencode($category); ?>&sort=product_price+ASC">가격 낮은순 ▲</a> | 
    <a href="shop_list.php?search_keyword=<?php echo urlencode($search_keyword); ?>&category=<?php echo urlencode($category); ?>&sort=product_price+DESC">가격 높은순 ▼</a>
</div>

<table style="width: 100%; border-collapse: collapse;">
    <thead>
        <tr style="background: #e9ecef; border-bottom: 2px solid #dee2e6; text-align: center;">
            <th style="padding: 10px; width: 10%;">코드</th>
            <th style="padding: 10px; width: 15%;">분류</th>
            <th style="padding: 10px; text-align: left; width: 55%;">용품명</th>
            <th style="padding: 10px; width: 20%;">보급 단가</th>
        </tr>
    </thead>
    <tbody>
        <?php
        if ($result && mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                ?>
                <tr style="border-bottom: 1px solid #eee; text-align: center;">
                    <td style="padding: 12px;"><?php echo $row['product_id']; ?></td>
                    <td style="padding: 12px;"><span style="background:#dee2e6; padding:3px 6px; border-radius:4px; font-size:12px;"><?php echo htmlspecialchars($row['product_category']); ?></span></td>
                    <td style="padding: 12px; text-align: left;">
                        <a href="shop_detail.php?product_id=<?php echo $row['product_id']; ?>" style="color: #007bff; text-decoration: none; font-weight: bold;">
                            <?php echo htmlspecialchars($row['product_name']); ?>
                        </a>
                    </td>
                    <td style="padding: 12px; font-weight: bold; color: #ff6b6b;"><?php echo number_format($row['product_price']); ?>원</td>
                </tr>
                <?php
            }
        } else {
            if (mysqli_error($conn)) {
                echo "<tr><td colspan='4' style='padding: 20px; text-align: center; color: red;'>SQL Syntax 에러 도출 결과: " . mysqli_error($conn) . "</td></tr>";
            } else {
                echo "<tr><td colspan='4' style='padding: 20px; text-align: center; color: #999;'>부합하는 용품이 없습니다.</td></tr>";
            }
        }
        ?>
    </tbody>
</table>

<?php include_once 'footer.php'; ?>

