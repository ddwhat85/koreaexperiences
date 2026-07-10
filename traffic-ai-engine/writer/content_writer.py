"""
writer/content_writer.py
TrafficAI Engine 1.0 — AI Content Writer v2

참조 계정 통합:
  ddwhat1985  — 쇼핑 발견, 가족 맥락, "스친이들", "업청/팔어/사구있음"
  dior8524    — ".." 줄임표, "치니덜", 알고리즘 썰, "좋앙!/있었냥?"
  yoonseul_ys — 솔직 고백 구조 (조회4735, 좋아요173) 바이럴 패턴 차용

5 톤 × 5 구조 = 25가지 조합 / 핸드폰 오타 의무 삽입
섀도우밴 예방 5대 규칙 준수
"""

import os
import re
import json
import random
import logging
from abc import ABC, abstractmethod
from dataclasses import dataclass, field
from typing import Optional

logger = logging.getLogger(__name__)


# ===========================================================================
# [A] 공유 데이터 구조
# ===========================================================================

@dataclass
class StoryPacket:
    product_id:       str
    product_name:     str
    store_name:       str
    product_url:      str
    image_url:        str
    city:             str
    situation:        str
    emotion:          str
    story_theme:      str
    product_features: list = field(default_factory=list)
    price_krw:        Optional[int] = None


@dataclass
class ContentOutput:
    content:           str
    first_comment:     str
    faq_data:          list
    tone_variant:      str
    structure_variant: str
    raw_prompt:        str = ""


# ===========================================================================
# [B] 톤앤매너 레이어 — 5 톤 × 5 구조
# ===========================================================================

# 팔로워 호칭 풀 — 계정별 실제 사용 표현
FOLLOWER_TERMS = [
    "스친이들",    # ddwhat1985
    "치니덜",      # dior8524
    "스치나들",    # yoonseul_ys
    "치니",        # dior8524 단수형
]

# ---------- 구조 변형 ----------
STRUCTURE_VARIANTS = {
    "situation_first": {
        "label": "상황 → 발견 → 특징 → 질문",
        "hint": (
            "어디 갔다가 발견했는지 짧게 쓰고, 가격이나 특징 언급하고, "
            "마지막은 팔로워한테 아는지/경험 있는지 묻는 질문으로 끝."
        ),
    },
    "price_shock": {
        "label": "가격 놀람 → 설명 → 질문",
        "hint": (
            "가격이 얼마나 싼지/좋은지 먼저 치고, "
            "어디서 샀는지 어떤 상품인지 설명하고, "
            "팔로워들 여기 아는지 물어봐."
        ),
    },
    "contrast": {
        "label": "별기대없었는데 반전 → 질문",
        "hint": (
            "처음엔 그냥 지나치려 했는데 사게 됐다는 흐름. "
            "써보니 생각보다 좋았다는 솔직한 반응. "
            "마지막은 이런거 본 적 있는지 경험 유도."
        ),
    },
    "recommendation": {
        "label": "강추 공유형 → 질문",
        "hint": (
            "일본 가면 이거 꼭 사야 한다는 강추 톤. "
            "상품 특징 짧게 설명하고 가격 언급. "
            "팔로워들도 챙겨갔는지 물어봐."
        ),
    },
    "honest_confession": {
        "label": "솔직 고백 → 상품 구원 → 공감 유도",
        "hint": (
            "팔로워들아... 솔직하게 말할게 식으로 시작. "
            "내가 왜 이 상품이 필요했는지 짧은 상황 고백. "
            "이 상품 덕분에 해결됐다는 흐름. "
            "마지막은 '나만 이래?' 또는 '써본 사람 있어?' 식의 부드러운 공감 유도 질문."
        ),
    },
}

