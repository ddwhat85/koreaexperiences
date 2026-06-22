// server.js
// TourAPI 프록시 서버 — 브라우저의 CORS 제약을 우회하기 위한 중간 서버

const express = require('express');
const cors = require('cors');
require('dotenv').config();

const app = express();
const PORT = process.env.PORT || 3000;

app.use(cors());

const TOUR_API_KEY = process.env.TOUR_API_KEY;
const BASE_URL = 'https://apis.data.go.kr/B551011';

if (!TOUR_API_KEY) {
  console.warn('⚠️  TOUR_API_KEY가 설정되지 않았습니다. .env 파일을 확인하세요.');
}

// Keyword search — GET /api/search?keyword=Gyeongbokgung&lang=eng
app.get('/api/search', async (req, res) => {
  const { keyword, lang = 'eng', contentTypeId, pageNo = 1, numOfRows = 20 } = req.query;
  if (!keyword) return res.status(400).json({ error: 'keyword required' });
  const serviceName = lang === 'kor' ? 'KorService2' : 'EngService2';
  const url = new URL(`${BASE_URL}/${serviceName}/searchKeyword2`);
  url.searchParams.set('serviceKey', TOUR_API_KEY);
  url.searchParams.set('MobileOS', 'ETC');
  url.searchParams.set('MobileApp', 'KoreaExperiences');
  url.searchParams.set('keyword', keyword);
  url.searchParams.set('pageNo', pageNo);
  url.searchParams.set('numOfRows', numOfRows);
  url.searchParams.set('_type', 'json');
  if (contentTypeId) url.searchParams.set('contentTypeId', contentTypeId);
  try {
    const response = await fetch(url.toString());
    const text = await response.text();
    let data;
    try { data = JSON.parse(text); } catch {
      return res.status(502).json({ error: 'TourAPI returned non-JSON', raw: text.slice(0, 500) });
    }
    const header = data?.response?.header;
    if (header && header.resultCode !== '0000' && header.resultCode !== '00') {
      return res.status(502).json({ error: `TourAPI error: ${header.resultMsg} (${header.resultCode})` });
    }
    const items = data?.response?.body?.items?.item || [];
    const list = Array.isArray(items) ? items : [items];
    res.json({ count: list.length, totalCount: data?.response?.body?.totalCount || 0, items: list });
  } catch (err) {
    res.status(500).json({ error: err.message });
  }
});

// Area list — GET /api/area?areaCode=1&contentTypeId=12
app.get('/api/area', async (req, res) => {
  const { areaCode, contentTypeId, lang = 'eng', pageNo = 1, numOfRows = 20 } = req.query;
  const serviceName = lang === 'kor' ? 'KorService2' : 'EngService2';
  const url = new URL(`${BASE_URL}/${serviceName}/areaBasedList2`);
  url.searchParams.set('serviceKey', TOUR_API_KEY);
  url.searchParams.set('MobileOS', 'ETC');
  url.searchParams.set('MobileApp', 'KoreaExperiences');
  url.searchParams.set('pageNo', pageNo);
  url.searchParams.set('numOfRows', numOfRows);
  url.searchParams.set('_type', 'json');
  if (areaCode) url.searchParams.set('areaCode', areaCode);
  if (contentTypeId) url.searchParams.set('contentTypeId', contentTypeId);
  try {
    const response = await fetch(url.toString());
    const data = await response.json();
    const items = data?.response?.body?.items?.item || [];
    const list = Array.isArray(items) ? items : [items];
    res.json({ count: list.length, items: list });
  } catch (err) {
    res.status(500).json({ error: err.message });
  }
});

