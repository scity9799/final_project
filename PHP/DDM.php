// 1. DB 연결 설정 (db.php)

<?php
// db.php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$db_host = "localhost";
$db_user = "root";      // 환경에 맞게 수정
$db_pass = "password";  // 환경에 맞게 수정
$db_name = "your_db";   // 환경에 맞게 생성한 데이터베이스명 입력

$conn = mysqli_connect($db_host, $db_user, $db_pass, $db_name);

if (!$conn) {
    die("데이터베이스 연결 실패: " . mysqli_connect_error());
}

// 한글 깨짐 방지
mysqli_set_charset($conn, "utf8mb4");
?>

//1. 공통 및 회원 관리
<?php
// index.php
include_once 'db.php';

if (isset($_SESSION['user_id'])) {
    if ($_SESSION['user_type'] === 'A') {
        header("Location: product_add.php");
    } else {
        header("Location: main.php");
    }
} else {
    header("Location: login.php");
}
exit;
?>

//logout.php (로그아웃 처리)
<?php
// logout.php
include_once 'db.php';

$_SESSION = array();

if (ini_get("session_use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

session_destroy();
echo "<script>alert('로그아웃되었습니다.'); location.href='login.php';</script>";
exit;
?>

//

// 2. 로그인 화면 (login.php)

<?php include_once 'db.php'; ?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title>딩동몰 로그인</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 50px; }
        .login-box { width: 300px; padding: 20px; border: 1px solid #ccc; border-radius: 5px; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; }
        .form-group input, .form-group select { width: 100%; padding: 8px; box-sizing: border-box; }
        .btn-submit { width: 100%; padding: 10px; background-color: #007BFF; color: white; border: none; cursor: pointer; }
        .btn-submit:hover { background-color: #0056b3; }
    </style>
</head>
<body>

<div class="login-box">
    <h2>딩동몰 로그인</h2>
    <form action="login_proc.php" method="POST">
        <div class="form-group">
            <label for="user_type">회원 유형</label>
            <select name="user_type" id="user_type">
                <option value="S">보호소 회원 (User)</option>
                <option value="A">지사 관리자 (Admin)</option>
            </select>
        </div>
        <div class="form-group">
            <label for="user_id">아이디</label>
            <input type="text" name="user_id" id="user_id" required>
        </div>
        <div class="form-group">
            <label for="user_pw">비밀번호</label>
            <input type="password" name="user_pw" id="user_pw" required>
        </div>
        <button type="submit" class="btn-submit">로그인</button>
    </form>
</div>

</body>
</html>

// 3. 로그인 처리 로직 (login_proc.php)
<?php
// login_proc.php
include_once 'db.php';

// POST 요청 변수 바인딩
$user_id   = isset($_POST['user_id']) ? $_POST['user_id'] : '';
$user_pw   = isset($_POST['user_pw']) ? $_POST['user_pw'] : '';
$user_type = isset($_POST['user_type']) ? $_POST['user_type'] : 'S';

// [취약점 유도] SQL Injection이 가능한 동적 쿼리 구성
// 싱글 쿼테이션(') 등을 입력하면 쿼리 구조가 변조되어 ID/PW를 몰라도 인증 우회가 가능해집니다.
$query = "SELECT * FROM ddd_user WHERE user_id = '$user_id' AND user_pw = '$user_pw' AND user_type = '$user_type'";
$result = mysqli_query($conn, $query);

if ($result && mysqli_num_rows($result) > 0) {
    $user = mysqli_fetch_assoc($result);

    // [취약점 유도] Session Fixation (세션 고정 취약점)
    // 안전한 코드가 되려면 로그인 성공 직후 session_regenerate_id(true); 를 호출해야 하지만,
    // 취약점 테스트를 위해 이를 생략하고 기존 세션 식별자를 그대로 유지합니다.
    
    $_SESSION['user_id']   = $user['user_id'];
    $_SESSION['user_type'] = $user['user_type'];
    $_SESSION['user_name'] = $user['user_name']; // ddd_user 테이블 내 이름 컬럼 가정

    // 권한(user_type)에 따른 페이지 이동 흐름 분기
    if ($_SESSION['user_type'] === 'A') {
        echo "<script>alert('관리자 로그인 성공'); location.href='product_add.php';</script>";
    } else {
        echo "<script>alert('보호소 회원 로그인 성공'); location.href='main.php';</script>";
    }
} else {
    // 로그인 실패 시
    echo "<script>alert('아이디 또는 비밀번호가 올바르지 않습니다.'); history.back();</script>";
}

mysqli_close($conn);
?>

// 1. 공통 상단 영역 (header.php)
<?php
// header.php
include_once 'db.php';
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title>딩동몰</title>
    <style>
        body { font-family: 'Malgun Gothic', sans-serif; margin: 0; padding: 0; background-color: #f9f9f9; }
        header { background-color: #333; color: white; padding: 15px; display: flex; justify-content: space-between; align-items: center; }
        header a { color: white; text-decoration: none; margin-left: 15px; }
        .container { width: 1000px; margin: 30px auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0px 0px 10px rgba(0,0,0,0.05); }
        .nav-links { display: flex; gap: 15px; }
        .search-box { margin: 20px 0; display: flex; gap: 10px; }
        .search-box input[type="text"] { width: 70%; padding: 10px; font-size: 16px; border: 1px solid #ddd; border-radius: 4px; }
        .search-box button { width: 25%; background-color: #28a745; color: white; border: none; font-size: 16px; cursor: pointer; border-radius: 4px; }
    </style>
</head>
<body>

<header>
    <div class="logo">
        <a href="main.php" style="font-size: 20px; font-weight: bold; margin-left: 0;">🐾 DingDongDog Mall</a>
    </div>
    <div class="nav-links">
        <?php if (isset($_SESSION['user_id'])): ?>
            <span><strong><?php echo htmlspecialchars($_SESSION['user_name'] ?? $_SESSION['user_id']); ?></strong>님 환영합니다.</span>
            <a href="main.php">메인</a>
            <a href="shop_list.php">상품목속</a>
            <a href="cart.php">장바구니</a>
            <a href="mypage.php">마이페이지</a>
            [cite_start]<a href="logout.php" style="color: #ff6b6b;">로그아웃</a> [cite: 1]
        <?php else: ?>
            [cite_start]<a href="login.php">로그인</a> [cite: 1]
        <?php endif; ?>
    </div>
</header>

<div class="container">

// 2. 메인 페이지 (main.php)
<?php
// main.php
include_once 'header.php';

// 세션이 없으면 로그인 페이지로 리다이렉트
if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('로그인이 필요합니다.'); location.href='login.php';</script>";
    exit;
}
?>

<h2>🔍 무엇을 찾으시나요?</h2>
<form action="shop_list.php" method="GET" class="search-box">
    <input type="text" name="search_keyword" placeholder="검색할 상품명을 입력하세요..." required>
    <button type="submit">검색하기</button>
</form>

<div style="margin: 30px 0; padding: 15px; background: #f1f3f5; border-radius: 6px;">
    <h3>🏷️ 카테고리 별 보기</h3>
    <a href="shop_list.php?category=사료" style="margin-right: 15px; font-weight: bold; color: #007bff;">🌾 사료</a>
    <a href="shop_list.php?category=간식" style="margin-right: 15px; font-weight: bold; color: #007bff;">🍖 간식</a>
    <a href="shop_list.php?category=위생" style="margin-right: 15px; font-weight: bold; color: #007bff;">🧼 위생/케어</a>
    <a href="shop_list.php?category=장난감" style="font-weight: bold; color: #007bff;">🧸 장난감</a>
</div>

<hr>

<h2>🎲 오늘의 추천 상품 (랜덤 8선)</h2>
<div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-top: 20px;">
    <?php
    [cite_start]// 테이블 정의서의 ddm_product 테이블에서 랜덤하게 8개 추출 [cite: 3, 6]
    [cite_start]$rand_query = "SELECT * FROM ddm_product ORDER BY RAND() LIMIT 8"; [cite: 3]
    $rand_result = mysqli_query($conn, $rand_query);

    if ($rand_result && mysqli_num_rows($rand_result) > 0) {
        while ($product = mysqli_fetch_assoc($rand_result)) {
            ?>
            <div style="border: 1px solid #eee; padding: 15px; border-radius: 6px; text-align: center; background: #fff;">
                <a href="shop_detail.php?product_id=<?php echo $product['product_id']; ?>" style="text-decoration: none; color: black;">
                    <div style="width: 100%; height: 120px; background: #e9ecef; margin-bottom: 10px; display: flex; align-items: center; justify-content: center; color: #aaa;">
                        No Image
                    </div>
                    <h4 style="margin: 10px 0 5px 0; font-size: 16px;"><?php echo htmlspecialchars($product['product_name']); ?></h4> [cite: 6]
                    <p style="color: #ff6b6b; font-weight: bold; margin: 0;"><?php echo number_format($product['product_price']); ?>원</p> [cite: 6]
                </a>
            </div>
            <?php
        }
    } else {
        echo "<p style='grid-column: span 4; text-align: center; color: #999;'>등록된 상품이 없습니다.</p>";
    }
    ?>
</div>

<?php
include_once 'footer.php';
?>

//3. 상품 목록 및 검색 페이지 (shop_list.php)
<?php
// shop_list.php
include_once 'header.php';

// 검색어 및 카테고리 파라미터 추출
$search_keyword = isset($_GET['search_keyword']) ? $_GET['search_keyword'] : ''; [cite: 3]
$category       = isset($_GET['category']) ? $_GET['category'] : ''; [cite: 3]
$sort           = isset($_GET['sort']) ? $_GET['sort'] : 'product_id DESC'; // 기본 정렬

// 기본 베이스 쿼리 생성
$query = "SELECT * FROM ddm_product WHERE 1=1"; [cite: 6]

if ($category !== '') {
    $query .= " AND product_category = '$category'"; [cite: 6]
}

// [취약점 유도] SQL Injection 구역
// 사용자의 입력값이 검증 없이 쿼리에 그대로 결합되므로 에러 기반(Error-based) 또는 유니온(Union) 인젝션이 가능합니다.
if ($search_keyword !== '') {
    $query .= " AND product_name LIKE '%$search_keyword%'"; [cite: 3, 6]
}

// 정렬 조건 결합 (가격 오름차순/내림차순 기능 포함)
$query .= " ORDER BY $sort"; [cite: 3]

// 디버깅 및 교육용 목적으로 화면에 쿼리 출력 (SQL Injection 성공 여부 판별용)
echo "<div style='background:#f8d7da; color:#721c24; padding:10px; margin-bottom:20px; font-family:monospace; border-radius:4px;'>
        <strong>[LAB DEBUG] 실행된 SQL 쿼리:</strong> " . $query . "
      </div>";

$result = mysqli_query($conn, $query);
?>

<h2>📦 상품 목록 및 검색 결과</h2>

<div style="margin-bottom: 20px; font-size: 14px;">
    정렬: 
    <a href="shop_list.php?search_keyword=<?php echo urlencode($search_keyword); ?>&category=<?php echo urlencode($category); ?>&sort=product_price+ASC">가격 오름차순 ▲</a> | 
    <a href="shop_list.php?search_keyword=<?php echo urlencode($search_keyword); ?>&category=<?php echo urlencode($category); ?>&sort=product_price+DESC">가격 내림차순 ▼</a> [cite: 3]
</div>

<table style="width: 100%; border-collapse: collapse; margin-top: 10px;">
    <thead>
        <tr style="background: #e9ecef; border-bottom: 2px solid #dee2e6;">
            <th style="padding: 10px; width: 10%;">번호</th>
            <th style="padding: 10px; width: 20%;">카테고리</th>
            <th style="padding: 10px; width: 50%; text-align: left;">상품명</th>
            <th style="padding: 10px; width: 20%;">가격</th>
        </tr>
    </thead>
    <tbody>
        <?php
        if ($result && mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                ?>
                <tr style="border-bottom: 1px solid #eee;">
                    <td style="padding: 12px; text-align: center;"><?php echo $row['product_id']; ?></td> [cite: 6]
                    <td style="padding: 12px; text-align: center;"><span style="background:#dee2e6; padding:3px 7px; border-radius:4px; font-size:12px;"><?php echo htmlspecialchars($row['product_category']); ?></span></td> [cite: 6]
                    <td style="padding: 12px;">
                        <a href="shop_detail.php?product_id=<?php echo $row['product_id']; ?>" style="color: #007bff; text-decoration: none; font-weight: bold;">
                            <?php echo htmlspecialchars($row['product_name']); ?> [cite: 6]
                        </a>
                    </td>
                    <td style="padding: 12px; text-align: center; font-weight: bold; color: #ff6b6b;"><?php echo number_format($row['product_price']); ?>원</td> [cite: 6]
                </tr>
                <?php
            }
        } else {
            // SQL 에러 발생 시 에러 메시지 출력 (SQL Injection 공격 시 유용)
            if (mysqli_error($conn)) {
                echo "<tr><td colspan='4' style='padding: 20px; text-align: center; color: red; font-weight: bold;'>SQL 에러 발생: " . mysqli_error($conn) . "</td></tr>";
            } else {
                echo "<tr><td colspan='4' style='padding: 20px; text-align: center; color: #999;'>검색 결과 조건에 맞는 상품이 존재하지 않습니다.</td></tr>";
            }
        }
        ?>
    </tbody>
</table>

<?php
include_once 'footer.php';
?>

//4. 공통 하단 영역 (footer.php)
</div> <footer style="background: #333; color: #888; text-align: center; padding: 20px; margin-top: 50px; font-size: 13px;">
    &copy; 2026 DingDongDog 모의해킹 훈련 인프라. All rights reserved.
</footer>

</body>
</html>

//1. 상품 상세 조회 (shop_detail.php)
<?php
// shop_detail.php
include_once 'header.php';

if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('로그인이 필요합니다.'); location.href='login.php';</script>";
    exit;
}

// URL 파라미터로 넘어온 상품 고유 번호 바인딩
$product_id = isset($_GET['product_id']) ? intval($_GET['product_id']) : 0;

// 데이터베이스에서 상품 및 매핑된 이미지 파일 정보 조회
$query = "SELECT p.*, f.file_save_name FROM ddm_product p 
          LEFT JOIN ddm_file f ON p.product_id = f.product_id 
          WHERE p.product_id = $product_id";
$result = mysqli_query($conn, $query);
$product = mysqli_fetch_assoc($result);

if (!$product) {
    echo "<script>alert('존재하지 않는 상품입니다.'); history.back();</script>";
    exit;
}
?>

<div style="display: flex; gap: 40px; margin-top: 20px;">
    <div style="width: 400px; height: 400px; background: #e9ecef; display: flex; align-items: center; justify-content: center; border-radius: 8px;">
        <?php if (!empty($product['file_save_name'])): ?>
            <img src="uploads/<?php echo htmlspecialchars($product['file_save_name']); ?>" alt="상품 이미지" style="width: 100%; height: 100%; object-fit: cover; border-radius: 8px;">
        <?php else: ?>
            <span style="color: #aaa; font-size: 18px;">No Image</span>
        <?php endif; ?>
    </div>

    <div style="flex: 1; display: flex; flex-direction: column; justify-content: space-between;">
        <div>
            <span style="background: #20c997; color: white; padding: 4px 8px; border-radius: 4px; font-size: 14px; font-weight: bold;">
                <?php echo htmlspecialchars($product['product_category']); ?>
            </span>
            <h2 style="margin: 15px 0 10px 0; font-size: 28px;"><?php echo htmlspecialchars($product['product_name']); ?></h2>
            <p style="font-size: 24px; color: #ff6b6b; font-weight: bold; margin: 0;"><?php echo number_format($product['product_price']); ?> 원</p>
            <hr style="border: 0; border-top: 1px solid #eee; margin: 20px 0;">
            <p style="color: #666; line-height: 1.6; min-height: 120px;"><?php echo nl2br(htmlspecialchars($product['product_description'] ?? '')); ?></p>
        </div>

        <div style="background: #f8f9fa; padding: 20px; border-radius: 6px;">
            <form id="purchase_form" method="POST" action="">
                <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
                
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px;">
                    <label for="quantity" style="font-weight: bold; color: #333;">주문 수량 선택</label>
                    <input type="number" id="quantity" name="quantity" value="1" min="1" max="99" style="width: 70px; padding: 8px; text-align: center; border: 1px solid #ddd; border-radius: 4px; font-size: 16px;">
                </div>

                <div style="display: flex; gap: 10px;">
                    <button type="button" onclick="submitForm('cart_add_proc.php')" style="flex: 1; background: #6c757d; color: white; border: none; padding: 15px; font-size: 16px; font-weight: bold; border-radius: 4px; cursor: pointer;">🛒 장바구니 담기</button>
                    <button type="button" onclick="submitForm('order_direct_proc.php')" style="flex: 1; background: #e03131; color: white; border: none; padding: 15px; font-size: 16px; font-weight: bold; border-radius: 4px; cursor: pointer;">⚡ 바로 구매</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function submitForm(targetAction) {
    const form = document.getElementById('purchase_form');
    form.action = targetAction;
    form.submit();
}
</script>

<?php
include_once 'footer.php';
?>

//2. 장바구니 저장 처리 (cart_add_proc.php)
<?php
// cart_add_proc.php
include_once 'db.php';

// 세션 검증만 거친 후 동작 수행
if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('로그인이 필요한 서비스입니다.'); location.href='login.php';</script>";
    exit;
}

$user_id    = $_SESSION['user_id'];
$product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
$quantity   = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;

if ($product_id <= 0) {
    echo "<script>alert('잘못된 접근입니다.'); history.back();</script>";
    exit;
}

// [취약점 유도] CSRF 방어용 고유 토큰 검증 단계가 누락됨
// 해커의 악성 서브밋 폼 링크 클릭 시 원격으로 자동 요청 수렴이 일어납니다.

// 기존에 동일한 유저가 해당 상품을 장바구니에 담았는지 체크
$check_query = "SELECT * FROM ddm_cart WHERE user_id = '$user_id' AND product_id = $product_id";
$check_result = mysqli_query($conn, $check_query);

if ($check_result && mysqli_num_rows($check_result) > 0) {
    // 수량 누적 업데이트
    $update_query = "UPDATE ddm_cart SET cart_quantity = cart_quantity + $quantity WHERE user_id = '$user_id' AND product_id = $product_id";
    mysqli_query($conn, $update_query);
} else {
    // 새 항목 추가 (테이블 구조: user_id, product_id, cart_quantity)
    $insert_query = "INSERT INTO ddm_cart (user_id, product_id, cart_quantity) VALUES ('$user_id', $product_id, $quantity)";
    mysqli_query($conn, $insert_query);
}

echo "<script>
        if(confirm('장바구니에 상품이 정상적으로 담겼습니다.\\n장바구니 페이지로 이동하시겠습니까?')) {
            location.href = 'cart.php';
        } else {
            history.back();
        }
      </script>";

mysqli_close($conn);
?>

//3. 즉시 구매 처리 (order_direct_proc.php)
<?php
// order_direct_proc.php
include_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('로그인이 필요한 서비스입니다.'); location.href='login.php';</script>";
    exit;
}

$user_id    = $_SESSION['user_id'];
// POST와 GET 방식을 모두 수용하게 설계하여 공격자가 URL 파라미터 링크(GET)로도 CSRF 공격을 감행할 수 있도록 취약하게 구성합니다.
$product_id = isset($_REQUEST['product_id']) ? intval($_REQUEST['product_id']) : 0;
$quantity   = isset($_REQUEST['quantity']) ? intval($_REQUEST['quantity']) : 1;

if ($product_id <= 0) {
    echo "<script>alert('상품 번호가 올바르지 않습니다.'); history.back();</script>";
    exit;
}

// 1. 단가 확인을 위한 상품 정보 질의
$prod_query = "SELECT product_price FROM ddm_product WHERE product_id = $product_id";
$prod_result = mysqli_query($conn, $prod_query);
$product = mysqli_fetch_assoc($prod_result);

if (!$product) {
    echo "<script>alert('해당 상품을 찾을 수 없습니다.'); history.back();</script>";
    exit;
}

$unit_price = $product['product_price'];
$total_price = $unit_price * $quantity;

// 2. 주문 마스터 테이블(ddm_order)에 데이터 삽입
$order_query = "INSERT INTO ddm_order (user_id, order_total_price, order_status) VALUES ('$user_id', $total_price, '주문완료')";
if (mysqli_query($conn, $order_query)) {
    $order_id = mysqli_insert_id($conn); // 생성된 주문 고유 번호 추출

    // 3. 주문 상세 테이블(ddm_order_detail)에 매핑 레코드 삽입
    $detail_query = "INSERT INTO ddm_order_detail (order_id, product_id, detail_quantity, detail_price) 
                     VALUES ($order_id, $product_id, $quantity, $unit_price)";
    mysqli_query($conn, $detail_query);

    echo "<script>alert('⚡ [원클릭 즉시 구매 성공] 주문이 정상적으로 완료되었습니다! 마이페이지에서 확인 가능합니다.'); location.href='mypage.php';</script>";
} else {
    echo "<script>alert('주문 처리 중 에러가 발생했습니다.'); history.back();</script>";
}

mysqli_close($conn);
?>

//1. 장바구니 목록 조회 (cart.php)
<?php
// cart.php
include_once 'header.php';

if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('로그인이 필요합니다.'); location.href='login.php';</script>";
    exit;
}

$user_id = $_SESSION['user_id'];

// ddm_cart와 ddm_product 테이블을 조인하여 현재 유저의 장바구니 내역 조회
$query = "SELECT c.cart_id, c.cart_quantity, p.product_id, p.product_name, p.product_price, p.product_category 
          FROM ddm_cart c
          JOIN ddm_product p ON c.product_id = p.product_id
          WHERE c.user_id = '$user_id'
          ORDER BY c.cart_reg_date DESC";
$result = mysqli_query($conn, $query);
?>

<h2>🛒 내 장바구니</h2>
<form action="cart_order_proc.php" method="POST">
    <table style="width: 100%; border-collapse: collapse; margin-top: 10px;">
        <thead>
            <tr style="background: #e9ecef; border-bottom: 2px solid #dee2e6;">
                <th style="padding: 10px; width: 5%;"><input type="checkbox" id="select_all" checked onclick="toggleAll(this)"></th>
                <th style="padding: 10px; width: 45%; text-align: left;">상품명</th>
                <th style="padding: 10px; width: 15%;">판매가</th>
                <th style="padding: 10px; width: 15%;">수량</th>
                <th style="padding: 10px; width: 10%;">소계</th>
                <th style="padding: 10px; width: 10%;">관리</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $total_cart_price = 0;
            if ($result && mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $subtotal = $row['product_price'] * $row['cart_quantity'];
                    $total_cart_price += $subtotal;
                    ?>
                    <tr style="border-bottom: 1px solid #eee;">
                        <td style="padding: 12px; text-align: center;">
                            <input type="checkbox" name="cart_ids[]" value="<?php echo $row['cart_id']; ?>" class="cart_checkbox" checked>
                        </td>
                        <td style="padding: 12px;">
                            <span style="color:#868e96; font-size:12px;">[<?php echo htmlspecialchars($row['product_category']); ?>]</span><br>
                            <a href="shop_detail.php?product_id=<?php echo $row['product_id']; ?>" style="color: #333; text-decoration: none; font-weight: bold;">
                                <?php echo htmlspecialchars($row['product_name']); ?>
                            </a>
                        </td>
                        <td style="padding: 12px; text-align: center;"><?php echo number_format($row['product_price']); ?>원</td>
                        <td style="padding: 12px; text-align: center;"><?php echo $row['cart_quantity']; ?>개</td>
                        <td style="padding: 12px; text-align: center; font-weight: bold; color: #ff6b6b;"><?php echo number_format($subtotal); ?>원</td>
                        <td style="padding: 12px; text-align: center;">
                            <a href="cart_delete_proc.php?cart_id=<?php echo $row['cart_id']; ?>" 
                               onclick="return confirm('이 상품을 장바구니에서 삭제하시겠습니까?');" 
                               style="background: #e03131; color: white; padding: 5px 8px; border-radius: 4px; text-decoration: none; font-size: 12px;">삭제</a>
                        </td>
                    </tr>
                    <?php
                }
            } else {
                echo "<tr><td colspan='6' style='padding: 40px; text-align: center; color: #999;'>장바구니가 비어 있습니다.</td></tr>";
            }
            ?>
        </tbody>
    </table>

    <?php if ($total_cart_price > 0): ?>
        <div style="text-align: right; margin-top: 20px; font-size: 18px;">
            <strong>선택 상품 총 결제 예정 금액: </strong> 
            <span style="color: #e03131; font-weight: bold; font-size: 22px;"><?php echo number_format($total_cart_price); ?></span>원
        </div>
        <div style="text-align: center; margin-top: 30px;">
            <button type="submit" style="background: #228be6; color: white; border: none; padding: 15px 40px; font-size: 18px; font-weight: bold; border-radius: 4px; cursor: pointer;">🛍️ 선택 상품 주문하기</button>
        </div>
    <?php endif; ?>
</form>

<script>
function toggleAll(master) {
    const checkboxes = document.getElementsByClassName('cart_checkbox');
    for (let i = 0; i < checkboxes.length; i++) {
        checkboxes[i].checked = master.checked;
    }
}
</script>

<?php
include_once 'footer.php';
?>

//2. 장바구니 삭제 처리 (cart_delete_proc.php)
<?php
// cart_delete_proc.php
include_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('로그인이 필요한 서비스입니다.'); location.href='login.php';</script>";
    exit;
}

