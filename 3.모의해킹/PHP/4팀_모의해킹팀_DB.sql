CREATE DATABASE IF NOT EXISTS ddm_db DEFAULT CHARACTER SET utf8mb4;

CREATE USER IF NOT EXISTS 'team04db'@'localhost' IDENTIFIED BY 'team04!';
GRANT ALL PRIVILEGES ON ddm_db.* TO 'team04db'@'localhost';
FLUSH PRIVILEGES;

USE ddm_db;

-- 1. 안전하게 테이블 삭제 (외래키 무시)
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS ddm_order_detail;
DROP TABLE IF EXISTS ddm_order;
DROP TABLE IF EXISTS ddm_cart;
DROP TABLE IF EXISTS ddm_file;
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

CREATE TABLE ddm_file (
    file_id INT NOT NULL AUTO_INCREMENT,
    product_id INT NOT NULL,
    file_org_name VARCHAR(255) NOT NULL,
    file_save_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    file_size INT NOT NULL,
    PRIMARY KEY (file_id),
    FOREIGN KEY (product_id) REFERENCES ddm_product(product_id) ON DELETE CASCADE
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
    order_total_price INT NOT NULL,
    order_status VARCHAR(50) DEFAULT '주문완료',
    PRIMARY KEY (order_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE ddm_order_detail (
    detail_id INT NOT NULL AUTO_INCREMENT,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    detail_quantity INT NOT NULL,
    detail_price INT NOT NULL,
    PRIMARY KEY (detail_id),
    FOREIGN KEY (order_id) REFERENCES ddm_order(order_id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES ddm_product(product_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 1. 기존 데이터 초기화 (주의: 데이터가 모두 삭제됨!)
TRUNCATE TABLE ddm_product;

-- 2. 상품 데이터 주입 (카테고리: 'feed', 'item'으로 통일)
INSERT INTO ddm_product (product_category, product_name, product_price, product_description) VALUES 
-- 사료/간식류 (feed)
('feed', '인섹트 소프트 사료 5kg', 42000, '알레르기 개선 사료'),
('feed', '동결건조 북어 트릿', 29000, '100% 신선 원물'),
('feed', '연어 습식 캔', 24000, '영양 습식 캔'),
('feed', '덴탈 케어 츄 껌', 35000, '치석 제거 껌'),
('feed', '관절 영양 사료', 55000, '관절 케어'),
('feed', '피부 개선 사료', 48000, '피부 보호'),
('feed', '강아지 프리미엄 간식', 15000, '고품질 간식'),
('feed', '저알러지 사료', 39000, '민감성 사료'),

-- 용품류 (item)
('item', '이지 바잇 그립', 15000, '삼킴 방지 홀더'),
('item', '아이스베어 쿨매트', 19800, '냉감 매트'),
('item', '보습 미스트', 18500, '피모 보습'),
('item', '마이크로 펫타올', 12000, '극세사 타올'),
('item', '삼킴 방지 홀더', 11000, '안전 가드'),
('item', '강화 플라스틱 케이지', 62000, '이동장'),
('item', '자동 급식기', 85000, '스마트 급식기'),
('item', '강아지 이동가방', 45000, '메쉬 가방'),
('item', '실리콘 급수기', 9000, '휴대용 급수기'),
('item', '강아지 하네스(S)', 22000, '안전 하네스'),
('item', '야광 목줄(M)', 15000, '야간 산책용'),
('item', '노즈워크 매트', 28000, '지능 개발 매트');

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
