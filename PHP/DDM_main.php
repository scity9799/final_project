<?php
// main.php
include_once 'header.php';

if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('로그인이 필요합니다.'); location.href='login.php';</script>";
    exit;
}
?>

<h2>🔍 원클릭 물품 검색</h2>
<form action="shop_list.php" method="GET" class="search-box">
    <input type="text" name="search_keyword" placeholder="보급 신청할 강아지 용품 입력...">
    <button type="submit">검색하기</button>
</form>

<div style="margin: 25px 0; padding: 15px; background: #f1f3f5; border-radius: 6px;">
    <h3>🏷️ 카테고리 퀵 메뉴</h3>
    <a href="shop_list.php?category=사료" style="margin-right: 15px; font-weight: bold; color: #007bff;">🌾 사료</a>
    <a href="shop_list.php?category=간식" style="margin-right: 15px; font-weight: bold; color: #007bff;">🍖 간식</a>
    <a href="shop_list.php?category=위생" style="margin-right: 15px; font-weight: bold; color: #007bff;">🧼 위생/케어</a>
    <a href="shop_list.php?category=장난감" style="font-weight: bold; color: #007bff;">🧸 장난감</a>
</div>

<hr>

<h2>🎲 실시간 추천 상품 (랜덤 8선)</h2>
<div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-top: 20px;">
    <?php
    $rand_query = "SELECT * FROM ddm_product ORDER BY RAND() LIMIT 8";
    $rand_result = mysqli_query($conn, $rand_query);

    if ($rand_result && mysqli_num_rows($rand_result) > 0) {
        while ($product = mysqli_fetch_assoc($rand_result)) {
            ?>
            <div style="border: 1px solid #eee; padding: 15px; border-radius: 6px; text-align: center; background: #fff;">
                <a href="shop_detail.php?product_id=<?php echo $product['product_id']; ?>" style="text-decoration: none; color: black;">
                    <div style="width: 100%; height: 120px; background: #e9ecef; margin-bottom: 10px; display: flex; align-items: center; justify-content: center; color: #aaa;">No Image</div>
                    <h4 style="margin: 10px 0 5px 0; font-size: 15px;"><?php echo htmlspecialchars($product['product_name']); ?></h4>
                    <p style="color: #ff6b6b; font-weight: bold; margin: 0;"><?php echo number_format($product['product_price']); ?>원</p>
                </a>
            </div>
            <?php
        }
    } else {
        echo "<p style='grid-column: span 4; text-align: center; color: #999;'>등록된 상품 카탈로그가 없습니다.</p>";
    }
    ?>
</div>

<?php include_once 'footer.php'; ?>