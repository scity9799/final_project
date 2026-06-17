<?php include 'db.php'; include 'header.php';

$user_id = $_SESSION['user_id'];
$query = "SELECT * FROM cart WHERE user_id = '$user_id'";
$result = $conn->query($query);
?>

<h2 class="text-purple">내 장바구니</h2>
<table class="table">
    <thead><tr><th>상품명</th><th>수량</th><th>삭제</th></tr></thead>
    <tbody>
    <?php while($row = $result->fetch_assoc()): ?>
    <tr>
        <td>상품번호: <?= $row['product_number'] ?></td>
        <td><?= $row['quantity'] ?></td>
        <td>
            <a href="cart_delete_proc.php?cart_id=<?= $row['cart_id'] ?>" class="btn btn-danger btn-sm">삭제</a>
        </td>
    </tr>
    <?php endwhile; ?>
    </tbody>
</table>

<?php include 'footer.php'; ?>