<?php include 'db.php'; include 'header.php'; 

// [취약점] 카테고리 및 검색 파라미터를 필터링 없이 그대로 사용
$cat = $_GET['category'] ?? '';
$search = $_GET['search'] ?? '';

// SQL Injection 발생 포인트: 사용자가 URL에 ?category=' OR '1'='1 등의 값을 넣으면 모든 데이터 노출 가능
$query = "SELECT * FROM product WHERE category LIKE '%$cat%' AND product_name LIKE '%$search%'";
$result = $conn->query($query);
?>

<h2 class="text-purple">상품 목록</h2>
<table class="table">
    <?php while($row = $result->fetch_assoc()): ?>
    <tr>
        <td><?= $row['product_name'] ?></td>
        <td><?= number_format($row['price']) ?>원</td>
        <td><a href="shop_detail.php?p_no=<?= $row['product_number'] ?>">상세보기</a></td>
    </tr>
    <?php endwhile; ?>
</table>

<?php include 'footer.php'; ?>