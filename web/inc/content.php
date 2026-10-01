<?php
/* =========================================================
 *  정적 소개 콘텐츠
 *  사용처: 메인화면 / 좋은 부모교육 이란? / 부모되기 준비
 * ========================================================= */
if (!defined('GP_WEB')) { http_response_code(403); exit('No direct access'); }

/* 메인 히어로 */
$GP_HERO = array(
    'title'    => "아이 키우기 힘드시죠?\n",
	'subtitle' => "전문가의 도움을 받으세요.",
    'cta'      => '좋은부모교육 바로가기',
    'cta_href' => 'index.php?p=learn',
    'title2'    => "부모님들, 오늘도\n",
	'subtitle2' => "충분히 잘하고 있어요.",
	'descrtion2' => "아이를 이해하는 작은 배움이 부모의 마음을 단단하게 만들어요.\n지금 부모님들에게 필요한 이야기를 함께 찾아볼까요?",
    'cta2'      => '학습하러 가기',
    'cta_href2' => 'index.php?p=learn',	
);

/* ===== 좋은 부모 교육 이란? (메인화면 + about 공용) ===== */
$GP_ABOUT = array(
    'title' => '좋은 부모 교육 이란?',
    'html'  => <<<'HTML'
<p class="lead-p">&lsquo;좋은 부모&rsquo;란 부모가 완벽한 양육자가 되는 법을 배우는 것이 아니라, 아이를 독립된 인격체로 이해하고 부모 스스로도 함께 성장하도록 돕는 배움의 과정입니다.</p>
<p>과거의 부모 교육이 &ldquo;아이를 어떻게 잘 통제하고 가르칠 것인가(기법/기술)&rdquo;에 집중했다면, 현대의 좋은 부모 교육은 &ldquo;부모인 나와 아이의 관계를 어떻게 건강하게 세울 것인가(지향점/관점)&rdquo;에 초점을 맞춥니다.</p>
<p>좋은 부모 교육이 담아야 할 핵심 요소와 가치는 다음과 같습니다.</p>

<section class="about-sec">
  <h3><span class="num">1</span> 지식 전달이 아닌 &lsquo;자기 이해&rsquo;와 &lsquo;감정 조절&rsquo;</h3>
  <ul>
    <li><strong>부모 자신의 상처와 패턴 인식 :</strong> 부모가 자신의 어린 시절 경험이나 미해결된 감정을 이해하지 못하면, 아이의 특정 행동에 과도하게 화를 내거나 두려움을 느끼기 쉽습니다.</li>
    <li><strong>자신의 감정 다스리기 :</strong> 아이에게 화내지 않는 법을 배우기 전에, 부모가 자신의 스트레스와 분노를 어떻게 건강하게 다스릴 수 있는지 내면의 힘을 기르는 법을 다룹니다.</li>
  </ul>
</section>

<section class="about-sec">
  <h3><span class="num">2</span> 아이의 &lsquo;발달 단계&rsquo;에 대한 객관적 이해</h3>
  <ul>
    <li><strong>비현실적인 기대 내려놓기 :</strong> 초등 저학년에게 완벽한 자기통제력을 기대하거나, 사춘기 아이에게 무조건적인 순종을 바라는 것은 발달 특성에 맞지 않습니다.</li>
    <li><strong>아이의 언어 이해하기 :</strong> 아이의 떼쓰기, 말대꾸, 거짓말 등의 행동이 &lsquo;부모를 괴롭히려 하는 것&rsquo;이 아니라 <b>자신의 욕구나 불안을 표현하는 미숙한 방식</b>임을 이해하도록 돕습니다.</li>
  </ul>
</section>

<section class="about-sec">
  <h3><span class="num">3</span> &lsquo;통제&rsquo;가 아닌 &lsquo;수용과 한계 설정&rsquo;의 균형</h3>
  <ul>
    <li><strong>감정은 수용하고 행동은 제한하기 :</strong> 아이의 화, 슬픔, 억울함 같은 감정은 100% 인정해주되, 타인에게 피해를 주거나 위험한 행동에는 분명한 한계(규칙)를 그어주는 법을 배웁니다.</li>
    <li><strong>I-Message(나-전달법)와 경청 :</strong> 아이를 비난하거나 평가하지 않고, 부모의 감정과 상황을 솔직하게 전달하며 아이의 이야기를 경청하는 대화법을 익힙니다.</li>
  </ul>
</section>

<section class="about-sec">
  <h3><span class="num">4</span> &lsquo;완벽한 부모&rsquo;라는 환상에서 벗어나기</h3>
  <ul>
    <li><strong>충분히 좋은 부모(Good Enough Parent) :</strong> 실수하지 않는 부모는 없습니다. 좋은 부모 교육은 실수했을 때 솔직하게 사과하고 관계를 회복하는 &lsquo;복원력&rsquo;을 가르쳐 줍니다.</li>
    <li><strong>부모의 삶과 아이의 삶 분리하기 :</strong> 아이를 부모의 소유물이나 대리 만족의 수단으로 보지 않고, 독립된 하나의 인격체로 존중하는 거리두기를 연습합니다.</li>
  </ul>
</section>

<div class="tablewrap" style="margin:18px 0">
<table class="cmp-table">
  <thead><tr><th>구분</th><th>과거의 부모 교육</th><th>진정한 &lsquo;좋은 부모 교육&rsquo;</th></tr></thead>
  <tbody>
    <tr><th>목표</th><td>아이의 행동을 바꾸고 통제하기</td><td>부모와 아이의 관계를 개선하고 함께 성장하기</td></tr>
    <tr><th>초점</th><td>훈육의 기술, 공부 시키는 법</td><td>부모의 감정 조절, 아이 발달의 이해</td></tr>
    <tr><th>지향점</th><td>완벽한 양육자 만들기</td><td>완벽하지 않아도 수용하고 회복하는 부모 되기</td></tr>
  </tbody>
</table>
</div>

<p class="closing">결국 좋은 부모 교육은 &ldquo;아이를 바꾸는 기술&rdquo;을 가르치는 것이 아니라, &ldquo;아이를 바라보는 부모의 시선과 마음&rdquo;을 넓혀주는 과정이라고 할 수 있습니다.</p>
HTML
    ,
);

