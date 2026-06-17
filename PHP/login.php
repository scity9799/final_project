<?php include 'header.php'; ?>

<div class="d-flex justify-content-center align-items-center" style="min-height: 50vh;">
    <div class="card shadow p-4" style="width: 400px;">
        <h3 class="text-center mb-4" style="color: #6f42c1;">딩동몰 로그인</h3>
        <form action="login_proc.php" method="POST">
            <div class="mb-3">
                <select name="user_type" class="form-select">
                    <option value="C">일반 회원</option>
                    <option value="A">관리자</option>
                </select>
            </div>
            <div class="mb-3">
                <input type="text" name="user_id" class="form-control" placeholder="아이디" required>
            </div>
            <div class="mb-3">
                <input type="password" name="user_pw" class="form-control" placeholder="비밀번호" required>
            </div>
            <button type="submit" class="btn w-100 text-white" style="background-color: #6f42c1;">로그인</button>
        </form>
    </div>
</div>

<?php include 'footer.php'; ?>