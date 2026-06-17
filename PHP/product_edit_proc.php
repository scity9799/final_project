<?php include 'db.php';
// [취약점] File Upload: 확장자 제한 없는 업로드 (웹쉘 업로드 가능)
if ($_FILES["product_img"]["name"]) {
    $filename = $_FILES["product_img"]["name"]; // 공격자가 webshell.php 등을 업로드 시 실행됨
    move_uploaded_file($_FILES["product_img"]["tmp_name"], "uploads/" . $filename);
    $conn->query("UPDATE product SET img_path = '$filename' WHERE product_number = '{$_POST['p_no']}'");
}
// ... 정보 업데이트 로직 ...
?>