/* ===== 부모되기 준비 (서브 아티클) ===== */
$GP_PREP = array();

$GP_PREP['ready'] = array(
    'title' => '부모가 되기 위한 준비',
    'html'  => <<<'HTML'
<p class="lead-p">초등학교 입학은 아이뿐만 아니라 부모에게도 커다란 삶의 전환점입니다.<br />유치원이나 어린이집이라는 &lsquo;보호 위주의 울타리&rsquo;에서 나와 &lsquo;사회성과 규칙의 세계&rsquo;로 들어가는 시기이기 때문에 부모 역시 기대와 불안을 동시에 느끼게 됩니다.</p>
<p>부모가 단단하고 담담한 마음의 중심을 잡고 있을 때 아이도 새로운 환경에 훨씬 더 잘 적응할 수 있습니다. 입학을 앞둔 부모가 마음에 담아두면 좋은 <b>4가지 마음의 자세</b>를 정리해 드립니다.</p>

<section class="about-sec scontxt">
  <h3><span class="num">1</span> 완벽에 대한 부담감 내려놓기 &mdash; &ldquo;잘하지 않아도 괜찮다&rdquo;</h3>
  <p class="lead">성취보다 적응이 먼저입니다</p>
  <ul>
    <li>입학 초기 부모의 가장 큰 불안은 &ldquo;내 아이가 한글을 다 못 뗐는데&rdquo;, &ldquo;수학을 잘 못 따라가면 어쩌지&rdquo; 같은 학업적 걱정입니다.</li>
    <li>하지만 1학년 1학기에서 가장 중요한 과업은 &lsquo;학교라는 공간과 규칙에 익숙해지는 것&rsquo;입니다. 글씨가 좀 삐뚤빼뚤하거나 행동이 느려도 괜찮습니다. 아이가 학교를 &lsquo;안전하고 재미있는 곳&rsquo;으로 느끼게 해주는 것이 최우선입니다.</li>
  </ul>
  <p class="lead">아이의 시행착오를 인정해 주세요</p>
  <ul>
    <li>준비물을 챙기지 못하거나 알림장을 제대로 써오지 못하는 일은 너무나 당연하게 일어납니다. 부모가 대신 모든 것을 완벽하게 세팅해주려 하기보다, <b>아이가 실수하고 스스로 바로잡아가는 과정을 느긋하게 기다려주는 마음</b>이 필요합니다.</li>
  </ul>
</section>

<section class="about-sec scontxt">
  <h3><span class="num">2</span> &lsquo;대신해 주기&rsquo;에서 &lsquo;믿고 지켜봐 주기&rsquo;로의 전환</h3>
  <p class="lead">아이는 부모가 생각하는 것보다 훨씬 힘이 셉니다</p>
  <ul>
    <li>부모의 불안이 크면 아이의 일상을 과도하게 통제하거나 모든 걸 대신해주게 됩니다.</li>
    <li>&ldquo;혼자서 화장실은 잘 갈 수 있을까?&rdquo;, &ldquo;친구랑 싸우면 어쩌지?&rdquo;라는 걱정이 들 때, 아이에게 &ldquo;너는 스스로 잘 해낼 수 있는 아이야&rdquo;라는 신뢰의 눈빛을 보내주는 것이 부모가 줄 수 있는 가장 큰 선물입니다.</li>
  </ul>
  <p class="lead">관심과 간섭의 경계 지키기</p>
  <ul>
    <li>아이의 일과를 매 순간 점검하려 하기보다, 스스로 시도해 보고 도움을 요청할 때 따뜻하게 응답해 주는 &lsquo;한 걸음 물러선 조력자&rsquo;의 자세가 좋습니다.</li>
  </ul>
</section>

<section class="about-sec scontxt">
  <h3><span class="num">3</span> 학교와 교사를 향한 &lsquo;건강한 신뢰&rsquo; 갖기</h3>
  <p class="lead">선생님과 학교에 대해 긍정적으로 이야기해 주세요</p>
  <ul>
    <li>부모의 태도는 아이가 학교를 바라보는 프레임이 됩니다. 가정에서 &ldquo;너 학교 가면 선생님한테 혼나!&rdquo;, &ldquo;학교 가면 마음대로 못 해&rdquo; 같은 말로 학교를 두려운 장소로 만들지 마세요.</li>
    <li>&ldquo;선생님은 너를 도와주시고 가르쳐주시는 고마운 분이야&rdquo;, &ldquo;학교는 새로운 친구들과 재밌는 걸 배우는 곳이야&rdquo;라는 긍정적인 인식을 심어주는 것이 중요합니다.</li>
  </ul>
  <p class="lead">문제가 생겼을 땐 객관적이고 의연하게 대처하기</p>
  <ul>
    <li>아이가 학교 생활에서 억울한 일이나 어려움을 호소할 때, 부모가 먼저 너무 감정적으로 흔들리거나 격분하지 않아야 합니다. 아이의 마음은 충분히 공감해 주되, 상황은 객관적으로 파악하고 선생님과 협력해 해결해 나가겠다는 담담한 태도를 유지해 주세요.</li>
  </ul>
</section>

<section class="about-sec scontxt">
  <h3><span class="num">4</span> 부모 자신을 돌보는 &lsquo;여유&rsquo; 확보하기</h3>
  <p class="lead">부모의 불안은 아이에게 그대로 전이됩니다</p>
  <ul>
    <li>입학 초기에는 부모도 알게 모르게 큰 스트레스와 피로감을 느낍니다. 부모의 마음이 불안하고 지쳐 있으면 작은 일에도 아이에게 화를 내기 쉽습니다.</li>
    <li>아이의 입학 준비만큼이나 <b>부모 자신의 마음을 다스리고 쉴 수 있는 시간</b>을 의도적으로 만드세요. 부모가 마음의 여유를 가질 때 아이의 감정적 기복도 너그럽게 품어줄 수 있습니다.</li>
  </ul>
</section>

<p class="closing">입학은 아이가 부모의 품을 떠나 독립된 한 사람의 사회인으로 나아가는 <b>빛나는 첫걸음</b>입니다. 조바심을 내기보다는 아이의 눈높이에서 함께 설레어하고, 아이가 한 걸음씩 성장하는 과정을 대견하게 바라봐 주는 &lsquo;따뜻한 응원단장&rsquo;이 되어주시길 바랍니다.</p>
HTML
    ,
);

