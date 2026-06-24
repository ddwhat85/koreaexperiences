"""
analyzer/product_analyzer.py
TrafficAI Engine 1.0 — Product Analyzer

4개 소스 스토어에서 상품 데이터 수집 → PublishPacket 변환 준비
소스: sapporofactory, portablejapan, dunkjapan, geminijapan

Naver Smartstore: 상품 목록 페이지 파싱 (requests + BeautifulSoup)
"""

from __future__ import annotations

import os
import re
import time
import logging
import hashlib
import random
from abc import ABC, abstractmethod
from dataclasses import dataclass, field
from typing import Optional
from urllib.parse import urljoin, urlparse, quote

import requests
from bs4 import BeautifulSoup

logger = logging.getLogger(__name__)

# ---------------------------------------------------------------------------
# 상수
# ---------------------------------------------------------------------------

USER_AGENTS = [
    "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 "
    "(KHTML, like Gecko) Chrome/125.0.0.0 Safari/537.36",
    "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 "
    "(KHTML, like Gecko) Chrome/125.0.0.0 Safari/537.36",
    "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:126.0) Gecko/20100101 Firefox/126.0",
]

# 네이버 쇼핑 API 검색 키워드 목록
# GitHub Actions에서 스마트스토어 직접 스크레이핑이 차단(429)되므로
# 키워드 검색 방식으로 일본직구 상품을 수집합니다.
SEARCH_KEYWORDS = [
    {"query": "일본직구 드럭스토어", "category": "드럭스토어", "city": "일본"},
    {"query": "일본 화장품 직구", "category": "뷰티/스킨케어", "city": "일본"},
    {"query": "일본 과자 직구", "category": "식품/간식", "city": "일본"},
    {"query": "일본 직구 생활용품", "category": "생활용품", "city": "일본"},
    {"query": "삿포로 직구", "category": "드럭스토어", "city": "삿포로"},
]

# 하위 호환성을 위해 유지 (스크레이퍼 초기화에서 사용 안 함)
SOURCE_STORES = {}

# ---------------------------------------------------------------------------
# 데이터 스키마
# ---------------------------------------------------------------------------

@dataclass
class ProductData:
    """
    스토어에서 수집한 상품 데이터.
    PublishPacket 생성의 입력값으로 사용됨.
    """
    product_id:   str           # 고유 ID (store_key + 상품번호 해시)
    store_key:    str           # SOURCE_STORES 키
    store_name:   str           # 표시용 스토어명
    product_name: str           # 상품명
    price:        int           # 판매가 (KRW, 0=미확인)
    original_price: int         # 원가 (KRW, 0=미확인)
    image_url:    str           # 대표 이미지 URL
    product_url:  str           # 상품 상세 URL (ManyChat DM 전용)
    category:     str           # 카테고리 (드럭스토어, 식품, 뷰티 등)
    tags:         list = field(default_factory=list)
    review_count: int = 0
    review_score: float = 0.0   # 0~100 스케일로 정규화
    city:         str = "일본"
    raw_data:     dict = field(default_factory=dict)

    @property
    def discount_rate(self) -> float:
        if self.original_price and self.original_price > self.price:
            return round((1 - self.price / self.original_price) * 100, 1)
        return 0.0

    def to_story_hint(self) -> dict:
        """ContentWriter에게 전달할 스토리 힌트 딕셔너리"""
        return {
            "product_name": self.product_name,
            "store_name":   self.store_name,
            "city":         self.city,
            "category":     self.category,
            "price":        self.price,
            "discount_rate": self.discount_rate,
            "review_score": self.review_score,
            "tags":         self.tags,
        }


# ---------------------------------------------------------------------------
# HTTP 헬퍼
# ---------------------------------------------------------------------------

