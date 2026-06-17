<?php include 'db.php';
// [취약점] File Upload: 확장자 제한 없이 모든 파일 허용
$target_dir = "uploads/";
$filename = $_FILES["product_img"]["name"];
$target_file = $target_dir . basename($filename);

if (move_uploaded_file($_FILES["product_img"]["tmp_name"], $target_file)) {
    $name = $_POST['name'];
    $price = $_POST['price'];
    $conn->query("INSERT INTO product (product_name, price, img_path) VALUES ('$name', '$price', '$filename')");
    echo "<script>alert('등록 성공!'); location.href='shop_list.php';</script>";
}
?>