$GP_PREP['emotion'] = array(
    'title' => '두려움, 분노등 감정 대처하기',
    'html'  => <<<'HTML'
<p class="lead-p">아이를 키우며 느끼는 <b>두려움과 분노</b>는 부모로서 매우 자연스럽고 당연한 감정입니다. 특히 초등학생 시기는 아이의 자율성이 커지고 또래 관계나 학업 문제가 본격화되면서 부모가 느끼는 감정의 파도가 더욱 커지는 시기입니다.</p>
<p>이러한 감정에 휩쓸리지 않고 건강하게 대처하는 방법을 단계별로 정리해 드립니다.</p>

<section class="about-sec scontxt">
  <h3><span class="num">1</span> 분노(화)에 대처하는 즉각적인 방법</h3>
  <p class="exptt">초등학생 아이는 자기주장이 강해지고 말대꾸를 시작하면서 부모의 감정을 자극하기 쉽습니다. 순간적으로 화가 치밀어 올랐을 때는 &lsquo;멈춤&rsquo;이 가장 중요합니다.</p>
  <p class="lead">&lsquo;생각 정지&rsquo;와 Time-out (잠시 떨어지기)</p>
  <ul>
    <li>화가 나면 뇌의 이성적인 영역(전두엽)이 마비되고 감정 뇌(편도체)가 제어권을 쥐게 됩니다.</li>
    <li>&ldquo;엄마 지금 화가 많이 나서 잠시 방에 다녀올게&rdquo;라고 말한 뒤, 최소 6초 이상 그 자리를 피하세요. 6초는 감정의 급격한 폭발을 가라앉히는 데 필요한 최소한의 시간입니다.</li>
  </ul>
  <p class="lead">신체 감각에 집중하기</p>
  <ul>
    <li>차가운 물을 한 잔 마시거나, 깊게 숨을 들여마시고 천천히 내쉬며 몸의 긴장을 푸세요.</li>
  </ul>
  <p class="lead">I-Message(나-전달법)로 표현하기</p>
  <ul>
    <li>분노가 가라앉은 후 이야기를 나눕니다. &ldquo;너 왜 또 그래?&rdquo;(You-Message) 대신 &ldquo;네가 약속한 시간을 안 지키니까 엄마는 걱정되고 화가 나&rdquo;처럼 부모의 감정과 상황에 집중해 전달하세요.</li>
  </ul>
</section>

<section class="about-sec scontxt">
  <h3><span class="num">2</span> 두려움과 불안에 대처하는 방법</h3>
  <p class="exptt">&ldquo;내가 아이를 잘 키우고 있는 걸까?&rdquo;, &ldquo;학업이나 또래 관계에서 뒤처지면 어쩌지?&rdquo; 같은 두려움은 대개 미래에 대한 불확실성에서 옵니다.</p>
  <ul>
    <li><strong>두려움의 원인 시각화하기 :</strong> 막연한 불안은 종이에 직접 써보는 것만으로도 크기가 줄어듭니다. &lsquo;내가 지금 두려워하는 것은 무엇인가?&rsquo; &rarr; &lsquo;이 중 내가 지금 통제할 수 있는 것은 무엇인가?&rsquo;를 구분해 보세요.</li>
    <li><strong>&lsquo;완벽한 부모&rsquo;라는 환상 내려놓기 :</strong> 아이에게 필요한 것은 완벽한 부모가 아니라 &lsquo;충분히 좋은 부모(Good enough parent)&rsquo;입니다. 실수하고 사과하는 부모의 모습을 통해 아이도 완벽하지 않은 자신과 타인을 수용하는 법을 배웁니다.</li>
    <li><strong>타인과의 비교 차단하기 :</strong> Mom-cafe, SNS, 주변 학부모와의 과도한 정보 교류는 불안을 증폭시킵니다. 아이의 성장 속도는 저마다 다르므로 비교의 기준을 &lsquo;남의 아이&rsquo;가 아닌 &lsquo;아이의 과거&rsquo;에 두세요.</li>
  </ul>
</section>

<section class="about-sec scontxt">
  <h3><span class="num">3</span> 부모 자신의 마음 자산 관리</h3>
  <p class="exptt">감정 조절의 핵심은 결국 부모 자신의 마음 에너지 잔여량에 달려 있습니다.</p>
  <ul>
    <li><strong>감정 수용 :</strong> &ldquo;부모인데 이런 감정을 느끼면 안 돼&rdquo;라는 죄책감을 버리세요. 화나 두려움도 나를 보호하기 위한 자연스러운 감정입니다.</li>
    <li><strong>에너지 충전 :</strong> 하루 20~30분이라도 온전히 &lsquo;엄마&rsquo;가 아닌 &lsquo;나 자신&rsquo;으로 존재하는 시간(독서, 산책, 취미 등)을 확보하세요.</li>
    <li><strong>지속적인 사과 :</strong> 아이에게 화를 냈다면 솔직하게 사과하세요. &ldquo;아까는 엄마가 감정을 조절하지 못하고 크게 소리쳐서 미안해. 하지만 네 행동의 어떤 점은 수정이 필요해&rdquo;라고 구분해 설명해주면 됩니다.</li>
  </ul>
</section>

<p class="closing">아이를 키우는 과정에서 느끼는 부정적 감정은 부모로서 부족해서가 아니라, 아이를 잘 키우고 싶다는 강한 책임감에서 비롯된 경우가 많습니다. 스스로에게 조금 더 너그러워져도 괜찮습니다.</p>
HTML
    ,
);