// URL 파라미터나 요청으로부터 변수 수집
$cart_id = isset($_GET['cart_id']) ? intval($_GET['cart_id']) : 0;

if ($cart_id <= 0) {
    echo "<script>alert('잘못된 접근입니다.'); history.back();</script>";
    exit;
}

// [취약점 유도] IDOR (Insecure Direct Object Reference)
// 세션 내 유저 정보($user_id)를 조건절에 추가하지 않아, 
// 공격자가 다른 사용자의 cart_id를 알아내어 주소창에 변조 대입하면 타인의 장바구니 물품이 지워집니다.
$query = "DELETE FROM ddm_cart WHERE cart_id = $cart_id";
$result = mysqli_query($conn, $query);

if ($result) {
    echo "<script>alert('장바구니에서 삭제되었습니다.'); location.href='cart.php';</script>";
} else {
    echo "<script>alert('삭제 처리 중 에러가 발생했습니다.'); history.back();</script>";
}

mysqli_close($conn);
?>

//3. 선택 상품 주문 처리 (cart_order_proc.php)
<?php
// cart_order_proc.php
include_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('로그인이 필요한 서비스입니다.'); location.href='login.php';</script>";
    exit;
}

$user_id = $_SESSION['user_id'];
$cart_ids = isset($_POST['cart_ids']) ? $_POST['cart_ids'] : [];