// Detail — GET /api/detail?contentId=12345
app.get('/api/detail', async (req, res) => {
  const { contentId, contentTypeId, lang = 'eng' } = req.query;
  if (!contentId) return res.status(400).json({ error: 'contentId required' });
  const serviceName = lang === 'kor' ? 'KorService2' : 'EngService2';
  const url = new URL(`${BASE_URL}/${serviceName}/detailCommon2`);
  url.searchParams.set('serviceKey', TOUR_API_KEY);
  url.searchParams.set('MobileOS', 'ETC');
  url.searchParams.set('MobileApp', 'KoreaExperiences');
  url.searchParams.set('contentId', contentId);
  if (contentTypeId) url.searchParams.set('contentTypeId', contentTypeId);
  url.searchParams.set('overviewYN', 'Y');
  url.searchParams.set('_type', 'json');
  try {
    const response = await fetch(url.toString());
    const data = await response.json();
    const items = data?.response?.body?.items?.item || [];
    const list = Array.isArray(items) ? items : [items];
    res.json({ count: list.length, items: list });
  } catch (err) {
    res.status(500).json({ error: err.message });
  }
});

// Area codes — GET /api/areacodes
app.get('/api/areacodes', async (req, res) => {
  const { areaCode, lang = 'eng' } = req.query;
  const serviceName = lang === 'kor' ? 'KorService2' : 'EngService2';
  const url = new URL(`${BASE_URL}/${serviceName}/areaCode2`);
  url.searchParams.set('serviceKey', TOUR_API_KEY);
  url.searchParams.set('MobileOS', 'ETC');
  url.searchParams.set('MobileApp', 'KoreaExperiences');
  url.searchParams.set('numOfRows', '100');
  url.searchParams.set('_type', 'json');
  if (areaCode) url.searchParams.set('areaCode', areaCode);
  try {
    const response = await fetch(url.toString());
    const data = await response.json();
    const items = data?.response?.body?.items?.item || [];
    const list = Array.isArray(items) ? items : [items];
    res.json({ count: list.length, items: list });
  } catch (err) {
    res.status(500).json({ error: err.message });
  }
});

// Batch collect — GET /api/batch?areaCodes=1,6,39&contentTypeId=12
app.get('/api/batch', async (req, res) => {
  const { areaCodes, contentTypeId, lang = 'eng', numOfRows = 50 } = req.query;
  const serviceName = lang === 'kor' ? 'KorService2' : 'EngService2';
  const codes = areaCodes ? areaCodes.split(',').map(c => c.trim()) : [null];
  const allItems = [];
  const errors = [];
  for (const code of codes) {
    const url = new URL(`${BASE_URL}/${serviceName}/areaBasedList2`);
    url.searchParams.set('serviceKey', TOUR_API_KEY);
    url.searchParams.set('MobileOS', 'ETC');
    url.searchParams.set('MobileApp', 'KoreaExperiences');
    url.searchParams.set('pageNo', '1');
    url.searchParams.set('numOfRows', numOfRows);
    url.searchParams.set('_type', 'json');
    if (code) url.searchParams.set('areaCode', code);
    if (contentTypeId) url.searchParams.set('contentTypeId', contentTypeId);
    try {
      const response = await fetch(url.toString());
      const data = await response.json();
      const header = data?.response?.header;
      if (header && header.resultCode !== '0000' && header.resultCode !== '00') {
        errors.push({ areaCode: code, error: header.resultMsg }); continue;
      }
      const items = data?.response?.body?.items?.item || [];
      const list = Array.isArray(items) ? items : (items ? [items] : []);
      allItems.push(...list);
    } catch (err) {
      errors.push({ areaCode: code, error: err.message });
    }
    await new Promise(r => setTimeout(r, 150));
  }
  const seen = new Set();
  const deduped = allItems.filter(item => {
    if (seen.has(item.contentid)) return false;
    seen.add(item.contentid); return true;
  });
  res.json({ count: deduped.length, errors, items: deduped });
});

app.get('/', (req, res) => {
  res.send('KoreaExperiences TourAPI proxy is running. Try /api/search?keyword=Gyeongbokgung');
});

app.listen(PORT, () => {
  console.log(`TourAPI proxy running: http://localhost:${PORT}`);
});
