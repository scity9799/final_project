-- 1. 오타가 교정된 정확한 데이터베이스 생성
CREATE DATABASE IF NOT EXISTS ddm_db DEFAULT CHARACTER SET utf8mb4;

-- 2. ★ 중요: 웹 서버의 통신 접근을 무조건 수용하도록 와일드카드(%) 유저 생성
CREATE USER IF NOT EXISTS 'team04db'@'%' IDENTIFIED BY 'team04!';

-- 3. 생성된 원격 계정에 ddm_db 쇼핑몰 데이터베이스 마스터 권한 부여
GRANT ALL PRIVILEGES ON ddm_db.* TO 'team04db'@'%';

-- 4. 변경된 유저 권한 테이블을 메모리에 즉시 반영
FLUSH PRIVILEGES;

-- 5. 데이터베이스 전환 및 더미 데이터 주입 대기 상태 진입
USE ddm_db;


-- 1. 안전하게 테이블 삭제 (외래키 무시)
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS ddm_order;
DROP TABLE IF EXISTS ddm_cart;
DROP TABLE IF EXISTS ddm_product;
DROP TABLE IF EXISTS ddd_user_shelter;
DROP TABLE IF EXISTS ddd_admin;
DROP TABLE IF EXISTS ddd_user;
SET FOREIGN_KEY_CHECKS = 1;

-- 2. 테이블 생성
-- 1. 유저 테이블 (기본)
CREATE TABLE ddd_user (
    user_number INT AUTO_INCREMENT PRIMARY KEY,
    user_type CHAR(1) NOT NULL CHECK (user_type IN ('c', 's')),
    user_id VARCHAR(100) NOT NULL UNIQUE,
    user_password VARCHAR(100) NOT NULL,
    user_name VARCHAR(100) NOT NULL,
    user_nickname VARCHAR(50) NOT NULL UNIQUE,
    user_gender CHAR(1) NOT NULL CHECK (user_gender IN ('f', 'm')),
    user_birth VARCHAR(50) NOT NULL,
    user_phone VARCHAR(20) NOT NULL UNIQUE,
    user_address VARCHAR(100),
    user_email VARCHAR(100) NOT NULL UNIQUE,
    user_status VARCHAR(10) DEFAULT 'kind' NOT NULL CHECK (user_status IN ('kind', 'black', 'withdraw'))
);

-- 2. 관리자 테이블
CREATE TABLE ddd_admin (
    admin_number INT AUTO_INCREMENT PRIMARY KEY,
    admin_id VARCHAR(100) NOT NULL UNIQUE,
    admin_password VARCHAR(100) NOT NULL,
    user_type CHAR(1) DEFAULT 'a' NOT NULL CHECK (user_type = 'a')
);

-- 3. 보호소 추가 정보 테이블 (user_number 외래키 연결)
CREATE TABLE ddd_user_shelter (
    user_number INT PRIMARY KEY,
    shelter_name VARCHAR(100) NOT NULL,
    shelter_business_number VARCHAR(100) NOT NULL,
    shelter_zipcode VARCHAR(20) NOT NULL,
    shelter_address VARCHAR(300) NOT NULL,
    shelter_address_detail VARCHAR(300),
    shelter_certification VARCHAR(20) DEFAULT 'n' NOT NULL CHECK (shelter_certification IN ('n', 'y')),
    CONSTRAINT fk_user_shelter FOREIGN KEY (user_number) REFERENCES ddd_user(user_number) ON DELETE CASCADE
);