if (empty($cart_ids)) {
    echo "<script>alert('선택된 상품이 없습니다.'); history.back();</script>";
    exit;
}

// 1. 트랜잭션 처리를 위해 안전하게 선택한 장바구니 고유 ID 리스트 정제
$cart_id_list = implode(',', array_map('intval', $cart_ids));

// 2. 결제해야 할 총 금액 산출을 위한 질의
$sum_query = "SELECT SUM(p.product_price * c.cart_quantity) AS total_price 
              FROM ddm_cart c 
              JOIN ddm_product p ON c.product_id = p.product_id 
              WHERE c.cart_id IN ($cart_id_list)";
$sum_result = mysqli_query($conn, $sum_query);
$sum_row = mysqli_fetch_assoc($sum_result);
$order_total_price = $sum_row['total_price'] ?? 0;

if ($order_total_price <= 0) {
    echo "<script>alert('주문할 상품 금액이 0원입니다.'); history.back();</script>";
    exit;
}

// 3. 주문 마스터 테이블(ddm_order) 생성
$order_query = "INSERT INTO ddm_order (user_id, order_total_price, order_status) 
                VALUES ('$user_id', $order_total_price, '주문완료')";

if (mysqli_query($conn, $order_query)) {
    $order_id = mysqli_insert_id($conn); // 신규 생성된 주문 번호 추출

    // 4. 장바구니 정보를 바탕으로 주문 상세 레코드 생성 후 이관 (ddm_order_detail)
    $detail_query = "SELECT c.product_id, c.cart_quantity, p.product_price 
                     FROM ddm_cart c
                     JOIN ddm_product p ON c.product_id = p.product_id
                     WHERE c.cart_id IN ($cart_id_list)";
    $detail_result = mysqli_query($conn, $detail_query);

    while ($item = mysqli_fetch_assoc($detail_result)) {
        $p_id = $item['product_id'];
        $qty  = $item['cart_quantity'];
        $price = $item['product_price'];

        $insert_item = "INSERT INTO ddm_order_detail (order_id, product_id, detail_quantity, detail_price) 
                        VALUES ($order_id, $p_id, $qty, $price)";
        mysqli_query($conn, $insert_item);
    }

    // 5. 주문 처리가 최종 완료된 상품들은 장바구니 테이블에서 즉시 소거
    $delete_cart = "DELETE FROM ddm_cart WHERE cart_id IN ($cart_id_list)";
    mysqli_query($conn, $delete_cart);

    echo "<script>alert('🎉 장바구니 상품 주문이 안전하게 완료되었습니다!'); location.href='mypage.php';</script>";
} else {
    echo "<script>alert('주문 트랜잭션 중 에러가 발생했습니다.'); history.back();</script>";
}

