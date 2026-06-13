-- =========================================================
-- 0. 데이터베이스 생성 및 UTF-8 문자셋 초기 설정
-- =========================================================
CREATE DATABASE IF NOT EXISTS ddd_db 
DEFAULT CHARACTER SET utf8mb4 
DEFAULT COLLATE utf8mb4_unicode_ci;

USE ddd_db;

-- SET 설정으로 클라이언트-서버 통신 문자셋 강제 통일
SET NAMES utf8mb4;

-- =========================================================
-- 1. 공통 회원 테이블 생성
-- =========================================================
DROP TABLE IF EXISTS ddd_user;
CREATE TABLE ddd_user (
    user_number     INT,
    user_type        CHAR(1)         NOT NULL,
    user_id          VARCHAR(100)    NOT NULL,
    user_password    VARCHAR(100)    NOT NULL,
    user_name        VARCHAR(100)    NOT NULL,
    user_nickname    VARCHAR(50)     NOT NULL,
    user_gender      CHAR(1)         NOT NULL,
    user_birth       VARCHAR(50)     NOT NULL,
    user_phone       VARCHAR(20)     NOT NULL,
    user_email       VARCHAR(100)    NOT NULL,
    user_status      VARCHAR(10)     DEFAULT 'kind' NOT NULL,
    
    CONSTRAINT pk_user PRIMARY KEY (user_number),
    CONSTRAINT uk_user_id UNIQUE (user_id),
    CONSTRAINT uk_user_nick UNIQUE (user_nickname),
    CONSTRAINT uk_user_phone UNIQUE (user_phone),
    CONSTRAINT uk_user_email UNIQUE (user_email),
    CONSTRAINT ck_user_type CHECK (user_type IN ('C', 'S')),
    CONSTRAINT ck_user_gender CHECK (user_gender IN ('F', 'M')),
    CONSTRAINT ck_user_status CHECK (user_status IN ('kind', 'black', 'withdraw'))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- =========================================================
-- 2. 관리자 테이블 생성
-- =========================================================
DROP TABLE IF EXISTS ddd_admin;
CREATE TABLE ddd_admin (
    admin_number     INT AUTO_INCREMENT,
    admin_id         VARCHAR(100)    NOT NULL,
    admin_password   VARCHAR(100)    NOT NULL,
    user_type        CHAR(1)         DEFAULT 'A' NOT NULL,
    
    CONSTRAINT pk_admin PRIMARY KEY (admin_number),
    CONSTRAINT uk_admin_id UNIQUE (admin_id),
    CONSTRAINT ck_admin_type CHECK (user_type = 'A')
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- 3. 회원 상세 정보 테이블들 생성
-- =========================================================
DROP TABLE IF EXISTS ddd_user_common;
CREATE TABLE ddd_user_common (
    user_number            INT,
    common_report_count    INT DEFAULT 0 NOT NULL,
    
    CONSTRAINT pk_user_common PRIMARY KEY (user_number),
    CONSTRAINT fk_user_common_user FOREIGN KEY (user_number) REFERENCES ddd_user(user_number) ON DELETE CASCADE,
    CONSTRAINT ck_common_report_count CHECK (common_report_count >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS ddd_user_shelter;
CREATE TABLE ddd_user_shelter (
    user_number               INT,
    shelter_name              VARCHAR(100)    NOT NULL,
    shelter_business_number   VARCHAR(100)    NOT NULL,
    shelter_zipcode           VARCHAR(20)     NOT NULL,
    shelter_address           VARCHAR(300)    NOT NULL,
    shelter_address_detail    VARCHAR(300),
    shelter_certification     VARCHAR(20)     DEFAULT 'N' NOT NULL,
    
    CONSTRAINT pk_user_shelter PRIMARY KEY (user_number),
    CONSTRAINT fk_user_shelter_user FOREIGN KEY (user_number) REFERENCES ddd_user(user_number) ON DELETE CASCADE,
    CONSTRAINT ck_shelter_cert CHECK (shelter_certification IN ('N', 'Y'))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- 4. 멍카이브(유기견) 관련 테이블 생성
-- =========================================================
DROP TABLE IF EXISTS ddd_archive;
CREATE TABLE ddd_archive (
    dog_number           INT AUTO_INCREMENT,
    user_number          INT            NOT NULL,
    dog_name             VARCHAR(50)    NOT NULL,
    dog_breed            VARCHAR(50),
    dog_gender           CHAR(1)        NOT NULL,
    dog_age              VARCHAR(20),
    dog_weight           INT,
    dog_safe_date        DATE,
    dog_archive_date     DATETIME       DEFAULT NOW() NOT NULL,
    archive_modify_date  DATETIME       DEFAULT NOW(),
    dog_detail           TEXT,
    
    CONSTRAINT pk_archive PRIMARY KEY (dog_number),
    CONSTRAINT fk_archive_user FOREIGN KEY (user_number) REFERENCES ddd_user(user_number) ON DELETE CASCADE,
    CONSTRAINT ck_archive_gender CHECK (dog_gender IN ('F', 'M')),
    CONSTRAINT ck_archive_weight CHECK (dog_weight IS NULL OR dog_weight > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS ddd_archive_score;
CREATE TABLE ddd_archive_score (
    dog_number         INT,
    dog_activity       INT NOT NULL,
    dog_sociality      INT NOT NULL,
    dog_independence   INT NOT NULL,
    dog_barking        INT NOT NULL,
    dog_grooming       INT NOT NULL,
    
    CONSTRAINT pk_archive_score PRIMARY KEY (dog_number),
    CONSTRAINT fk_archive_score FOREIGN KEY (dog_number) REFERENCES ddd_archive(dog_number) ON DELETE CASCADE,
    CONSTRAINT ck_score_activity CHECK (dog_activity BETWEEN 1 AND 5),
    CONSTRAINT ck_score_sociality CHECK (dog_sociality BETWEEN 1 AND 5),
    CONSTRAINT ck_score_independence CHECK (dog_independence BETWEEN 1 AND 5),
    CONSTRAINT ck_score_barking CHECK (dog_barking BETWEEN 1 AND 5),
    CONSTRAINT ck_score_grooming CHECK (dog_grooming BETWEEN 1 AND 5)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS ddd_archive_img;
CREATE TABLE ddd_archive_img (
    archive_img_number   INT AUTO_INCREMENT,
    dog_number           INT            NOT NULL,
    archive_img_name     VARCHAR(200)   NOT NULL,
    archive_img_path     VARCHAR(200),
    
    CONSTRAINT pk_archive_img PRIMARY KEY (archive_img_number),
    CONSTRAINT fk_archive_img FOREIGN KEY (dog_number) REFERENCES ddd_archive(dog_number) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- 5. 매칭 시스템 테이블 생성
-- =========================================================
DROP TABLE IF EXISTS ddd_match_result;
CREATE TABLE ddd_match_result (
    result_number        INT AUTO_INCREMENT,
    user_number          INT          NOT NULL,
    dog_activity         INT,
    dog_sociality        INT,
    dog_independence     INT,
    dog_barking          INT,
    dog_grooming         INT,
    result_created_date  DATETIME     DEFAULT NOW() NOT NULL,
    
    CONSTRAINT pk_match_result PRIMARY KEY (result_number),
    CONSTRAINT fk_match_result_user FOREIGN KEY (user_number) REFERENCES ddd_user(user_number) ON DELETE CASCADE,
    CONSTRAINT ck_match_activity CHECK (dog_activity BETWEEN 5 AND 20),
    CONSTRAINT ck_match_sociality CHECK (dog_sociality BETWEEN 5 AND 20),
    CONSTRAINT ck_match_independence CHECK (dog_independence BETWEEN 5 AND 20),
    CONSTRAINT ck_match_barking CHECK (dog_barking BETWEEN 5 AND 20),
    CONSTRAINT ck_match_grooming CHECK (dog_grooming BETWEEN 5 AND 20)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS ddd_match_result_dog;
CREATE TABLE ddd_match_result_dog (
    result_dog_number   INT AUTO_INCREMENT,
    result_number       INT NOT NULL,
    dog_number          INT NOT NULL,
    result_rank         INT NOT NULL,
    
    CONSTRAINT pk_match_result_dog PRIMARY KEY (result_dog_number),
    CONSTRAINT fk_match_dog_result FOREIGN KEY (result_number) REFERENCES ddd_match_result(result_number) ON DELETE CASCADE,
    CONSTRAINT fk_match_dog_archive FOREIGN KEY (dog_number) REFERENCES ddd_archive(dog_number) ON DELETE CASCADE,
    CONSTRAINT ck_match_dog_rank CHECK (result_rank BETWEEN 1 AND 8)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- 6. 멍케어(돌봄) 관련 테이블 생성
-- =========================================================
DROP TABLE IF EXISTS ddd_care;
CREATE TABLE ddd_care (
    care_number       INT AUTO_INCREMENT,
    user_number       INT            NOT NULL,
    care_title        VARCHAR(100)   NOT NULL,
    care_post         TEXT           NOT NULL,
    care_date         DATE           NOT NULL,
    care_recruit      INT,
    care_write_date   DATETIME       DEFAULT NOW() NOT NULL,
    care_status       VARCHAR(10)    DEFAULT 'open' NOT NULL,
    apply_count       INT            DEFAULT 0 NOT NULL,

    CONSTRAINT pk_care PRIMARY KEY (care_number),
    CONSTRAINT fk_care_user FOREIGN KEY (user_number) REFERENCES ddd_user(user_number) ON DELETE CASCADE,
    CONSTRAINT ck_care_recruit CHECK (care_recruit IS NULL OR care_recruit > 0),
    CONSTRAINT ck_care_status CHECK (care_status IN ('open', 'closed', 'deleted')),
    CONSTRAINT ck_care_apply_count CHECK (apply_count >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS ddd_care_apply;
CREATE TABLE ddd_care_apply (
    apply_number    INT AUTO_INCREMENT,
    care_number     INT NOT NULL,
    user_number     INT NOT NULL,
    apply_date      DATETIME DEFAULT NOW() NOT NULL,
    
    CONSTRAINT pk_care_apply PRIMARY KEY (apply_number),
    CONSTRAINT uk_care_apply UNIQUE (care_number, user_number),
    CONSTRAINT fk_care_apply_care FOREIGN KEY (care_number) REFERENCES ddd_care(care_number) ON DELETE CASCADE,
    CONSTRAINT fk_care_apply_user FOREIGN KEY (user_number) REFERENCES ddd_user(user_number) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- 7. 신고 및 커뮤니티(멍로그) 테이블 생성
-- =========================================================
DROP TABLE IF EXISTS ddd_report;
CREATE TABLE ddd_report (
    report_number          INT AUTO_INCREMENT,
    reporter_user_number   INT            NOT NULL,
    reported_user_number   INT            NOT NULL,
    apply_number           INT            NOT NULL,
    report_reason          VARCHAR(200)   NOT NULL,
    report_date            DATETIME       DEFAULT NOW() NOT NULL,
    
    CONSTRAINT pk_report PRIMARY KEY (report_number),
    CONSTRAINT uk_report_apply UNIQUE (reporter_user_number, apply_number),
    CONSTRAINT fk_report_reporter FOREIGN KEY (reporter_user_number) REFERENCES ddd_user(user_number),
    CONSTRAINT fk_report_reported FOREIGN KEY (reported_user_number) REFERENCES ddd_user(user_number),
    CONSTRAINT fk_report_apply FOREIGN KEY (apply_number) REFERENCES ddd_care_apply(apply_number) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS ddd_log;
CREATE TABLE ddd_log (
    log_number        INT AUTO_INCREMENT,
    user_number       INT            NOT NULL,
    log_title         VARCHAR(50)    NOT NULL,
    log_post          TEXT           NOT NULL,
    log_date          DATETIME       DEFAULT NOW() NOT NULL,
    log_modify_date   DATETIME       DEFAULT NOW(),
    
    CONSTRAINT pk_log PRIMARY KEY (log_number),
    CONSTRAINT fk_log_user KEY (user_number) REFERENCES ddd_user(user_number) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS ddd_log_comment;
CREATE TABLE ddd_log_comment (
    comment_number   INT AUTO_INCREMENT,
    log_number       INT            NOT NULL,
    user_number      INT            NOT NULL,
    comment_post     VARCHAR(500)   NOT NULL,
    comment_date     DATETIME       DEFAULT NOW() NOT NULL,
    
    CONSTRAINT pk_log_comment PRIMARY KEY (comment_number),
    CONSTRAINT fk_log_comment_log FOREIGN KEY (log_number) REFERENCES ddd_log(log_number) ON DELETE CASCADE,
    CONSTRAINT fk_log_comment_user FOREIGN KEY (user_number) REFERENCES ddd_user(user_number) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS ddd_log_img;
CREATE TABLE ddd_log_img (
    log_img_number   INT AUTO_INCREMENT,
    log_number       INT            NOT NULL,
    log_img_name     VARCHAR(200)   NOT NULL,
    log_img_path     VARCHAR(200),
    
    CONSTRAINT pk_log_img PRIMARY KEY (log_img_number),
    CONSTRAINT fk_log_img_log FOREIGN KEY (log_number) REFERENCES ddd_log(log_number) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- 8. 1:1 문의 및 보호소 첨부파일 테이블 생성
-- =========================================================
DROP TABLE IF EXISTS ddd_inquiry;
CREATE TABLE ddd_inquiry (
    inquiry_number   INT AUTO_INCREMENT,
    user_number      INT            NOT NULL,
    inquiry_title    VARCHAR(50)    NOT NULL,
    inquiry_post     TEXT           NOT NULL,
    inquiry_date     DATETIME       DEFAULT NOW() NOT NULL,
    answer_status    CHAR(1)        DEFAULT 'N' NOT NULL,
    answer_post      TEXT,
    answer_date      DATETIME,
    
    CONSTRAINT pk_inquiry PRIMARY KEY (inquiry_number),
    CONSTRAINT fk_inquiry_user FOREIGN KEY (user_number) REFERENCES ddd_user(user_number) ON DELETE CASCADE,
    CONSTRAINT ck_inquiry_status CHECK (answer_status IN ('Y', 'N'))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS ddd_file;
CREATE TABLE ddd_file (
    file_system_name    VARCHAR(300),
    file_original_name  VARCHAR(300),
    user_number         INT NOT NULL,
    
    CONSTRAINT pk_file PRIMARY KEY (file_system_name),
    CONSTRAINT fk_file_user FOREIGN KEY (user_number) REFERENCES ddd_user(user_number) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- 9. 사용자지정 일련번호 시퀀스 관리 구성
-- =========================================================
DROP TABLE IF EXISTS ddd_user_sequence;
CREATE TABLE ddd_user_sequence (
    seq_name VARCHAR(50) PRIMARY KEY,
    seq_value INT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 시퀀스 기본값 입력
INSERT INTO ddd_user_sequence VALUES ('common_user', 10000);
INSERT INTO ddd_user_sequence VALUES ('shelter_user', 0);

-- 일반회원 다음 번호 호출 함수 생성
DROP FUNCTION IF EXISTS next_common_user_seq;
DELIMITER $$
CREATE FUNCTION next_common_user_seq()
RETURNS INT
DETERMINISTIC
MODIFIES SQL DATA
BEGIN
    UPDATE ddd_user_sequence
    SET seq_value = LAST_INSERT_ID(seq_value + 1)
    WHERE seq_name = 'common_user';

    RETURN LAST_INSERT_ID();
END $$
DELIMITER ;

-- 보호소회원 다음 번호 호출 함수 생성
DROP FUNCTION IF EXISTS next_shelter_user_seq;
DELIMITER $$
CREATE FUNCTION next_shelter_user_seq()
RETURNS INT
DETERMINISTIC
MODIFIES SQL DATA
BEGIN
    UPDATE ddd_user_sequence
    SET seq_value = LAST_INSERT_ID(seq_value + 1)
    WHERE seq_name = 'shelter_user';

    RETURN LAST_INSERT_ID();
END $$
DELIMITER ;


-- =========================================================
-- 10. DingDangDog 더미데이터 삽입
-- =========================================================

-- ① 관리자
INSERT INTO ddd_admin (admin_number, admin_id, admin_password, user_type) VALUES (1, 'admin',     'admin1234', 'A');
INSERT INTO ddd_admin (admin_number, admin_id, admin_password, user_type) VALUES (2, 'superadmin', 'super1234', 'A');

-- ② 회원 (보호소 회원 4명 등록)
INSERT INTO ddd_user (
    user_number, user_type, user_id, user_password, user_name, user_nickname,
    user_gender, user_birth, user_phone, user_email, user_status
) VALUES (
    next_shelter_user_seq(), 'S', 'happy_tails', 'pass1234', '해피테일즈보호소', '해피테일즈',
    'F', '2015-01-01', '010-5000-0001', 'happytails@ddd.com', 'kind'
);

INSERT INTO ddd_user (
    user_number, user_type, user_id, user_password, user_name, user_nickname,
    user_gender, user_birth, user_phone, user_email, user_status
) VALUES (
    next_shelter_user_seq(), 'S', 'hope_shelter', 'pass1234', '희망보호소', '희망쉼터',
    'M', '2014-02-10', '010-5000-0002', 'hope@ddd.com', 'kind'
);

INSERT INTO ddd_user (
    user_number, user_type, user_id, user_password, user_name, user_nickname,
    user_gender, user_birth, user_phone, user_email, user_status
) VALUES (
    next_shelter_user_seq(), 'S', 'with_dogs', 'pass1234', '함께걷는보호소', '함께걷개',
    'F', '2016-03-15', '010-5000-0003', 'withdogs@ddd.com', 'kind'
);

INSERT INTO ddd_user (
    user_number, user_type, user_id, user_password, user_name, user_nickname,
    user_gender, user_birth, user_phone, user_email, user_status
) VALUES (
    next_shelter_user_seq(), 'S', 'blue_paw', 'pass1234', '블루포우보호소', '블루포우',
    'M', '2017-04-20', '010-5000-0004', 'bluepaw@ddd.com', 'kind'
);

-- ③ 회원 (일반 회원 8명 등록)
INSERT INTO ddd_user VALUES (next_common_user_seq(), 'C', 'minsu01',   'pass1234', '김민수', '민수',     'M', '2000-05-11', '010-6000-0001', 'minsu01@ddd.com',   'kind');
INSERT INTO ddd_user VALUES (next_common_user_seq(), 'C', 'jiyeon02',  'pass1234', '박지연', '지연',     'F', '1999-08-21', '010-6000-0002', 'jiyeon02@ddd.com',  'kind');
INSERT INTO ddd_user VALUES (next_common_user_seq(), 'C', 'sunwoo03',  'pass1234', '이선우', '선우',     'M', '2001-01-09', '010-6000-0003', 'sunwoo03@ddd.com',  'kind');
INSERT INTO ddd_user VALUES (next_common_user_seq(), 'C', 'yujin04',   'pass1234', '최유진', '유진',     'F', '2002-02-18', '010-6000-0004', 'yujin04@ddd.com',   'black');
INSERT INTO ddd_user VALUES (next_common_user_seq(), 'C', 'haneul05',  'pass1234', '정하늘', '하늘',     'F', '2000-12-25', '010-6000-0005', 'haneul05@ddd.com',  'kind');
INSERT INTO ddd_user VALUES (next_common_user_seq(), 'C', 'junho06',   'pass1234', '오준호', '준호',     'M', '1998-11-03', '010-6000-0006', 'junho06@ddd.com',   'kind');
INSERT INTO ddd_user VALUES (next_common_user_seq(), 'C', 'sohee07',   'pass1234', '한소희', '소희',     'F', '2001-07-14', '010-6000-0007', 'sohee07@ddd.com',   'withdraw');
INSERT INTO ddd_user VALUES (next_common_user_seq(), 'C', 'taemin08',  'pass1234', '강태민', '태민',     'M', '1999-03-30', '010-6000-0008', 'taemin08@ddd.com',  'kind');

-- ④ 일반회원 상세 데이터
INSERT INTO ddd_user_common (user_number, common_report_count) VALUES (10001, 0);
INSERT INTO ddd_user_common (user_number, common_report_count) VALUES (10002, 0);
INSERT INTO ddd_user_common (user_number, common_report_count) VALUES (10003, 1);
INSERT INTO ddd_user_common (user_number, common_report_count) VALUES (10004, 3);
INSERT INTO ddd_user_common (user_number, common_report_count) VALUES (10005, 1);
INSERT INTO ddd_user_common (user_number, common_report_count) VALUES (10006, 0);
INSERT INTO ddd_user_common (user_number, common_report_count) VALUES (10007, 0);
INSERT INTO ddd_user_common (user_number, common_report_count) VALUES (10008, 0);

-- ⑤ 보호소 상세 데이터
INSERT INTO ddd_user_shelter (user_number, shelter_name, shelter_business_number, shelter_zipcode, shelter_address, shelter_address_detail, shelter_certification) 
VALUES (1, '해피테일즈보호소', 'HT-2026-001', '54001', '전북특별자치도 군산시 중앙로 11', '1층 안내실 옆', 'Y');
INSERT INTO ddd_user_shelter (user_number, shelter_name, shelter_business_number, shelter_zipcode, shelter_address, shelter_address_detail, shelter_certification) 
VALUES (2, '희망보호소', 'HS-2026-002', '54890', '전북특별자치도 전주시 덕진구 백제로 22', '본관 2층', 'Y');
INSERT INTO ddd_user_shelter (user_number, shelter_name, shelter_business_number, shelter_zipcode, shelter_address, shelter_address_detail, shelter_certification) 
VALUES (3, '함께걷는보호소', 'WD-2026-003', '54321', '전북특별자치도 익산시 무왕로 33', '후문 사무동', 'Y');
INSERT INTO ddd_user_shelter (user_number, shelter_name, shelter_business_number, shelter_zipcode, shelter_address, shelter_address_detail, shelter_certification) 
VALUES (4, '블루포우보호소', 'BP-2026-004', '54999', '전북특별자치도 전주시 완산구 기린대로 44', '별관 1층', 'N');

-- ⑥ 보호소 인증 첨부파일
INSERT INTO ddd_file (file_system_name, file_original_name, user_number) VALUES ('shelter1_cert_20260323.pdf', '해피테일즈_사업자등록증.pdf', 1);
INSERT INTO ddd_file (file_system_name, file_original_name, user_number) VALUES ('shelter2_cert_20260323.pdf', '희망보호소_사업자등록증.pdf', 2);
INSERT INTO ddd_file (file_system_name, file_original_name, user_number) VALUES ('shelter3_cert_20260323.pdf', '함께걷는보호소_사업자등록증.pdf', 3);
INSERT INTO ddd_file (file_system_name, file_original_name, user_number) VALUES ('shelter4_cert_20260323.pdf', '블루포우보호소_사업자등록증.pdf', 4);

-- ⑦ 멍카이브 강아지 기본정보
INSERT INTO ddd_archive VALUES (1,  1, '콩이',   '말티즈',      'F', '2살',  4.2, '2026-01-12', '2026-01-15 00:00:00', '2026-01-15 00:00:00', '사람을 좋아하고 안기는 걸 좋아하는 아이입니다.');
INSERT INTO ddd_archive VALUES (2,  1, '초코',   '푸들',        'M', '3살',  5.8, '2026-01-20', '2026-01-22 00:00:00', '2026-01-22 00:00:00', '산책 에너지가 높은 편이며 장난감 놀이를 좋아합니다.');
INSERT INTO ddd_archive VALUES (3,  1, '보리',   '진도믹스',    'F', '4살', 12.4, '2026-02-02', '2026-02-05 00:00:00', '2026-02-05 00:00:00', '차분하고 적응이 빠른 편이라 초보 입양자에게도 잘 맞습니다.');
INSERT INTO ddd_archive VALUES (4,  2, '두부',   '비숑',        'M', '1살',  6.0, '2026-01-08', '2026-01-10 00:00:00', '2026-01-10 00:00:00', '낯선 사람에게도 금방 다가오는 밝은 성격입니다.');
INSERT INTO ddd_archive VALUES (5,  2, '모카',   '포메라니안',  'F', '2살',  3.6, '2026-02-10', '2026-02-12 00:00:00', '2026-02-12 00:00:00', '짖음은 조금 있지만 사회성이 좋아 다른 강아지와도 잘 지냅니다.');
INSERT INTO ddd_archive VALUES (6,  2, '밤이',   '시바믹스',    'M', '5살', 10.2, '2026-02-14', '2026-02-18 00:00:00', '2026-02-18 00:00:00', '독립심이 있고 규칙적인 생활을 선호합니다.');
INSERT INTO ddd_archive VALUES (7,  3, '하니',   '코커스패니얼','F', '3살',  8.7, '2026-01-25', '2026-01-27 00:00:00', '2026-01-27 00:00:00', '털 관리가 필요하지만 사람과 교감이 좋고 훈련 반응도 좋습니다.');
INSERT INTO ddd_archive VALUES (8,  3, '루이',   '리트리버믹스','M', '2살', 18.5, '2026-02-01', '2026-02-03 00:00:00', '2026-02-03 00:00:00', '활동량이 높고 사회성이 매우 좋은 대형견 성향입니다.');
INSERT INTO ddd_archive VALUES (9,  3, '별이',   '말티푸',      'F', '1살',  4.8, '2026-02-20', '2026-02-21 00:00:00', '2026-02-21 00:00:00', '애교가 많고 미용 관리에 비교적 잘 적응합니다.');
INSERT INTO ddd_archive VALUES (10, 4, '탄이',   '웰시코기믹스','M', '4살', 11.6, '2026-01-17', '2026-01-19 00:00:00', '2026-01-19 00:00:00', '기본 복종이 되어 있고 산책 시 통제가 쉬운 편입니다.');
INSERT INTO ddd_archive VALUES (11, 4, '나나',   '치와와',      'F', '2살',  2.9, '2026-02-07', '2026-02-09 00:00:00', '2026-02-09 00:00:00', '작은 체구지만 호기심이 많고 사람을 금방 기억합니다.');
INSERT INTO ddd_archive VALUES (12, 4, '호두',   '스피츠',      'M', '6살',  9.4, '2026-02-26', '2026-02-28 00:00:00', '2026-02-28 00:00:00', '차분한 중년견으로 실내 생활 적응력이 높습니다.');

-- ⑧ 강아지 성향 점수
INSERT INTO ddd_archive_score VALUES (1,  3, 5, 2, 2, 4);
INSERT INTO ddd_archive_score VALUES (2,  5, 4, 2, 3, 3);
INSERT INTO ddd_archive_score VALUES (3,  2, 4, 4, 1, 3);
INSERT INTO ddd_archive_score VALUES (4,  4, 5, 2, 2, 3);
INSERT INTO ddd_archive_score VALUES (5,  3, 5, 2, 4, 4);
INSERT INTO ddd_archive_score VALUES (6,  2, 3, 5, 2, 2);
INSERT INTO ddd_archive_score VALUES (7,  3, 4, 3, 2, 5);
INSERT INTO ddd_archive_score VALUES (8,  5, 5, 2, 2, 3);
INSERT INTO ddd_archive_score VALUES (9,  4, 4, 2, 3, 4);
INSERT INTO ddd_archive_score VALUES (10, 4, 3, 4, 2, 3);
INSERT INTO ddd_archive_score VALUES (11, 3, 3, 3, 4, 2);
INSERT INTO ddd_archive_score VALUES (12, 2, 3, 4, 1, 3);

-- ⑨ 강아지 이미지
INSERT INTO ddd_archive_img VALUES (1,  1,  'images.jpg',     '/assets/upload/images.jpg');
INSERT INTO ddd_archive_img VALUES (2,  2,  'images(1).jpg',   '/assets/upload/images(1).jpg');
INSERT INTO ddd_archive_img VALUES (3,  3,  'images(2).jpg',   '/assets/upload/images(2).jpg');
INSERT INTO ddd_archive_img VALUES (4,  4,  'images(3).jpg',   '/assets/upload/images(3).jpg');
INSERT INTO ddd_archive_img VALUES (5,  5,  'images(4).jpg',   '/assets/upload/images(4).jpg');
INSERT INTO ddd_archive_img VALUES (6,  6,  'images(5).jpg',   '/assets/upload/images(5).jpg');
INSERT INTO ddd_archive_img VALUES (7,  7,  'images(6).jpg',   '/assets/upload/images(6).jpg');
INSERT INTO ddd_archive_img VALUES (8,  8,  'images(7).jpg',   '/assets/upload/images(7).jpg');
INSERT INTO ddd_archive_img VALUES (9,  9,  'images(8).jpg',   '/assets/upload/images(8).jpg');
INSERT INTO ddd_archive_img VALUES (10, 10, 'images(9).jpg',   '/assets/upload/images(9).jpg');
INSERT INTO ddd_archive_img VALUES (11, 11, 'images(10).jpg',  '/assets/upload/images(10).jpg');
INSERT INTO ddd_archive_img VALUES (12, 12, 'images(11).jpg',  '/assets/upload/images(11).jpg');

-- ⑩ 멍케어 게시글
INSERT INTO ddd_care (care_number, user_number, care_title, care_post, care_date, care_recruit, care_write_date, care_status, apply_count) 
VALUES (1, 1, '주말 산책 봉사 모집', '군산 은파호수공원에서 2시간 산책 봉사를 함께할 분을 모집합니다.', '2026-03-29', 4, '2026-03-20', 'open', 2);
INSERT INTO ddd_care (care_number, user_number, care_title, care_post, care_date, care_recruit, care_write_date, care_status, apply_count) 
VALUES (2, 1, '목욕 보조 봉사', '소형견 목욕과 드라이를 도와주실 분 2명을 구합니다.', '2026-03-26', 2, '2026-03-18', 'closed', 2);
INSERT INTO ddd_care (care_number, user_number, care_title, care_post, care_date, care_recruit, care_write_date, care_status, apply_count) 
VALUES (3, 2, '입양행사 준비 봉사', '행사 부스 세팅과 강아지 케이지 정리를 도와주세요.', '2026-03-30', 3, '2026-03-19', 'closed', 3);
INSERT INTO ddd_care (care_number, user_number, care_title, care_post, care_date, care_recruit, care_write_date, care_status, apply_count) 
VALUES (4, 3, '견사 청소 정기봉사', '매월 마지막 주 청소 봉사입니다. 체력이 필요합니다.', '2026-03-27', 3, '2026-03-16', 'closed', 3);
INSERT INTO ddd_care (care_number, user_number, care_title, care_post, care_date, care_recruit, care_write_date, care_status, apply_count) 
VALUES (5, 2, '봄맞이 물품정리', '사료와 패드 재고 정리 봉사였으나 일정 변경으로 마감 처리합니다.', '2026-03-22', 5, '2026-03-10', 'deleted', 1);
INSERT INTO ddd_care (care_number, user_number, care_title, care_post, care_date, care_recruit, care_write_date, care_status, apply_count) 
VALUES (6, 4, '보호소 사진 촬영 봉사', '입양홍보용 프로필 사진 촬영을 도와주실 분을 모집합니다.', '2026-04-02', 2, '2026-03-21', 'open', 0);

-- ⑪ 멍케어 신청 기록
INSERT INTO ddd_care_apply VALUES (1,  1, 10001, '2026-03-21 09:10:00');
INSERT INTO ddd_care_apply VALUES (2,  1, 10006, '2026-03-21 10:05:00');
INSERT INTO ddd_care_apply VALUES (3,  2, 10004, '2026-03-19 13:20:00');
INSERT INTO ddd_care_apply VALUES (4,  2, 10005, '2026-03-19 14:00:00');
INSERT INTO ddd_care_apply VALUES (5,  3, 10002, '2026-03-20 11:00:00');
INSERT INTO ddd_care_apply VALUES (6,  3, 10003, '2026-03-20 11:25:00');
INSERT INTO ddd_care_apply VALUES (7,  3, 10004, '2026-03-20 11:40:00');
INSERT INTO ddd_care_apply VALUES (8,  4, 10004, '2026-03-17 16:20:00');
INSERT INTO ddd_care_apply VALUES (9,  4, 10006, '2026-03-17 16:35:00');
INSERT INTO ddd_care_apply VALUES (10, 4, 10008, '2026-03-17 17:00:00');
INSERT INTO ddd_care_apply VALUES (11, 5, 10007, '2026-03-11 12:00:00');

-- ⑫ 신고 데이터
INSERT INTO ddd_report VALUES (1, 1, 10004, 3, '목욕 보조 봉사 당일 무단 불참', '2026-03-20');
INSERT INTO ddd_report VALUES (2, 1, 10005, 4, '연락 없이 40분 이상 지각', '2026-03-20');
INSERT INTO ddd_report VALUES (3, 2, 10003, 6, '행사 준비 중 반복적인 지시 불이행', '2026-03-21');
INSERT INTO ddd_report VALUES (4, 2, 10004, 7, '현장 규칙 위반 및 스태프와 마찰', '2026-03-21');
INSERT INTO ddd_report VALUES (5, 3, 10004, 8, '청소 봉사 중 중도 이탈', '2026-03-22');

-- ⑬ 멍로그 게시글
INSERT INTO ddd_log VALUES (1, 10001, '처음 봉사 다녀온 후기', '주말 산책 봉사를 다녀왔는데 강아지들이 생각보다 사람 손길을 많이 기다리고 있더라구요. 다음에도 꼭 참여하고 싶습니다.', '2026-03-18 09:30:00', '2026-03-18 09:30:00');
INSERT INTO ddd_log VALUES (2, 10002, '입양행사 참여 기록', '부스 준비부터 정리까지 바빴지만 보호소 팀원분들이 친절해서 즐겁게 마무리했습니다.', '2026-03-19 14:10:00', '2026-03-19 14:10:00');
INSERT INTO ddd_log VALUES (3, 10006, '산책봉사 팁 공유', '리드줄 잡는 법, 간식 타이밍, 처음 만나는 아이와 거리 두기 같은 기본 팁을 적어봅니다.', '2026-03-20 18:25:00', '2026-03-20 18:25:00');
INSERT INTO ddd_log VALUES (4, 1, '해피테일즈 주간 소식', '이번 주에는 신규 구조견 2마리가 입소했고, 의료 체크를 마친 뒤 입양홍보를 준비하고 있습니다.', '2026-03-21 11:00:00', '2026-03-21 11:00:00');
INSERT INTO ddd_log VALUES (5, 3, '함께걷개 청소봉사 후기', '정기 청소봉사에 참여해주신 분들 덕분에 견사 환경이 훨씬 쾌적해졌습니다.', '2026-03-22 15:40:00', '2026-03-22 15:40:00');
INSERT INTO ddd_log VALUES (6, 10008, '입양 고민 중인 분들께', '강아지를 만나는 순간보다 함께 살아갈 생활패턴을 먼저 생각해보면 훨씬 좋은 선택을 할 수 있는 것 같습니다.', '2026-03-23 08:50:00', '2026-03-23 08:50:00');

-- ⑭ 멍로그 이미지
INSERT INTO ddd_log_img VALUES (1, 1, 'images.jpg',     '/assets/upload/images.jpg');
INSERT INTO ddd_log_img VALUES (2, 1, 'images(1).jpg',  '/assets/upload/images(1).jpg');
INSERT INTO ddd_log_img VALUES (3, 2, 'images(2).jpg',  '/assets/upload/images(2).jpg');
INSERT INTO ddd_log_img VALUES (4, 3, 'images(3).jpg',  '/assets/upload/images(3).jpg');
INSERT INTO ddd_log_img VALUES (5, 4, 'images(4).jpg',  '/assets/upload/images(4).jpg');
INSERT INTO ddd_log_img VALUES (6, 5, 'images(5).jpg',  '/assets/upload/images(5).jpg');
INSERT INTO ddd_log_img VALUES (7, 6, 'images(6).jpg',  '/assets/upload/images(6).jpg');
INSERT INTO ddd_log_img VALUES (8, 6, 'images(7).jpg',  '/assets/upload/images(7).jpg');

-- ⑮ 멍로그 댓글
INSERT INTO ddd_log_comment VALUES (1, 1, 10002, '후기 잘 봤어요. 저도 다음 주에 신청해보려구요!', '2026-03-18 10:05:00');
INSERT INTO ddd_log_comment VALUES (2, 1, 1,     '봉사 와주셔서 정말 감사했습니다 :)', '2026-03-18 10:30:00');
INSERT INTO ddd_log_comment VALUES (3, 2, 10006, '행사 준비 생각보다 힘들죠. 고생 많으셨어요.', '2026-03-19 15:12:00');
INSERT INTO ddd_log_comment VALUES (4, 3, 10001, '팁 정리 좋네요. 초보자한테 도움 될 듯합니다.', '2026-03-20 19:03:00');
INSERT INTO ddd_log_comment VALUES (5, 4, 10008, '신규 구조견 소식 보니 마음이 쓰이네요.', '2026-03-21 11:48:00');
INSERT INTO ddd_log_comment VALUES (6, 4, 2,     '곧 입양홍보 카드도 업데이트할 예정입니다.', '2026-03-21 12:10:00');
INSERT INTO ddd_log_comment VALUES (7, 5, 10006, '다음 청소봉사 일정도 열리면 신청하겠습니다.', '2026-03-22 16:02:00');
INSERT INTO ddd_log_comment VALUES (8, 6, 10002, '입양 전 체크포인트 정리 감사합니다.', '2026-03-23 09:15:00');
INSERT INTO ddd_log_comment VALUES (9, 6, 4,     '좋은 글이네요. 보호소에서도 자주 안내하는 부분입니다.', '2026-03-23 09:40:00');
INSERT INTO ddd_log_comment VALUES (10, 2, 3,    '행사 봉사자분들 덕분에 진행이 매끄러웠습니다.', '2026-03-19 16:20:00');

-- ⑯ 1:1 문의 게시판
INSERT INTO ddd_inquiry (inquiry_number, user_number, inquiry_title, inquiry_post, inquiry_date, answer_status, answer_post, answer_date) 
VALUES (1, 10001, '봉사 신청 후 준비물 문의', '산책 봉사 당일 개인 장갑이나 간식을 따로 챙겨가야 하나요?', '2026-03-18 08:40:00', 'Y', '장갑은 보호소에서 제공하며 개인 물통 정도만 준비해주시면 됩니다.', '2026-03-18 13:20:00');
INSERT INTO ddd_inquiry VALUES (2, 10002, '입양 상담 예약 가능 시간', '평일 저녁에도 입양 상담이 가능한지 문의드립니다.', '2026-03-19 17:05:00', 'Y', '평일은 오후 6시까지 가능하며 사전 예약 부탁드립니다.', '2026-03-20 10:10:00');
INSERT INTO ddd_inquiry VALUES (3, 10006, '매칭 결과 저장 개수 문의', '마이페이지에서 매칭 결과는 몇 개까지 보관되나요?', '2026-03-21 09:00:00', 'N', NULL, NULL);
INSERT INTO ddd_inquiry VALUES (4, 10008, '보호소 방문 가능 여부', '입양 전 실제 성격 확인을 위해 평일 방문이 가능한가요?', '2026-03-22 18:20:00', 'Y', '가능합니다. 다만 보호소별 운영시간이 다르니 방문 전 전화 확인 부탁드립니다.', '2026-03-23 09:30:00');
INSERT INTO ddd_inquiry VALUES (5, 10005, '신고 처리 결과 문의', '최근 신고 관련 상태가 반영되었는지 확인하고 싶습니다.', '2026-03-23 10:00:00', 'N', NULL, NULL);

-- ⑰ 매칭 결과 마스터 데이터
INSERT INTO ddd_match_result VALUES (1, 10001, 12, 18, 13, 7, 16, '2026-03-18 20:00:00');
INSERT INTO ddd_match_result VALUES (2, 10001, 8, 13, 7, 9, 17, '2026-03-20 09:10:00');
INSERT INTO ddd_match_result VALUES (3, 10002, 9, 17, 11, 11, 9, '2026-03-20 21:15:00');
INSERT INTO ddd_match_result VALUES (4, 10006, 18, 5, 14, 20, 9, '2026-03-21 22:10:00');
INSERT INTO ddd_match_result VALUES (5, 10008, 7, 8, 14, 19, 8, '2026-03-22 14:20:00');
INSERT INTO ddd_match_result VALUES (6, 10001, 6, 11, 5, 20, 20, '2026-03-23 07:45:00');

-- ⑱ 결과별 추천 강아지 매핑 데이터
INSERT INTO ddd_match_result_dog VALUES (1, 1, 1, 1);
INSERT INTO ddd_match_result_dog VALUES (2, 1, 4, 2);
INSERT INTO ddd_match_result_dog VALUES (3, 1, 9, 3);
INSERT INTO ddd_match_result_dog VALUES (4, 2, 5, 1);
INSERT INTO ddd_match_result_dog VALUES (5, 2, 9, 2);
INSERT INTO ddd_match_result_dog VALUES (6, 2, 11, 3);
INSERT INTO ddd_match_result_dog VALUES (7, 3, 3, 1);
INSERT INTO ddd_match_result_dog VALUES (8, 3, 6, 2);
INSERT INTO ddd_match_result_dog VALUES (9, 3, 12, 3);
INSERT INTO ddd_match_result_dog VALUES (10, 4, 8, 1);
INSERT INTO ddd_match_result_dog VALUES (11, 4, 4, 2);
INSERT INTO ddd_match_result_dog VALUES (12, 4, 2, 3);
INSERT INTO ddd_match_result_dog VALUES (13, 5, 10, 1);
INSERT INTO ddd_match_result_dog VALUES (14, 5, 12, 2);
INSERT INTO ddd_match_result_dog VALUES (15, 5, 3, 3);
INSERT INTO ddd_match_result_dog VALUES (16, 6, 6, 1);
INSERT INTO ddd_match_result_dog VALUES (17, 6, 12, 2);
INSERT INTO ddd_match_result_dog VALUES (18, 6, 3, 3);

COMMIT;