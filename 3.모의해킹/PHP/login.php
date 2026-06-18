<?php
include 'header.php'; // 공통 헤더 조립
?>

<div class="container d-flex justify-content-center align-items-center" style="min-height: 80vh;">
    <div class="card shadow-sm p-4" style="width: 100%; max-width: 420px; border-radius: 15px; border-top: 5px solid #6f42c1;">
        <div class="text-center mb-4">
            <h3 class="fw-bold" style="color: #6f42c1;">🐾 딩동몰 로그인</h3>
            <p class="text-muted small">보호소 파트너 및 관리자 전용 인증</p>
        </div>

<form action="login_proc.php" method="POST" id="loginForm">
    <div class="mb-3">
        <label for="user_id" class="form-label text-secondary small fw-bold">아이디</label>
        <input type="text" class="form-style form-control" id="user_id" name="user_id" required>
    </div>
    <div class="mb-4">
        <label for="user_pw" class="form-label text-secondary small fw-bold">비밀번호</label>
        <input type="password" class="form-style form-control" id="user_pw" name="user_pw" required>
    </div>

    <div class="form-check mb-4">
        <input class="form-check-input" type="checkbox" value="admin" id="login_type" name="login_type">
        <label class="form-check-label text-secondary small" for="login_type">
            🛡️ <strong>관리자로 로그인</strong>
        </label>
    </div>

    <button type="submit" class="btn w-100 text-white p-2.5 fw-bold" style="background-color: #6f42c1;">로그인</button>
</form>
    </div>
</div>

<style>
.form-control:focus {
    border-color: #6f42c1;
    box-shadow: 0 0 0 0.25rem rgba(111, 66, 193, 0.25);
}
.form-style {
    border-radius: 8px;
}
</style>

<?php
include 'footer.php'; // 공통 푸터 조립
?>