$GP_PREP['routine'] = array(
    'title' => '아이의 취침 루틴 만들기',
    'html'  => <<<'HTML'
<p class="lead-p">초등학교 입학 후 취침 루틴은 &lsquo;수면 시간 확보&rsquo;와 &lsquo;등교 준비&rsquo;가 자연스럽게 연결되도록 만드는 것이 핵심입니다.</p>
<p>초등 저학년 아동에게 권장되는 수면 시간은 <b>하루 9~11시간</b>입니다. 보통 오전 7시~7시 30분 사이에 일어나는 일정에 맞추려면 <b>밤 9시~9시 30분 사이에는 잠들어야</b> 규칙적인 등교 생활이 가능해집니다.</p>
<p>아침 일과를 수월하게 만들고 밤에 편안히 잠들 수 있도록 돕는 4단계 취침 루틴을 안내해 드립니다.</p>

<h3 class="about-h">초등 입학생을 위한 4단계 취침 루틴</h3>

<div class="about-sub scontxt">
  <h4 class="stit"><span>1단계 · 잠들기 1시간 전 (저녁 8:00 ~ 8:30) &mdash; 준비 및 차단</span></h4>
  <ul class="plnone">
    <li><strong>시각 자극 차단 :</strong> TV, 스마트폰, 패드 등 디스플레이 화면은 멜라토닌 분비를 방해하므로 잠들기 최소 1시간 전에 끕니다.</li>
    <li><strong>등교 준비 미리 하기 :</strong> 내일 입을 옷을 함께 골라두고, 책가방(알림장, 필통 등)을 스스로 챙기게 합니다. 이 과정이 루틴에 포함되면 아침 등교 시간의 전쟁과 불안감이 크게 줄어듭니다.</li>
  </ul>
</div>

<div class="about-sub scontxt">
  <h4 class="stit"><span>2단계 · 잠들기 30분 전 (저녁 8:30 ~ 8:50) &mdash; 신체 이완</span></h4>
  <ul class="plnone">
    <li><strong>미온수 씻기 :</strong> 미지근한 물로 양치와 세수, 또는 가벼운 목욕을 합니다. 체온이 살짝 올랐다 내려가면서 수면 욕구가 자연스럽게 촉진됩니다.</li>
    <li><strong>조명 낮추기 :</strong> 집안의 메인 조명을 끄고 주황색 계열의 간접 조명이나 무드등만 켜두어 &lsquo;이제 잘 시간&rsquo;이라는 신호를 뇌에 전달합니다.</li>
  </ul>
</div>

<div class="about-sub scontxt">
  <h4 class="stit"><span>3단계 · 잠들기 10분 전 (저녁 8:50 ~ 9:00) &mdash; 수면 정서 형성</span></h4>
  <ul class="plnone">
    <li><strong>잠자리 독서 :</strong> 잔잔한 그림책이나 동화책을 1~2권 읽어줍니다. 스스로 읽기보다는 부모가 나긋한 목소리로 읽어주는 것이 아이의 심리적 안정에 훨씬 효과적입니다.</li>
    <li><strong>하루 마감 대화 :</strong> &ldquo;오늘 학교(유치원)에서 가장 재미있었던 일이 뭐야?&rdquo;, &ldquo;오늘 고생 많았어&rdquo;처럼 긍정적인 감정으로 하루를 마무리합니다.</li>
  </ul>
</div>

<div class="about-sub scontxt">
  <h4 class="stit"><span>4단계 · 소등 및 수면 (저녁 9:00~)</span></h4>
  <ul class="plnone">
    <li>잠자리에 들 때는 조명을 완전히 끄고, 애착 인형이나 포근한 이불을 덮어주며 인사(&ldquo;잘 자, 내일 만나자&rdquo;)를 건넵니다.</li>
  </ul>
</div>

<h3 class="about-h">성공적인 루틴 정착을 위한 Tip</h3>
<ul class="plnone">
  <li><strong>주말에도 일정한 시각 유지 :</strong> 주말이라고 늦게 자고 늦게 깨면 &lsquo;월요병&rsquo;이 생기고 루틴이 무너집니다. 주말 일어나는 시간 오차는 최대 1시간 이내로 유지해 주세요.</li>
  <li><strong>수면 시각표 시각화하기 :</strong> 시계 그림이나 귀여운 스티커판을 활용해 &lsquo;8:30 씻기 &rarr; 8:45 책 읽기 &rarr; 9:00 불 끄기&rsquo;처럼 아이 눈에 보이는 루틴표를 만들면 스스로 행동하는 데 도움이 됩니다.</li>
</ul>
HTML
    ,
);

function prep_subnav($cur) {
    global $GP_PREP;
    $h = '<div class="subnav">';
    foreach ($GP_PREP as $k => $a) {
        $on = ($k === $cur) ? ' on' : '';
        $h .= '<a class="sub' . $on . '" href="index.php?p=prep&sub=' . e($k) . '">' . e($a['title']) . '</a>';
    }
    $h .= '</div>';
    return $h;
}
