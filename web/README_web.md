# 좋은 부모 배움터 · 사용자 페이지 (web/)

기존 `admin/` 과 같은 DB(`goodparents`)를 사용하는 절차형 PHP 사이트.

## 설치

1. `web/` 폴더 전체를 서버의 `public_html/web/` 에 업로드
2. 브라우저에서 **한 번** 실행:  `http://goodparents.byus.net/web/install.php?key=gp-web-setup-6741`
   - 관리자 스키마 확장 컬럼/신규 테이블 생성 (여러 번 실행해도 안전)
3. 완료되면 `web/install.php` 와 (루트의) `_gpddl.php` 삭제
4. 접속:  `http://goodparents.byus.net/web/`
   - 임시 로그인:  **goodparents / 123456**

## 라우팅 (web/index.php?p=...)

| p | 화면 | 접근 |
|---|---|---|
| main | 메인화면 | 공개 |
| about | 좋은 부모교육 이란? | 공개 |
| prep(&sub=ready\|emotion\|routine) | 부모되기 준비 | 공개 |
| offline / offline_guide | 오프라인 모임 / 교육안내 | 공개(목록), 좋아요는 로그인 |
| login / signup / logout | 인증 | - |
| parenting / parenting_result | 부모양육태도 체크리스트 / 결과 | 로그인 |
| mypage / mypage_children | 내정보(기본/자녀) | 로그인 |
| learn | 학습하기 메인 + 검색결과 | 로그인 + 부모양육태도 진단 완료 |
| learn_step(&sid=&step=1..6) | 상황 학습 6단계 | 로그인 + 진단 완료 |

## 관리자(admin) 쪽 추가 필요 작업 (별도)

install.php 가 아래 컬럼/테이블을 만들지만, **관리자 입력 화면은 아직 없음**:

- `situation.expert_conti`, `situation.expert_html` — 전문가 조언 콘티 + HTML
- `situation_media.kind = 'expert'` — 전문가 조언 동영상 (kind 값만 추가하면 기존 _video_section 재사용)
- `situation_checklist_item.answer_type`(scale|choice|essay), `.scale_steps`
- `parenting_checklist_item.answer_type`, `.scale_steps`
- `checklist_item_option` / `checklist_option_score` — 4지선다 보기 + 보기별 양육태도 배점 (scope: situation|parenting)
- `offline_meeting`(+kind: meeting|guide), `meeting_situation_link`, `learning_resource`

기존 `parenting_checklist_item` 25행은 모두 `answer_type='scale', scale_steps=5` 로 자동 설정됨.

## 채점 방식 (inc/scoring.php)

- 척도형: (선택단계 / scale_steps) × 문항 양육태도 가중치(%) / 100
- 4지선다: 선택 보기의 양육태도 가중치(%) / 100
- 서술형: 0 (ai_text = "AI 분석 준비 중입니다" 로 저장)
- 양육태도별 누적합 → 상위 2개 표출 (설명 문구는 추후)

## 임시/스텁 항목

- SNS 로그인 5종 — 버튼만(비활성). 임시 계정 goodparents/123456 → member.id=1
- 휴대폰 SMS 인증 — 형식만, 실제 발송/검증 없음 (phone_verified 는 가입 시 1)
- 주소검색 — 다음 우편번호(무료) 연동. 스크립트 로드 실패 시 직접 입력
- 검색 — 자유입력 받되 결과는 '초등입학' 생애주기 콘텐츠 고정
- 6단계 총평 — 자동 로직 미정. 체크리스트 상위 2개 양육태도 요약만 표시
- 학습자료실 rail — 메뉴만. '전문가 그룹'·'성과보기' 는 다음 차수(비활성)
- 소개/부모되기준비 본문 — inc/content.php 의 임시 문구 (확정 원고로 교체 예정)

## 참고

- `web/config.php` 에 DB 접속정보/임시로그인/SETUP_KEY 있음 (.htaccess 로 직접접근 차단)
- 동영상 파일은 `admin/uploads/` 를 `../admin/uploads/` 로 참조
- DB가 MySQL 8.0 이상이면 예약어 `member` 때문에 쿼리에 백틱이 필요할 수 있음 (현재 서버는 5.7/MariaDB 계열로 확인되어 미적용)
