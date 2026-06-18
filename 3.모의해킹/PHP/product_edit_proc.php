<?php
include 'db.php';
session_start();

$product_id = $_POST['product_id'];
$category = $_POST['product_category'];
$price = $_POST['product_price'];
$name = $_POST['product_name'];

// 이미지 변경 여부 체크
if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] == 0) {
    $target_path = "uploads/" . $_FILES['product_image']['name'];
    move_uploaded_file($_FILES['product_image']['tmp_name'], $target_path);
    // 새 이미지가 등록되면 경로 업데이트
    $sql = "UPDATE ddm_product SET product_category='$category', product_name='$name', product_price=$price, product_image='$target_path' WHERE product_id=$product_id";
} else {
    // 이미지를 변경하지 않는 경우
    $sql = "UPDATE ddm_product SET product_category='$category', product_name='$name', product_price=$price WHERE product_id=$product_id";
}

$result = mysqli_query($conn, $sql);

if ($result) {
    echo "<script>alert('상품 정보가 성공적으로 변경되었습니다!'); location.href='shop_list.php';</script>";
} else {
    echo "<script>alert('수정 실패'); history.back();</script>";
}
?>