# ---------- 톤 변형 ----------
TONE_VARIANTS = {
    "ddwhat_basic": {
        "label": "ddwhat 기본체",
        "instruction": (
            "ddwhat1985 실제 말투. 짧은 문장 줄바꿈. "
            "'스친이들' 또는 '치니덜' 호칭. "
            "~있어, ~팔어, ~있음, ~아는 사람? 종결어미. "
            "가족 언급('우리 별씌', '애기') 자연스럽게 한 번 넣어도 됨. "
            "예시:\n"
            "오늘 삿포로 놀러왔는데 니시마츠야 있어\n"
            "여기가 유아옷 애기 용품 업청 싸게 팔어\n"
            "티셔츠 한장에 380엔 정도야\n"
            "진짜 업청싸! 스친이들 여기 아는 사람?\n"
            "우리 별씌 업청사구있음"
        ),
    },
    "ddwhat_excited": {
        "label": "ddwhat 흥분체",
        "instruction": (
            "진짜 좋아서 흥분된 상태. '진짜' 2~3번 반복 자연스럽게. "
            "상품 경험을 '흡입했다', '미쳤다' 식으로 과장. "
            "예시:\n"
            "진짜 지난번에 일본가서 먹은 브런치인데\n"
            "진짜 흡입하면서 먹은듯\n"
            "스친이들 오늘 아침 뭐먹나\n"
            "---\n"
            "흥분 뒤 팔로워 일상 끌어들이는 질문으로 끝."
        ),
    },
    "dior_chill": {
        "label": "dior 여유체 + 알고리즘 썰",
        "instruction": (
            "dior8524 말투. '..' 줄임표로 말 흐리기. 'ㅎㅎ' 가볍게. "
            "'치니덜' 또는 '스치나들' 호칭. "
            "'넘나', '넘', '짱이지', '~아님?', '~있었냥?' 사용. "
            "알고리즘/우연 타이밍 썰 한 줄 넣기 (예: '알고리즘이 자꾸 보여주는거야..', "
            "'새벽에 눈눴는데 이게 뜨는거야..') "
            "예시:\n"
            "오지마..전쟁통이야..\n"
            "세일기간 이야..?\n"
            "중간에 세일 품목 몇개 걸리긴 함 ㅎㅎ\n"
            "치니덜 이런거 봤었냥?"
        ),
    },
    "dior_info": {
        "label": "dior 정보공유체",
        "instruction": (
            "dior8524 정보 공유 스타일. '★★' 또는 감정 훅으로 시작. "
            "상품 특징을 줄마다 나열. '좋앙!', '맛나더라', '한 번에' 표현. "
            "'치니덜' 또는 '스친이들' 호칭. 마지막 질문. "
            "예시:\n"
            "★★ 파운드 케이크 좋아하는 치니 있었냥?\n"
            "나 부산갔을 때 넘 맛난 파운드케이크를 먹었었는데\n"
            "이거 초코맛은 그냥 브라우니라고 보면 되는\n"
            "꾸덕 고급진 초코 맛\n"
            "한 번에 여러개 주문해서 냉동보관하기 좋앙!"
        ),
    },
    "honest_confession": {
        "label": "솔직 고백체",
        "instruction": (
            "yoonseul_ys 바이럴 구조 차용. '스치나들...' 또는 '치니덜...' + 감정 훅. "
            "'오늘 진짜 솔직하게 말할게' 또는 '솔직히...' 식으로 고백 선언. "
            "내가 왜 이게 필요했나 1~2줄 솔직한 상황 설명. "
            "이 상품 덕분에 해결/발견했다는 흐름. "
            "마지막: '나만 이래?', '써본 사람?', '공감되면 댓글 ㅎ' 식 부드러운 참여 유도. "
            "예시 구조:\n"
            "스치나들...솔직히 말할게\n"
            "나 요즘 [상황] 너무 힘들었거든\n"
            "근데 이거 쓰기 시작하니까 진짜 달라\n"
            "[상품 특징 1~2줄]\n"
            "나만 이런 거 몰랐던 거야..?"
        ),
    },
}

