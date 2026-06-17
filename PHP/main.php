<?php 
// 1. 공통 세션 및 DB 연결 (db.php에 session_start()와 $conn 정의)
include 'db.php'; 

// [보안] 비인가 접근 차단: 세션 정보가 없으면 로그인 페이지로 추방
if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('로그인이 필요한 서비스입니다.'); location.href='login.php';</script>";
    exit;
}

include 'header.php'; 
?>

<div class="container mt-5">
    <h2 class="text-purple mb-4">딩동몰 메인</h2>
    
    <div class="row mb-4">
        <div class="col-md-6">
            <form action="shop_list.php" method="GET" class="d-flex">
                <input type="text" name="search" class="form-control me-2" placeholder="상품명 검색">
                <button type="submit" class="btn btn-purple">검색</button>
            </form>
        </div>
        <div class="col-md-6 text-end">
            <a href="shop_list.php?category=feed" class="btn btn-outline-purple">사료</a>
            <a href="shop_list.php?category=pad" class="btn btn-outline-purple">배변패드</a>
        </div>
    </div>

    <div class="row">
        <?php
        // [취약점 대응] 랜덤하게 8개 상품 가져오기
        $query = "SELECT * FROM product ORDER BY RAND() LIMIT 8";
        $result = $conn->query($query);
        
        while ($row = $result->fetch_assoc()) {
            // [보안] XSS 방지: 상품 정보 출력 시 htmlspecialchars 적용
            $name = htmlspecialchars($row['product_name'], ENT_QUOTES, 'UTF-8');
            echo "
            <div class='col-md-3 mb-4'>
                <div class='card h-100'>
                    <div class='card-body'>
                        <h5 class='card-title'>$name</h5>
                        <p class='card-text'>가격: " . number_format($row['price']) . "원</p>
                        <a href='shop_detail.php?p_no=" . $row['product_number'] . "' class='btn btn-sm btn-purple'>상세보기</a>
                    </div>
                </div>
            </div>";
        }
        ?>
    </div>
</div>

<?php include 'footer.php'; ?>