CREATE DATABASE IF NOT EXISTS vulnlab DEFAULT CHARACTER SET utf8mb4;

CREATE USER IF NOT EXISTS 'team04db'@'localhost' IDENTIFIED BY 'team04!';
GRANT ALL PRIVILEGES ON ddm_db.* TO 'team04db'@'localhost';
FLUSH PRIVILEGES;

USE ddm_db;

-- ========================================================
-- [안전 조치] 기존에 테스트로 만들었을지 모를 신규 쇼핑몰 테이블만 삭제합니다.
-- (기존 ddd_user, ddd_admin 테이블은 절대 건드리지 않습니다.)
-- ========================================================
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS ddm_order_detail;
DROP TABLE IF EXISTS ddm_order;
DROP TABLE IF EXISTS ddm_cart;
DROP TABLE IF EXISTS ddm_file;
DROP TABLE IF EXISTS ddm_product;
SET FOREIGN_KEY_CHECKS = 1;


-- ========================================================
-- 1. 쇼핑몰 신규 테이블 생성 (ddm_xxx)
-- ========================================================

-- (1) 상품 테이블
CREATE TABLE ddm_product (
    product_id INT NOT NULL AUTO_INCREMENT,
    product_category VARCHAR(50) NOT NULL,
    product_name VARCHAR(200) NOT NULL,
    product_price INT NOT NULL,
    product_description TEXT NULL,
    product_reg_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- (2) 파일 관리 테이블 (ddm_product 참조)
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

-- (3) 장바구니 테이블 (기존 ddd_user 및 신규 ddm_product 참조)
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

-- (4) 주문 마스터 테이블 (기존 ddd_user 참조)
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

-- (5) 주문 상세 테이블 (ddm_order 및 ddm_product 참조)
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