# ---------- 핸드폰 오타 패턴 ----------
PHONE_TYPO_EXAMPLES = [
    ("엄청",      "업청"),
    ("엄청나",    "업청나"),
    ("너무",       "넘"),
    ("너무나",    "넘나"),
    ("팔아",       "팔어"),
    ("있어",       "있응"),
    ("사고있음",  "사구있음"),
    ("좋아",       "좋앙"),
    ("있었냐",    "있었냥"),
    ("이거봐",    "이거봐봐"),
    ("눈떴는데",  "눈눴는데"),
    ("솔직히",    "솔직이"),
    ("진짜로",    "진짜루"),
    ("아이고",    "아이구"),
    ("먹었는데",  "먹은듯"),
    ("아는사람",  "아는사람?ㅎ"),
]

# ---------- 오프닝 훅 풀 ----------
OPENING_HOOKS = [
    "오늘 {city} 놀러왔는데",
    "지난번에 {city} 갔을때",
    "{city} 갔다가 {situation}에서",
    "오늘 {situation} 들렀는데",
    "{city} 여행중에 {situation} 들렀는데",
    "진짜 {city} 가면",
    "{situation} 갔다가 발견한건데",
    "이번에 {city} 갔을때",
    "{city} {situation}..오지마..",
    "스친이들 {city} 가면 꼭",
    "{city} 알고리즘이 자꾸 보여주는거야..",
    "새벽에 눈눴는데 {city} {situation}이 뜨는거야..",
    "스치나들...오늘 솔직하게 말할게",
    "진짜 {city} {situation} 다들 알아?",
]


# ===========================================================================
# [C] AI 제공자 추상화
# ===========================================================================

class BaseAIProvider(ABC):
    @abstractmethod
    def complete(self, system: str, user: str, max_tokens: int = 600) -> str:
        ...


class OpenAIProvider(BaseAIProvider):
    def __init__(self):
        try:
            from openai import OpenAI
        except ImportError:
            raise ImportError("pip install openai")
        api_key = os.getenv("OPENAI_API_KEY")
        if not api_key:
            raise EnvironmentError("OPENAI_API_KEY 환경변수 필요")
        self._client = OpenAI(api_key=api_key)
        self.model = os.getenv("OPENAI_MODEL", "gpt-4o")

    def complete(self, system: str, user: str, max_tokens: int = 600) -> str:
        resp = self._client.chat.completions.create(
            model=self.model,
            messages=[
                {"role": "system", "content": system},
                {"role": "user",   "content": user},
            ],
            max_tokens=max_tokens,
            temperature=0.93,
        )
        return resp.choices[0].message.content.strip()


class ClaudeProvider(BaseAIProvider):
    def __init__(self):
        try:
            import anthropic
        except ImportError:
            raise ImportError("pip install anthropic")
        api_key = os.getenv("ANTHROPIC_API_KEY")
        if not api_key:
            raise EnvironmentError("ANTHROPIC_API_KEY 환경변수 필요")
        self._client = anthropic.Anthropic(api_key=api_key)
        self.model = os.getenv("CLAUDE_MODEL", "claude-opus-4-8")

    def complete(self, system: str, user: str, max_tokens: int = 600) -> str:
        import anthropic
        msg = self._client.messages.create(
            model=self.model,
            max_tokens=max_tokens,
            system=system,
            messages=[{"role": "user", "content": user}],
        )
        return msg.content[0].text.strip()


def get_ai_provider() -> BaseAIProvider:
    provider = os.getenv("AI_PROVIDER", "openai").lower()
    if provider == "claude":
        return ClaudeProvider()
    return OpenAIProvider()


# ===========================================================================
# [D] ContentWriter 메인 클래스
# ===========================================================================

