"""
publisher/make_connector.py
TrafficAI Engine 1.0 — Make.com Webhook Connector

Data flow: Python Engine -> Make.com Webhook -> Google Sheets log + Buffer -> Threads
"""

import os
import re
import json
import logging
import time
from datetime import datetime, timedelta, timezone
from dataclasses import dataclass, asdict, field
from typing import Optional
import requests
from requests.adapters import HTTPAdapter
from urllib3.util.retry import Retry

# KST = UTC+9
KST = timezone(timedelta(hours=9))


def build_target_datetime(target_time: str) -> str:
    h, m = map(int, target_time.split(":"))
    now_kst = datetime.now(KST)
    scheduled_kst = now_kst.replace(hour=h, minute=m, second=0, microsecond=0)
    if scheduled_kst <= now_kst:
        scheduled_kst += timedelta(days=1)
    return scheduled_kst.astimezone(timezone.utc).strftime("%Y-%m-%dT%H:%M:%SZ")

logger = logging.getLogger(__name__)

PUBLISH_SLOTS = ["08:10", "12:05", "18:20", "22:10"]


@dataclass
class FAQItem:
    question: str
    answer: str


@dataclass
class PublishPacket:
    product_id: str
    store_name: str
    product_name: str
    image_url: str
    product_url: str
    content: str
    target_time: str
    faq_data: list = field(default_factory=list)
    review_score: float = 0.0
    story_theme: str = ""
        image_url_2: str = ""
generation_attempt: int = 1
    pipeline_id: str = field(default_factory=lambda: datetime.utcnow().strftime("%Y%m%d-%H%M%S"))
    created_at: str = field(default_factory=lambda: datetime.utcnow().isoformat())

    def to_webhook_payload(self) -> dict:
        return {
            "pipeline_id":        self.pipeline_id,
            "product_id":         self.product_id,
            "store_name":         self.store_name,
            "product_name":       self.product_name,
            "image_url":          self.image_url,
            "image_url_2":        self.image_url_2,
            "product_url":        self.product_url,
            "content":            self.content,
            "target_time":        self.target_time,
            "target_datetime":    build_target_datetime(self.target_time),
            "faq_data":           [asdict(f) for f in self.faq_data],
            "review_score":       self.review_score,
            "story_theme":        self.story_theme,
            "generation_attempt": self.generation_attempt,
            "created_at":         self.created_at,
        }


class ContentSafetyChecker:
    _URL_RE = re.compile(r'https?://\S+|www\.\S+', re.IGNORECASE)
    _HASHTAG_RE = re.compile(r'#\w+')

    def check(self, content: str):
        violations = []
        if self._URL_RE.search(content):
            violations.append("URL in content")
        if self._HASHTAG_RE.search(content):
            violations.append("hashtag in content")
        return len(violations) == 0, violations


class MakeConnector:
    DEFAULT_WEBHOOK = "https://hook.eu1.make.com/km29aysbsbv1w8y2ll9lsmzjoqviy9dm"

    def __init__(self):
        self.webhook_url = os.getenv("MAKE_WEBHOOK_URL", self.DEFAULT_WEBHOOK)
        self.timeout = int(os.getenv("MAKE_TIMEOUT_SEC", "10"))
        self._checker = ContentSafetyChecker()
        from requests.adapters import HTTPAdapter
        from urllib3.util.retry import Retry
        retry = Retry(total=3, backoff_factor=1, status_forcelist=[429,500,502,503,504])
        adapter = HTTPAdapter(max_retries=retry)
        self._session = requests.Session()
        self._session.mount("https://", adapter)
        self._session.mount("http://", adapter)

    def _headers(self):
        h = {"Content-Type": "application/json; charset=utf-8"}
        token = os.getenv("MAKE_SECRET_TOKEN", "")
        if token:
            h["X-Make-Token"] = token
        return h

    def send(self, packet: PublishPacket) -> dict:
        is_safe, violations = self._checker.check(packet.content)
        if not is_safe:
            return {"success": False, "status_code": 0, "response": "Content safety check failed", "violations": violations}
        payload_bytes = json.dumps(packet.to_webhook_payload(), ensure_ascii=False).encode("utf-8")
        try:
            resp = self._session.post(self.webhook_url, data=payload_bytes, headers=self._headers(), timeout=self.timeout)
            resp.raise_for_status()
            return {"success": True, "status_code": resp.status_code, "response": resp.text, "violations": []}
        except Exception as e:
            return {"success": False, "status_code": 0, "response": str(e), "violations": []}


def pick_target_time(prefer=None):
    if prefer and prefer in PUBLISH_SLOTS:
        return prefer
    now = datetime.now(KST).strftime("%H:%M")
    for slot in PUBLISH_SLOTS:
        if slot > now:
            return slot
    return PUBLISH_SLOTS[0]
