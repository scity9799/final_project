<?php
include 'db.php';
session_start();

// 1. 관리자 체크 (보안!)
if ($_SESSION['user_type'] !== 'a') {
    echo "<script>alert('관리자만 가능합니다.'); history.back();</script>";
    exit;
}

// 2. 파일 업로드 처리 (이 코드를 넣는 위치야!)
$upload_dir = 'uploads/'; // 파일을 저장할 폴더명
if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

$img_path = ''; // DB에 저장할 경로 변수

if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] == 0) {
    $filename = time() . '_' . basename($_FILES['product_image']['name']);
    $target_file = $upload_dir . $filename;
    
    if (move_uploaded_file($_FILES['product_image']['tmp_name'], $target_file)) {
        $img_path = $target_file; // 성공 시 경로 저장
    }
}

// 3. DB 데이터 삽입 (이제 $img_path를 같이 넣어줘!)
$name = mysqli_real_escape_string($conn, $_POST['product_name']);
$price = (int)$_POST['product_price'];
$desc = mysqli_real_escape_string($conn, $_POST['product_description']);
$cate = mysqli_real_escape_string($conn, $_POST['product_category']);

$sql = "INSERT INTO ddm_product (product_name, product_price, product_description, product_category, product_image) 
        VALUES ('$name', $price, '$desc', '$cate', '$img_path')";

if (mysqli_query($conn, $sql)) {
    echo "<script>alert('상품이 등록되었습니다, 왈!'); location.href='shop_list.php';</script>";
} else {
    echo "<script>alert('DB 저장 실패: " . mysqli_error($conn) . "'); history.back();</script>";
}
?>