class FetchClient:
    """Rate-limited, retrying HTTP client"""

    def __init__(
        self,
        timeout: int = 12,
        max_retries: int = 3,
        min_delay: float = 1.0,
        max_delay: float = 3.0,
    ):
        self.timeout    = timeout
        self.max_retries = max_retries
        self.min_delay  = min_delay
        self.max_delay  = max_delay
        self._session   = requests.Session()

    def _headers(self) -> dict:
        return {
            "User-Agent":      random.choice(USER_AGENTS),
            "Accept":          "text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8",
            "Accept-Language": "ko-KR,ko;q=0.9,ja;q=0.8,en;q=0.7",
            "Accept-Encoding": "gzip, deflate, br",
        }

    def get(self, url: str) -> Optional[BeautifulSoup]:
        delay = self.min_delay
        for attempt in range(1, self.max_retries + 1):
            try:
                time.sleep(delay + random.uniform(0, 0.5))
                resp = self._session.get(
                    url,
                    headers=self._headers(),
                    timeout=self.timeout,
                    allow_redirects=True,
                )
                resp.raise_for_status()
                return BeautifulSoup(resp.text, "html.parser")
            except requests.HTTPError as e:
                logger.warning(f"[HTTP {e.response.status_code}] {url} (attempt {attempt})")
                if e.response.status_code in (403, 404):
                    return None
            except requests.RequestException as e:
                logger.warning(f"[CONN ERR] {url}: {e} (attempt {attempt})")
            delay = min(delay * 2, 8.0)
        logger.error(f"[GIVE UP] {url} after {self.max_retries} retries")
        return None

    def fetch_og_image(self, url: str) -> str:
        """
        상품 페이지에서 og:image 메타태그를 읽어 실제 대표 이미지 URL을 반환.
        실패 시 빈 문자열 반환.
        """
        if not url:
            return ""
        try:
            resp = self._session.get(
                url,
                headers=self._headers(),
                timeout=self.timeout,
                allow_redirects=True,
            )
            resp.raise_for_status()
            soup = BeautifulSoup(resp.text, "html.parser")
            # 1순위: og:image
            og = soup.find("meta", property="og:image")
            if og and og.get("content"):
                return og["content"].strip()
            # 2순위: twitter:image
            tw = soup.find("meta", attrs={"name": "twitter:image"})
            if tw and tw.get("content"):
                return tw["content"].strip()
        except Exception as e:
            logger.debug(f"[OG:IMAGE] {url}: {e}")
        return ""


# ---------------------------------------------------------------------------
# 기본 스크레이퍼
# ---------------------------------------------------------------------------

class BaseStoreScraper(ABC):
    """모든 스토어 스크레이퍼의 공통 인터페이스"""

    def __init__(self, store_key: str, client: FetchClient):
        meta = SOURCE_STORES[store_key]
        self.store_key  = store_key
        self.store_name = meta["name"]
        self.base_url   = meta["url"]
        self.city       = meta.get("city", "일본")
        self.client     = client

    def _make_product_id(self, raw_id: str) -> str:
        digest = hashlib.md5(f"{self.store_key}:{raw_id}".encode()).hexdigest()[:8]
        return f"{self.store_key[:3].upper()}-{digest}"

    @abstractmethod
    def fetch_products(self, limit: int = 20) -> list:
        """상품 목록 반환. limit: 최대 반환 수"""


# ---------------------------------------------------------------------------
# Naver Smartstore 스크레이퍼
# ---------------------------------------------------------------------------

