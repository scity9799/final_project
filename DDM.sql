-- ========================================================
-- [1단계] 딩동몰 사설 데이터베이스 공간 수립 및 인코딩 UTF-8 설정
-- ========================================================
CREATE DATABASE IF NOT EXISTS ddm_db DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- [2단계] 모의해킹 전용 실습 DB 계정 생성 및 ddm_db 제어 권한 일괄 인가
CREATE USER IF NOT EXISTS 'team04db'@'localhost' IDENTIFIED BY 'team04!';
GRANT ALL PRIVILEGES ON ddm_db.* TO 'team04db'@'localhost';
FLUSH PRIVILEGES;

-- [3단계] 작업 대상을 방금 만든 ddm_db로 정밀 지정 (No database selected 에러 차단)
USE ddm_db;

-- ========================================================
-- [4단계] 안전장치 가동 - 기존에 연습용으로 생성된 구조물 선제 삭제
-- ========================================================
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS ddm_order_detail;
DROP TABLE IF EXISTS ddm_order;
DROP TABLE IF EXISTS ddm_cart;
DROP TABLE IF EXISTS ddm_file;
DROP TABLE IF EXISTS ddm_product;
DROP TABLE IF EXISTS ddd_user; -- 외래키 충돌 방지를 위해 순서대로 파기
SET FOREIGN_KEY_CHECKS = 1;

-- ========================================================
-- [5단계] 마스터 테이블 수립 (외래키 부모 개체 선제 안착)
-- ========================================================

