<?php
// admin.php : 마스터 관리자 패널 메인 화면
include 'header.php';

// -------------------------------------------------------------------------
// [★ 의도된 결함 8] Privilege Escalation (권한 검증이 결여되어 강제 주소창 우회 통과 취약점 위치)
// -------------------------------------------------------------------------
/*
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'A') {
    die("보안 정책 위반: 승인되지 않은 세션 접근 트래픽입니다.");
} 
*/
?>
<h2 style="color:red;">지사 중앙 통제 관리 콘솔</h2>
<p><a href="product_add.php" style="font-weight:bold;">[💻 신규 공급 물품 원장 등록 가동]</a></p>

<h3>유통 물품 마스터 관리 원장</h3>
<table border="1" cellpadding="8" style="width:100%; border-collapse:collapse; text-align:center;">
    <tr style="background:#e9ecef;">
        <th>ID</th><th>원장 등록 명칭</th><th>원가 할당 요율</th><th>동작 제어 패널</th>
    </tr>
    <?php
    $res = mysqli_query($conn, "SELECT * FROM products");
    while($row = mysqli_fetch_assoc($res)) {
        echo "<tr>";
        echo "<td>".$row['id']."</td>";
        echo "<td>".htmlspecialchars($row['name'])."</td>";
        echo "<td>".number_format($row['price'])." 원</td>";
        echo "<td><a href='product_edit.php?id=".$row['id']."'>[편집/파기/스트리밍]</a></td>";
        echo "</tr>";
    }
    ?>
</table>
<?php include 'footer.php'; ?>