CREATE TABLE ddm_product (
    product_id INT NOT NULL AUTO_INCREMENT,
    product_category VARCHAR(50) NOT NULL,
    product_name VARCHAR(200) NOT NULL,
    product_price INT NOT NULL,
    product_image VARCHAR(255) DEFAULT 'image_18c0ca.png',
    product_description TEXT NULL,
    product_reg_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (product_id),
    CONSTRAINT chk_product_category CHECK (product_category IN ('feed', 'item'))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE ddm_cart (
    cart_id INT NOT NULL AUTO_INCREMENT,
    user_id VARCHAR(50) NOT NULL,
    product_id INT NOT NULL,
    cart_quantity INT NOT NULL DEFAULT 1,
    PRIMARY KEY (cart_id),
    FOREIGN KEY (product_id) REFERENCES ddm_product(product_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE ddm_order (
    order_id INT NOT NULL AUTO_INCREMENT,
    user_id VARCHAR(50) NOT NULL,
    product_id INT NOT NULL,
    product_count INT NOT NULL DEFAULT 1,
    product_name VARCHAR(255) NOT NULL,
    order_price INT NOT NULL,
    order_address VARCHAR(255) DEFAULT '기본 주소지 미등록',
    order_status VARCHAR(50) DEFAULT '배송준비중',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (order_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. 상품 데이터 주입 (카테고리: 'feed', 'item'으로 통일)

INSERT INTO ddm_product (product_id, product_category, product_name, product_price, product_image, product_description, product_reg_date) VALUES
(1, 'feed', '수제 간식', 42000, 'uploads/6.jfif', '알레르기 개선 사료', '2026-06-19 00:08:36'),
(2, 'feed', '동결건조 북어 트릿', 29000, 'uploads/건강한 간식.jpg', '100% 신선 원물', '2026-06-19 00:08:36'),
(3, 'feed', '연어 습식 캔', 24000, 'uploads/9.jfif', '영양 습식 캔', '2026-06-19 00:08:36'),
(4, 'feed', '덴탈 케어 츄 껌', 35000, 'uploads/5.jfif', '치석 제거 껌', '2026-06-19 00:08:36'),
(5, 'feed', '관절 영양 사료', 55000, 'uploads/3.jfif', '관절 케어', '2026-06-19 00:08:36'),
(6, 'feed', '피부 개선 사료', 48000, 'uploads/1.jfif', '피부 보호', '2026-06-19 00:08:36'),
(7, 'feed', '강아지 프리미엄 간식', 15000, 'uploads/8.jfif', '고품질 간식', '2026-06-19 00:08:36'),
(8, 'feed', '황태채', 39000, 'uploads/2.jfif', '민감성 사료', '2026-06-19 00:08:36'),
(9, 'item', '이지 바잇 그립', 15000, 'uploads/gd.jfif', '삼킴 방지 홀더', '2026-06-19 00:08:36'),
(10, 'item', '아이스베어 쿨매트', 19800, 'uploads/images (1).jfif', '냉감 매트', '2026-06-19 00:08:36'),
(11, 'item', '배변봉투', 18500, 'uploads/images.jfif', '피모 보습', '2026-06-19 00:08:36'),
(12, 'item', '마이크로 펫타올', 12000, 'uploads/v.jfif', '극세사 타올', '2026-06-19 00:08:36'),
(13, 'item', '삼킴 방지 홀더', 11000, 'uploads/g.jfif', '안전가드가 있어 강아지의 안전한 식사시간 보장', '2026-06-19 00:08:36'),
(14, 'item', '강화 플라스틱 케이지', 62000, 'uploads/c.jfif', '이동장', '2026-06-19 00:08:36'),
(15, 'item', '자동 급식기', 85000, 'uploads/a.jfif', '스마트 급식기', '2026-06-19 00:08:36'),
(16, 'item', '강아지 이동가방', 45000, 'uploads/q.jfif', '메쉬 가방', '2026-06-19 00:08:36'),
(17, 'item', '실리콘 급수기', 9000, 'uploads/d.jfif', '휴대용 급수기', '2026-06-19 00:08:36'),
(18, 'item', '강아지 하네스(S)', 22000, 'uploads/e.jfif', '안전 하네스', '2026-06-19 00:08:36'),
(19, 'item', '야광 목줄(M)', 15000, 'uploads/f.jfif', '야간 산책용', '2026-06-19 00:08:36'),
(20, 'item', '노즈워크 매트', 28000, 'uploads/s.jfif', '지능 개발 매트', '2026-06-19 00:08:36');

-- 보호소 회원 (ddd_user)
INSERT INTO ddd_user (user_number, user_type, user_id, user_password, user_name, user_nickname, user_gender, user_birth, user_phone, user_address, user_email, user_status) 
VALUES 
(1, 's', 'happy_tails', 'pass1234', '해피테일즈보호소', '해피', 'f', '2015-01-01', '010-5000-0001', '서울시 강남구', 'happytails@ddd.com', 'kind'),
(2, 's', 'hope_shelter', 'pass1234', '희망보호소', '희망쉼터', 'm', '2014-02-10', '010-5000-0002', NULL, 'hope@ddd.com', 'kind'),
(3, 's', 'with_dogs', 'pass1234', '함께걷는보호소', 'hacking', 'f', '2016-03-15', '010-5000-0003', 'hacking_success', 'hacking@ddd.com', 'kind'),
(4, 's', 'blue_paw', 'pass1234', '블루포우보호소', '블루포우', 'm', '2017-04-20', '010-5000-0004', '서울시 송파구', 'bluepaw@ddd.com', 'kind');

-- 관리자 (ddd_admin)
INSERT INTO ddd_admin (admin_number, admin_id, admin_password, user_type) VALUES 
(1, 'admin', 'admin1234', 'a'),
(2, 'superadmin', 'super1234', 'a');

-- 주문 테이블(ddm_order)
INSERT INTO ddm_order (order_id, user_id, product_id, product_count, product_name, order_price, order_address, order_status, created_at) 
VALUES 
(1, 'happy_tails', 7, 1, '강아지 프리미엄 간식', 15000, 'hacking_success', '배송준비중', '2026-06-26 15:51:54'),
(2, 'blue_paw', 13, 1, '삼킴 방지 홀더', 11000, '서울시 송파구', '배송준비중', '2026-06-26 18:02:54'),
(3, 'blue_paw', 4, 1, '덴탈 케어 츄 껌', 35000, '서울시 송파구', '배송준비중', '2026-06-26 18:02:54'),
(4, 'with_dogs', 12, 1, '마이크로 펫타올', 12000, 'hacking_success', '배송준비중', '2026-06-27 10:14:26'),
(5, 'with_dogs', 3, 1, '연어 습식 캔', 24000, 'hacking_success', '배송준비중', '2026-06-27 10:14:26'),
(6, 'with_dogs', 4, 1, '덴탈 케어 츄 껌', 35000, 'hacking_success', '배송준비중', '2026-06-27 10:14:26');

-- 장바구니 테이블(ddm_cart)
INSERT INTO ddm_cart (cart_id, user_id, product_id, cart_quantity) 
VALUES 
(6, 'with_dogs', 19, 1),
(7, 'with_dogs', 16, 1),
(8, 'happy_tails', 1, 100),
(9, 'blue_paw', 9, 1),
(10, 'blue_paw', 17, 2);