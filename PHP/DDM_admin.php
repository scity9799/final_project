<?php
// product_add.php
include_once 'db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'A') {
    echo "<script>alert('지사 관리자 전용 구역입니다.'); location.href='login.php';</script>"; exit;
}
?>
<!DOCTYPE html>
<html lang="ko">
<head><meta charset="UTF-8"><title>지사 관리자 - 상품 등록</title></head>
<body style="font-family:sans-serif; padding:30px; background:#f1f3f5;">
<div style="width:500px; margin:0 auto; background:white; padding:25px; border-radius:6px; box-shadow:0 2px 5px rgba(0,0,0,0.1);">
    <h3>📦 신규 물품 스펙 대장 기재</h3>
    <form action="product_add_proc.php" method="POST" enctype="multipart/form-data">
        <p>물품명: <input type="text" name="product_name" style="width:100%; padding:8px;" required></p>
        <p>카테고리: <select name="product_category" style="width:100%; padding:8px;">
            <option value="사료">사료</option><option value="간식">간식</option><option value="위생">위생/케어</option><option value="장난감">장난감</option>
        </select></p>
        <p>공급 단가: <input type="number" name="product_price" style="width:100%; padding:8px;" required></p>
        <p>상세 명세: <textarea name="product_description" style="width:100%; height:8px0px; padding:8px;"></textarea></p>
        <p style="background:#fff3bf; padding:10px; border-radius:4px;">📸 이미지 증빙 (웹쉘 업로드 허용): <input type="file" name="product_image"></p>
        <button type="submit" style="width:100%; padding:12px; background:#333; color:white; border:none; font-weight:bold; cursor:pointer;">카탈로그 배포 등록</button>
    </form>
</div>
</body>
</html>

<?php
// product_add_proc.php
include_once 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'A') { exit('비인가'); }

$product_name        = mysqli_real_escape_string($conn, $_POST['product_name']);
$product_category    = $_POST['product_category'];
$product_price       = intval($_POST['product_price']);
$product_description = mysqli_real_escape_string($conn, $_POST['product_description']);

if (mysqli_query($conn, "INSERT INTO ddm_product (product_category, product_name, product_price, product_description) VALUES ('$product_category', '$product_name', $product_price, '$product_description')")) {
    $product_id = mysqli_insert_id($conn);

    if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] == UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/';
        if (!is_dir($upload_dir)) { mkdir($upload_dir, 0777, true); }

        $origin_name = $_FILES['product_image']['name'];
        // [취약점 유도] 확장자 유효성 검증 일절 누락
        $ext = pathinfo($origin_name, PATHINFO_EXTENSION);
        $save_name = time() . "_" . bin2hex(random_bytes(3)) . "." . $ext;
        
        if (move_uploaded_file($_FILES['product_image']['tmp_name'], $upload_dir . $save_name)) {
            mysqli_query($conn, "INSERT INTO ddm_file (product_id, file_origin_name, file_save_name) VALUES ($product_id, '$origin_name', '$save_name')");
        }
    }
    echo "<script>alert('물품 등록 완료 (물리 업로드 성공)'); location.href='product_add.php';</script>";
}
mysqli_close($conn);
?>

<?php
// product_edit.php
include_once 'db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'A') { exit; }

$product_id = isset($_GET['product_id']) ? intval($_GET['product_id']) : 0;
$product = mysqli_fetch_assoc(mysqli_query($conn, "SELECT p.*, f.file_origin_name, f.file_save_name FROM ddm_product p LEFT JOIN ddm_file f ON p.product_id=f.product_id WHERE p.product_id=$product_id"));
if(!$product) exit('자원 없음');
?>
<!DOCTYPE html>
<html lang="ko">
<head><meta charset="UTF-8"><title>상품 수정</title></head>
<body style="font-family:sans-serif; padding:30px;">
<div style="width:480px; margin:0 auto; background:white; padding:25px; border:1px solid #ccc;">
    <h3>🔄 카탈로그 스펙 수정 제어</h3>
    <form action="product_edit_proc.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
        <p>상품명: <input type="text" name="product_name" value="<?php echo htmlspecialchars($product['product_name']); ?>" style="width:100%; padding:6px;"></p>
        <p>단가: <input type="number" name="product_price" value="<?php echo $product['product_price']; ?>" style="width:100%; padding:6px;"></p>
        <p>기존 증빙: 
            <?php if(!empty($product['file_save_name'])): ?>
                <a href="uploads/<?php echo urlencode($product['file_save_name']); ?>" download="<?php echo htmlspecialchars($product['file_origin_name']); ?>" style="font-weight:bold; color:blue;">[📥 기존 명세 다운로드]</a>
            <?php else: echo "없음"; endif; ?>
        </p>
        <p>미디어 교체 변경: <input type="file" name="product_image"></p>
        <button type="submit" style="width:100%; padding:10px; background:blue; color:white; border:none; cursor:pointer;">데이터 전송 수정</button>
    </form>
</div>
</body>
</html>

<?php
// product_edit_proc.php
include_once 'db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'A') { exit; }

$product_id   = intval($_POST['product_id']);
$product_name = mysqli_real_escape_string($conn, $_POST['product_name']);
$product_price = intval($_POST['product_price']);

if (mysqli_query($conn, "UPDATE ddm_product SET product_name='$product_name', product_price=$product_price WHERE product_id=$product_id")) {
    if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] == UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/';
        $origin_name = $_FILES['product_image']['name'];
        // [취약점 유도] 확장자 체크 누락
        $ext = pathinfo($origin_name, PATHINFO_EXTENSION);
        $save_name = time() . "_edit." . $ext;

        if (move_uploaded_file($_FILES['product_image']['tmp_name'], $upload_dir . $save_name)) {
            mysqli_query($conn, "DELETE FROM ddm_file WHERE product_id=$product_id");
            mysqli_query($conn, "INSERT INTO ddm_file (product_id, file_origin_name, file_save_name) VALUES ($product_id, '$origin_name', '$save_name')");
        }
    }
    echo "<script>alert('명세 갱신 이관 완료'); location.href='shop_list.php';</script>";
}
mysqli_close($conn);
?>

<?php
// product_delete_proc.php
include_once 'db.php';

// [취약점 유도] 고의적인 지사 관리자 세션 유형('A') 판단 검증 생략 미흡 결함
$product_id = isset($_GET['product_id']) ? intval($_GET['product_id']) : 0;

if ($product_id > 0) {
    $file = mysqli_fetch_assoc(mysqli_query($conn, "SELECT file_save_name FROM ddm_file WHERE product_id=$product_id"));
    if ($file) {
        @unlink('uploads/' . $file['file_save_name']);
        mysqli_query($conn, "DELETE FROM ddm_file WHERE product_id=$product_id");
    }
    mysqli_query($conn, "DELETE FROM ddm_product WHERE product_id=$product_id");
    echo "<script>alert('🗑️ [인증 필터 우회] 카탈로그가 소멸 소거되었습니다.'); location.href='shop_list.php';</script>";
}
mysqli_close($conn);
?>