-- (0) 본사 연동 유저 기본 마스터 테이블 (S: 보호소회원, A: 지사관리자)
CREATE TABLE ddd_user (
    user_id VARCHAR(50) NOT NULL,
    password VARCHAR(255) NOT NULL,
    user_type CHAR(1) DEFAULT 'S',
    PRIMARY KEY (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- (1) 상품 마스터 테이블 (SQL 인젝션 및 정렬 주입 공격 벡터)
CREATE TABLE ddm_product (
    product_id INT NOT NULL AUTO_INCREMENT,
    product_category VARCHAR(50) NOT NULL,
    product_name VARCHAR(200) NOT NULL,
    product_price INT NOT NULL,
    product_description TEXT NULL,
    product_reg_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- (2) 증빙 및 이미지 파일 관리 테이블 (Path Traversal 및 웹쉘 다운로드 벡터)
CREATE TABLE ddm_file (
    file_id INT NOT NULL AUTO_INCREMENT,
    product_id INT NOT NULL,
    file_org_name VARCHAR(255) NOT NULL,
    file_save_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    file_size INT NOT NULL,
    file_reg_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (file_id),
    CONSTRAINT FK_ddm_file_product FOREIGN KEY (product_id) 
        REFERENCES ddm_product (product_id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- (3) 임시 장바구니 보관 테이블 (IDOR 변조 주입 및 CSRF 대리 요청 벡터)
CREATE TABLE ddm_cart (
    cart_id INT NOT NULL AUTO_INCREMENT,
    user_id VARCHAR(50) NOT NULL,
    product_id INT NOT NULL,
    cart_quantity INT NOT NULL DEFAULT 1,
    cart_reg_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (cart_id),
    CONSTRAINT FK_ddm_cart_user FOREIGN KEY (user_id) 
        REFERENCES ddd_user (user_id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT FK_ddm_cart_product FOREIGN KEY (product_id) 
        REFERENCES ddm_product (product_id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- (4) 최종 결제주문 통제 마스터 테이블 (CSRF 원클릭 구매 유도 벡터)
CREATE TABLE ddm_order (
    order_id INT NOT NULL AUTO_INCREMENT,
    user_id VARCHAR(50) NOT NULL,
    order_total_price INT NOT NULL,
    order_status VARCHAR(50) NOT NULL DEFAULT '주문완료',
    order_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (order_id),
    CONSTRAINT FK_ddm_order_user FOREIGN KEY (user_id) 
        REFERENCES ddd_user (user_id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- (5) 영수증 세부 정산 명세 테이블 (IDOR 수평적 타인 거래 내역 탈취 벡터)
CREATE TABLE ddm_order_detail (
    detail_id INT NOT NULL AUTO_INCREMENT,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    detail_quantity INT NOT NULL,
    detail_price INT NOT NULL,
    PRIMARY KEY (detail_id),
    CONSTRAINT FK_ddm_detail_order FOREIGN KEY (order_id) 
        REFERENCES ddm_order (order_id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT FK_ddm_detail_product FOREIGN KEY (product_id) 
        REFERENCES ddm_product (product_id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ========================================================
-- [6단계] 시뮬레이션 및 상호 연동 확인용 실습 코어 데이터 주입
-- ========================================================

-- (0) 본사 연동 유저 크레덴셜 사전 적재
INSERT INTO ddd_user (user_id, password, user_type) VALUES ('shelter01', 'pass1234', 'S');
INSERT INTO ddd_user (user_id, password, user_type) VALUES ('shelter02', 'pass5678', 'S');
INSERT INTO ddd_user (user_id, password, user_type) VALUES ('admin01', 'admin1234', 'A');

-- (1) 자재 상품 도메인 주입
INSERT INTO ddm_product (product_id, product_category, product_name, product_price, product_description) VALUES 
(1, '사료', '[대용량] 딩동몰 인섹트 소프트 사료 5kg', 42000, '알레르기 개선과 눈물 자국 완화에 도움을 주는 가수분해 곤충 사료입니다.'),
(2, '사료', '[5개세트할인] 딩동몰 프리미엄 동결건조 북어 트릿', 29000, '첨가물 없이 100% 신선한 원물 그대로 동결건조한 건강 사료 및 간식 세트입니다.'),
(3, '사료', '딩동몰 연어 품은 미트 정보 캔 (12개입)', 24000, '음수량 충족과 영양 보충을 위한 부드러운 유기견·반려견 전용 습식 캔입니다.'),
(4, '사료', '[기획전] 딩동몰 덴탈 케어 츄 껌 (대용량 버킷)', 35000, '매일 먹이면서 치석 제거와 구강 건강을 동시에 챙기는 기능성 껌입니다.'),
(5, '용품', '[5개세트할인] 딩동몰 이지 바잇 그립 (삼킴 방지 간식 홀더)', 15000, '반려견이 간식을 급하게 삼키다 목에 걸리는 사고를 방지해 주는 안전 가드 홀더입니다.'),
(6, '용품', '[9주년 생일파티] 딩동몰 아이스베어 듀라론 기능성 냉감 쿨매트 (S)', 19800, '체온을 빠르게 낮춰주는 듀라론 신소재를 사용해 여름철 무더위를 식혀주는 쿨매트입니다.'),
(7, '용품', '[5개세트할인] 딩동몰 브러시미 플러스 반려동물 보습 미스트', 18500, '건조한 피모에 수분을 공급하고 죽은 털 제거와 엉킴 방지에 탁월한 보습 미스트입니다.'),
(8, '용품', '[5개세트할인] 딩동몰 마이크로화이버 펫타올 드라이미', 12000, '일반 타올보다 흡수력이 5배 빨라 목욕 후 드라이 시간을 획기적으로 줄여주는 극세사 타올입니다.');

-- (2) 파일 시스템 스펙 인덱스 적재
INSERT INTO ddm_file (product_id, file_org_name, file_save_name, file_path, file_size) VALUES 
(1, 'insect_feed.jpg', '20260617_feed_01.jpg', '/uploads/products/', 1104857),
(2, 'pollock_treat.jpg', '20260617_feed_02.jpg', '/uploads/products/', 945120),
(3, 'salmon_can.jpg', '20260617_feed_03.jpg', '/uploads/products/', 852412),
(4, 'dental_chew.jpg', '20260617_feed_04.jpg', '/uploads/products/', 1204512),
(5, 'snack_holder.jpg', '20260617_item_01.jpg', '/uploads/products/', 612400),
(6, 'cool_mat.jpg', '20260617_item_02.jpg', '/uploads/products/', 1545120),
(7, 'coat_mist.jpg', '20260617_item_03.jpg', '/uploads/products/', 743120),
(8, 'dry_towel.jpg', '20260617_item_04.jpg', '/uploads/products/', 524100);

-- (3) 사용자 장바구니 적재
INSERT INTO ddm_cart (cart_id, user_id, product_id, cart_quantity) VALUES 
(1, 'shelter01', 1, 1),
(2, 'shelter01', 5, 2),
(3, 'shelter02', 6, 1);

-- (4) 발주 마스터 명세 주입
INSERT INTO ddm_order (order_id, user_id, order_total_price, order_status) VALUES 
(1, 'shelter01', 72000, '배송중'),
(2, 'shelter02', 19800, '주문완료');

-- (5) 주문 거래 명세서 세부 내역 적재
INSERT INTO ddm_order_detail (order_id, product_id, detail_quantity, detail_price) VALUES 
(1, 1, 1, 42000),
(1, 5, 2, 15000),
(2, 6, 1, 19800);

-- [7단계] 수립 연동 결과 검증용 커맨드 가동 후 완전 이탈
SHOW TABLES;
SELECT * FROM ddm_product