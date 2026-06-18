<?php
include 'db.php';
include 'header.php';

// 1. 로그인 상태 체크
if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('로그인이 필요한 서비스입니다, 왈!'); location.href='login.php';</script>";
    exit;
}

$session_id = $_SESSION['user_id'];

// 2. 장바구니 조인 쿼리
$sql = "SELECT c.cart_id, c.product_id, c.cart_quantity, 
               p.product_name, p.product_price, p.product_image, p.product_category 
        FROM ddm_cart c 
        JOIN ddm_product p ON c.product_id = p.product_id 
        WHERE c.user_id = '$session_id' 
        ORDER BY c.cart_id DESC";

$result = mysqli_query($conn, $sql);
$cart_count = mysqli_num_rows($result);
$total_cart_price = 0;
?>

<form id="orderForm" action="cart_order_proc.php" method="POST">
<div class="container my-5" style="min-height: 75vh;">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="text-dark fw-bold border-start border-4 border-indigo ps-2" style="border-color: #6f42c1 !important;">
            🛒 내 장바구니
        </h3>
        <span class="text-muted">담은 상품 <?=$cart_count?>개</span>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm p-4" style="border-radius: 16px;">
                <table class="table align-middle">
                    <thead class="text-muted small text-uppercase" style="background-color: #fcfbff;">
                        <tr>
                            <th style="width: 40px;">선택</th>
                            <th>상품 정보</th>
                            <th class="text-center">가격</th>
                            <th class="text-center">수량</th>
                            <th class="text-center">합계</th>
                            <th class="text-center">삭제</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($cart_count > 0) {
                            while ($row = mysqli_fetch_assoc($result)) {
                                $subtotal = $row['product_price'] * $row['cart_quantity'];
                                $total_cart_price += $subtotal;
                                $img_path = !empty($row['product_image']) ? $row['product_image'] : 'images/no_image.png';
                        ?>
                            <tr id="item_row_<?=$row['cart_id']?>">
                                <td>
                                    <input type="checkbox" name="cart_ids[]" value="<?=$row['cart_id']?>" 
                                           data-subtotal="<?=$subtotal?>" class="form-check-input cart-checkbox" onclick="calcTotal()" checked>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        <img src="<?=htmlspecialchars($img_path)?>" onerror="this.src='images/no_image.png'" style="width: 70px; height: 70px; object-fit: cover; border-radius: 8px;">
                                        <div>
                                            <span class="badge" style="background: #ebe5fc; color: #6f42c1;"><?=htmlspecialchars($row['product_category'])?></span>
                                            <h6 class="fw-bold m-0 mt-1"><?=htmlspecialchars($row['product_name'])?></h6>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center"><?=number_format($row['product_price'])?>원</td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center align-items-center">
                                        <input type="number" id="qty_<?=$row['cart_id']?>" value="<?=$row['cart_quantity']?>" 
                                               min="1" class="form-control form-control-sm text-center" style="width: 75px;" 
                                               onchange="updateQuantity(<?=$row['cart_id']?>)">
                                    </div>
                                </td>
                                <td class="text-center fw-bold"><?=number_format($subtotal)?>원</td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-link text-decoration-none p-0" onclick="deleteCartItem(<?=$row['cart_id']?>)">❌</button>
                                </td>
                            </tr>
                        <?php } 
                        } else {
                            echo '<tr><td colspan="6" class="text-center py-5">장바구니가 비어있습니다, 왈! 🐶</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm p-4" style="background-color: #fdfcff; border-radius: 16px;">
                <h5 class="fw-bold mb-4">💳 결제 금액</h5>
                <div class="d-flex justify-content-between mb-2">
                    <span>총 상품 금액</span>
                    <span class="fw-bold" id="displayTotal"><?=number_format($total_cart_price)?>원</span>
                </div>
                <hr>
                <div class="d-flex justify-content-between mb-4">
                    <span class="fw-bold fs-5">최종 결제 금액</span>
                    <span class="fw-bold fs-4 text-danger" id="displayFinal"><?=number_format($total_cart_price)?>원</span>
                </div>
                <button type="submit" class="btn text-white w-100 py-3 fw-bold" style="background-color: #6f42c1;" <?=$cart_count == 0 ? 'disabled' : ''?>>
                    주문하기 🦴
                </button>
            </div>
        </div>
    </div>
    </form>
</div>

<script>
// 1. 체크박스 선택/해제 연동 계산기
function calcTotal() {
    var checkboxes = document.querySelectorAll('.cart-checkbox');
    var currentTotal = 0;
    checkboxes.forEach(function(box) {
        if (box.checked) {
            currentTotal += parseInt(box.getAttribute('data-subtotal'));
        }
    });
    var formattedPrice = currentTotal.toLocaleString() + '원';
    document.getElementById('displayTotal').innerText = formattedPrice;
    document.getElementById('displayFinal').innerText = formattedPrice;
}

// 2. 💡 [자동 실시간 변경 핵심 함수]: 수량 입력을 조작하면 대기 없이 백엔드로 동적 폼 전송 처리
function updateQuantity(cartId) {
    var qty = document.getElementById('qty_' + cartId).value;
    
    // 수량이 1 미만으로 떨어지는 비정상적 조작 가드
    if(qty < 1) {
        alert('수량은 1개 이상이어야 합니다, 왈!');
        document.getElementById('qty_' + cartId).value = 1;
        return;
    }

    var form = document.createElement('form');
    form.method = 'POST';
    form.action = 'cart_update_proc.php';
    
    var inputId = document.createElement('input');
    inputId.type = 'hidden';
    inputId.name = 'cart_id';
    inputId.value = cartId;
    form.appendChild(inputId);
    
    var inputQty = document.createElement('input');
    inputQty.type = 'hidden';
    inputQty.name = 'cart_quantity';
    inputQty.value = qty;
    form.appendChild(inputQty);
    
    document.body.appendChild(form);
    form.submit();
}

// 3. 삭제 처리 독립 바인딩
function deleteCartItem(cartId) {
    if (confirm('선택하신 상품을 장바구니에서 삭제하시겠습니까, 왈?')) {
        window.location.href = 'cart_delete_proc.php?cart_id=' + cartId;
    }
}
</script>

<?php include 'footer.php'; ?>