mysqli_close($conn);
?>

//1. 마이페이지 (mypage.php)
<?php
// db.php 내부에 session_start()가 포함되어 있다고 가정합니다.
require_once 'db.php';

// 세션 체크 (최소한의 로그인 여부만 검증)
if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('로그인이 필요합니다.'); location.href='login.php';</script>";
    exit;
}

$user_id = $_SESSION['user_id'];

// 1. 로그인한 유저의 상세 정보 조회
$user_query = "SELECT * FROM ddd_user WHERE user_id = '$user_id'";
$user_result = mysqli_query($conn, $user_query);
$user_info = mysqli_fetch_assoc($user_result);

// 2. 해당 유저의 주문 내역 리스트 조회
// 실제 테이블 설계명(ddd_order 등)에 맞추어 쿼리를 수행합니다.
$order_query = "SELECT * FROM ddd_order WHERE user_id = '$user_id' ORDER BY order_date DESC";
$order_result = mysqli_query($conn, $order_query);
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title>딩동몰 - 마이페이지</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="card p-4 shadow-sm mb-4">
        <h2 class="text-primary mb-3">🐾 마이페이지 (회원 정보)</h2>
        <p><strong>아이디:</strong> <?php echo htmlspecialchars($user_info['user_id']); ?></p>
        <p><strong>이름:</strong> <?php echo htmlspecialchars($user_info['user_name']); ?></p>
        <p><strong>이메일:</strong> <?php echo htmlspecialchars($user_info['user_email']); ?></p>
    </div>

    <div class="card p-4 shadow-sm">
        <h3 class="mb-3">📦 나의 주문 내역</h3>
        <table class="table table-hover">
            <thead class="table-dark">
                <tr>
                    <th>주문 번호</th>
                    <th>상품명</th>
                    <th>결제 금액</th>
                    <th>주문 일시</th>
                    <th>상세 보기</th>
                </tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($order_result) > 0): ?>
                    <?php while($row = mysqli_fetch_assoc($order_result)): ?>
                        <tr>
                            <td><?php echo $row['order_id']; ?></td>
                            <td><?php echo htmlspecialchars($row['product_name']); ?></td>
                            <td><?php echo number_format($row['total_price']); ?>원</td>
                            <td><?php echo $row['order_date']; ?></td>
                            <td>
                                <a href="order_view.php?order_id=<?php echo $row['order_id']; ?>" class="btn btn-sm btn-outline-secondary">조회</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="text-center py-3">주문 내역이 없습니다.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>