class NaverSmartStoreScraper(BaseStoreScraper):
    """
    Naver Smartstore 상품 목록 스크레이퍼.

    Naver Smartstore는 React SPA라 SSR 데이터가 제한적이지만,
    __PRELOADED_STATE__ JSON에 초기 상품 데이터가 포함되어 있어 활용.
    폴백: /products 목록 페이지 HTML 파싱.
    """

    # Naver Shopping API (오픈 API 키 있을 때 사용)
    NAVER_API_URL = "https://openapi.naver.com/v1/search/shop.json"

    def __init__(self, store_key: str, client: FetchClient):
        super().__init__(store_key, client)
        self.naver_client_id     = os.getenv("NAVER_CLIENT_ID", "")
        self.naver_client_secret = os.getenv("NAVER_CLIENT_SECRET", "")

    # ------------------------------------------------------------------ #
    # Naver 오픈 API 경로 (권장)
    # ------------------------------------------------------------------ #

    def _fetch_via_api(self, limit: int) -> list:
        """Naver Shopping API로 해당 스토어 상품 검색"""
        if not (self.naver_client_id and self.naver_client_secret):
            return []

        params = {
            "query":   self.store_key,   # 영문 스토어 ID로 검색 (예: sapporofactory)
            "display": min(limit, 100),
            "sort":    "date",           # 최신순
        }
        headers = {
            "X-Naver-Client-Id":     self.naver_client_id,
            "X-Naver-Client-Secret": self.naver_client_secret,
        }
        try:
            resp = requests.get(
                self.NAVER_API_URL,
                params=params,
                headers=headers,
                timeout=10,
            )
            resp.raise_for_status()
            items = resp.json().get("items", [])
            logger.info(f"[NAVER API] {self.store_key}: 원본 {len(items)}개 수신")
        except Exception as e:
            logger.warning(f"[NAVER API] {self.store_key}: {e}")
            return []

        products = []
        for item in items:
            mall = item.get("mallName", "")
            if self.store_key not in item.get("link", "").lower() and self.store_name not in mall:
                continue

            price   = int(re.sub(r"\D", "", item.get("lprice", "0")) or 0)
            h_price = int(re.sub(r"\D", "", item.get("hprice", "0")) or 0)
            name_clean = re.sub(r"<[^>]+>", "", item.get("title", "")).strip()
            image_raw  = item.get("image", "")

            products.append(ProductData(
                product_id   = self._make_product_id(item.get("productId", name_clean)),
                store_key    = self.store_key,
                store_name   = self.store_name,
                product_name = name_clean,
                price        = price,
                original_price = h_price if h_price > price else price,
                image_url    = image_raw,
                product_url  = item.get("link", ""),
                category     = item.get("category4", item.get("category3", "일반상품")),
                tags         = [item.get("brand", "")],
                review_score = 75.0,  # API 기본값
                city         = self.city,
                raw_data     = item,
            ))
        return products

    # ------------------------------------------------------------------ #
    # HTML 스크레이핑 경로 (폴백)
    # ------------------------------------------------------------------ #

    def _fetch_via_html(self, limit: int) -> list:
        """스토어 상품 목록 페이지 HTML 파싱"""
        products_url = f"{self.base_url}/products"
        soup = self.client.get(products_url)
        if not soup:
            logger.error(f"[NAVER HTML] 접근 실패: {products_url}")
            return []

        products = []

        # __PRELOADED_STATE__ JSON에서 상품 데이터 추출
        script_tags = soup.find_all("script")
        for script in script_tags:
            text = script.string or ""
            if "__PRELOADED_STATE__" not in text:
                continue
            match = re.search(r"__PRELOADED_STATE__\s*=\s*(\{.*?\});?\s*</", text, re.DOTALL)
            if not match:
                continue
            try:
                import json
                state = json.loads(match.group(1))
                products = self._parse_preloaded_state(state, limit)
                if products:
                    return products
            except Exception as e:
                logger.debug(f"[PRELOADED_STATE parse] {e}")
            break

        # 폴백: 일반 카드 선택자
        products = self._parse_product_cards(soup, limit)
        return products

    def _parse_preloaded_state(self, state, limit: int) -> list:
        """Naver SSR JSON에서 상품 추출"""
        items_raw = []
        for key in ("productList", "products", "goodsList"):
            found = self._deep_find(state, key)
            if found and isinstance(found, list):
                items_raw = found
                break

        products = []
        for item in items_raw[:limit]:
            try:
                name   = item.get("name") or item.get("productName") or ""
                pid    = str(item.get("id") or item.get("productNo") or name[:10])
                price  = int(item.get("salePrice") or item.get("price") or 0)
                o_price = int(item.get("retailPrice") or item.get("originalPrice") or price)
                imgs   = item.get("images") or item.get("representativeImages") or []
                img    = (imgs[0].get("url") if isinstance(imgs[0], dict) else imgs[0]) if imgs else ""
                link   = (
                    item.get("productUrl")
                    or f"{self.base_url}/products/{pid}"
                )
                reviews = item.get("reviewCount") or 0
                score   = float(item.get("starScore") or item.get("rating") or 0)
                normalized_score = (score / 5.0) * 100 if score else 70.0

                cat_raw = item.get("category", "일반상품")
                if isinstance(cat_raw, dict):
                    cat = cat_raw.get("name", "일반상품")
                else:
                    cat = str(cat_raw)

                products.append(ProductData(
                    product_id    = self._make_product_id(pid),
                    store_key     = self.store_key,
                    store_name    = self.store_name,
                    product_name  = name,
                    price         = price,
                    original_price = o_price,
                    image_url     = img,
                    product_url   = link,
                    category      = cat,
                    review_count  = reviews,
                    review_score  = normalized_score,
                    city          = self.city,
                    raw_data      = item,
                ))
            except Exception as e:
                logger.debug(f"[PARSE ITEM] {e}")
        return products

    def _parse_product_cards(self, soup: BeautifulSoup, limit: int) -> list:
        """HTML 카드 방식 폴백 파싱"""
        card_selectors = [
            "li.bd_3f",
            "div._2sDBS8s-Ag",
            "li[class*='product']",
            "div[class*='product-item']",
        ]
        cards = []
        for sel in card_selectors:
            cards = soup.select(sel)
            if cards:
                break

        products = []
        for card in cards[:limit]:
            try:
                name_el = card.select_one("strong, .bd_1sBuy, [class*='name']")
                name = name_el.get_text(strip=True) if name_el else ""
                if not name:
                    continue

                price_el = card.select_one(".bd_18, [class*='price']")
                price_text = price_el.get_text(strip=True) if price_el else "0"
                price = int(re.sub(r"\D", "", price_text) or 0)

                img_el = card.select_one("img")
                img_url = img_el.get("src") or img_el.get("data-src") or "" if img_el else ""

                a_el = card.select_one("a[href]")
                href = a_el.get("href", "") if a_el else ""
                if href and not href.startswith("http"):
                    href = urljoin(self.base_url, href)

                pid_match = re.search(r"/products/(\d+)", href)
                raw_pid = pid_match.group(1) if pid_match else name[:12]

                products.append(ProductData(
                    product_id   = self._make_product_id(raw_pid),
                    store_key    = self.store_key,
                    store_name   = self.store_name,
                    product_name = name,
                    price        = price,
                    original_price = price,
                    image_url    = img_url,
                    product_url  = href,
                    category     = "일반상품",
                    city         = self.city,
                ))
            except Exception as e:
                logger.debug(f"[CARD PARSE] {e}")

        return products

    @staticmethod
    def _deep_find(obj, key: str):
        """JSON 트리에서 특정 키 재귀 탐색"""
        if isinstance(obj, dict):
            if key in obj:
                return obj[key]
            for v in obj.values():
                result = NaverSmartStoreScraper._deep_find(v, key)
                if result is not None:
                    return result
        elif isinstance(obj, list):
            for item in obj:
                result = NaverSmartStoreScraper._deep_find(item, key)
                if result is not None:
                    return result
        return None

    def fetch_products(self, limit: int = 20) -> list:
        products = self._fetch_via_api(limit)
        if products:
            logger.info(f"[{self.store_key}] Naver API: {len(products)}개")
            return products

        products = self._fetch_via_html(limit)
        logger.info(f"[{self.store_key}] HTML 스크레이핑: {len(products)}개")
        return products


