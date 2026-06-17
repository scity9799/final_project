<?php include 'db.php'; include 'header.php';

$user_id = $_SESSION['user_id'];
// 해당 유저의 주문 내역만 조회
$query = "SELECT * FROM orders WHERE user_id = '$user_id'";
$result = $conn->query($query);
?>

<h2 class="text-purple">마이페이지</h2>
<table class="table mt-4">
    <thead><tr><th>주문번호</th><th>주문일자</th><th>상세보기</th></tr></thead>
    <tbody>
    <?php while($row = $result->fetch_assoc()): ?>
    <tr>
        <td><?= $row['order_id'] ?></td>
        <td><?= $row['order_date'] ?></td>
        <td><a href="order_view.php?order_id=<?= $row['order_id'] ?>" class="btn btn-sm btn-purple text-white">조회</a></td>
    </tr>
    <?php endwhile; ?>
    </tbody>
</table>

<?php include 'footer.php'; ?>