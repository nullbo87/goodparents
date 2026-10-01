/* 좋은 부모 배움터 · 사용자 페이지 공용 스크립트 */
(function () {
  'use strict';

  /* ---- toggle 버튼 그룹 (성별/자녀수 등) ----
     <div class="toggle" data-name="gender"><button data-val="M">아빠</button>...</div>
     같은 form 의 <input type="hidden" name="gender"> 로 선택값 반영 */
  document.querySelectorAll('.toggle[data-name]').forEach(function (grp) {
    var name = grp.getAttribute('data-name');
    var form = grp.closest('form');
    var hidden = form ? form.querySelector('input[name="' + name + '"]') : null;
    grp.querySelectorAll('button,[data-val]').forEach(function (b) {
      b.addEventListener('click', function (ev) {
        ev.preventDefault();
        grp.querySelectorAll('button,[data-val]').forEach(function (x) { x.classList.remove('on'); });
        b.classList.add('on');
        if (hidden) hidden.value = b.getAttribute('data-val');
        grp.dispatchEvent(new CustomEvent('togglechange', { detail: b.getAttribute('data-val') }));
      });
    });
  });

  /* ---- 자녀수 선택 -> 자녀 입력 블록 표시/필수 토글 ---- */
  var cntGrp = document.querySelector('.toggle[data-name="child_count"]');
  if (cntGrp) {
    var apply = function (n) {
      n = parseInt(n, 10) || 0;
      document.querySelectorAll('.child-block').forEach(function (blk, i) {
        var show = i < n;
        blk.hidden = !show;
        blk.querySelectorAll('input,select').forEach(function (f) {
          if (f.dataset.req === '1') f.required = show;
          if (!show) { /* keep values but they'll be ignored server-side */ }
        });
      });
    };
    cntGrp.addEventListener('togglechange', function (e) { apply(e.detail); });
    var pre = cntGrp.querySelector('.on');
    apply(pre ? pre.getAttribute('data-val') : 1);
  }

  /* ---- 학습 동영상: 끝까지 보면 '다음학습' 활성화 ----
     감지 불가한 외부 URL 이면 버튼은 활성 상태로 둔다(클릭 시 이동). */
  var vb = document.querySelector('.videobox[data-detect="1"]');
  var nextBtn = document.querySelector('[data-next-lock="1"]');
  if (vb && nextBtn) {
    var v = vb.querySelector('video');
    if (v) {
      nextBtn.setAttribute('disabled', 'disabled');
      var watched = false;
      var unlock = function () { watched = true; nextBtn.removeAttribute('disabled'); };
      v.addEventListener('timeupdate', function () {
        if (v.duration && v.currentTime / v.duration >= 0.9) unlock();
      });
      v.addEventListener('ended', unlock);
    }
    /* YouTube/Vimeo iframe 은 별도 API 필요 — 우선 활성 상태 유지 */
  }

  /* ---- 다음 우편번호(주소검색) ---- */
  var addrBtn = document.querySelector('[data-postcode]');
  if (addrBtn) {
    addrBtn.addEventListener('click', function () {
      if (typeof daum === 'undefined' || !daum.Postcode) {
        alert('주소검색 서비스를 불러오지 못했습니다. 직접 입력해 주세요.');
        return;
      }
      new daum.Postcode({
        oncomplete: function (data) {
          var f = addrBtn.closest('form');
          if (f.querySelector('[name=region_zip]')) f.querySelector('[name=region_zip]').value = data.zonecode || '';
          if (f.querySelector('[name=region_addr]')) f.querySelector('[name=region_addr]').value = data.roadAddress || data.jibunAddress || '';
          var d = f.querySelector('[name=region_detail]');
          if (d) d.focus();
        }
      }).open();
    });
    var s = document.createElement('script');
    s.src = 'https://t1.daumcdn.net/mapjsapi/bundle/postcode/prod/postcode.v2.js';
    document.head.appendChild(s);
  }

  /* ---- 휴대폰 인증 (스텁: 실제 SMS 없음) ---- */
  document.querySelectorAll('[data-phone-send]').forEach(function (b) {
    b.addEventListener('click', function () {
      alert('임시: 인증번호 발송은 아직 연동되지 않았습니다. 아무 값이나 입력하면 통과합니다.');
    });
  });

  /* ---- 학습검색: 생애주기 칩 클릭 시 입력 중(미제출)인 검색어 유지 ----
     칩은 <a> 링크라 그냥 누르면 textarea 에 타이핑만 하고 안 누른 검색어가 사라진다.
     클릭 순간 textarea 값을 읽어 링크의 q 파라미터에 다시 실어 준다. */
  var chipBox = document.querySelector('.chips');
  var searchTa = document.querySelector('.searchbox textarea[name="q"]');
  if (chipBox && searchTa) {
    chipBox.addEventListener('click', function (ev) {
      var a = ev.target.closest ? ev.target.closest('a.chip') : null;
      if (!a) return;
      var q = searchTa.value.replace(/^\s+|\s+$/g, '');
      var href = (a.getAttribute('href') || '').replace(/&q=[^&#]*/, '');
      if (q) href += '&q=' + encodeURIComponent(q);
      a.setAttribute('href', href);
    });
  }
})();