# ---------------------------------------------------------------------------
# 오케스트레이터
# ---------------------------------------------------------------------------

class ProductAnalyzer:
    """
    4개 소스 스토어에서 상품 데이터를 수집하고 점수화.

    환경변수:
        NAVER_CLIENT_ID     : Naver 오픈 API 클라이언트 ID (선택)
        NAVER_CLIENT_SECRET : Naver 오픈 API 시크릿 (선택)
        PA_MIN_SCORE        : 최소 리뷰 점수 필터 (기본 60.0)
        PA_MIN_PRICE        : 최소 가격 필터 KRW (기본 0)
        PA_MAX_PRICE        : 최대 가격 필터 KRW (기본 500000)
    """

    def __init__(self):
        self.min_score = float(os.getenv("PA_MIN_SCORE", "60.0"))
        self.min_price = int(os.getenv("PA_MIN_PRICE", "0"))
        self.max_price = int(os.getenv("PA_MAX_PRICE", "500000"))
        self._client   = FetchClient()
        self._scrapers = self._init_scrapers()

    def _init_scrapers(self) -> dict:
        return {}  # 키워드 검색 방식에서는 스크레이퍼 불필요

    def fetch_all(self, per_store: int = 20) -> list:
        """키워드별 네이버 쇼핑 API로 상품 수집"""
        all_products = []
        seen_ids = set()

        naver_id = os.getenv("NAVER_CLIENT_ID", "")
        naver_secret = os.getenv("NAVER_CLIENT_SECRET", "")

        if not (naver_id and naver_secret):
            logger.error("[ANALYZER] NAVER_CLIENT_ID/SECRET 미설정. 수집 불가.")
            return []

        for kw in SEARCH_KEYWORDS:
            try:
                logger.info(f"[ANALYZER] 키워드 검색: {kw['query']}")
                batch = self._fetch_by_keyword(
                    query=kw["query"],
                    category=kw["category"],
                    city=kw["city"],
                    limit=per_store,
                    naver_id=naver_id,
                    naver_secret=naver_secret,
                )
                new = [p for p in batch if p.product_id not in seen_ids]
                seen_ids.update(p.product_id for p in new)
                all_products.extend(new)
                logger.info(f"[ANALYZER] '{kw['query']}' → {len(new)}개")
            except Exception as e:
                logger.error(f"[ANALYZER] '{kw['query']}' 오류: {e}")

        logger.info(f"[ANALYZER] 총 {len(all_products)}개 수집")
        self._enrich_images(all_products)
        return all_products

    def _fetch_by_keyword(self, query: str, category: str, city: str,
                          limit: int, naver_id: str, naver_secret: str) -> list:
        """네이버 쇼핑 API로 키워드 검색"""
        import hashlib as _hashlib
        NAVER_API_URL = "https://openapi.naver.com/v1/search/shop.json"
        params = {"query": query, "display": min(limit, 100), "sort": "date"}
        headers = {"X-Naver-Client-Id": naver_id, "X-Naver-Client-Secret": naver_secret}
        try:
            resp = requests.get(NAVER_API_URL, params=params, headers=headers, timeout=10)
            resp.raise_for_status()
            items = resp.json().get("items", [])
            logger.info(f"[NAVER API] '{query}': {len(items)}개 수신")
        except Exception as e:
            logger.warning(f"[NAVER API] '{query}': {e}")
            return []

        products = []
        for item in items:
            name_clean = re.sub(r"<[^>]+>", "", item.get("title", "")).strip()
            if not name_clean:
                continue
            price = int(re.sub(r"\D", "", item.get("lprice", "0")) or 0)
            h_price = int(re.sub(r"\D", "", item.get("hprice", "0")) or 0)
            raw_id = item.get("productId") or item.get("link", name_clean)[:20]
            pid = "KW-" + _hashlib.md5(f"{query}:{raw_id}".encode()).hexdigest()[:8]
            products.append(ProductData(
                product_id=pid,
                store_key="naver_search",
                store_name=item.get("mallName", "네이버쇼핑"),
                product_name=name_clean,
                price=price,
                original_price=h_price if h_price > price else price,
                image_url=item.get("image", ""),
                product_url=item.get("link", ""),
                category=category,
                tags=[item.get("brand", "")],
                review_score=75.0,
                city=city,
                raw_data=item,
            ))
        return products

    def fetch_store(self, store_key: str, limit: int = 20) -> list:
        """특정 스토어만 수집"""
        if store_key not in self._scrapers:
            raise ValueError(f"알 수 없는 스토어: {store_key}. 가능: {list(self._scrapers)}")
        products = self._scrapers[store_key].fetch_products(limit)
        self._enrich_images(products)
        return products

    def _enrich_images(self, products: list) -> None:
        """
        image_url이 완전히 비어 있는 경우에만
        상품 페이지의 og:image 메타태그로 교체.
        (untrusted domain URL도 그대로 사용 — 스토어 배너 오염 방지)
        """
        for p in products:
            needs_refresh = not p.image_url
            if needs_refresh and p.product_url:
                logger.debug(f"[OG:IMAGE] {p.product_name[:30]} → og:image 조회 중")
                og_url = self._client.fetch_og_image(p.product_url)
                if og_url:
                    logger.info(f"[OG:IMAGE] ✓ {p.product_name[:30]}")
                    p.image_url = og_url
                else:
                    logger.warning(f"[OG:IMAGE] ✗ {p.product_name[:30]} — og:image 없음")

    def pick_best(
        self,
        products: list,
        n: int = 4,
        exclude_ids=None,
    ) -> list:
        """
        오늘 발행용 상품 n개 선별.
        점수 + 가격 필터 + 중복 제외 + 스토어 다양성 보장.
        """
        if exclude_ids is None:
            exclude_ids = set()

        filtered = []
        for p in products:
            if p.product_id in exclude_ids:
                continue
            if not p.product_name:
                continue
            # 이미지 없으면 제외하지 말고 플레이스홀더 유지 (og:image 실패해도 발행 가능)
            if not (self.min_price <= p.price <= self.max_price):
                logger.debug(f"[pick_best] 가격 필터 제외: {p.product_name[:20]} price={p.price}")
                continue
            if p.review_score < self.min_score:
                logger.debug(f"[pick_best] 점수 필터 제외: {p.product_name[:20]} score={p.review_score}")
                continue
            filtered.append(p)
        logger.info(f"[pick_best] {len(products)}개 중 {len(filtered)}개 통과 (image필터 해제)")

        def _score(p):
            from datetime import datetime as _dt
            base = p.review_score
            hour = _dt.now().hour
            # 오후 3시 슬롯: 식품/간식 우선
            if 14 <= hour <= 16:
                cat = p.category.lower()
                name = p.product_name.lower()
                if any(k in cat for k in ("식품", "간식", "food", "스낵", "과자")):
                    base += 30.0
                if any(k in name for k in ("과자", "초콜릿", "구미", "스낵", "캔디", "쿠키", "젤리", "킷캣", "포키", "칩")):
                    base += 25.0
            # 의류/패션 스토어 우선 (portablejapan)
            if p.store_key == "portablejapan":
                base += 25.0
            # 의류 카테고리 키워드 감지
            cat = p.category.lower()
            name = p.product_name.lower()
            if any(k in cat for k in ("의류", "패션", "clothing", "apparel", "wear", "shirt", "pants")):
                base += 20.0
            if any(k in name for k in ("티셔츠", "바지", "원피스", "자켓", "코트", "셔츠", "블라우스", "가디건", "니트", "후드", "점퍼")):
                base += 15.0
            return base
        filtered.sort(key=_score, reverse=True)

        # 스토어별 최대 1개씩 우선 선택 (다양성)
        selected = []
        used_stores = set()

        for p in filtered:
            if len(selected) >= n:
                break
            if p.store_key not in used_stores:
                selected.append(p)
                used_stores.add(p.store_key)

        # 다양성 부족하면 점수순으로 채우기 (같은 store_key면 카테고리로 다양성)
        for p in filtered:
            if len(selected) >= n:
                break
            if p not in selected:
                selected.append(p)

        logger.info(f"[pick_best] 선별 완료: {len(selected)}개")
        return selected[:n]

 
