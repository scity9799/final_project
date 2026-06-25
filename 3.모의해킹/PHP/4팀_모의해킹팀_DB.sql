-- 1. 오타가 교정된 정확한 데이터베이스 생성
CREATE DATABASE IF NOT EXISTS ddm_db DEFAULT CHARACTER SET utf8mb4;

-- 2. 기존 접속 범위가 잘못 설정된 원격 유저 삭제 (클리어화)
DROP USER IF EXISTS 'team04db'@'10.200.40.18';

-- 3. ★ 중요: 웹 서버의 통신 접근을 무조건 수용하도록 와일드카드(%) 유저 생성
CREATE USER IF NOT EXISTS 'team04db'@'%' IDENTIFIED BY 'team04!';

-- 4. 생성된 원격 계정에 ddm_db 쇼핑몰 데이터베이스 마스터 권한 부여
GRANT ALL PRIVILEGES ON ddm_db.* TO 'team04db'@'%';

-- 5. 변경된 유저 권한 테이블을 메모리에 즉시 반영
FLUSH PRIVILEGES;

-- 6. 데이터베이스 전환 및 더미 데이터 주입 대기 상태 진입
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
(1, 'feed', '프리미엄 연어 사료', 42000, 'uploads/1.jfif', '피부 개선에 도움을 주는 고단백 사료', '2026-06-01 10:00:00'),
(2, 'feed', '동결건조 북어 트릿', 29000, 'uploads/2.jfif', '100% 신선 북어로 만든 건강 간식', '2026-06-02 10:00:00'),
(3, 'feed', '관절 케어 습식 캔', 24000, 'uploads/3.jfif', '소화가 잘되는 부드러운 습식 사료', '2026-06-03 10:00:00'),
(4, 'feed', '덴탈 케어 츄 껌', 35000, 'uploads/4.jfif', '치석 제거와 구강 관리를 동시에', '2026-06-04 10:00:00'),
(5, 'feed', '관절 영양 사료', 55000, 'uploads/5.jfif', '성장기 강아지 관절 건강 사료', '2026-06-05 10:00:00'),
(6, 'feed', '피부 보호 사료', 48000, 'uploads/6.jfif', '민감성 피부를 위한 저알러지 포뮬러', '2026-06-06 10:00:00'),
(7, 'feed', '강아지 고구마 간식', 15000, 'uploads/7.jfif', '달콤하고 소화가 잘되는 천연 간식', '2026-06-07 10:00:00'),
(8, 'feed', '장 건강 유산균 사료', 39000, 'uploads/8.jfif', '변 상태를 건강하게 만드는 유산균 함유', '2026-06-08 10:00:00'),
(9, 'item', '이지 바잇 그립', 15000, 'uploads/9.jfif', '삼킴 방지 기능이 있는 안전 홀더', '2026-06-09 10:00:00'),
(10, 'item', '아이스베어 쿨매트', 19800, 'uploads/10.jfif', '여름철 시원함을 유지하는 냉감 매트', '2026-06-10 10:00:00'),
(11, 'item', '친환경 배변봉투', 18500, 'uploads/11.jfif', '생분해성 소재의 위생적인 배변봉투', '2026-06-11 10:00:00'),
(12, 'item', '마이크로 펫타올', 12000, 'uploads/12.jfif', '강력한 흡수력을 가진 극세사 타올', '2026-06-12 10:00:00'),
(13, 'item', '강아지 안전가드 식기', 11000, 'uploads/13.jfif', '안전가드가 있어 식사 시간이 편안합니다', '2026-06-13 10:00:00'),
(14, 'item', '강화 플라스틱 케이지', 62000, 'uploads/14.jfif', '튼튼하고 이동이 편리한 케이지', '2026-06-14 10:00:00'),
(15, 'item', '스마트 자동 급식기', 85000, 'uploads/15.jfif', '스마트폰으로 제어하는 자동 급식기', '2026-06-15 10:00:00'),
(16, 'item', '메쉬 강아지 이동가방', 45000, 'uploads/16.jfif', '통기성이 좋은 메쉬 소재 이동장', '2026-06-16 10:00:00'),
(17, 'item', '휴대용 실리콘 급수기', 9000, 'uploads/17.jfif', '산책 시 필수인 가벼운 휴대용 물통', '2026-06-17 10:00:00'),
(18, 'item', '안전 하네스 (S)', 22000, 'uploads/18.jfif', '강아지 체형에 딱 맞는 안전 하네스', '2026-06-18 10:00:00'),
(19, 'item', '야광 산책 목줄 (M)', 15000, 'uploads/19.jfif', '야간 산책에도 안전한 LED 목줄', '2026-06-19 10:00:00'),
(20, 'item', '지능 개발 노즈워크 매트', 28000, 'uploads/20.jfif', '강아지의 지능을 높여주는 놀이 매트', '2026-06-20 10:00:00');

-- 보호소 회원 (ddd_user)
INSERT INTO ddd_user (
    user_number, user_type, user_id, user_password, user_name, user_nickname,
    user_gender, user_birth, user_phone, user_email, user_status
) VALUES 
(1, 's', 'happy_tails', 'pass1234', '해피테일즈보호소', '해피테일즈', 'f', '2015-01-01', '010-5000-0001', 'happytails@ddd.com', 'kind'),
(2, 's', 'hope_shelter', 'pass1234', '희망보호소', '희망쉼터', 'm', '2014-02-10', '010-5000-0002', 'hope@ddd.com', 'kind'),
(3, 's', 'with_dogs', 'pass1234', '함께걷는보호소', '함께걷개', 'f', '2016-03-15', '010-5000-0003', 'withdogs@ddd.com', 'kind'),
(4, 's', 'blue_paw', 'pass1234', '블루포우보호소', '블루포우', 'm', '2017-04-20', '010-5000-0004', 'bluepaw@ddd.com', 'kind');

-- 관리자 (ddd_admin)
INSERT INTO ddd_admin (admin_number, admin_id, admin_password, user_type) VALUES 
(1, 'admin', 'admin1234', 'a'),
(2, 'superadmin', 'super1234', 'a');