//2. 주문 상세 내역 조회 (order_view.php)
<?php
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('로그인이 필요합니다.'); location.href='login.php';</script>";
    exit;
}

// GET 파라미터로 넘어오는 주문 번호를 검증 없이 수용
$order_id = $_GET['order_id'];

// ❌ 취약점 포인트: WHERE 조건에 order_id만 검증하고, 요청한 사람(세션 유저)이 이 주문의 주인인지 크로스 체크하지 않음!
$query = "SELECT * FROM ddd_order WHERE order_id = '$order_id'";
$result = mysqli_query($conn, $query);
$order = mysqli_fetch_assoc($result);

if (!$order) {
    echo "<script>alert('존재하지 않는 주문입니다.'); history.back();</script>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title>딩동독 - 주문 상세 내역</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="card p-4 shadow-sm mx-auto" style="max-width: 600px;">
        <h3 class="text-success mb-4 text-center">🧾 주문 상세 내역 (영수증)</h3>
        
        <table class="table table-bordered">
            <tr>
                <th class="table-light" style="width: 30%;">주문 번호</th>
                <td><?php echo $order['order_id']; ?></td>
            </tr>
            <tr>
                <th class="table-light">주문 고객 ID</th>
                <td class="text-danger fw-bold"><?php echo htmlspecialchars($order['user_id']); ?></td>
            </tr>
            <tr>
                <th class="table-light">주문 상품</th>
                <td><?php echo htmlspecialchars($order['product_name']); ?></td>
            </tr>
            <tr>
                <th class="table-light">결제 금액</th>
                <td><?php echo number_format($order['total_price']); ?>원</td>
            </tr>
            <tr>
                <th class="table-light">배송지 주소</th>
                <td><?php echo htmlspecialchars($order['shipping_address']); ?></td>
            </tr>
            <tr>
                <th class="table-light">주문 상태</th>
                <td><span class="badge bg-info"><?php echo htmlspecialchars($order['order_status']); ?></span></td>
            </tr>
        </table>
        
        <div class="text-center mt-3">
            <a href="mypage.php" class="btn btn-primary">마이페이지로 돌아가기</a>
        </div>
    </div>
</div>
</body>
</html>

//1. 지사 관리자 상품 등록 화면 (product_add.php)
<?php
// product_add.php
include_once 'db.php';

// [권한 검증] 세션이 없거나 관리자(A) 권한이 아니면 비인가 접근으로 판단하고 차단
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'A') {
    echo "<script>alert('지사 관리자만 접근 가능한 페이지입니다.'); location.href='login.php';</script>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title>지사 관리자 - 상품 등록</title>
    <style>
        body { font-family: 'Malgun Gothic', sans-serif; background-color: #f1f3f5; margin: 0; padding: 40px; }
        .admin-box { width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: bold; color: #495057; }
        .form-group input[type="text"], .form-group input[type="number"], .form-group select, .form-group textarea {
            width: 100%; padding: 10px; box-sizing: border-box; border: 1px solid #ced4da; border-radius: 4px; font-size: 15px;
        }
        .form-group textarea { height: 15px; min-height: 120px; resize: vertical; }
        .btn-submit { width: 100%; padding: 12px; background-color: #343a40; color: white; border: none; font-size: 16px; font-weight: bold; border-radius: 4px; cursor: pointer; }
        .btn-submit:hover { background-color: #212529; }
        .header-menu { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding-bottom: 10px; border-bottom: 2px solid #dee2e6; }
    </style>
</head>
<body>

<div class="admin-box">
    <div class="header-menu">
        <h2>🛠️ 상품 관리 시스템 (지사)</h2>
        <a href="logout.php" style="color: #e03131; text-decoration: none; font-size: 14px; font-weight: bold;">로그아웃</a>
    </div>
    
    <p style="color: #666; font-size: 14px; margin-bottom: 25px;">※ 지사 물품 보급 및 신규 상품 카탈로그 등록을 위한 관제 페이지입니다.</p>

    <form action="product_add_proc.php" method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label for="product_name">상품명</label>
            <input type="text" name="product_name" id="product_name" placeholder="예: 프리미엄 유기농 사료" required>
        </div>

        <div class="form-group">
            <label for="product_category">카테고리 선택</label>
            <select name="product_category" id="product_category" required>
                <option value="사료">🌾 사료 (Feed)</option>
                <option value="간식">🍖 간식 (Snack)</option>
                <option value="위생">🧼 위생/케어 (Pad)</option>
                <option value="장난감">🧸 장난감 (Toy)</option>
            </select>
        </div>

        <div class="form-group">
            <label for="product_price">상품 가격 (원)</label>
            <input type="number" name="product_price" id="product_price" min="0" placeholder="숫자만 입력" required>
        </div>

        <div class="form-group">
            <label for="product_description">상품 상세 설명</label>
            <textarea name="product_description" id="product_description" placeholder="상품에 대한 상세 명세 및 보호소 지급 지침 입력..."></textarea>
        </div>

        <div class="form-group" style="background: #f8f9fa; padding: 15px; border-radius: 4px; border: 1px dashed #adb5bd;">
            <label for="product_image" style="margin-bottom: 5px;">📸 상품 대표 이미지 첨부</label>
            <p style="color: #e03131; font-size: 12px; margin: 0 0 10px 0;">[LAB NOTICE] 모의훈련 목적으로 확장자 우회 필터가 꺼져 있습니다.</p>
            <input type="file" name="product_image" id="product_image">
        </div>

        <button type="submit" class="btn-submit">📦 신규 상품 등록</button>
    </form>
</div>

</body>
</html>

//2. 상품 등록 및 파일 업로드 처리 (product_add_proc.php)
<?php
// product_add_proc.php
include_once 'db.php';

// 관리자 권한 재차 검증
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'A') {
    echo "<script>alert('비인가 접근입니다.'); location.href='login.php';</script>";
    exit;
}

// POST 입력값 정제 수집
$product_name        = isset($_POST['product_name']) ? mysqli_real_escape_string($conn, $_POST['product_name']) : '';
$product_category    = isset($_POST['product_category']) ? mysqli_real_escape_string($conn, $_POST['product_category']) : '';
$product_price       = isset($_POST['product_price']) ? intval($_POST['product_price']) : 0;
$product_description = isset($_POST['product_description']) ? mysqli_real_escape_string($conn, $_POST['product_description']) : '';

if (empty($product_name) || $product_price < 0) {
    echo "<script>alert('필수 입력 항목이 누락되었거나 유효하지 않습니다.'); history.back();</script>";
    exit;
}

// 1. 상품 마스터 테이블(ddm_product) 데이터 인서트
$insert_query = "INSERT INTO ddm_product (product_category, product_name, product_price, product_description) 
                 VALUES ('$product_category', '$product_name', $product_price, '$product_description')";

if (mysqli_query($conn, $insert_query)) {
    $product_id = mysqli_insert_id($conn); // 매핑용 생성 번호 로드

    // 2. 파일 업로드 루틴 제어 시작
    if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] == UPLOAD_ERR_OK) {
        
        $upload_dir = 'uploads/'; // 웹서버 권한이 열려 있는 저장 폴더 경로
        
        // 실습 환경 상 uploads 디렉토리가 없을 시 자동 생성 제어
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $origin_name = $_FILES['product_image']['name']; // 원래 사용자가 올린 실제 파일명
        $tmp_name    = $_FILES['product_image']['tmp_name'];
        
        // [취약점 유도] 확장자 우회 및 White-list 검증이 일절 존재하지 않음!
        // 공격자가 임의의 attack.php (웹쉘) 파일을 업로드하면 변조 없이 그대로 수용됩니다.
        
        // 파일명 중복 충돌 방지를 위해 유니크 키 조합형 파일 세이브 명칭 정의
        $file_ext = pathinfo($origin_name, PATHINFO_EXTENSION);
        $save_name = time() . "_" . bin2hex(random_bytes(4)) . "." . $file_ext;
        $dest_path = $upload_dir . $save_name;

        // 임시 저장 파일 보관 폴더로 최종 이동 이관
        if (move_uploaded_file($tmp_name, $dest_path)) {
            
            // 데이터베이스 파일 매핑 테이블(ddm_file)에 레코드 보관 처리
            $file_query = "INSERT INTO ddm_file (product_id, file_origin_name, file_save_name) 
                           VALUES ($product_id, '$origin_name', '$save_name')";
            mysqli_query($conn, $file_query);

            // 침투 테스트 모의 진단 가독성을 위해 웹쉘 도출 업로드 최종 물리 경로를 명시해 줌
            echo "<script>
                    alert('📦 [상품 등록 및 파일 업로드 성공]\\n업로드 파일명: $origin_name\\n서버 저장소 위치: $dest_path');
                    location.href = 'product_add.php';
                  </script>";
        } else {
            echo "<script>alert('파일 이동 중 오류가 발생했습니다. 권한 설정을 확인하세요.'); location.href='product_add.php';</script>";
        }
    } else {
        // 이미지를 업로드하지 않고 텍스트 정보만 등록했을 경우
        echo "<script>alert('텍스트 정보만 성공적으로 등록되었습니다.'); location.href='product_add.php';</script>";
    }
} else {
    echo "<script>alert('DB 등록 중 장애가 발생했습니다.'); history.back();</script>";
}

mysqli_close($conn);
?>