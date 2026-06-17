<?php 
// admin_login.php : 지사 전용 관리자 로그인 화면
include 'header.php'; 
?>
<h2>지사 책임 관리자 통합 인증 관문</h2>
<div style="border: 2px solid #ffcc00; padding: 20px; background: #fffdf0; width:380px;">
    <form action="admin_login_proc.php" method="POST">
        <p>본부 책임 마스터 ID: <input type="text" name="admin_id" style="width: 100%; padding: 5px;" required></p>
        <p>중앙 통제 패스워드: <input type="password" name="password" style="width: 100%; padding: 5px;" required></p>
        <button type="submit" style="width:100%; padding:8px; background:#ffa000; font-weight:bold; border:0; cursor:pointer;">통제 본부 서명 요청</button>
    </form>
</div>
<?php include 'footer.php'; ?>