class ContentWriter:
    _SAFETY_RULES = """[절대 준수 규칙 — 위반 시 즉시 재생성]
1. 본문에 URL(http, www 등) 절대 포함 금지
2. 해시태그(#으로 시작하는 단어) 절대 포함 금지
3. 이모지는 본문 전체에서 최대 1개만 허용
4. 본문 마지막 문장은 반드시 독자에게 던지는 자연스러운 질문으로 끝낼 것
5. 광고 문구('구매', '할인', '이벤트', '지금 구입', '클릭', '링크') 절대 사용 금지""".strip()

    # 실제 바이럴 포스트 5개 예시 (시스템 프롬프트 내장)
    _POST_EXAMPLES = """[실제 포스트 예시 1 — ddwhat1985 쇼핑 발견체]
오늘 삿포로 놀러왔는데 니시마츠야 있어
여기가 유아옷 애기 용품 업청 싸게 팔어
티셔츠 한장에 380엔 정도야
진짜 업청싸! 스친이들 여기 아는 사람?
우리 별씌 업청사구있음

[실제 포스트 예시 2 — ddwhat1985 흥분체]
진짜 지난번에 일본가서 먹은 브런치인데
진짜 흡입하면서 먹은듯
스친이들 오늘 아침 뭐먹나

[실제 포스트 예시 3 — dior8524 정보공유체]
★★ 파운드 케이크 좋아하는 치니 있었냥?
나 부산갔을 때 넘 맛난 파운드케이크를 먹었었는데
이거 초코맛은 그냥 브라우니라고 보면 되는
꾸덕 고급진 초코 맛
한 번에 여러개 주문해서 냉동보관하기 좋앙!

[실제 포스트 예시 4 — dior8524 여유체]
오지마..전쟁통이야..
세일기간 이야..?
중간에 세일 품목 몇개 걸리긴 함 ㅎㅎ
치니덜 이런거 봤었냥?

[실제 포스트 예시 5 — 솔직고백체 (바이럴 173좋아요 구조)]
스치나들...솔직히 말할게
나 요즘 너무 바빠서 제대로 못 챙겼거든
근데 이거 쓰기 시작하니까 진짜 달라졌어
[상품 특징 2줄]
나만 이런 거 몰랐던 거야..?""".strip()

    def __init__(self, provider: Optional[BaseAIProvider] = None):
        self._ai = provider or get_ai_provider()

    def _pick_variants(self):
        structure    = random.choice(list(STRUCTURE_VARIANTS.values()))
        tone         = random.choice(list(TONE_VARIANTS.values()))
        hook_tmpl    = random.choice(OPENING_HOOKS)
        follower_term = random.choice(FOLLOWER_TERMS)
        return structure, tone, hook_tmpl, follower_term

    def _build_system_prompt(self, structure: dict, tone: dict, follower_term: str) -> str:
        typo_samples = random.sample(PHONE_TYPO_EXAMPLES, 2)
        typo_hint = ", ".join(f'"{a}" 대신 "{b}"처럼' for a, b in typo_samples)

        return f"""너는 일본 직구 상품을 직접 써본 한국인 Threads 사용자야.
팔로워들한테 자연스러운 일상 공유처럼 글을 써.

{self._POST_EXAMPLES}

---
[이번 글에서 팔로워 호칭] → "{follower_term}"

[이번 글 구조]
{structure['label']}: {structure['hint']}

[이번 글 톤]
{tone['label']}: {tone['instruction']}

[핸드폰 오타 규칙 — 반드시 1~2개 포함]
빠르게 핸드폰 타이핑하다 생기는 자연스러운 오타.
이번 글엔 {typo_hint} 스타일 오타 포함.
오타는 억지스럽지 않게, 실제 타이핑 실수처럼 자연스럽게.

{self._SAFETY_RULES}

[글 길이 규칙 — 필수]
본문(content)은 반드시 100자 이내 (공백 포함). 3~5줄 최대. 짧고 임팩트 있게.

출력 형식 (JSON만, 추가 설명 없이):
{{
  "content": "Threads 본문 (줄바꿈은 \\n으로, 100자 이내)",
  "first_comment": "첫 댓글 (URL 포함 가능, 자연스럽게 유도)",
  "faq": [
    {{"question": "예상 질문1", "answer": "답변1"}},
    {{"question": "예상 질문2", "answer": "답변2"}}
  ]
}}"""

    def _build_user_prompt(self, packet: StoryPacket, hook_tmpl: str, follower_term: str) -> str:
        hook = hook_tmpl.format(
            city=packet.city,
            situation=packet.situation,
            emotion=packet.emotion,
        )
        features = ", ".join(packet.product_features[:3]) if packet.product_features else "효과 좋음"
        price_line = f"가격: 약 {packet.price_krw:,}원" if packet.price_krw else ""

        return f"""[스토리 컨텍스트]
여행지: {packet.city}
상황: {packet.situation}
감성: {packet.emotion}
스토리 테마: {packet.story_theme}
추천 오프닝 방향: "{hook}"
팔로워 호칭: "{follower_term}"

[상품 정보]
상품명: {packet.product_name}
주요 특징: {features}
{price_line}
스토어: {packet.store_name}

중요: product_url 절대 본문에 포함 금지.
댓글에 '직구' 또는 '정보' 댓글 달아달라고 자연스럽게 유도."""

    def _parse_output(self, raw: str) -> dict:
        raw = re.sub(r'^```(?:json)?\s*', '', raw.strip())
        raw = re.sub(r'\s*```$', '', raw.strip())
        return json.loads(raw)

    def _validate_content(self, content: str) -> list:
        issues = []
        if len(content) > 120:
            issues.append(f"글 너무 김: {len(content)}자 (최대 100자)")
        if re.search(r'https?://\S+|www\.\S+', content):
            issues.append("URL 포함")
        if re.search(r'#\w+', content):
            issues.append("해시태그 포함")
        emoji_count = len(re.findall(
            r'[\U0001F300-\U0001F9FF\U0001FA00-\U0001FA9F\U00002702-\U000027B0]',
            content
        ))
        if emoji_count > 1:
            issues.append(f"이모지 {emoji_count}개 (최대 1개)")
        for w in ['구매하세요', '클릭', '지금 구입', '이벤트', '할인코드']:
            if w in content:
                issues.append(f"광고 단어 포함: {w}")
        return issues

    def generate(self, packet: StoryPacket, max_attempts: int = 3) -> ContentOutput:
        for attempt in range(1, max_attempts + 1):
            structure, tone, hook_tmpl, follower_term = self._pick_variants()
            system = self._build_system_prompt(structure, tone, follower_term)
            user   = self._build_user_prompt(packet, hook_tmpl, follower_term)

            logger.info(
                f"[GEN attempt={attempt}] "
                f"structure={structure['label']} | tone={tone['label']} | follower={follower_term}"
            )

            try:
                raw  = self._ai.complete(system, user, max_tokens=700)
                data = self._parse_output(raw)
            except Exception as e:
                logger.error(f"[GEN ERROR] {e}")
                continue

            content = data.get("content", "")
            issues  = self._validate_content(content)

            if issues:
                logger.warning(f"[RULE VIOLATION] attempt={attempt} | {issues}")
                continue

            logger.info(f"[GEN OK] attempt={attempt}")
            return ContentOutput(
                content           = content,
                first_comment     = data.get("first_comment", ""),
                faq_data          = data.get("faq", []),
                tone_variant      = tone["label"],
                structure_variant = structure["label"],
                raw_prompt        = user,
            )

        raise RuntimeError(f"콘텐츠 생성 실패: {max_attempts}회 모두 규칙 위반")


if __name__ == "__main__":
    logging.basicConfig(level=logging.INFO,
                        format="%(asctime)s %(levelname)s %(message)s")
    writer = ContentWriter()
    packet = StoryPacket(
        product_id="JP-001", product_name="로이스 포테이토칩 초콜렛 버터맛",
        store_name="삿포로팩토리직구", product_url="", image_url="",
        city="삿포로", situation="니시마츠야 쇼핑", emotion="신남",
        story_theme="삿포로 쇼핑 중 발견",
        product_featur