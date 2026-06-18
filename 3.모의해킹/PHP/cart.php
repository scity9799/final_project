<?php
include 'db.php';
include 'header.php';

// 1. 로그인 상태 체크
if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('로그인이 필요한 서비스입니다, 왈!'); location.href='login.php';</script>";
    exit;
}

$session_id = $_SESSION['user_id'];

// 2. 조인 쿼리: DB 컬럼명(cart_quantity)과 일치시킴
$sql = "SELECT c.cart_id, c.product_id, c.cart_quantity, 
               p.product_name, p.product_price, p.product_image, p.product_category 
        FROM ddm_cart c 
        JOIN ddm_product p ON c.product_id = p.product_id 
        WHERE c.user_id = '$session_id' 
        ORDER BY c.cart_id DESC";

$result = mysqli_query($conn, $sql);
$total_cart_price = 0;
?>

<div class="container my-5" style="min-height: 75vh;">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="text-dark fw-bold border-start border-4 border-indigo ps-2" style="border-color: #6f42c1 !important;">
            🛒 내 장바구니
        </h3>
        <span class="text-muted">담은 상품 <?=mysqli_num_rows($result)?>개</span>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm p-4" style="border-radius: 16px;">
                <table class="table align-middle">
                    <thead class="text-muted small text-uppercase" style="background-color: #fcfbff;">
                        <tr>
                            <th>상품 정보</th>
                            <th class="text-center">가격</th>
                            <th class="text-center">수량</th>
                            <th class="text-center">합계</th>
                            <th class="text-center">삭제</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if (mysqli_num_rows($result) > 0) {
                            while ($row = mysqli_fetch_assoc($result)) {
                                $subtotal = $row['product_price'] * $row['cart_quantity'];
                                $total_cart_price += $subtotal;
                                $img_path = !empty($row['product_image']) ? $row['product_image'] : 'https://via.placeholder.com/100';
                        ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        <img src="<?=$img_path?>" style="width: 70px; height: 70px; object-fit: cover; border-radius: 8px;">
                                        <div>
                                            <span class="badge" style="background: #ebe5fc; color: #6f42c1;"><?=$row['product_category']?></span>
                                            <h6 class="fw-bold m-0"><?=htmlspecialchars($row['product_name'])?></h6>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center"><?=number_format($row['product_price'])?>원</td>
                                <td class="text-center">
                                    <form action="cart_update.php" method="POST" class="d-flex justify-content-center align-items-center gap-1">
                                        <input type="hidden" name="cart_id" value="<?=$row['cart_id']?>">
                                        <input type="number" name="cart_quantity" value="<?=$row['cart_quantity']?>" class="form-control form-control-sm text-center" style="width: 60px;">
                                        <button type="submit" class="btn btn-sm btn-light" style="color: #6f42c1;">변경</button>
                                    </form>
                                </td>
                                <td class="text-center fw-bold"><?=number_format($subtotal)?>원</td>
                                <td class="text-center">
                                    <a href="cart_delete.php?cart_id=<?=$row['cart_id']?>" class="text-danger" onclick="return confirm('삭제하시겠습니까?');">❌</a>
                                </td>
                            </tr>
                        <?php } 
                        } else {
                            echo '<tr><td colspan="5" class="text-center py-5">장바구니가 비어있습니다, 왈! 🐶</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm p-4" style="background-color: #fdfcff;">
                <h5 class="fw-bold mb-4">💳 결제 금액</h5>
                <div class="d-flex justify-content-between mb-2">
                    <span>총 상품 금액</span>
                    <span class="fw-bold"><?=number_format($total_cart_price)?>원</span>
                </div>
                <hr>
                <div class="d-flex justify-content-between mb-4">
                    <span class="fw-bold fs-5">최종 결제 금액</span>
                    <span class="fw-bold fs-4 text-danger"><?=number_format($total_cart_price)?>원</span>
                </div>
                <form action="order_process.php" method="POST">
                    <button type="submit" class="btn text-white w-100 py-3 fw-bold" style="background-color: #6f42c1;" <?=mysqli_num_rows($result) == 0 ? 'disabled' : ''?>>
                        주문하기 🦴
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>