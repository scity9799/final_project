-- ========================================================
-- 2. 딩동몰 쇼핑몰 데이터(DML)만 주입
-- ========================================================

-- (1) 상품 데이터 주입
INSERT INTO ddm_product (product_id, product_category, product_name, product_price, product_description) VALUES 
(1, '사료', '[대용량] 딩동몰 인섹트 소프트 사료 5kg', 42000, '알레르기 개선과 눈물 자국 완화에 도움을 주는 가수분해 곤충 사료입니다.'),
(2, '사료', '[5개세트할인] 딩동몰 프리미엄 동결건조 북어 트릿', 29000, '첨가물 없이 100% 신선한 원물 그대로 동결건조한 건강 사료 및 간식 세트입니다.'),
(3, '사료', '딩동몰 연어 품은 미트 정보 캔 (12개입)', 24000, '음수량 충족과 영양 보충을 위한 부드러운 유기견·반려견 전용 습식 캔입니다.'),
(4, '사료', '[기획전] 딩동몰 덴탈 케어 츄 껌 (대용량 버킷)', 35000, '매일 먹이면서 치석 제거와 구강 건강을 동시에 챙기는 기능성 껌입니다.'),
(5, '용품', '[5개세트할인] 딩동몰 이지 바잇 그립 (삼킴 방지 간식 홀더)', 15000, '반려견이 간식을 급하게 삼키다 목에 걸리는 사고를 방지해 주는 안전 가드 홀더입니다.'),
(6, '용품', '[9주년 생일파티] 딩동몰 아이스베어 듀라론 기능성 냉감 쿨매트 (S)', 19800, '체온을 빠르게 낮춰주는 듀라론 신소재를 사용해 여름철 무더위를 식혀주는 쿨매트입니다.'),
(7, '용품', '[5개세트할인] 딩동몰 브러시미 플러스 반려동물 보습 미스트', 18500, '건조한 피모에 수분을 공급하고 죽은 털 제거와 엉킴 방지에 탁월한 보습 미스트입니다.'),
(8, '용품', '[5개세트할인] 딩동몰 마이크로화이버 펫타올 드라이미', 12000, '일반 타올보다 흡수력이 5배 빨라 목욕 후 드라이 시간을 획기적으로 줄여주는 극세사 타올입니다.');

-- (2) 파일 데이터 주입
INSERT INTO ddm_file (product_id, file_org_name, file_save_name, file_path, file_size) VALUES 
(1, 'insect_feed.jpg', '20260617_feed_01.jpg', '/uploads/products/', 1104857),
(2, 'pollock_treat.jpg', '20260617_feed_02.jpg', '/uploads/products/', 945120),
(3, 'salmon_can.jpg', '20260617_feed_03.jpg', '/uploads/products/', 852412),
(4, 'dental_chew.jpg', '20260617_feed_04.jpg', '/uploads/products/', 1204512),
(5, 'snack_holder.jpg', '20260617_item_01.jpg', '/uploads/products/', 612400),
(6, 'cool_mat.jpg', '20260617_item_02.jpg', '/uploads/products/', 1545120),
(7, 'coat_mist.jpg', '20260617_item_03.jpg', '/uploads/products/', 743120),
(8, 'dry_towel.jpg', '20260617_item_04.jpg', '/uploads/products/', 524100);

-- (3) 장바구니 데이터 주입 (기존 ddd_user에 shelter01, shelter02가 있다고 가정)
INSERT INTO ddm_cart (cart_id, user_id, product_id, cart_quantity) VALUES 
(1, 'shelter01', 1, 1),
(2, 'shelter01', 5, 2),
(3, 'shelter02', 6, 1);

-- (4) 주문 마스터 데이터 주입
INSERT INTO ddm_order (order_id, user_id, order_total_price, order_status) VALUES 
(1, 'shelter01', 72000, '배송중'),
(2, 'shelter02', 19800, '주문완료');

-- (5) 주문 상세 데이터 주입
INSERT INTO ddm_order_detail (order_id, product_id, detail_quantity, detail_price) VALUES 
(1, 1, 1, 42000),
(1, 5, 2, 15000),
(2, 6, 1, 19800);